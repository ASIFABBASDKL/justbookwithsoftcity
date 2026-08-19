<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\ServiceAndPricing;
use Faker\Factory as Faker;

class ServicesAndPricingSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // ✅ Categories + Subcategories from your Flutter code
        $categories = [
            "Home Services" => ["House Cleaning", "Plumbing", "Electrical Repair", "Carpentry"],
            "Personal Care Services" => ["Haircut & Styling", "Spa & Massage", "Makeup & Beauty", "Personal Training"],
            "Transportation Services" => ["Taxi Service", "Bike Ride", "Goods Transport", "Airport Shuttle"],
            "Event & Party Services" => ["Event Planning", "DJ & Music", "Catering", "Photography"],
            "Security Services" => ["Security Guards", "CCTV Installation", "Alarm Systems"],
            "Business & Professional Services" => ["Business Consulting", "Legal Services", "Financial Advisor"],
            "Tech & Digital Services" => ["IT Support", "Software Development", "App Development", "Web Design"],
            "Construction/Maintenance Services" => ["Renovation", "Painting", "Roofing", "General Maintenance"],
            "Pet Services" => ["Pet Grooming", "Veterinary", "Pet Boarding"],
            "Health Services" => ["Doctor Consultation", "Health Checkups", "Physiotherapy"],
            "Education & Tutoring Services" => ["Math Tutors", "Language Classes", "Science Tutoring", "Music Teacher"],
            "Fitness Services" => ["Gym Training", "Yoga Classes", "Running Coach"],
        ];

        // ✅ Get all providers
        $providers = ServiceProvider::all();

        foreach ($providers as $provider) {
            foreach ($categories as $category => $subcategories) {
                foreach ($subcategories as $subcategory) {
                    ServiceAndPricing::create([
                        'service_provider_id' => $provider->id,
                        'category'            => $category,
                        'subcategory'         => $subcategory,
                        'service_name'        => $faker->sentence(3), // e.g. "Modern Plumbing Repair"
                        'description'         => $faker->sentence(12),
                        'img'                 => $faker->imageUrl(400, 300, 'business', true, 'service'),
                        'service_price'       => $faker->numberBetween(500, 5000),
                        'service_duration'    => $faker->randomElement(['30 mins', '1 hour', '2 hours', 'Half Day', 'Full Day']),
                    ]);
                }
            }
        }
    }
}
