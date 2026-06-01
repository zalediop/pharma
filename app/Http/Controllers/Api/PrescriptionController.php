<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Prescription::with(['customer', 'medicines'])->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'issued_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'medicines' => ['required', 'array', 'min:1'],
            'medicines.*.medicine_id' => ['required', 'exists:medicines,id'],
            'medicines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $prescription = Prescription::create([
            'customer_id' => $data['customer_id'],
            'issued_at' => $data['issued_at'],
            'notes' => $data['notes'] ?? '',
        ]);

        foreach ($data['medicines'] as $item) {
            $prescription->medicines()->attach($item['medicine_id'], ['quantity' => $item['quantity']]);
        }

        return response()->json($prescription->load(['customer', 'medicines']), 201);
    }

    public function show(Prescription $prescription)
    {
        return response()->json($prescription->load(['customer', 'medicines']));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'issued_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'medicines' => ['nullable', 'array'],
            'medicines.*.medicine_id' => ['required_with:medicines', 'exists:medicines,id'],
            'medicines.*.quantity' => ['required_with:medicines', 'integer', 'min:1'],
        ]);

        $prescription->update([
            'issued_at' => $data['issued_at'],
            'notes' => $data['notes'] ?? $prescription->notes,
        ]);

        if (! empty($data['medicines'])) {
            $sync = [];
            foreach ($data['medicines'] as $item) {
                $sync[$item['medicine_id']] = ['quantity' => $item['quantity']];
            }
            $prescription->medicines()->sync($sync);
        }

        return response()->json($prescription->load(['customer', 'medicines']));
    }

    public function destroy(Prescription $prescription)
    {
        $prescription->medicines()->detach();
        $prescription->delete();

        return response()->json(['message' => 'Ordonnance supprimée.']);
    }
}
