<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['creator_id', 'name', 'description', 'member_count'];
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function members() { return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')->withPivot('is_owner')->withTimestamps(); }
}
