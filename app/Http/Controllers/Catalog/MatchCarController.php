<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\MatchCar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MatchCarController extends Controller
{
    public function index(Request $request)
    {
        $matchCars = MatchCar::with('product')
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('car_maker', 'LIKE', "%{$request->search}%")
                   ->orWhere('car_model', 'LIKE', "%{$request->search}%")
                   ->orWhere('item_code', 'LIKE', "%{$request->search}%");
            }))
            ->when($request->car_maker, fn($q) => $q->where('car_maker', $request->car_maker))
            ->paginate(20);

        return response()->json($matchCars);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:products,id_produk',
            'item_code'    => 'required|string|max:255',
            'car_maker'    => 'required|string|max:255',
            'car_model'    => 'required|string|max:255',
            'year'         => 'nullable|string|max:30',
            'engine_desc'  => 'nullable|string|max:255',
            'chassis_code' => 'nullable|string|max:255',
            'car_body'     => 'nullable|string|max:255',
        ]);

        $data['id_match'] = 'MCH-' . strtoupper(Str::random(8));
        $matchCar = MatchCar::create($data);

        return response()->json(['message' => 'Match car berhasil ditambahkan.', 'match_car' => $matchCar->load('product')], 201);
    }

    public function update(Request $request, $id)
    {
        $matchCar = MatchCar::findOrFail($id);
        $data = $request->validate([
            'product_id'   => 'sometimes|exists:products,id_produk',
            'item_code'    => 'sometimes|string|max:255',
            'car_maker'    => 'sometimes|string|max:255',
            'car_model'    => 'sometimes|string|max:255',
            'year'         => 'nullable|string|max:30',
            'engine_desc'  => 'nullable|string|max:255',
            'chassis_code' => 'nullable|string|max:255',
            'car_body'     => 'nullable|string|max:255',
        ]);

        $matchCar->update($data);
        return response()->json(['message' => 'Match car diperbarui.', 'match_car' => $matchCar->load('product')]);
    }

    public function destroy($id)
    {
        MatchCar::findOrFail($id)->delete();
        return response()->json(['message' => 'Match car berhasil dihapus.']);
    }
}