<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            'Salary',
            'Freelance',
            'Bonus',
            'Gift Received',
            'Investment Income',
            'Refund',

            'Groceries',
            'Dining Out',
            'Transportation',
            'Fuel',
            'Shopping',
            'Personal Care',

            'Rent / Mortgage',
            'Utilities',
            'Home Supplies',
            'Maintenance / Repairs',
            'Internet & Mobile',

            'Kids Education',
            'Kids Activities',
            'Babysitting',
            'Family Health',

            'Medical',
            'Pharmacy',
            'Insurance',

            'Entertainment',
            'Travel',
            'Sports / Gym',
            'Hobbies',
            'Subscriptions',

            'Savings',
            'Loan Payment',
            'Credit Card',
            'Taxes',
            'Charity',
        ];

        DB::table('categories')->truncate();

        DB::table('categories')->insert(
            collect($categories)->map(function ($name) use ($now) {
                return [
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray()
        );
    }
}
