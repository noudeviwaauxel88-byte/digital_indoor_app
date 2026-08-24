<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Stockout;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource with search support.
     */
    public function index(Request $request)
    {
        $query = Equipment::withCount(['items as available_items_count' => function ($q) {
            $q->where('status', 'available');
        }]);

        // Filtrage dynamique : Intitulé (title) ou Type (type) ou Marque (brand)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $equipments = $query->latest()->get();

        return view('equipments.index', compact('equipments'));
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'entry_date' => 'nullable|date',
            'brand' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'required_with:serial_numbers|string|distinct',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('equipments', 'public');
            $validated['image_path'] = $path;
        }

        DB::transaction(function () use ($validated, $request) {
            $equipment = Equipment::create($validated);

            if (!empty($request->serial_numbers)) {
                foreach ($request->serial_numbers as $serialNumber) {
                    EquipmentItem::create([
                        'equipment_id' => $equipment->id,
                        'serial_number' => $serialNumber,
                        'status' => 'available',
                    ]);
                }
            }
        });

        return redirect()->route('equipments.index')->with('success', 'Équipement ajouté avec succès.');
    }

    /**
     * Show the form for editing the specified equipment.
     */
    public function edit(Equipment $equipment)
    {
        return view('equipments.edit', compact('equipment'));
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'entry_date' => 'nullable|date',
            'brand' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($equipment->image_path) {
                Storage::disk('public')->delete($equipment->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('equipments', 'public');
        }

        $equipment->update($validated);

        return redirect()->route('equipments.index')->with('success', 'Équipement mis à jour avec succès.');
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(Equipment $equipment)
    {
        if ($equipment->image_path) {
            Storage::disk('public')->delete($equipment->image_path);
        }

        $equipment->delete();

        return redirect()->route('equipments.index')->with('success', 'Équipement supprimé avec succès.');
    }

    /**
     * Show the form for creating a stockout entry.
     */
    public function createStockout(Equipment $equipment)
    {
        $equipment->load(['availableItems']);
        $users = User::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('equipments.stockout', compact('equipment', 'users', 'projects'));
    }

    /**
     * Store a stockout transaction.
     */
    public function storeStockout(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'movement_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'other_destination' => 'nullable|string|max:255',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:equipment_items,id',
            'reason' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:5000',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('stockouts', 'public');
            }

            foreach ($validated['item_ids'] as $itemId) {
                Stockout::create([
                    'equipment_item_id' => $itemId,
                    'user_id' => $validated['user_id'],
                    'project_id' => $validated['project_id'] ?? null,
                    'other_destination' => $validated['other_destination'] ?? null,
                    'movement_date' => $validated['movement_date'],
                    'reason' => $validated['reason'] ?? null,
                    'document_path' => $documentPath,
                ]);

                EquipmentItem::where('id', $itemId)->update(['status' => 'out']);
            }
        });

        return redirect()->route('equipments.index')->with('success', 'Sortie de stock enregistrée avec succès.');
    }

    /**
     * Display stockout history.
     */
    public function stockoutHistory()
    {
        $outs = Stockout::with(['item.equipment', 'user', 'project'])
            ->latest()
            ->paginate(15);

        return view('equipments.stockout_history', compact('outs'));
    }

    /**
     * Mark an item as returned to stock.
     */
    public function returnStockout(Stockout $stockout)
    {
        DB::transaction(function () use ($stockout) {
            if ($stockout->item) {
                $stockout->item->update(['status' => 'available']);
            }
        });

        return redirect()->back()->with('success', 'L\'équipement a été restitué et remis en stock.');
    }
}