<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Main Branch - Downtown',
                'address' => '123 Main Street, Downtown City',
                'phone' => '+1-555-0123',
                'lat' => 40.7128,
                'lng' => -74.0060,
                'status' => 1,
            ],
            [
                'name' => 'North Branch - Uptown',
                'address' => '456 North Avenue, Uptown District',
                'phone' => '+1-555-0124',
                'lat' => 40.7589,
                'lng' => -73.9851,
                'status' => 1,
            ],
            [
                'name' => 'South Branch - Riverside',
                'address' => '789 South Boulevard, Riverside Area',
                'phone' => '+1-555-0125',
                'lat' => 40.6782,
                'lng' => -73.9442,
                'status' => 1,
            ],
            [
                'name' => 'East Branch - Harbor',
                'address' => '321 East Road, Harbor District',
                'phone' => '+1-555-0126',
                'lat' => 40.6892,
                'lng' => -74.0445,
                'status' => 1,
            ],
            [
                'name' => 'West Branch - Hillside',
                'address' => '654 West Lane, Hillside Area',
                'phone' => '+1-555-0127',
                'lat' => 40.7282,
                'lng' => -74.0776,
                'status' => 1,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}