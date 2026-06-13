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
        $query = Prescription::with(['customer', 'doctor', 'medicines'])->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($cQuery) use ($search) {
                    $cQuery->where('name', 'like', "%{$search}%");
                })->orWhereHas('doctor', function ($dQuery) use ($search) {
                    $dQuery->where('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $limit = min((int) $request->query('limit', 20), 1000);
        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'issued_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'medicines' => ['required', 'array', 'min:1'],
            'medicines.*.medicine_id' => ['required', 'exists:medicines,id'],
            'medicines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $prescription = Prescription::create([
            'customer_id' => $data['customer_id'],
            'doctor_id' => $data['doctor_id'] ?? null,
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
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'issued_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,completed'],
            'medicines' => ['nullable', 'array'],
            'medicines.*.medicine_id' => ['required_with:medicines', 'exists:medicines,id'],
            'medicines.*.quantity' => ['required_with:medicines', 'integer', 'min:1'],
        ]);

        $prescription->update([
            'doctor_id' => $data['doctor_id'] ?? $prescription->doctor_id,
            'issued_at' => $data['issued_at'] ?? $prescription->issued_at,
            'notes' => $data['notes'] ?? $prescription->notes,
            'status' => $data['status'] ?? $prescription->status,
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
