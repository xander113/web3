<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    protected $fillable = ['name', 'is_developer', 'sort_order'];
    protected function casts(): array { return ['is_developer' => 'boolean']; }
    public function forums() { return $this->hasMany(Forum::class, 'category_id')->orderBy('id'); }
}
