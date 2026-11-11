<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /** Public invitation page. */
    public function show()
    {
        return view('invitation');
    }

    /** Receives the invitation form (JSON). */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['full_name' => Registration::normalizeName($request->input('full_name'))]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^\S+(\s\S+)+$/u'],
        ], [
            'full_name.regex' => 'Enter your first and last name.',
        ]);

        // Same name registering twice is treated as success, not a new row.
        $registration = Registration::firstOrCreate(
            ['name_key' => Registration::keyFor($data['full_name'])],
            [
                'full_name' => $data['full_name'],
                'source' => 'form',
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ],
        );

        return response()->json([
            'ok' => true,
            'full_name' => $registration->full_name,
            'already_registered' => ! $registration->wasRecentlyCreated,
        ], $registration->wasRecentlyCreated ? 201 : 200);
    }
}
