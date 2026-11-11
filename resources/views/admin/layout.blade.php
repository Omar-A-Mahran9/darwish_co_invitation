<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>@yield('title', 'Dashboard') · Digilians Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--brand:#0089E6;--brand-d:#006fbb;--ink:#0f1b2d;--muted:#5d6b7e;--line:#e3e8ef;--bg:#f5f7fa;--card:#fff;--ok:#12805c;--ok-bg:#e5f6ef;--warn:#9a6700;--warn-bg:#fff4d6;--danger:#c62828;--danger-bg:#fdecec}
*{box-sizing:border-box}
body{margin:0;font:14px/1.5 Inter,system-ui,sans-serif;color:var(--ink);background:var(--bg)}
a{color:var(--brand)}
.top{background:var(--ink);color:#fff}
.top .wrap{display:flex;align-items:center;gap:16px;height:58px}
.brand{font-weight:700;letter-spacing:.2px;color:#fff;text-decoration:none;display:flex;align-items:center;gap:10px}
.brand i{width:10px;height:10px;border-radius:50%;background:#FDD400;display:inline-block}
.top nav{margin-left:auto;display:flex;gap:14px;align-items:center}
.top nav a{color:#c9d4e3;text-decoration:none}.top nav a:hover{color:#fff}
.wrap{max-width:1180px;margin:0 auto;padding:0 20px}
main.wrap{padding-top:28px;padding-bottom:60px}
h1{font-size:22px;margin:0}
.head{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:20px}
.head .actions{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:6px;border:1px solid transparent;background:var(--brand);color:#fff;padding:8px 14px;border-radius:8px;font:600 13px Inter,sans-serif;cursor:pointer;text-decoration:none;white-space:nowrap}
.btn:hover{background:var(--brand-d)}
.btn-ghost{background:#fff;color:var(--ink);border-color:var(--line)}.btn-ghost:hover{background:#eef2f7}
.btn-danger{background:var(--danger)}.btn-danger:hover{background:#a31f1f}
.btn-sm{padding:5px 10px;font-size:12px}
.btn:disabled{opacity:.45;cursor:not-allowed}
.link-btn{background:none;border:0;padding:0;color:var(--danger);font:inherit;cursor:pointer}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.stat{padding:16px 18px}.stat small{color:var(--muted);font-weight:500}.stat b{display:block;font-size:28px;line-height:1.2;margin-top:4px}
.stat .bar{height:6px;background:#eef2f7;border-radius:3px;margin-top:10px;overflow:hidden}.stat .bar span{display:block;height:100%;background:var(--ok)}
.chart{padding:16px 18px;margin-bottom:18px}
.chart h2{font-size:13px;color:var(--muted);font-weight:600;margin:0 0 12px}
.bars{display:flex;align-items:flex-end;gap:6px;height:110px}
.bars div{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:4px}
.bars span{width:100%;max-width:34px;background:var(--brand);border-radius:4px 4px 0 0;min-height:2px}
.bars em{font-style:normal;font-size:10px;color:var(--muted);white-space:nowrap}
.bars b{font-size:11px;font-weight:600}
.filters{display:flex;flex-wrap:wrap;gap:8px;padding:14px;border-bottom:1px solid var(--line)}
input[type=text],input[type=search],input[type=email],input[type=password],select,textarea{font:inherit;color:inherit;border:1px solid var(--line);border-radius:8px;padding:8px 10px;background:#fff;min-width:0}
input:focus,select:focus,textarea:focus{outline:2px solid rgba(0,137,230,.25);border-color:var(--brand)}
.filters input[type=search]{flex:1 1 240px}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:11px 14px;border-bottom:1px solid var(--line);vertical-align:middle}
th{font-size:12px;color:var(--muted);font-weight:600;background:#fafbfd;white-space:nowrap}
th a{color:inherit;text-decoration:none}th a:hover{color:var(--ink)}
tr:hover td{background:#fafcff}
td.name{font-weight:600}
td .sub{display:block;font-weight:400;color:var(--muted);font-size:12px;max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
td.num{color:var(--muted);width:1%}
td.row-actions{white-space:nowrap;text-align:right}
td.row-actions form{display:inline}
.pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:12px;font-weight:600}
.pill-ok{background:var(--ok-bg);color:var(--ok)}.pill-wait{background:var(--warn-bg);color:var(--warn)}.pill-src{background:#eef2f7;color:var(--muted);font-weight:500}
.empty{padding:50px 20px;text-align:center;color:var(--muted)}
.foot{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:12px 14px}
.foot .muted{color:var(--muted)}
.pager{margin-left:auto;display:flex;gap:4px}
.pager a,.pager span{padding:5px 10px;border:1px solid var(--line);border-radius:6px;text-decoration:none;color:var(--ink);background:#fff;font-size:13px}
.pager .on{background:var(--brand);color:#fff;border-color:var(--brand)}.pager .off{color:#b3bdca}
.flash{padding:10px 14px;border-radius:8px;background:var(--ok-bg);color:var(--ok);margin-bottom:16px;font-weight:500}
.errors{padding:10px 14px;border-radius:8px;background:var(--danger-bg);color:var(--danger);margin-bottom:16px}
.field{margin-bottom:16px}.field label{display:block;font-weight:600;margin-bottom:6px}.field input[type=text],.field input[type=email],.field input[type=password],.field textarea{width:100%}
.check{display:flex;align-items:center;gap:8px;font-weight:500}
.form-card{max-width:560px;padding:24px}
@media (max-width:800px){.stats{grid-template-columns:repeat(2,1fr)}.hide-sm{display:none}}
</style>
</head>
<body>
@auth
<header class="top"><div class="wrap">
  <a class="brand" href="{{ route('admin.registrations.index') }}"><i></i>Digilians · Registrations</a>
  <nav>
    <a href="{{ route('invitation') }}" target="_blank" rel="noopener" class="hide-sm">View invitation ↗</a>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf
      <button class="btn btn-ghost btn-sm">Log out</button>
    </form>
  </nav>
</div></header>
@endauth
<main class="wrap">
  @if (session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())<div class="errors" role="alert">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
  @yield('content')
</main>
</body>
</html>
