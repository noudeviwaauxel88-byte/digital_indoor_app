<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentTypeController extends Controller
{
    /**
     * Enregistrer un nouveau type d'équipement avec son image.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Sauvegarde de l'image dans storage/app/public/equipment_types
        $imagePath = $request->file('image')->store('equipment_types', 'public');

        EquipmentType::create([
            'name'  => $request->name,
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Type d\'équipement ajouté avec succès.');
    }

    /**
     * Mettre à jour un type d'équipement existant.
     */
    public function update(Request $request, EquipmentType $equipmentType)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name,' . $equipmentType->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Si une nouvelle image est téléversée, supprimer l'ancienne du disque public
        if ($request->hasFile('image')) {
            if ($equipmentType->image && Storage::disk('public')->exists($equipmentType->image)) {
                Storage::disk('public')->delete($equipmentType->image);
            }
            $equipmentType->image = $request->file('image')->store('equipment_types', 'public');
        }

        $equipmentType->name = $request->name;
        $equipmentType->save();

        return back()->with('success', 'Type d\'équipement mis à jour avec succès.');
    }

    /**
     * Supprimer un type d'équipement et son fichier image associé.
     */
    public function destroy(EquipmentType $equipmentType)
    {
        // Supprimer le fichier image du disque public
        if ($equipmentType->image && Storage::disk('public')->exists($equipmentType->image)) {
            Storage::disk('public')->delete($equipmentType->image);
        }

        $equipmentType->delete();

        return back()->with('success', 'Type d\'équipement supprimé avec succès.');
    }
}