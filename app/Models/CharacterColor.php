<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CharacterColor extends Model
{
    protected $fillable = ['user_id', 'type', 'color'];
    public function user() { return $this->belongsTo(User::class); }
}
