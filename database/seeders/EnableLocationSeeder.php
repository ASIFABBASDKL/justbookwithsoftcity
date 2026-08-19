<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\EnableLocation;
use Faker\Factory as Faker;

class EnableLocationSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Saare service providers uthao
        $providers = ServiceProvider::all();

        foreach ($providers as $provider) {
            EnableLocation::firstOrCreate(
                ['service_provider_id' => $provider->id], // avoid duplicate
                [
                    'is_location_enabled' => $faker->boolean(70), // 70% chance enabled
                    'latitude'            => $faker->latitude(24.0, 37.0), // Pakistan region approx
                    'longitude'           => $faker->longitude(60.0, 77.0), // Pakistan region approx
                ]
            );
        }
    }
}
