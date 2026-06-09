<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GameJoin extends Model
{
    protected $fillable = ['user_id', 'game_server_id'];
    public function user() { return $this->belongsTo(User::class); }
    public function server() { return $this->belongsTo(GameServer::class, 'game_server_id'); }
}
