<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['username', 'email', 'password', 'rank', 'banned', 'ban_reason', 'ban_expires_at', 'coins', 'about', 'email_verified', 'email_verification_token', 'two_factor_enabled', 'two_factor_secret', 'hide_status', 'post_count', 'register_ip', 'last_ip', 'game_key', 'in_game', 'last_seen_at', 'last_post_at', 'last_friend_request_at', 'avatar_hash'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'banned' => 'boolean',
            'email_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'hide_status' => 'boolean',
            'in_game' => 'boolean',
            'last_seen_at' => 'datetime',
            'last_post_at' => 'datetime',
            'last_friend_request_at' => 'datetime',
            'ban_expires_at' => 'datetime',
        ];
    }

    // Rank helpers
    public function isAdmin(): bool { return $this->rank === 1; }
    public function isModerator(): bool { return $this->rank >= 1; }

    // Online status (active in last 5 minutes)
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    // Relationships
    public function friends() { return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id'); }
    public function friendRequests() { return $this->hasMany(FriendRequest::class, 'receiver_id'); }
    public function sentFriendRequests() { return $this->hasMany(FriendRequest::class, 'sender_id'); }
    public function badges() { return $this->hasMany(Badge::class); }
    public function sentMessages() { return $this->hasMany(Message::class, 'sender_id'); }
    public function receivedMessages() { return $this->hasMany(Message::class, 'receiver_id'); }
    public function forumTopics() { return $this->hasMany(ForumTopic::class); }
    public function forumReplies() { return $this->hasMany(ForumReply::class); }
    public function ownedItems() { return $this->hasMany(OwnedItem::class); }
    public function equippedItems() { return $this->hasMany(EquippedItem::class); }
    public function characterColors() { return $this->hasMany(CharacterColor::class); }
    public function gameServers() { return $this->hasMany(GameServer::class, 'creator_id'); }
    public function groups() { return $this->belongsToMany(Group::class, 'group_members', 'user_id', 'group_id'); }
    public function createdGroups() { return $this->hasMany(Group::class, 'creator_id'); }
    public function reports() { return $this->hasMany(Report::class, 'reporter_id'); }
}
