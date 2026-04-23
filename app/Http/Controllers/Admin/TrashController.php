<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cross;
use App\Models\MatchCar;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * TrashController – Super Admin only
 * GET    /api/trash/{type}        → list data soft-deleted
 * PATCH  /api/trash/{type}/{id}   → restore
 * DELETE /api/trash/{type}/{id}   → force delete permanen
 */
class TrashController extends Controller
{
    // GET /api/trash/{type}
    public function index(Request $request, string $type)
    {
        try {
            $perPage = min((int)($request->per_page ?? 20), 100);

            switch ($type) {
                case 'products':
                    $results = Product::onlyTrashed()
                        ->with('category')
                        ->latest('deleted_at')
                        ->paginate($perPage);
                    break;

                case 'categories':
                    $results = Category::onlyTrashed()
                        ->latest('deleted_at')
                        ->paginate($perPage);
                    break;

                case 'crosses':
                    $results = Cross::onlyTrashed()
                        ->with('product')
                        ->latest('deleted_at')
                        ->paginate($perPage);
                    break;

                case 'match_cars':
                    $results = MatchCar::onlyTrashed()
                        ->with('product')
                        ->latest('deleted_at')
                        ->paginate($perPage);
                    break;

                case 'users':
                    $results = User::onlyTrashed()
                        ->with('role')
                        ->latest('deleted_at')
                        ->paginate($perPage);
                    break;

                default:
                    return response()->json(['message' => "Tipe '{$type}' tidak dikenal."], 400);
            }

            return response()->json($results);

        } catch (\Exception $e) {
            \Log::error('TrashController@index error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat data trash: ' . $e->getMessage()], 500);
        }
    }

    // PATCH /api/trash/{type}/{id}
    public function restore(string $type, string $id)
    {
        try {
            $item = $this->findTrashed($type, $id);

            if (!$item) {
                return response()->json(['message' => "Tipe '{$type}' tidak dikenal."], 400);
            }

            $item->restore();

            return response()->json([
                'message' => 'Data berhasil dipulihkan.',
                'item'    => $item,
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Data tidak ditemukan di trash.'], 404);
        } catch (\Exception $e) {
            \Log::error('TrashController@restore error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memulihkan: ' . $e->getMessage()], 500);
        }
    }

    // DELETE /api/trash/{type}/{id}
    public function forceDelete(string $type, string $id)
    {
        try {
            $item = $this->findTrashed($type, $id);

            if (!$item) {
                return response()->json(['message' => "Tipe '{$type}' tidak dikenal."], 400);
            }

            $item->forceDelete();

            return response()->json(['message' => 'Data dihapus permanen dari database.']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Data tidak ditemukan di trash.'], 404);
        } catch (\Exception $e) {
            \Log::error('TrashController@forceDelete error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal hapus permanen: ' . $e->getMessage()], 500);
        }
    }

    // Helper: cari item di trash berdasarkan type
    private function findTrashed(string $type, string $id)
    {
        switch ($type) {
            case 'products':   return Product::onlyTrashed()->findOrFail($id);
            case 'categories': return Category::onlyTrashed()->findOrFail($id);
            case 'crosses':    return Cross::onlyTrashed()->findOrFail($id);
            case 'match_cars': return MatchCar::onlyTrashed()->findOrFail($id);
            case 'users':      return User::onlyTrashed()->findOrFail($id);
            default:           return null;
        }
    }
}