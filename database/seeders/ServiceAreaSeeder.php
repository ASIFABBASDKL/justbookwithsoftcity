<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\ServiceArea;
use Faker\Factory as Faker;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Saare service providers uthao
        $providers = ServiceProvider::all();

        foreach ($providers as $provider) {
            // Har provider ke liye 1–2 service areas
            for ($i = 0; $i < rand(1, 2); $i++) {
                ServiceArea::create([
                    'service_provider_id' => $provider->id,
                    'city'                => $faker->city,
                    'building'            => $faker->company,
                    'apartment'           => $faker->randomElement([null, 'Apt ' . $faker->buildingNumber]),
                    'floor'               => $faker->randomElement([null, 'Floor ' . rand(1, 10)]),
                    'street'              => $faker->streetName,
                    'live_location'       => $faker->boolean(50), // 50% chance
                    'latitude'            => $faker->latitude,
                    'longitude'           => $faker->longitude,
                ]);
            }
        }
    }
}
