<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\CatalogItem;
use App\Models\CharacterColor;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\Group;
use App\Models\OwnedItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $bodyColors = ['#FFCC99', '#F1C27D', '#E0AC69', '#C68642', '#8D5524', '#FFDAB9', '#FFE4C4', '#FFF8DC'];

    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────────────────
        $admin = User::create([
            'username'       => 'Admin',
            'email'          => 'admin@graphictoria.com',
            'password'       => Hash::make('changeme123'),
            'rank'           => 1,
            'coins'          => 9999,
            'email_verified' => true,
            'about'          => 'Welcome to Graphictoria! I am the site administrator.',
            'last_seen_at'   => now()->subMinutes(2),
        ]);
        $admin->badges()->createMany([['badge_id' => 1], ['badge_id' => 2]]);
        $this->initColors($admin);

        // ── Forum structure ────────────────────────────────────────────────
        $general   = ForumCategory::create(['name' => 'General',   'sort_order' => 1]);
        $creations = ForumCategory::create(['name' => 'Creations', 'sort_order' => 2]);
        $offTopic  = ForumCategory::create(['name' => 'Off-Topic', 'sort_order' => 3]);
        $dev       = ForumCategory::create(['name' => 'Developer', 'sort_order' => 4, 'is_developer' => true]);

        $forums = [
            Forum::create(['category_id' => $general->id,   'name' => 'General Discussion', 'description' => 'Talk about anything and everything.']),
            Forum::create(['category_id' => $general->id,   'name' => 'Announcements',      'description' => 'Official announcements from the Graphictoria team.']),
            Forum::create(['category_id' => $general->id,   'name' => 'Suggestions',        'description' => 'Share your ideas to improve Graphictoria.']),
            Forum::create(['category_id' => $creations->id, 'name' => 'Scripting',          'description' => 'Get help with scripting your games.']),
            Forum::create(['category_id' => $creations->id, 'name' => 'Building',           'description' => 'Discuss building techniques and share your work.']),
            Forum::create(['category_id' => $creations->id, 'name' => 'Art & Design',       'description' => 'Share artwork, clothing, and other designs.']),
            Forum::create(['category_id' => $offTopic->id,  'name' => 'Spam',               'description' => 'Fun and games. Keep it clean!']),
            Forum::create(['category_id' => $dev->id,       'name' => 'Dev Discussion',     'description' => 'For Graphictoria developers only.', 'is_developer' => true]),
        ];

        // ── Catalog ────────────────────────────────────────────────────────
        $catalogItems = $this->seedCatalog($admin);

        // ── Users (300 total) ──────────────────────────────────────────────
        $allUsers = [$admin];

        foreach ($this->generateUserData(300) as $i => $userData) {
            // Distribution: 3 mods, 3 senior-admins, 25 banned, rest regular
            if ($i < 3) {
                $rank = 2; $banned = false; $badgeIds = [3, 5];
            } elseif ($i < 6) {
                $rank = 1; $banned = false; $badgeIds = [1, 5];
            } elseif ($i >= 20 && $i < 45) {
                $rank = 0; $banned = true;  $badgeIds = [5];
            } else {
                $rank = 0; $banned = false; $badgeIds = [5];
            }

            $user = User::create(array_merge($userData, [
                'rank'           => $rank,
                'banned'         => $banned,
                'ban_reason'     => $banned ? fake()->randomElement(['Spamming', 'Harassment', 'Exploiting', 'Inappropriate content', 'Cheating']) : null,
                'email_verified' => true,
                'coins'          => fake()->numberBetween(0, 1500),
                'last_seen_at'   => fake()->dateTimeBetween('-60 days', 'now'),
            ]));

            foreach ($badgeIds as $bid) {
                $user->badges()->create(['badge_id' => $bid]);
            }
            $this->initColors($user);

            // Give user 1-3 owned items
            foreach ($catalogItems->random(min(3, $catalogItems->count())) as $item) {
                OwnedItem::firstOrCreate(['user_id' => $user->id, 'catalog_item_id' => $item->id]);
            }

            $allUsers[] = $user;
        }

        // ── Forum posts & replies ──────────────────────────────────────────
        $nonBanned = collect($allUsers)->filter(fn($u) => !$u->banned)->values();
        $topics    = [];

        foreach ($this->topicData() as $td) {
            $author = $nonBanned->random();
            $forum  = $forums[array_rand($forums)];

            $topic = ForumTopic::create([
                'forum_id'         => $forum->id,
                'user_id'          => $author->id,
                'title'            => $td['title'],
                'body'             => $td['body'],
                'last_activity_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
            $forum->increment('post_count');
            $author->increment('post_count');
            $topics[] = $topic;

            // 0-8 replies
            $replyCount = fake()->numberBetween(0, 8);
            $replyPool  = $nonBanned->filter(fn($u) => $u->id !== $author->id)->random(min($replyCount, $nonBanned->count() - 1));
            foreach ($replyPool as $replier) {
                ForumReply::create([
                    'topic_id' => $topic->id,
                    'forum_id' => $forum->id,
                    'user_id'  => $replier->id,
                    'body'     => fake()->randomElement($this->replyBodies()),
                ]);
                $topic->increment('reply_count');
                $forum->increment('reply_count');
                $replier->increment('post_count');
            }
        }

        // Ensure every non-banned user has at least 1 post
        foreach ($nonBanned as $user) {
            if ($user->post_count > 0) continue;
            $forum = $forums[array_rand($forums)];
            $topic = ForumTopic::create([
                'forum_id'         => $forum->id,
                'user_id'          => $user->id,
                'title'            => fake()->sentence(6),
                'body'             => fake()->paragraphs(2, true),
                'last_activity_at' => fake()->dateTimeBetween('-14 days', 'now'),
            ]);
            $forum->increment('post_count');
            $user->increment('post_count');
            $topics[] = $topic;
        }

        // ── Groups ─────────────────────────────────────────────────────────
        $groupNames = ['Builders Guild', 'Scripters Union', 'The Art Collective', 'Graphictoria Veterans', 'Beta Testers'];
        foreach ($groupNames as $gname) {
            $creator = $nonBanned->filter(fn($u) => $u->rank === 0 && $u->coins >= 50)->random();
            $creator->decrement('coins', 50);
            $group = Group::create([
                'creator_id'   => $creator->id,
                'name'         => $gname,
                'description'  => fake()->sentence(12),
                'member_count' => 1,
            ]);
            $group->members()->attach($creator->id, ['is_owner' => true]);
            $members = $nonBanned->filter(fn($u) => $u->id !== $creator->id)->random(fake()->numberBetween(3, 15));
            foreach ($members as $m) {
                if (!$group->members()->where('users.id', $m->id)->exists()) {
                    $group->members()->attach($m->id, ['is_owner' => false]);
                    $group->increment('member_count');
                }
            }
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function initColors(User $user): void
    {
        $color = fake()->randomElement($this->bodyColors);
        foreach (['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'] as $part) {
            CharacterColor::create(['user_id' => $user->id, 'type' => $part, 'color' => $color]);
        }
    }

    private function seedCatalog(User $admin): \Illuminate\Support\Collection
    {
        $items = collect();
        $catalog = [
            'hat'    => ['Classic Top Hat', 'Baseball Cap', 'Viking Helmet', 'Wizard Hat', 'Cowboy Hat', 'Propeller Hat', 'Crown of Kings', 'Party Hat', 'Pirate Hat', 'Construction Helmet', 'Astronaut Helmet', 'Bucket Hat', 'Beret', 'Fedora', 'Beanie'],
            'head'   => ['Blocky Head', 'Round Head', 'Square Head', 'Slim Head', 'Classic Head'],
            'face'   => ['Happy Face', 'Angry Face', 'Surprised Face', 'Cool Shades', 'Ninja Mask', 'Big Grin', 'Sleepy Eyes', 'Clueless Face', 'Determined Look', 'Smirk'],
            'shirt'  => ['Red Plaid Shirt', 'Blue Polo', 'Striped Tee', 'Leather Jacket', 'Business Suit Top', 'Sports Jersey', 'Hoodie - Gray', 'Camo Jacket', 'Flannel Shirt', 'Track Jacket'],
            'pants'  => ['Blue Jeans', 'Cargo Pants', 'Slacks', 'Track Pants', 'Shorts - Khaki', 'Swim Trunks', 'Ripped Jeans', 'Formal Trousers'],
            'tshirt' => ['Plain White Tee', 'Black Graphic Tee', 'Logo Shirt', 'Vintage Tee', 'Tie-Dye Tee'],
            'gear'   => ['Wooden Sword', 'Laser Gun', 'Magic Staff', 'Jetpack', 'Shield', 'Boom Box', 'Grappling Hook', 'Flamethrower'],
            'decal'  => ['Community Logo', 'Star Decal', 'Lightning Bolt', 'Dragon Emblem', 'Crest of Honor'],
        ];

        foreach ($catalog as $type => $names) {
            foreach ($names as $name) {
                $items->push(CatalogItem::create([
                    'creator_id'  => $admin->id,
                    'name'        => $name,
                    'description' => fake()->sentence(10),
                    'type'        => $type,
                    'price'       => fake()->randomElement([0, 0, 5, 10, 25, 50, 100, 200]),
                    'approved'    => true,
                    'deleted'     => false,
                ]));
            }
        }

        return $items;
    }

    private function generateUserData(int $count): array
    {
        $used  = [];
        $data  = [];
        $pfx   = ['xX', 'the', 'real', 'dark', 'cool', 'pro', 'epic', 'mega', 'super', 'hyper', 'ultra', 'neo', 'old', 'gr8', 'mr', 'dr', 'lil', 'big', 'the_real'];
        $mid   = ['gamer', 'builder', 'coder', 'ninja', 'dragon', 'wolf', 'hawk', 'fire', 'ice', 'storm', 'rock', 'stone', 'blade', 'arrow', 'pixel', 'block', 'craft', 'forge', 'sky', 'star', 'moon', 'sun', 'thunder', 'flame', 'frost', 'shadow', 'ghost', 'knight', 'ranger', 'mage', 'rogue', 'paladin', 'scout'];
        $sfx   = ['', '123', '456', '007', 'x', 'pro', '2024', '99', 'z', 'hd', '_og', '_v2', 'gg', 'xd', 'lol'];

        while (count($data) < $count) {
            $username = fake()->randomElement($pfx) . ucfirst(fake()->randomElement($mid)) . fake()->randomElement($sfx);
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', substr($username, 0, 20));
            if (strlen($username) < 3 || isset($used[$username])) continue;
            $used[$username] = true;

            $data[] = [
                'username' => $username,
                'email'    => strtolower($username) . '_' . Str::random(4) . '@example.com',
                'password' => Hash::make('password'),
                'about'    => fake()->optional(0.6)->sentence(10),
            ];
        }
        return $data;
    }

    private function topicData(): array
    {
        $p = fn($n) => implode("\n\n", fake()->paragraphs($n));
        return [
            ['title' => 'Welcome to Graphictoria!',                  'body' => $p(2)],
            ['title' => 'Best building tips you have learned?',       'body' => $p(2)],
            ['title' => 'Show off your character!',                   'body' => $p(1)],
            ['title' => 'What games are you playing right now?',      'body' => $p(2)],
            ['title' => 'Scripting help needed - please read',        'body' => $p(3)],
            ['title' => 'New update - feedback wanted',               'body' => $p(2)],
            ['title' => 'How to earn more coins?',                    'body' => $p(2)],
            ['title' => 'Looking for group members',                  'body' => $p(1)],
            ['title' => 'Rate my latest build',                       'body' => $p(2)],
            ['title' => 'Forum rules reminder',                       'body' => $p(3)],
            ['title' => 'What was your first game here?',             'body' => $p(2)],
            ['title' => 'Catalog suggestions thread',                 'body' => $p(2)],
            ['title' => 'Bug report megathread',                      'body' => $p(2)],
            ['title' => 'Classic vs modern - which do you prefer?',   'body' => $p(2)],
            ['title' => 'Introduce yourself!',                        'body' => $p(1)],
            ['title' => 'Best hat in the catalog?',                   'body' => $p(1)],
            ['title' => 'How do I apply to be a moderator?',          'body' => $p(2)],
            ['title' => 'Share your group and get members!',          'body' => $p(1)],
            ['title' => 'Weekly challenge - post your creations',     'body' => $p(2)],
            ['title' => 'Thoughts on the latest catalog items?',      'body' => $p(2)],
            ['title' => 'Scripting tutorial - basic loops',           'body' => $p(3)],
            ['title' => 'I need help with my game server',            'body' => $p(2)],
            ['title' => 'Anyone else remember the early days?',       'body' => $p(2)],
            ['title' => 'Design philosophy of the original platform', 'body' => $p(3)],
            ['title' => 'AMA - Ask me anything about Graphictoria',   'body' => $p(1)],
            ['title' => 'Favorite building material poll',            'body' => $p(1)],
            ['title' => 'How to make your first gear item',           'body' => $p(3)],
            ['title' => 'Character customization ideas',              'body' => $p(2)],
            ['title' => 'Looking for a scripting partner',            'body' => $p(2)],
            ['title' => 'Server hosting guide for beginners',         'body' => $p(3)],
            ['title' => 'What features do you want to see added?',    'body' => $p(2)],
            ['title' => 'My first build - be gentle!',                'body' => $p(1)],
            ['title' => 'Coin farming strategies',                    'body' => $p(2)],
            ['title' => 'Group wars event announcement',              'body' => $p(2)],
            ['title' => 'Has anyone made a working RPG?',             'body' => $p(2)],
            ['title' => 'Hats or no hats - the eternal debate',       'body' => $p(2)],
            ['title' => 'Finding friends on Graphictoria',            'body' => $p(1)],
            ['title' => 'My suggestion for better forums',            'body' => $p(2)],
            ['title' => 'Monthly top builders showcase',              'body' => $p(2)],
            ['title' => 'Technical question about game scripts',      'body' => $p(2)],
            ['title' => 'Happy to be here - new member post',         'body' => $p(1)],
            ['title' => 'Graphictoria art contest entries',           'body' => $p(2)],
            ['title' => 'Tips for new players',                       'body' => $p(3)],
            ['title' => 'Best games of the week picks',               'body' => $p(2)],
            ['title' => 'What rank are you and why?',                 'body' => $p(1)],
            ['title' => 'The building update we all need',            'body' => $p(2)],
            ['title' => 'Who is your favorite Graphictoria creator?', 'body' => $p(1)],
            ['title' => 'Forum games - word association',             'body' => $p(1)],
            ['title' => 'Looking for beta testers for my game',       'body' => $p(2)],
            ['title' => 'Pixel art tips and tricks',                  'body' => $p(2)],
        ];
    }

    private function replyBodies(): array
    {
        return [
            'Great post! I totally agree with everything you said here.',
            'Thanks for sharing this - very helpful for newcomers like me.',
            'I had the same question a while back. The answer is simpler than you think.',
            'This is awesome! Keep up the great work.',
            'Not sure I agree entirely, but I appreciate the perspective.',
            'I tried this and it worked perfectly. Highly recommend.',
            'Could you elaborate a bit more? I am confused about the second point.',
            'Classic Graphictoria moment right here. Love it.',
            'Been playing since the beginning and this is still one of my favourite topics.',
            'Wow, I had no idea. Thanks for teaching me something new today!',
            'This deserves way more attention than it is getting.',
            'Agreed. The community here is amazing.',
            'I think there is more to it than what you described, but good start.',
            'Bookmarking this for later. Super useful thread.',
            'Has anyone tried the method in the third paragraph? Does it still work?',
            'The best post I have seen on this forum in weeks.',
            'Keep it up! We need more posts like this.',
            'I was going to make a similar post but you covered everything perfectly.',
            'Solid advice. My builds improved a lot after reading this.',
            'Bumping this because more people need to see it.',
        ];
    }
}
