<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'business_name', 'business_type', 'business_phone', 'business_whatsapp',
        'business_email', 'business_address', 'currency', 'receipt_footer',
        'warranty_message', 'return_policy', 'payment_methods',
    ];

    public function index()
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Setting::get($key, '')]);

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_whatsapp' => ['nullable', 'string', 'max:50'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'warranty_message' => ['nullable', 'string', 'max:500'],
            'return_policy' => ['nullable', 'string', 'max:1000'],
            'payment_methods' => ['required', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::record($request->user(), 'settings.updated', null, 'Business settings updated');

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }
}
