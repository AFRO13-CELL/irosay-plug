@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'POS / Sales', 'route' => 'pos.index'],
        ['label' => 'Inventory', 'route' => 'inventory.index'],
        ['label' => 'iPhones', 'route' => 'iphones.index'],
        ['label' => 'Apple Watches', 'route' => 'watches.index'],
        ['label' => 'Accessories', 'route' => 'accessories.index'],
        ['label' => 'Purchases', 'route' => 'purchases.index'],
        ['label' => 'Customers', 'route' => 'customers.index'],
        ['label' => 'Suppliers', 'route' => 'suppliers.index'],
        ['label' => 'Expenses', 'route' => 'expenses.index'],
        ['label' => 'Reports', 'route' => 'reports.index'],
        ['label' => 'Users & Roles', 'route' => 'users.index'],
        ['label' => 'Settings', 'route' => 'settings.index'],
    ];
@endphp

<div class="flex items-center gap-2 px-5 h-16 border-b border-white/10">
    <div class="w-8 h-8 rounded-lg bg-accent text-primarydark flex items-center justify-center text-sm font-bold">IP</div>
    <div class="leading-tight">
        <p class="text-sm font-bold tracking-wide"><span class="text-accent">i</span>ROZAY DE PLUG</p>
        <p class="text-[11px] text-white/50">Sales · Inventory · Reports</p>
    </div>
</div>

<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
    @foreach ($nav as $item)
        @php
            // Routes not built yet in this phase resolve to '#' instead of erroring.
            $href = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
            $active = \Illuminate\Support\Facades\Route::has($item['route']) && request()->routeIs($item['route'].'*');
        @endphp
        <a href="{{ $href }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                  {{ $active ? 'bg-accent text-primarydark' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
            <span class="w-2 h-2 rounded-full {{ $active ? 'bg-primarydark' : 'bg-white/30' }}"></span>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

<div class="px-3 pb-5 pt-2 border-t border-white/10">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/70 hover:bg-white/10 hover:text-white">
            Logout
        </button>
    </form>
</div>
