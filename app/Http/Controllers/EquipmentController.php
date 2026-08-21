<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentItem;
use App\Models\EquipmentOut;
use App\Models\User;
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
        'price'       => 'nullable|numeric|min:0', // Validation du prix
        'quantity'    => 'required|integer|min:1',
    ]);

    $equipment = Equipment::create([
        'title'       => $validated['title'],
        'brand'       => $validated['brand'],
        'type'        => $validated['type'] ?? 'Matériel',
        'description' => $validated['description'] ?? null,
        'price'       => $validated['price'] ?? 0, // Valeur par défaut à 0 si non renseigné
    ]);

    for ($i = 0; $i < $validated['quantity']; $i++) {
        EquipmentItem::create([
            'equipment_id' => $equipment->id,
            'status'       => 'available',
        ]);
    }

    return redirect()->route('equipments.index')
        ->with('success', 'Équipement et exemplaires créés avec succès.');
}

    public function show(Equipment $equipment)
    {
        $equipment->load(['items.outs.user', 'items.outs.project']);
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

    public function createStockOut()
    {
        $this->authorizeManagement();

        $equipments = Equipment::whereHas('availableItems')->get();
        $users = User::select('id', 'firstname', 'lastname', 'email')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->get();

        return view('equipments.stock_out', compact('equipments', 'users'));
    }

    public function storeStockOut(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'quantity'     => 'required|integer|min:1',
            'user_id'      => 'required|exists:users,id',
            'out_date'     => 'required|date',
            'return_date'  => 'nullable|date|after_or_equal:out_date',
            'notes'        => 'nullable|string',
        ]);

        $availableItems = EquipmentItem::where('equipment_id', $validated['equipment_id'])
            ->where('status', 'available')
            ->take($validated['quantity'])
            ->get();

        if ($availableItems->count() < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Quantité disponible insuffisante.'])->withInput();
        }

        foreach ($availableItems as $item) {
            $item->update(['status' => 'borrowed']);

            EquipmentOut::create([
                'equipment_item_id' => $item->id,
                'user_id'           => $validated['user_id'],
                'out_date'          => $validated['out_date'],
                'return_date'       => $validated['return_date'] ?? null,
                'notes'             => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->route('equipments.index')
            ->with('success', 'Sortie de stock enregistrée avec succès.');
    }

    public function stockOutHistory()
    {
        $this->authorizeManagement();

        $outs = EquipmentOut::with(['item.equipment', 'user'])
            ->latest('out_date')
            ->paginate(15);

        return view('equipments.stockout_history', compact('outs'));
    }

    public function returnStockOut(EquipmentOut $equipmentOut)
{
    $this->authorizeManagement();

    // Mettre à jour le statut de l'exemplaire de matériel à "available"
    if ($equipmentOut->item) {
        $equipmentOut->item->update([
            'status' => 'available'
        ]);
    }

    // Optionnel : Enregistrer la date effective de retour
    $equipmentOut->update([
        'return_date' => now()
    ]);

    return back()->with('success', 'Le matériel a été marqué comme retourné et est de nouveau disponible.');
}
}