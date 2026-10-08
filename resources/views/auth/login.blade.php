<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name', 'IROZAY DE PLUG') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',system-ui,sans-serif;background:#F5F5F7;}
        .brand-gradient { background: linear-gradient(160deg, #0B3B5C 0%, #072F49 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl brand-gradient text-white flex items-center justify-center font-bold text-lg mx-auto mb-3">IP</div>
            <h1 class="text-xl font-bold text-[#1D1D1F]"><span class="text-[#17C3C2]">i</span>ROZAY DE PLUG</h1>
            <p class="text-sm text-[#6E6E73]">iPhones &amp; Accessories · Sales · Inventory · Reports</p>
        </div>

        <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-[#EF4444] px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-[#1D1D1F] mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-[#E5E7EB] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#17C3C2]/40">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1D1D1F] mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-[#E5E7EB] px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#17C3C2]/40">
                </div>
                <label class="flex items-center gap-2 text-sm text-[#6E6E73]">
                    <input type="checkbox" name="remember" class="rounded border-[#E5E7EB]">
                    Remember me
                </label>
                <button type="submit"
                        class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                    Sign in
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-[#6E6E73] mt-6">&copy; {{ date('Y') }} IROZAY DE PLUG. All rights reserved.</p>
    </div>
</body>
</html>
