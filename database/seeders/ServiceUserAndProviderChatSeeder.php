<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceUser;
use App\Models\ServiceProvider;

class ServiceUserAndProviderChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: ServiceUser ID = 1, ServiceProvider ID = 1
        $serviceUser1 = ServiceUser::find(1);
        $serviceProvider1 = ServiceProvider::find(1);

        // Example: ServiceUser ID = 2, ServiceProvider ID = 2
        $serviceUser2 = ServiceUser::find(2);
        $serviceProvider2 = ServiceProvider::find(2);

        if ($serviceUser1 && $serviceProvider1) {
            DB::table('service_user_and_provider_chats')->insert([
                [
                    'message' => 'Hello, I need cleaning service tomorrow.',
                    'image_path' => null,
                    'voice_path' => null,
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser1->id,
                    'service_provider_id' => $serviceProvider1->id,
                    'sender_type' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'Sure, I am available at 10 AM.',
                    'image_path' => null,
                    'voice_path' => null,
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser1->id,
                    'service_provider_id' => $serviceProvider1->id,
                    'sender_type' => 'provider',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'Perfect! Please confirm the charges.',
                    'image_path' => 'chat_uploads/charges.png', // 👈 Example image
                    'voice_path' => null,
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser1->id,
                    'service_provider_id' => $serviceProvider1->id,
                    'sender_type' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'It will be 2000 PKR for 2 hours.',
                    'image_path' => null,
                    'voice_path' => 'chat_uploads/voice_reply.mp3', // 👈 Example voice
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser1->id,
                    'service_provider_id' => $serviceProvider1->id,
                    'sender_type' => 'provider',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if ($serviceUser2 && $serviceProvider2) {
            DB::table('service_user_and_provider_chats')->insert([
                [
                    'message' => 'Hi, can you fix my AC this week?',
                    'image_path' => null,
                    'voice_path' => null,
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser2->id,
                    'service_provider_id' => $serviceProvider2->id,
                    'sender_type' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'Yes, I can visit on Friday afternoon.',
                    'image_path' => null,
                    'voice_path' => 'chat_uploads/friday_confirm.wav',
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser2->id,
                    'service_provider_id' => $serviceProvider2->id,
                    'sender_type' => 'provider',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'Great, please bring the required tools.',
                    'image_path' => null,
                    'voice_path' => null,
                    'attachment_path' => 'chat_uploads/tools_list.pdf', // 👈 Example attachment
                    'service_user_id' => $serviceUser2->id,
                    'service_provider_id' => $serviceProvider2->id,
                    'sender_type' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'message' => 'Don’t worry, I will bring everything needed.',
                    'image_path' => 'chat_uploads/equipment.jpg',
                    'voice_path' => null,
                    'attachment_path' => null,
                    'service_user_id' => $serviceUser2->id,
                    'service_provider_id' => $serviceProvider2->id,
                    'sender_type' => 'provider',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
