<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\Project;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Liste des équipements avec comptage réel du stock disponible ('en_stock').
     */
    public function index(Request $request)
    {
        $query = Equipment::withCount(['items as available_items_count' => function ($q) {
            $q->where('status', 'en_stock');
        }]);

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
     * Création d'un nouvel équipement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'entry_date' => 'nullable|date',
            'brand' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'required_with:serial_numbers|string|distinct',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('equipments', 'public');
        }

        DB::transaction(function () use ($validated, $request) {
            $equipment = Equipment::create($validated);

            if (!empty($request->serial_numbers)) {
                foreach ($request->serial_numbers as $serialNumber) {
                    EquipmentItem::create([
                        'equipment_id' => $equipment->id,
                        'serial_number' => trim($serialNumber),
                        'status' => 'en_stock',
                    ]);
                }
            }
        });

        return redirect()->route('equipments.index')->with('success', 'Équipement ajouté avec succès.');
    }

    /**
     * Formulaire de sortie de stock : charge uniquement les éléments 'en_stock'.
     */
    public function createStockout(Equipment $equipment)
    {
        $equipment->load('availableItems');

        $users = User::all()->sortBy(function ($user) {
            return $user->name ?? $user->firstname ?? $user->id;
        })->values();

        $projects = Project::orderBy('name')->get();

        return view('equipments.stockout', compact('equipment', 'users', 'projects'));
    }

    /**
     * Enregistre la sortie de stock et marque les articles comme 'sorti'.
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
            'document' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:5000',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $filePath = null;
            if ($request->hasFile('document')) {
                $filePath = $request->file('document')->store('stockouts', 'public');
            }

            $movement = StockMovement::create([
                'user_id' => $validated['user_id'],
                'project_id' => $validated['project_id'] ?? null,
                'other_destination' => $validated['other_destination'] ?? null,
                'movement_date' => $validated['movement_date'],
                'reason' => $validated['reason'] ?? null,
                'file_path' => $filePath,
            ]);

            foreach ($validated['item_ids'] as $itemId) {
                StockMovementItem::create([
                    'stock_movement_id' => $movement->id,
                    'equipment_item_id' => $itemId,
                ]);

                EquipmentItem::where('id', $itemId)->update(['status' => 'sorti']);
            }
        });

        return redirect()->route('equipments.index')->with('success', 'Sortie de stock enregistrée avec succès.');
    }

    /**
     * Historique des mouvements.
     */
    public function stockoutHistory()
    {
        $outs = StockMovement::with(['user', 'project', 'equipmentItems.equipment'])
            ->latest('movement_date')
            ->paginate(15);

        return view('equipments.stockout_history', compact('outs'));
    }

    /**
     * Annule la sortie : réinitialise le statut à 'en_stock' et supprime le mouvement.
     */
    public function returnStockout(StockMovement $stockout)
    {
        DB::transaction(function () use ($stockout) {
            foreach ($stockout->equipmentItems as $item) {
                $item->update(['status' => 'en_stock']);
            }

            if ($stockout->file_path) {
                Storage::disk('public')->delete($stockout->file_path);
            }

            $stockout->delete();
        });

        return redirect()->back()->with('success', 'Sortie annulée avec succès. Le matériel a été remis en stock.');
    }
}