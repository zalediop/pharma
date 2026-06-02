<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::query()->where('archived', false);

        if ($request->query('with_stock')) {
            $query->with('stocks');
        }

        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('dci', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
            );
        }

        return response()->json($query->orderBy('name')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dci' => ['required', 'string', 'max:255'],
            'form' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
        ]);

        $data['archived'] = false;

        return response()->json(Medicine::create($data), 201);
    }

    public function show(Medicine $medicine)
    {
        return response()->json($medicine);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'dci' => ['required', 'string', 'max:255'],
            'form' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
        ]);

        $medicine->update($data);

        return response()->json($medicine);
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->update(['archived' => true]);

        return response()->json(['message' => 'Médicament archivé.']);
    }
}
