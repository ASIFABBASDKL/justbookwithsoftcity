<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServiceProvider;
use Faker\Factory as Faker;

class ServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Sirf un users ko lo jinka role provider hai
        $providers = User::where('is_seller', true)->get();

        foreach ($providers as $user) {
            ServiceProvider::firstOrCreate(
                ['user_id' => $user->id], // duplicate check
                [
                    'business_name'    => $faker->company,
                    'category'         => $faker->randomElement(['Plumbing', 'Electrical', 'Cleaning', 'IT Services']),
                    'service_type'     => $faker->randomElement(['Home Service', 'Office Service', 'Remote']),
                    'id_verification'  => strtoupper($faker->bothify('ID####')),
                    'country'          => $faker->country,
                    'driving_license'  => strtoupper($faker->bothify('DL####')),
                    'passport'         => strtoupper($faker->bothify('P#######')),
                    'gmc_dbs_number'   => strtoupper($faker->bothify('GMC####')),
                    'portfolio'        => $faker->url,
                    'experience_years' => $faker->numberBetween(1, 20),
                    'image'            => $faker->imageUrl(200, 200, 'business', true, 'logo'),
                    'description'      => $faker->sentence(12),
                ]
            );
        }
    }
}
