<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ServiceUser;
use Faker\Factory as Faker;

class ServiceUserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Sirf un users ko lo jinka role 'user' hai
        $users = User::where('is_buyer', true)->get();

        foreach ($users as $user) {
            ServiceUser::firstOrCreate(
                ['user_id' => $user->id], // duplication check
                [
                    'img'                     => $faker->imageUrl(200, 200, 'people', true, 'profile'),
                    'gender'                  => $faker->randomElement(['male', 'female']),
                    'preferred_language'      => $faker->randomElement(['English', 'Urdu', 'Spanish', 'French']),
                    'location'                => $faker->city . ', ' . $faker->country,
                    'enable_ai_voice_assistant' => $faker->boolean(30), // 30% chance ON
                    'notifications'           => true,
                    'recommendations'         => $faker->boolean(80), // 80% chance ON
                ]
            );
        }
    }
}
