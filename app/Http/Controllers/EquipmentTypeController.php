<?php

namespace App\Http\Controllers;

use App\Models\EquipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EquipmentTypeController extends Controller
{
    public function store(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Manager'])) {
            abort(403, 'Opération non autorisée.');
        }

        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('equipment_types', 'public');
        }

        EquipmentType::create([
            'name'  => $request->name,
            'image' => $imagePath,
        ]);

        // Invalidation du cache Redis
        Cache::forget('equipment_types_all');

        return redirect()->back()->with('success', 'Type d\'équipement créé avec succès.');
    }

    public function update(Request $request, EquipmentType $equipmentType)
    {
        if (!auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Manager'])) {
            abort(403, 'Opération non autorisée.');
        }

        $request->validate([
            'name'  => 'required|string|max:255|unique:equipment_types,name,' . $equipmentType->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = ['name' => $request->name];

        if ($request->hasFile('image')) {
            if ($equipmentType->image) {
                Storage::disk('public')->delete($equipmentType->image);
            }
            $data['image'] = $request->file('image')->store('equipment_types', 'public');
        }

        $equipmentType->update($data);

        // Invalidation du cache Redis
        Cache::forget('equipment_types_all');

        return redirect()->back()->with('success', 'Type d\'équipement mis à jour avec succès.');
    }

    public function destroy(EquipmentType $equipmentType)
    {
        if (!auth()->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Manager'])) {
            abort(403, 'Opération non autorisée.');
        }

        if ($equipmentType->image) {
            Storage::disk('public')->delete($equipmentType->image);
        }

        $equipmentType->delete();

        // Invalidation du cache Redis
        Cache::forget('equipment_types_all');

        return redirect()->back()->with('success', 'Type d\'équipement supprimé avec succès.');
    }
}