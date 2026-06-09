<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = ['user_id', 'badge_id'];
    public function user() { return $this->belongsTo(User::class); }

    public static function getBadgeName(int $id): string
    {
        return match($id) {
            1 => 'Administrator',
            2 => 'Senior Administrator',
            3 => 'Moderator',
            4 => 'Forumer',
            5 => 'Member',
            6 => 'Before 100',
            default => 'Unknown',
        };
    }
}
