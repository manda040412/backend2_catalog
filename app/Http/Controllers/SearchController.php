<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected SearchService $searchService) {}

    /**
     * GET /api/search/product?mode=item_code&q=SA-1762
     * GET /api/search/product?mode=oem&q=BBM2-34-380A
     */
    public function searchProduct(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:item_code,oem',
            'q'    => 'required|string|min:2|max:100',
        ]);

        $user     = $request->user();
        $internal = $user && $user->isInternal();

        $results = $this->searchService->searchByProduct(
            $request->mode,
            $request->q,
            $internal
        );

        ActivityLog::create([
            'user_id'     => $user->id_user,
            'action'      => 'search',
            'module'      => 'search_product',
            'description' => "mode={$request->mode} q={$request->q} results={$results->count()}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'query'   => $request->only(['mode', 'q']),
            'total'   => $results->count(),
            'results' => $results,
        ]);
    }

    /**
     * GET /api/search/application?car_brand=MAZDA&car_type=BIANTE&year_from=2015
     */
    public function searchApplication(Request $request)
    {
        $request->validate([
            'car_brand' => 'required|string|max:100',
            'car_type'  => 'nullable|string|max:100',
            'year_from' => 'nullable|digits:4|integer',
        ]);

        $user     = $request->user();
        $internal = $user && $user->isInternal();

        $results = $this->searchService->searchByApplication(
            $request->only(['car_brand', 'car_type', 'year_from']),
            $internal
        );

        ActivityLog::create([
            'user_id'     => $user->id_user,
            'action'      => 'search',
            'module'      => 'search_application',
            'description' => "brand={$request->car_brand} type={$request->car_type} year={$request->year_from} results={$results->count()}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json([
            'query'   => $request->only(['car_brand', 'car_type', 'year_from']),
            'total'   => $results->count(),
            'results' => $results,
        ]);
    }

    /**
     * GET /api/search/dropdown/brands
     */
    public function dropdownBrands()
    {
        return response()->json($this->searchService->getCarBrands());
    }

    /**
     * GET /api/search/dropdown/types?car_brand=TOYOTA
     */
    public function dropdownTypes(Request $request)
    {
        $request->validate(['car_brand' => 'nullable|string']);
        return response()->json($this->searchService->getCarTypes($request->car_brand));
    }

    /**
     * GET /api/search/dropdown/years?car_brand=TOYOTA&car_type=Avanza
     */
    public function dropdownYears(Request $request)
    {
        $request->validate([
            'car_brand' => 'required|string',
            'car_type'  => 'required|string',
        ]);
        return response()->json($this->searchService->getYears($request->car_brand, $request->car_type));
    }
}
