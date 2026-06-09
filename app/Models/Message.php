<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'receiver_id', 'subject', 'body', 'read', 'receiver_deleted', 'sender_deleted'];
    protected function casts(): array { return ['read' => 'boolean', 'receiver_deleted' => 'boolean', 'sender_deleted' => 'boolean']; }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
}
