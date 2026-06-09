<?php
namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\OwnedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $items = CatalogItem::query()
            ->where('approved', true)->where('deleted', false)
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->where('name', 'ilike', '%' . $request->search . '%'))
            ->with('creator:id,username')
            ->orderByDesc('created_at')
            ->paginate(24);
        return Inertia::render('Catalog/Index', ['items' => $items, 'type' => $request->type, 'search' => $request->search]);
    }

    public function show(int $id)
    {
        $item = CatalogItem::with('creator:id,username')->findOrFail($id);
        $owned = Auth::check() && OwnedItem::where('user_id', Auth::id())->where('catalog_item_id', $id)->exists();
        return Inertia::render('Catalog/Show', ['item' => $item, 'owned' => $owned]);
    }

    public function buy(int $id)
    {
        $item = CatalogItem::where('id', $id)->where('approved', true)->where('deleted', false)->firstOrFail();
        $user = Auth::user();
        if (OwnedItem::where('user_id', $user->id)->where('catalog_item_id', $id)->exists()) {
            return back()->withErrors(['error' => 'Already owned.']);
        }
        if ($user->coins < $item->price) {
            return back()->withErrors(['error' => 'Not enough coins.']);
        }
        $user->decrement('coins', $item->price);
        OwnedItem::create(['user_id' => $user->id, 'catalog_item_id' => $id]);
        if ($item->creator_id) {
            $item->creator->increment('coins', intval($item->price * 0.7));
        }
        return back()->with('success', 'Item purchased!');
    }

    public function create() { return Inertia::render('Catalog/Upload'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:hat,head,face,shirt,pants,tshirt,gear,decal'],
            'price' => ['required', 'integer', 'min:0', 'max:99999'],
        ]);
        CatalogItem::create([
            'creator_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
        ]);
        return redirect()->route('catalog.index')->with('success', 'Item submitted for approval.');
    }
}
