<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use SoftDeletes;
    protected $fillable = ['topic_id', 'forum_id', 'user_id', 'body'];
    public function topic() { return $this->belongsTo(ForumTopic::class); }
    public function user() { return $this->belongsTo(User::class); }
}
