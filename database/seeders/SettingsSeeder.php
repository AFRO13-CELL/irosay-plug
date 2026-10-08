<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'business_name'     => 'IROZAY DE PLUG',
            'business_type'     => 'Phones and Accessories',
            'business_phone'    => '0598590541',
            'business_whatsapp' => '0598590541',
            'business_email'    => '',
            'business_address'  => '',
            'currency'          => 'GH₵',
            'receipt_footer'    => 'Thank you for shopping with IROZAY DE PLUG!',
            'warranty_message'  => '',
            'return_policy'     => '',
            'payment_methods'   => 'Cash,Mobile Money,Card,Other',
            'brand_primary_color' => '#0B3B5C',
            'brand_accent_color'  => '#17C3C2',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
