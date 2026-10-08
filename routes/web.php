<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\PhoneDeviceController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReturnController;

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
| Phase 10 completes the spec's required pages (Returns, Users & Roles,
| Settings, Audit Log) and — most importantly — wires the 'permission'
| middleware onto every route a role might legitimately be denied, per the
| spec's explicit rule that permissions must be enforced on the backend,
| not just by hiding buttons in the UI. See RolePermissionSeeder (Phase 2)
| for exactly which of Admin/Manager/Sales Staff hold which permission.
|
| IMPORTANT ORDERING RULE: for any resource, a static path like /create
| MUST be registered before a dynamic /{id} path using the same HTTP verb —
| otherwise Laravel matches "create" as if it were the {id} parameter and
| 404s (or worse, misbehaves) instead of showing the create form. Every
| group below is ordered create/store -> index/show -> edit/update to keep
| this correct throughout.
*/

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Inventory (Phase 3) — viewing is open to any authenticated user
    // (Sales Staff needs "product availability"); only management actions
    // require inventory.manage. ---
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/apple-watches', [InventoryController::class, 'watches'])->name('watches.index');
    Route::get('/accessories', [InventoryController::class, 'accessories'])->name('accessories.index');

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::prefix('inventory/categories')->name('inventory.categories.')->group(function () {
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        });

        Route::prefix('inventory/products')->name('inventory.products.')->group(function () {
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('adjust-stock');
        });
    });
    // Viewing a single product stays open (same reasoning as the index above).
    // Registered after /inventory/products/create above, so "create" is never
    // mistaken for a product ID.
    Route::get('/inventory/products/{product}', [ProductController::class, 'show'])->name('inventory.products.show');

    Route::get('/inventory/stock-movements', [StockMovementController::class, 'index'])->name('inventory.stock-movements.index');

    // --- iPhone / IMEI Management (Phase 4) — viewing open, managing gated ---
    Route::prefix('iphones')->name('iphones.')->group(function () {
        Route::get('/', [PhoneDeviceController::class, 'index'])->name('index');

        Route::middleware('permission:inventory.manage')->group(function () {
            Route::get('/create', [PhoneDeviceController::class, 'create'])->name('create');
            Route::post('/', [PhoneDeviceController::class, 'store'])->name('store');
        });

        Route::get('/{device}', [PhoneDeviceController::class, 'show'])->name('show');

        Route::middleware('permission:inventory.manage')->group(function () {
            Route::get('/{device}/edit', [PhoneDeviceController::class, 'edit'])->name('edit');
            Route::put('/{device}', [PhoneDeviceController::class, 'update'])->name('update');
            Route::post('/{device}/reserve', [PhoneDeviceController::class, 'reserve'])->name('reserve');
            Route::post('/{device}/cancel-reservation', [PhoneDeviceController::class, 'cancelReservation'])->name('cancel-reservation');
        });
    });

    // --- Suppliers (Phase 5) ---
    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('permission:suppliers.view')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });
    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });

    // --- Purchases (Phase 5) ---
    Route::middleware('permission:purchases.manage')->prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
    });
    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });
    Route::middleware('permission:purchases.manage')->prefix('purchases')->name('purchases.')->group(function () {
        Route::post('/{purchase}/items', [PurchaseController::class, 'addItem'])->name('items.store');
        Route::delete('/{purchase}/items/{item}', [PurchaseController::class, 'removeItem'])->name('items.destroy');
        Route::post('/{purchase}/items/{item}/receive-device', [PurchaseController::class, 'receiveDevice'])->name('items.receive-device');
        Route::post('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('receive');
        Route::post('/{purchase}/record-payment', [PurchaseController::class, 'recordPayment'])->name('record-payment');
    });

    // --- POS (Phase 6) ---
    Route::middleware('permission:pos.access')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/products/{product}/devices', [PosController::class, 'selectDevice'])->name('select-device');
        Route::post('/cart/add-product', [PosController::class, 'addProduct'])->name('cart.add-product');
        Route::post('/cart/add-device', [PosController::class, 'addDevice'])->name('cart.add-device');
        Route::post('/cart/remove', [PosController::class, 'removeItem'])->name('cart.remove');
        Route::post('/customer', [PosController::class, 'setCustomer'])->name('customer');
        Route::post('/discount', [PosController::class, 'setDiscount'])->name('discount');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
    });

    // --- Sales (Phase 6) ---
    Route::middleware('permission:sales.view')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt'])->name('receipt');
    });

    // --- Returns / Refunds (Phase 10) ---
    Route::middleware('permission:returns.process')->group(function () {
        Route::get('/sales/{sale}/return', [ReturnController::class, 'create'])->name('returns.create');
        Route::post('/sales/{sale}/return', [ReturnController::class, 'store'])->name('returns.store');
    });
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');

    // --- Customers (Phase 7) ---
    Route::middleware('permission:customers.manage')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
    });
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });
    Route::middleware('permission:customers.manage')->prefix('customers')->name('customers.')->group(function () {
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    });

    // --- Expenses (Phase 8) ---
    Route::middleware('permission:expenses.manage')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
    });
    Route::middleware('permission:expenses.view')->get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::middleware('permission:expenses.manage')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // --- Reports (Phase 9) — plain reports need reports.view; profit/iPhone
    // reports reveal cost & margin data, so they need reports.financial.view. ---
    Route::middleware('permission:reports.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/export', [ReportController::class, 'salesExport'])->name('sales.export');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/inventory/export', [ReportController::class, 'inventoryExport'])->name('inventory.export');
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/purchases/export', [ReportController::class, 'purchasesExport'])->name('purchases.export');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/expenses/export', [ReportController::class, 'expensesExport'])->name('expenses.export');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/customers/export', [ReportController::class, 'customersExport'])->name('customers.export');
        Route::get('/suppliers', [ReportController::class, 'suppliers'])->name('suppliers');
        Route::get('/suppliers/export', [ReportController::class, 'suppliersExport'])->name('suppliers.export');
        Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('/best-selling', [ReportController::class, 'bestSelling'])->name('best-selling');
        Route::get('/best-selling/export', [ReportController::class, 'bestSellingExport'])->name('best-selling.export');
    });
    Route::middleware('permission:reports.financial.view')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('/iphones', [ReportController::class, 'iphones'])->name('iphones');
        Route::get('/iphones/export', [ReportController::class, 'iphonesExport'])->name('iphones.export');
    });

    // --- Users & Roles (Phase 10) ---
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // --- Settings (Phase 10) ---
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
