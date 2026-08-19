<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\Availability;
use Faker\Factory as Faker;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Sare service providers uthao
        $providers = ServiceProvider::all();

        foreach ($providers as $provider) {
            Availability::firstOrCreate(
                ['service_provider_id' => $provider->id], // duplicate avoid
                [
                    // Example: Monday-Friday available, Sat/Sun off
                    'monday'        => true,
                    'monday_start'  => '09:00',
                    'monday_end'    => '17:00',

                    'tuesday'       => true,
                    'tuesday_start' => '09:00',
                    'tuesday_end'   => '17:00',

                    'wednesday'       => true,
                    'wednesday_start' => '09:00',
                    'wednesday_end'   => '17:00',

                    'thursday'       => true,
                    'thursday_start' => '09:00',
                    'thursday_end'   => '17:00',

                    'friday'       => true,
                    'friday_start' => '09:00',
                    'friday_end'   => '17:00',

                    'saturday'       => $faker->boolean(30), // 30% chance available
                    'saturday_start' => $faker->boolean(30) ? '10:00' : null,
                    'saturday_end'   => $faker->boolean(30) ? '14:00' : null,

                    'sunday'       => false,
                    'sunday_start' => null,
                    'sunday_end'   => null,
                ]
            );
        }
    }
}
