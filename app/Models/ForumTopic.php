<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ForumTopic extends Model
{
    use HasFactory;
    protected $fillable = ['forum_id', 'user_id', 'title', 'body', 'is_locked', 'is_pinned', 'reply_count', 'last_activity_at'];
    protected function casts(): array { return ['is_locked' => 'boolean', 'is_pinned' => 'boolean', 'last_activity_at' => 'datetime']; }
    public function forum() { return $this->belongsTo(Forum::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function replies() { return $this->hasMany(ForumReply::class, 'topic_id'); }
}
