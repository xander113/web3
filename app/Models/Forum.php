<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Forum extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'post_count', 'reply_count', 'is_developer'];
    protected function casts(): array { return ['is_developer' => 'boolean']; }
    public function category() { return $this->belongsTo(ForumCategory::class, 'category_id'); }
    public function topics() { return $this->hasMany(ForumTopic::class); }
}
