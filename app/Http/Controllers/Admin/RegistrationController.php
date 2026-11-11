<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    private const SORTS = ['full_name', 'created_at', 'checked_in_at'];

    public function index(Request $request)
    {
        [$sort, $dir] = $this->sorting($request);

        $registrations = Registration::query()
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->orderBy($sort, $dir)
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $total = Registration::count();
        $checkedIn = Registration::whereNotNull('checked_in_at')->count();

        $stats = [
            'total' => $total,
            'today' => Registration::where('created_at', '>=', now()->startOfDay())->count(),
            'checked_in' => $checkedIn,
            'rate' => $total ? round($checkedIn / $total * 100) : 0,
        ];

        // Registrations per day, last 14 days
        $daily = Registration::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($r) => $r->created_at->toDateString())
            ->map->count();
        $chart = collect(range(13, 0))->map(function ($i) use ($daily) {
            $d = now()->subDays($i);

            return ['label' => $d->format('M j'), 'count' => $daily[$d->toDateString()] ?? 0];
        });

        return view('admin.registrations.index', compact('registrations', 'stats', 'chart', 'sort', 'dir'));
    }

    public function create()
    {
        return view('admin.registrations.form', ['registration' => new Registration]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $registration = new Registration($data + ['source' => 'admin']);
        $registration->checked_in_at = $request->boolean('checked_in') ? now() : null;
        $registration->save();

        return redirect()->route('admin.registrations.index')->with('status', "Added {$registration->full_name}.");
    }

    public function edit(Registration $registration)
    {
        return view('admin.registrations.form', compact('registration'));
    }

    public function update(Request $request, Registration $registration)
    {
        $registration->fill($this->validated($request, $registration));
        $wantsCheckedIn = $request->boolean('checked_in');
        if ($wantsCheckedIn && ! $registration->checked_in_at) {
            $registration->checked_in_at = now();
        } elseif (! $wantsCheckedIn) {
            $registration->checked_in_at = null;
        }
        $registration->save();

        return redirect()->route('admin.registrations.index')->with('status', "Updated {$registration->full_name}.");
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return back()->with('status', "Deleted {$registration->full_name}.");
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        $count = Registration::whereIn('id', $ids)->delete();

        return back()->with('status', "Deleted {$count} registration(s).");
    }

    public function toggleCheckIn(Registration $registration)
    {
        $registration->checked_in_at = $registration->checked_in_at ? null : now();
        $registration->save();

        return back()->with('status', $registration->checked_in_at
            ? "Checked in {$registration->full_name}."
            : "Undid check-in for {$registration->full_name}.");
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = Registration::query()
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->orderBy('created_at')
            ->cursor();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Arabic names correctly
            fputcsv($out, ['#', 'Full name', 'Registered at', 'Checked in at', 'Source', 'Notes']);
            foreach ($rows as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r->full_name,
                    $r->created_at?->timezone(config('app.display_timezone'))->format('Y-m-d H:i'),
                    $r->checked_in_at?->timezone(config('app.display_timezone'))->format('Y-m-d H:i'),
                    $r->source,
                    $r->notes,
                ]);
            }
            fclose($out);
        }, 'registrations-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request, ?Registration $current = null): array
    {
        $request->merge(['full_name' => Registration::normalizeName($request->input('full_name'))]);
        $request->merge(['name_key' => Registration::keyFor($request->input('full_name'))]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120'],
            'name_key' => [Rule::unique('registrations', 'name_key')->ignore($current?->id)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], ['name_key.unique' => 'This name is already registered.']);

        unset($data['name_key']);

        return $data;
    }

    private function sorting(Request $request): array
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        return [$sort, $dir];
    }
}
