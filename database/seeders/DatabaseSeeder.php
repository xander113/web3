<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\Forum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'username' => 'Admin',
            'email' => 'admin@graphictoria.com',
            'password' => Hash::make('changeme123'),
            'rank' => 1,
            'coins' => 9999,
            'email_verified' => true,
        ]);
        $admin->badges()->create(['badge_id' => 1]);

        // Initialize character colors for admin
        $parts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
        foreach ($parts as $part) {
            $admin->characterColors()->create(['type' => $part, 'color' => '#FFCC99']);
        }

        // Forum categories and forums
        $general = ForumCategory::create(['name' => 'General', 'sort_order' => 1]);
        Forum::create(['category_id' => $general->id, 'name' => 'General Discussion', 'description' => 'Talk about anything and everything.']);
        Forum::create(['category_id' => $general->id, 'name' => 'Announcements', 'description' => 'Official announcements from the Graphictoria team.']);
        Forum::create(['category_id' => $general->id, 'name' => 'Suggestions', 'description' => 'Share your ideas to improve Graphictoria.']);

        $creations = ForumCategory::create(['name' => 'Creations', 'sort_order' => 2]);
        Forum::create(['category_id' => $creations->id, 'name' => 'Scripting', 'description' => 'Get help with scripting your games.']);
        Forum::create(['category_id' => $creations->id, 'name' => 'Building', 'description' => 'Discuss building techniques and showcase your creations.']);
        Forum::create(['category_id' => $creations->id, 'name' => 'Art & Design', 'description' => 'Share your artwork, clothing, and other designs.']);

        $off = ForumCategory::create(['name' => 'Off-Topic', 'sort_order' => 3]);
        Forum::create(['category_id' => $off->id, 'name' => 'Spam', 'description' => 'Fun and games. Keep it clean!']);

        $dev = ForumCategory::create(['name' => 'Developer', 'sort_order' => 4, 'is_developer' => true]);
        Forum::create(['category_id' => $dev->id, 'name' => 'Developer Discussion', 'description' => 'For Graphictoria developers only.', 'is_developer' => true]);
    }
}
