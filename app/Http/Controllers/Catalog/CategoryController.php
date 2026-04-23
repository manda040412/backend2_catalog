<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_name' => 'required|string|max:255',
            'description'   => 'nullable|string|max:255',
        ]);

        // Auto-generate ID
        $data['id_category'] = Category::generateId();

        $cat = Category::create($data);
        return response()->json(['message' => 'Kategori dibuat.', 'category' => $cat], 201);
    }

    public function update(Request $request, string $id)
    {
        $cat  = Category::findOrFail($id);
        $data = $request->validate([
            'category_name' => 'sometimes|string|max:255',
            'description'   => 'nullable|string|max:255',
        ]);
        $cat->update($data);
        return response()->json(['message' => 'Kategori diperbarui.', 'category' => $cat]);
    }

    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Kategori dihapus.']);
    }
}