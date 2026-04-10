<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Request $request, string $id)
    {
        $user     = $request->user();
        $internal = $user && $user->isInternal();

        $query = Product::with(['category', 'crosses', 'matchCars']);

        if (!$internal) {
            $query->where('is_internal_only', 0);
        }

        $product = $query->where('id_produk', $id)->firstOrFail();

        // Log aktivitas
        if ($user) {
            ActivityLog::create([
                'user_id'   => $user->id_user,
                'action'    => 'view_product',
                'module'    => 'catalog',
                'module_id' => $id,
                'description' => "Melihat produk: {$product->nama_produk}",
                'ip_address'  => $request->ip(),
            ]);
        }

        return response()->json($product);
    }

    public function index(Request $request)
    {
        $user     = $request->user();
        $internal = $user && $user->isInternal();

        $products = Product::with('category')
            ->when(!$internal, fn($q) => $q->where('is_internal_only', 0))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->paginate(20);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_produk'        => 'required|string|max:15|unique:products,id_produk',
            'category_id'      => 'required|exists:categories,id_category',
            'item_code'        => 'required|string|max:255',
            'brand_produk'     => 'required|string|max:255',
            'nama_produk'      => 'required|string|max:255',
            'print_description'=> 'nullable|string|max:255',
            'is_internal_only' => 'boolean',
        ]);

        $product = Product::create($data);
        return response()->json(['message' => 'Produk berhasil ditambahkan.', 'product' => $product], 201);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $data    = $request->validate([
            'category_id'      => 'sometimes|exists:categories,id_category',
            'item_code'        => 'sometimes|string|max:255',
            'brand_produk'     => 'sometimes|string|max:255',
            'nama_produk'      => 'sometimes|string|max:255',
            'print_description'=> 'nullable|string|max:255',
            'is_internal_only' => 'boolean',
        ]);

        $product->update($data);
        return response()->json(['message' => 'Produk diperbarui.', 'product' => $product]);
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }
}
