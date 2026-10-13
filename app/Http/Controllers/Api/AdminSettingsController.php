<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    private const DEFAULTS = [
        'general' => [
            'platform_name' => 'Market Mirror',
            'support_email' => 'support@marketmirror.com',
            'timezone' => 'Africa/Lagos',
            'currency' => 'NGN',
            'description' => 'The premier multi-vendor marketplace connecting local buyers and sellers.',
            'maintenance_mode' => false,
        ],
        'payment' => [
            'commission_rate' => 12.5,
            'fixed_transaction_fee' => 0,
            'flutterwave_configured' => false,
        ],
    ];

    public function index()
    {
        $settings = collect(self::DEFAULTS)->mapWithKeys(function (array $defaults, string $key) {
            $setting = PlatformSetting::firstOrCreate(['key' => $key], ['value' => $defaults]);
            $stored = $setting->value;
            $value = array_replace($defaults, is_array($stored) ? $stored : []);
            if ($key === 'payment') {
                $value['flutterwave_configured'] = filled(config('services.flutterwave.secret_key'));
            }

            return [$key => $value];
        });

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'general' => ['sometimes', 'array'],
            'general.platform_name' => ['sometimes', 'string', 'max:255'],
            'general.support_email' => ['sometimes', 'email', 'max:255'],
            'general.timezone' => ['sometimes', 'string', 'max:100'],
            'general.currency' => ['sometimes', 'string', 'size:3'],
            'general.description' => ['sometimes', 'string', 'max:1000'],
            'general.maintenance_mode' => ['sometimes', 'boolean'],
            'payment' => ['sometimes', 'array'],
            'payment.commission_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'payment.fixed_transaction_fee' => ['sometimes', 'numeric', 'min:0'],
        ]);

        foreach (['general', 'payment'] as $key) {
            if (array_key_exists($key, $validated)) {
                PlatformSetting::updateOrCreate(['key' => $key], ['value' => $validated[$key]]);
            }
        }

        return $this->index();
    }
}
