<?php

namespace App\Http\Controllers;

use App\Models\Project; 
use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    /**
     * Affiche la liste des équipements.
     */
    public function index(Request $request)
    {
        $query = Equipment::query();
        
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('brand', 'like', "%{$searchTerm}%")
                  ->orWhere('type', 'like', "%{$searchTerm}%");
            });
        }

        // Charger les équipements avec le *compte* des articles disponibles
        $equipments = $query->withCount('availableItems')->latest()->get();
        
        return view('equipments.index', ['equipments' => $equipments]);
    }

    /**
     * Enregistre un nouvel équipement.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1', 
            'entry_date' => 'nullable|date', 
            'brand' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'serial_numbers' => 'required|array|size:' . $request->input('quantity', 0),
            'serial_numbers.*' => 'required|string|distinct',
        ]);

        $equipmentData = collect($validatedData)->except(['quantity', 'serial_numbers'])->toArray();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('equipments', 'public');
            $equipmentData['image_path'] = $path;
        }

        DB::beginTransaction();
        try {
            $equipment = Equipment::create($equipmentData);

            foreach ($validatedData['serial_numbers'] as $serial) {
                $equipment->items()->create([
                    'serial_number' => $serial,
                    'status' => 'en_stock',
                ]);
            }

            DB::commit();
            return redirect()->route('equipments.index')->with('success', 'Équipement ajouté avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire pour modifier un équipement.
     */
    public function edit(Equipment $equipment)
    {
        // Charger les items pour la modification
        $equipment->load('items');
        return view('equipments.edit', ['equipment' => $equipment]);
    }

    /**
     * Met à jour l'équipement ET ses numéros de série.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'entry_date' => 'nullable|date', 
            'brand' => 'nullable|string|max:255',
            'features' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Validation des items
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer',
            'items.*.serial_number' => 'required|string|distinct',
            'deleted_item_ids' => 'nullable|string',
        ]);

        $equipmentData = collect($validatedData)->except(['items', 'deleted_item_ids'])->toArray();

        if ($request->hasFile('image')) {
            if ($equipment->image_path) { Storage::disk('public')->delete($equipment->image_path); }
            $path = $request->file('image')->store('equipments', 'public');
            $equipmentData['image_path'] = $path;
        }

        DB::beginTransaction();
        try {
            $equipment->update($equipmentData);

            // Suppressions
            if (!empty($request->deleted_item_ids)) {
                $idsToDelete = explode(',', $request->deleted_item_ids);
                EquipmentItem::whereIn('id', $idsToDelete)->where('equipment_id', $equipment->id)->delete();
            }

            // Ajouts et modifications
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    if (isset($itemData['id']) && $itemData['id']) {
                        EquipmentItem::where('id', $itemData['id'])
                                     ->where('equipment_id', $equipment->id)
                                     ->update(['serial_number' => $itemData['serial_number']]);
                    } else {
                        $equipment->items()->create([
                            'serial_number' => $itemData['serial_number'],
                            'status' => 'en_stock',
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('equipments.index')->with('success', 'Équipement modifié avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Supprime l'équipement.
     */
    public function destroy(Equipment $equipment)
    {
        if ($equipment->image_path) { Storage::disk('public')->delete($equipment->image_path); }
        $equipment->delete();
        return redirect()->route('equipments.index')->with('success', 'Équipement supprimé avec succès !');
    }
    
    /**
     * Affiche le formulaire de sortie de stock.
     * C'EST LA MÉTHODE QUI MANQUAIT
     */
    public function createStockOut(Equipment $equipment)
    {
        $equipment->load('availableItems');
        
        // On charge les utilisateurs avec firstname/lastname
        $users = User::orderBy('firstname')->orderBy('lastname')->select('id', 'firstname', 'lastname', 'email')->get();
        
        // On charge les projets pour la recherche
        $projects = Project::orderBy('name')->select('id', 'name')->get(); 
        
        return view('equipments.stockout', [
            'equipment' => $equipment,
            'users' => $users,
            'projects' => $projects,
        ]);
    }

    /**
     * Enregistre une sortie de stock.
     */
    public function storeStockOut(Request $request, Equipment $equipment)
    {
        $validatedData = $request->validate([
            'reason' => 'nullable|string',
            'movement_date' => 'required|date',
            'user_id' => ['required', 'exists:users,id'],
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => [
                'required', 'integer',
                Rule::exists('equipment_items', 'id')->where(function ($query) use ($equipment) {
                    $query->where('equipment_id', $equipment->id)->where('status', 'en_stock');
                }),
            ],
            'project_id' => 'nullable|exists:projects,id',
            'other_destination' => 'nullable|string|max:255',
        ]);

        $filePath = null;
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('stockout_documents', 'public');
        }

        DB::beginTransaction();
        try {
            $stockMovement = StockMovement::create([
                'user_id' => $validatedData['user_id'],
                'reason' => $validatedData['reason'],
                'file_path' => $filePath,
                'movement_date' => $validatedData['movement_date'],
                'project_id' => $validatedData['project_id'] ?? null,
                'other_destination' => $validatedData['other_destination'] ?? null,
            ]);

            $itemIdsToUpdate = $validatedData['item_ids'];
            EquipmentItem::whereIn('id', $itemIdsToUpdate)->update(['status' => 'sorti']);
            
            $pivotData = [];
            foreach ($itemIdsToUpdate as $itemId) {
                $pivotData[] = [
                    'stock_movement_id' => $stockMovement->id,
                    'equipment_item_id' => $itemId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            StockMovementItem::insert($pivotData);

            DB::commit();
            return redirect()->route('equipments.index')->with('success', 'Sortie de stock enregistrée !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Affiche l'historique des sorties de stock.
     */
    public function stockOutHistory()
    {
        $movements = StockMovement::with([
            'user', 
            'project', // Charger aussi le projet
            'equipmentItems', 
            'equipmentItems.equipment'
        ])
        ->latest('movement_date')
        ->get();

        return view('equipments.stockout_history', ['movements' => $movements]);
    }

    /**
     * Supprime une sortie de stock et remet les articles en stock.
     */
    public function destroyStockOut(StockMovement $stockMovement)
    {
        DB::beginTransaction();
        try {
            // 1. Récupérer les IDs des articles concernés par cette sortie
            $itemIds = $stockMovement->equipmentItems->pluck('id');

            // 2. Remettre le statut de ces articles à "en_stock"
            EquipmentItem::whereIn('id', $itemIds)->update(['status' => 'en_stock']);
            
            // 3. Supprimer le fichier joint s'il existe
            if ($stockMovement->file_path) { 
                Storage::disk('public')->delete($stockMovement->file_path); 
            }

            // 4. Détacher les relations (proprement)
            $stockMovement->equipmentItems()->detach(); 
            
            // 5. Supprimer la sortie
            $stockMovement->delete(); 

            DB::commit();
            return back()->with('success', 'Sortie annulée. Les articles sont de retour en stock !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}