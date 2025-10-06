<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Package;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@vexim.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create packages
        Package::create([
            'name' => 'Free',
            'price' => 0,
            'duration_months' => 12,
            'features' => json_encode([
                'Basic company profile',
                'Up to 5 products',
                'Standard verification badge',
            ]),
        ]);

        Package::create([
            'name' => 'Silver',
            'price' => 299,
            'duration_months' => 12,
            'features' => json_encode([
                'Enhanced company profile',
                'Up to 20 products',
                'Silver verification badge',
                'Priority in search results',
            ]),
        ]);

        Package::create([
            'name' => 'Gold',
            'price' => 599,
            'duration_months' => 12,
            'features' => json_encode([
                'Premium company profile',
                'Up to 50 products',
                'Gold verification badge',
                'Top priority in search',
                'Featured supplier badge',
            ]),
        ]);

        Package::create([
            'name' => 'Premium',
            'price' => 999,
            'duration_months' => 12,
            'features' => json_encode([
                'Exclusive company profile',
                'Unlimited products',
                'Premium verification badge',
                'Homepage featured placement',
                'Dedicated account manager',
                'Custom branding options',
            ]),
        ]);
    }
}
