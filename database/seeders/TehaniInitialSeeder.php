<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TehaniInitialSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TehaniFinalCatalogSeeder::class);
        foreach ([['Dhaka City', 'ঢাকা সিটি', 60], ['Dhaka Sub-City', 'ঢাকা সাব-সিটি', 100], ['Outside Dhaka', 'ঢাকার বাইরে', 120]] as [$name, $nameBn, $charge]) {
            DeliveryZone::updateOrCreate(['name' => $name], ['name_bn' => $nameBn, 'charge' => $charge, 'is_active' => true]);
        }
        TaxRate::updateOrCreate(['name' => 'Default inclusive tax'], ['percentage' => 0, 'is_inclusive' => true, 'is_active' => true]);
        foreach (['brand_line' => 'এক কাপ চা নয়, এক কাপ ভালোবাসা', 'whatsapp_number' => '01711893169', 'facebook_url' => 'https://www.facebook.com/tehanipremiumtea/', 'bkash_active' => '0'] as $key => $value) {
            StoreSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
