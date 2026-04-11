<?php

namespace App\Services;

use App\Models\Product;
use App\Models\MatchCar;

class SearchService
{
    public function searchByProduct(string $mode, string $keyword, bool $isInternal = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = Product::with(['category', 'crosses', 'matchCars']);

        if (!$isInternal) {
            $query->where('is_internal_only', 0);
        }

        if ($mode === 'item_code') {
            $query->where(function ($q) use ($keyword) {
                $q->where('item_code', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama_produk', 'LIKE', "%{$keyword}%");
            });
        } elseif ($mode === 'oem') {
            $query->whereHas('crosses', function ($q) use ($keyword) {
                $q->where('oem_number', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->get();
    }

    public function searchByApplication(array $filters, bool $isInternal = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = Product::with(['category', 'crosses', 'matchCars'])
            ->whereHas('matchCars', function ($q) use ($filters) {
                if (!empty($filters['car_brand'])) {
                    $q->where('car_maker', $filters['car_brand']);
                }
                if (!empty($filters['car_type'])) {
                    $q->where('car_model', $filters['car_type']);
                }
                // FIX: year di DB adalah string "2008 - 2018", cukup match exact string
                if (!empty($filters['year'])) {
                    $q->where('year', $filters['year']);
                }
                // Optional filters
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

    // FIX: Kembalikan range string "2008 - 2018" langsung — tidak di-expand per tahun
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

    // NEW: Dropdown car_body (optional filter)
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

    // NEW: Dropdown engine_desc (optional filter)
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