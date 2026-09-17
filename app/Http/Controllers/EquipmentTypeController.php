<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;

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

        try {
            // Passez directement l'objet UploadedFile ($request->file('image')) à Cloudinary
            $upload = Cloudinary::uploadApi()->upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'equipment_types']
            );

            $uploadedFileUrl = $upload['secure_url'] ?? null;

            if (!$uploadedFileUrl) {
                return redirect()->back()->with('error', 'Échec de l\'envoi de l\'image sur Cloudinary.');
            }

            EquipmentType::create([
                'name'  => $request->name,
                'image' => $uploadedFileUrl,
            ]);

            return redirect()->back()->with('success', 'Type d\'équipement ajouté avec succès.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
        }
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
            try {
                $upload = Cloudinary::uploadApi()->upload(
                    $request->file('image')->getRealPath(),
                    ['folder' => 'equipment_types']
                );

                if (isset($upload['secure_url'])) {
                    $data['image'] = $upload['secure_url'];
                }
            } catch (Exception $e) {
                return redirect()->back()->with('error', 'Erreur lors de l\'upload de la nouvelle image : ' . $e->getMessage());
            }
        }

        $equipmentType->update($data);

        return redirect()->back()->with('success', 'Type d\'équipement mis à jour.');
    }

    /**
     * Supprimer un type.
     */
    public function destroy(EquipmentType $equipmentType)
    {
        if ($equipmentType->equipments()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible : des équipements utilisent déjà ce type.');
        }

        $equipmentType->delete();

        return redirect()->back()->with('success', 'Type d\'équipement supprimé.');
    }
}