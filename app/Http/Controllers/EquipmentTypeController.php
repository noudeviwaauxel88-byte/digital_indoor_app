<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EquipmentTypeController extends Controller
{
    /**
     * Enregistrer un nouveau type sur Cloudinary.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Envoi de l'image sur Cloudinary
        $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
            'folder' => 'equipment_types'
        ])->getSecurePath();

        EquipmentType::create([
            'name'  => $request->name,
            'image' => $uploadedFileUrl,
        ]);

        return redirect()->back()->with('success', 'Type d\'équipement ajouté avec succès.');
    }

    /**
     * Mettre à jour un type existant (nom et/ou image sur Cloudinary).
     */
    public function update(Request $request, EquipmentType $equipmentType)
    {
        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name,' . $equipmentType->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('image')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'equipment_types'
            ])->getSecurePath();

            $data['image'] = $uploadedFileUrl;
        }

        $equipmentType->update($data);

        return redirect()->back()->with('success', 'Type d\'équipement mis à jour.');
    }

    /**
     * Supprimer un type.
     */
    public function destroy(EquipmentType $equipmentType)
    {
        // Sécurité : On empêche la suppression si des équipements utilisent ce type
        if ($equipmentType->equipments()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible : des équipements utilisent déjà ce type.');
        }

        $equipmentType->delete();

        return redirect()->back()->with('success', 'Type d\'équipement supprimé.');
    }
}