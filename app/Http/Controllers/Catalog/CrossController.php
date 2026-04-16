<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Cross;
use Illuminate\Http\Request;

class CrossController extends Controller
{
    public function index(Request $request)
    {
        $crosses = Cross::with('product')
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('cross_brand', 'LIKE', "%{$request->search}%")
                   ->orWhere('cross_item_code', 'LIKE', "%{$request->search}%")
                   ->orWhere('oem_number', 'LIKE', "%{$request->search}%");
            }))
            ->paginate(20);

        return response()->json($crosses);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'        => 'required|exists:products,id_produk',
            'cross_brand'       => 'nullable|string|max:255',
            'cross_item_code'   => 'nullable|string|max:255',
            'cross_nama_produk' => 'nullable|string|max:255',
            'oem_number'        => 'required|string|max:255',
        ]);

        $cross = Cross::create($data);
        return response()->json(['message' => 'Cross berhasil ditambahkan.', 'cross' => $cross->load('product')], 201);
    }

    public function update(Request $request, $id)
    {
        $cross = Cross::findOrFail($id);
        $data  = $request->validate([
            'product_id'        => 'sometimes|exists:products,id_produk',
            'cross_brand'       => 'nullable|string|max:255',
            'cross_item_code'   => 'nullable|string|max:255',
            'cross_nama_produk' => 'nullable|string|max:255',
            'oem_number'        => 'sometimes|string|max:255',
        ]);

        $cross->update($data);
        return response()->json(['message' => 'Cross diperbarui.', 'cross' => $cross->load('product')]);
    }

    public function destroy($id)
    {
        Cross::findOrFail($id)->delete();
        return response()->json(['message' => 'Cross berhasil dihapus.']);
    }
}