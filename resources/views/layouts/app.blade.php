<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'IROZAY DE PLUG') }}</title>

    <!-- Tailwind (CDN play build — swap for a compiled build in production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Brand palette pulled from the IROZAY DE PLUG logo
                        primary: '#0B3B5C',       // navy — sidebar, headers, primary buttons
                        primarydark: '#072F49',    // deeper navy — gradients / hover states
                        accent: '#17C3C2',         // teal — active nav item, links, highlights
                        bg: '#F5F5F7',
                        card: '#FFFFFF',
                        maintext: '#1D1D1F',
                        secondary: '#6E6E73',
                        success: '#22C55E',
                        danger: '#EF4444',
                        border: '#E5E7EB',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background-color: #F5F5F7; color: #1D1D1F; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 999px; }
        .brand-gradient { background: linear-gradient(160deg, #0B3B5C 0%, #072F49 100%); }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-bg text-maintext antialiased">

    <div class="flex min-h-screen">

        {{-- Sidebar (desktop) --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 brand-gradient text-white">
            @include('components.sidebar')
        </aside>

        {{-- Mobile sidebar drawer --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-black/50" @click="sidebarOpen = false"></div>
            <aside class="relative z-50 flex flex-col w-64 h-full brand-gradient text-white">
                @include('components.sidebar')
            </aside>
        </div>

        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

            {{-- Topbar --}}
            <header class="sticky top-0 z-30 bg-card border-b border-border">
                <div class="flex items-center justify-between px-4 sm:px-6 py-3">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden p-2 rounded-lg hover:bg-bg" @click="sidebarOpen = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline text-sm text-secondary">{{ now()->format('D, d M Y') }}</span>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-full hover:bg-bg">
                                <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline text-sm font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-44 bg-card border border-border rounded-xl shadow-lg py-1">
                                <a href="#" class="block px-4 py-2 text-sm hover:bg-bg">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-danger hover:bg-bg">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-4 sm:px-6 pt-4">
                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-success/10 border border-success/30 text-success px-4 py-3 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <main class="flex-1 px-4 sm:px-6 pb-10">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
