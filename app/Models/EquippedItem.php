<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EquippedItem extends Model
{
    protected $fillable = ['user_id', 'catalog_item_id', 'type', 'asset_string'];
    public function user() { return $this->belongsTo(User::class); }
    public function item() { return $this->belongsTo(CatalogItem::class, 'catalog_item_id'); }
}
