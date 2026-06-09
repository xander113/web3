<?php
namespace App\Http\Controllers;

use App\Models\OwnedItem;
use App\Models\EquippedItem;
use App\Models\CharacterColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'hat');
        $ownedItems = OwnedItem::where('user_id', $user->id)
            ->whereHas('item', fn($q) => $q->where('type', $type)->where('deleted', false))
            ->with('item')
            ->paginate(12);
        $equipped = EquippedItem::where('user_id', $user->id)->with('item')->get();
        $colors = CharacterColor::where('user_id', $user->id)->get()->keyBy('type');
        return Inertia::render('Character/Index', [
            'ownedItems' => $ownedItems,
            'equipped' => $equipped,
            'colors' => $colors,
            'currentType' => $type,
        ]);
    }

    public function equip(string $catalogItemId)
    {
        $owned = OwnedItem::where('user_id', Auth::id())->where('catalog_item_id', $catalogItemId)->firstOrFail();
        $item = $owned->item;
        if ($item->type === 'hat') {
            $hatCount = EquippedItem::where('user_id', Auth::id())->where('type', 'hat')->count();
            if ($hatCount >= 5) return back()->withErrors(['error' => 'Maximum 5 hats allowed.']);
        } else {
            EquippedItem::where('user_id', Auth::id())->where('type', $item->type)->delete();
        }
        EquippedItem::updateOrCreate(
            ['user_id' => Auth::id(), 'catalog_item_id' => $catalogItemId],
            ['type' => $item->type]
        );
        return back()->with('success', 'Item equipped.');
    }

    public function unequip(string $catalogItemId)
    {
        EquippedItem::where('user_id', Auth::id())->where('catalog_item_id', $catalogItemId)->delete();
        return back()->with('success', 'Item removed.');
    }

    public function updateColors(Request $request)
    {
        $request->validate([
            'colors' => ['required', 'array'],
            'colors.*.type' => ['required', 'in:head,torso,left_arm,right_arm,left_leg,right_leg'],
            'colors.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        foreach ($request->colors as $colorData) {
            CharacterColor::updateOrCreate(
                ['user_id' => Auth::id(), 'type' => $colorData['type']],
                ['color' => $colorData['color']]
            );
        }
        return back()->with('success', 'Colors updated.');
    }
}
