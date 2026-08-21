<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    private function authorizeManagement(): void
    {
        if (!Auth::user()->hasAnyRole(['SuperAdmin', 'Manager'])) {
            abort(403, 'Action non autorisée. Seuls les SuperAdmins et Managers peuvent gérer le matériel.');
        }
    }

    public function index(Request $request)
    {
        $query = Equipment::withCount(['items', 'availableItems']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'available') {
                $query->whereHas('availableItems');
            } elseif ($request->input('status') === 'unavailable') {
                $query->whereDoesntHave('availableItems');
            }
        }

        $equipments = $query->latest()->paginate(10);
        $types = Equipment::distinct()->pluck('type')->filter();

        return view('equipments.index', compact('equipments', 'types'));
    }

    public function create()
    {
        $this->authorizeManagement();
        return view('equipments.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'brand'       => 'nullable|string|max:255',
            'type'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'quantity'    => 'required|integer|min:1',
        ]);

        $equipment = Equipment::create([
            'title'       => $validated['title'],
            'brand'       => $validated['brand'],
            'type'        => $validated['type'] ?? 'Matériel',
            'description' => $validated['description'] ?? null,
            'price'       => $validated['price'] ?? 0,
        ]);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            EquipmentItem::create([
                'equipment_id'  => $equipment->id,
                'status'        => 'en_stock',
                'serial_number' => 'EQ-' . $equipment->id . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            ]);
        }

        return redirect()->route('equipments.index')
            ->with('success', 'Équipement et exemplaires créés avec succès.');
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['items']);
        return view('equipments.show', compact('equipment'));
    }

    public function edit(Equipment $equipment)
    {
        $this->authorizeManagement();
        return view('equipments.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'brand'       => 'nullable|string|max:255',
            'type'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $equipment->update($validated);

        return redirect()->route('equipments.index')
            ->with('success', 'Équipement mis à jour avec succès.');
    }

    public function destroy(Equipment $equipment)
    {
        $this->authorizeManagement();

        $equipment->delete();

        return redirect()->route('equipments.index')
            ->with('success', 'Équipement supprimé avec succès.');
    }

    public function createStockOut(Equipment $equipment)
    {
        $this->authorizeManagement();

        $equipment->load('availableItems');
        $users = User::select('id', 'name')->orderBy('name')->get();
        $projects = Project::select('id', 'name')->get();

        return view('equipments.stockout', compact('equipment', 'users', 'projects'));
    }

    public function storeStockOut(Request $request, Equipment $equipment)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'movement_date'     => 'required|date',
            'user_id'           => 'required|exists:users,id',
            'project_id'        => 'nullable|exists:projects,id',
            'other_destination' => 'nullable|string|max:255',
            'item_ids'          => 'required|array|min:1',
            'item_ids.*'        => 'exists:equipment_items,id',
            'reason'            => 'nullable|string',
            'document'          => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $filePath = null;
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('stock_documents', 'public');
        }

        $movement = StockMovement::create([
            'user_id'           => $validated['user_id'],
            'reason'            => $validated['reason'] ?? 'Sortie de matériel',
            'movement_date'     => $validated['movement_date'],
            'project_id'        => $validated['project_id'] ?? null,
            'other_destination' => $validated['other_destination'] ?? null,
            'file_path'         => $filePath,
        ]);

        EquipmentItem::whereIn('id', $validated['item_ids'])->update(['status' => 'sorti']);

        foreach ($validated['item_ids'] as $itemId) {
            $movement->equipmentItems()->attach($itemId);
        }

        return redirect()->route('equipments.stockout.history')
            ->with('success', 'Sortie de stock enregistrée avec succès.');
    }

    public function stockOutHistory()
    {
        $this->authorizeManagement();

        $outs = StockMovement::with(['user', 'equipmentItems.equipment'])
            ->latest('movement_date')
            ->paginate(15);

        return view('equipments.stockout_history', compact('outs'));
    }
}