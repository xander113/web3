<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    protected $fillable = ['creator_id', 'name', 'description', 'type', 'price', 'asset_id', 'data_file', 'approved', 'declined', 'deleted'];
    protected function casts(): array { return ['approved' => 'boolean', 'declined' => 'boolean', 'deleted' => 'boolean']; }
    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function owners() { return $this->hasMany(OwnedItem::class); }
}
