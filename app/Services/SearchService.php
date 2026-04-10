<?php

namespace App\Services;

use App\Models\Product;
use App\Models\MatchCar;

class SearchService
{
    /**
     * Search by item_code atau oem_number
     */
    public function searchByProduct(string $mode, string $keyword, bool $isInternal = false)
    {
        $query = Product::with(['category', 'crosses', 'matchCars']);

        if (!$isInternal) {
            $query->where('is_internal_only', 0);
        }

        if ($mode === 'item_code') {
            $query->where('item_code', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama_produk', 'LIKE', "%{$keyword}%");
        } elseif ($mode === 'oem') {
            $query->whereHas('crosses', function ($q) use ($keyword) {
                $q->where('oem_number', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->get();
    }

    /**
     * Search by application (car info)
     * FIXED: year_to = NULL berarti masih diproduksi (data asli: "ON")
     *        sehingga harus tetap muncul saat search berdasarkan tahun
     */
    public function searchByApplication(array $filters, bool $isInternal = false)
    {
        $query = Product::with(['category', 'crosses', 'matchCars'])
            ->whereHas('matchCars', function ($q) use ($filters) {
                if (!empty($filters['car_brand'])) {
                    $q->where('car_brand', $filters['car_brand']);
                }
                if (!empty($filters['car_type'])) {
                    $q->where('car_type', $filters['car_type']);
                }
                if (!empty($filters['year_from'])) {
                    $year = (int) $filters['year_from'];
                    $q->where('year_from', '<=', $year)
                      ->where(function ($q2) use ($year) {
                          // year_to NULL = masih diproduksi (ON) → selalu cocok
                          $q2->whereNull('year_to')
                             ->orWhere('year_to', '>=', $year);
                      });
                }
            });

        if (!$isInternal) {
            $query->where('is_internal_only', 0);
        }

        return $query->get();
    }

    /**
     * Dropdown: daftar car_brand unik
     */
    public function getCarBrands(): array
    {
        return MatchCar::distinct()
            ->orderBy('car_brand')
            ->pluck('car_brand')
            ->toArray();
    }

    /**
     * Dropdown: daftar car_type berdasarkan car_brand
     */
    public function getCarTypes(string $carBrand = null): array
    {
        $query = MatchCar::distinct()->orderBy('car_type');
        if ($carBrand) {
            $query->where('car_brand', $carBrand);
        }
        return $query->pluck('car_type')->toArray();
    }

    /**
     * Dropdown: daftar tahun berdasarkan car_brand + car_type
     * Menghasilkan range dari year_from terkecil sampai tahun ini (untuk yg masih ON)
     */
    public function getYears(string $carBrand, string $carType): array
    {
        $rows = MatchCar::where('car_brand', $carBrand)
            ->where('car_type', $carType)
            ->selectRaw('MIN(year_from) as min_year, MAX(COALESCE(year_to, YEAR(NOW()))) as max_year')
            ->first();

        if (!$rows || !$rows->min_year) return [];

        return array_reverse(range((int) $rows->min_year, (int) $rows->max_year));
    }
}
