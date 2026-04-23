<?php

namespace App\Services;

use App\Models\Product;
use App\Models\MatchCar;

class SearchService
{
    /**
     * Search produk by item_code atau OEM number.
     * Internal (SADM/ADM/INT) → tampilkan crosses + matchCars
     * External (EXT)          → hanya matchCars, crosses TIDAK dikembalikan
     */
    public function searchByProduct(string $mode, string $keyword, bool $isInternal = false): \Illuminate\Database\Eloquent\Collection
    {
        // Eager load berdasarkan role
        $with = $isInternal
            ? ['category', 'crosses', 'matchCars']
            : ['category', 'matchCars'];

        $query = Product::with($with);

        if (!$isInternal) {
            $query->where('is_internal_only', 0);
        }

        if ($mode === 'item_code') {
            $query->where(function ($q) use ($keyword) {
                $q->where('item_code', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama_produk', 'LIKE', "%{$keyword}%");
            });
        } elseif ($mode === 'oem') {
            // Search by OEM hanya relevan untuk internal (yang bisa lihat cross)
            // Tapi tetap allow external search by OEM — hanya hasil cross-nya tidak ditampilkan
            $query->whereHas('crosses', function ($q) use ($keyword) {
                $q->where('oem_number', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->get();
    }

    /**
     * Search produk by aplikasi kendaraan.
     * Internal → tampilkan crosses + matchCars
     * External → hanya matchCars
     */
    public function searchByApplication(array $filters, bool $isInternal = false): \Illuminate\Database\Eloquent\Collection
    {
        $with = $isInternal
            ? ['category', 'crosses', 'matchCars']
            : ['category', 'matchCars'];

        $query = Product::with($with)
            ->whereHas('matchCars', function ($q) use ($filters) {
                if (!empty($filters['car_brand'])) {
                    $q->where('car_maker', $filters['car_brand']);
                }
                if (!empty($filters['car_type'])) {
                    $q->where('car_model', $filters['car_type']);
                }
                if (!empty($filters['year'])) {
                    $q->where('year', $filters['year']);
                }
                if (!empty($filters['car_body'])) {
                    $q->where('car_body', $filters['car_body']);
                }
                if (!empty($filters['engine_desc'])) {
                    $q->where('engine_desc', $filters['engine_desc']);
                }
            });

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!$isInternal) {
            $query->where('is_internal_only', 0);
        }

        return $query->get();
    }

    // ── Dropdown helpers ──────────────────────────────────────────

    public function getCarBrands(?string $categoryId = null): array
    {
        $query = MatchCar::distinct()->orderBy('car_maker');
        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        return $query->pluck('car_maker')->toArray();
    }

    public function getCarTypes(string $carMaker = null, ?string $categoryId = null): array
    {
        $query = MatchCar::distinct()->orderBy('car_model');
        if ($carMaker) $query->where('car_maker', $carMaker);
        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        return $query->pluck('car_model')->toArray();
    }

    public function getYears(string $carMaker = null, string $carModel = null, ?string $categoryId = null): array
    {
        $query = MatchCar::distinct()->orderBy('year');
        if ($carMaker) $query->where('car_maker', $carMaker);
        if ($carModel) $query->where('car_model', $carModel);
        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        return $query->whereNotNull('year')->pluck('year')->unique()->sort()->values()->toArray();
    }

    public function getCarBodies(string $carMaker = null, string $carModel = null, ?string $categoryId = null): array
    {
        $query = MatchCar::distinct()->orderBy('car_body');
        if ($carMaker) $query->where('car_maker', $carMaker);
        if ($carModel) $query->where('car_model', $carModel);
        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        return $query->whereNotNull('car_body')->pluck('car_body')->unique()->sort()->values()->toArray();
    }

    public function getEngines(string $carMaker = null, string $carModel = null, ?string $year = null, ?string $categoryId = null): array
    {
        $query = MatchCar::distinct()->orderBy('engine_desc');
        if ($carMaker) $query->where('car_maker', $carMaker);
        if ($carModel) $query->where('car_model', $carModel);
        if ($year) $query->where('year', $year);
        if ($categoryId) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
        }
        return $query->whereNotNull('engine_desc')->pluck('engine_desc')->unique()->sort()->values()->toArray();
    }
}