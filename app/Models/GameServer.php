<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GameServer extends Model
{
    protected $fillable = ['creator_id', 'name', 'description', 'ip', 'port', 'server_key', 'private_key', 'is_public'];
    protected function casts(): array { return ['is_public' => 'boolean']; }
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function players() { return $this->hasMany(GameJoin::class); }
}
