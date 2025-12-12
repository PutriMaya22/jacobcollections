<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->count() > 0) {
            $user = $users->first();
            
            // Sample notifications
            $notifications = [
                [
                    'type' => 'user_activity',
                    'title' => 'User Activity: Login',
                    'message' => 'User logged in successfully',
                    'icon' => 'fas fa-sign-in-alt',
                    'color' => 'green'
                ],
                [
                    'type' => 'data_update',
                    'title' => 'Data Update: Barang',
                    'message' => 'Created data barang: Laptop Dell',
                    'icon' => 'fas fa-box-open',
                    'color' => 'green'
                ],
                [
                    'type' => 'system',
                    'title' => 'System: Maintenance',
                    'message' => 'System maintenance completed successfully',
                    'icon' => 'fas fa-cog',
                    'color' => 'blue'
                ],
                [
                    'type' => 'role_change',
                    'title' => 'Role Changed',
                    'message' => 'Your role has been updated to admin',
                    'icon' => 'fas fa-user-tag',
                    'color' => 'purple'
                ]
            ];
            
            foreach ($notifications as $notificationData) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $notificationData['type'],
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'icon' => $notificationData['icon'],
                    'color' => $notificationData['color'],
                    'is_read' => false
                ]);
            }
        }
    }
}
