<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected SearchService $searchService) {}

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

        if ($user) {
            ActivityLog::create([
                'user_id'     => $user->id_user,
                'action'      => 'search',
                'module'      => 'search_product',
                'description' => "Search [{$request->mode}]: {$request->q}",
                'ip_address'  => $request->ip(),
            ]);
        }

        return response()->json([
            'query'   => $request->only(['mode', 'q']),
            'total'   => $results->count(),
            'results' => $results,
        ]);
    }

    public function searchApplication(Request $request)
    {
        $request->validate([
            'car_brand'   => 'required|string|max:100',
            'car_type'    => 'required|string|max:100',
            'year'        => 'nullable|string|max:30',
            'category_id' => 'nullable|string|max:20',
            'car_body'    => 'nullable|string|max:50',
            'engine_desc' => 'nullable|string|max:200',
        ]);

        $user     = $request->user();
        $internal = $user && $user->isInternal();

        $results = $this->searchService->searchByApplication(
            $request->only(['car_brand', 'car_type', 'year', 'category_id', 'car_body', 'engine_desc']),
            $internal
        );

        if ($user) {
            ActivityLog::create([
                'user_id'     => $user->id_user,
                'action'      => 'search',
                'module'      => 'search_application',
                'description' => "Search application: {$request->car_brand} {$request->car_type} {$request->year}",
                'ip_address'  => $request->ip(),
            ]);
        }

        return response()->json([
            'query'   => $request->only(['car_brand', 'car_type', 'year', 'category_id', 'car_body', 'engine_desc']),
            'total'   => $results->count(),
            'results' => $results,
        ]);
    }

    public function dropdownBrands(Request $request)
    {
        return response()->json(
            $this->searchService->getCarBrands($request->category_id)
        );
    }

    public function dropdownTypes(Request $request)
    {
        return response()->json(
            $this->searchService->getCarTypes($request->car_brand, $request->category_id)
        );
    }

    public function dropdownYears(Request $request)
    {
        return response()->json(
            $this->searchService->getYears($request->car_brand, $request->car_type, $request->category_id)
        );
    }

    // NEW: dropdown car_body (optional)
    public function dropdownCarBodies(Request $request)
    {
        return response()->json(
            $this->searchService->getCarBodies($request->car_brand, $request->car_type, $request->category_id)
        );
    }

    // NEW: dropdown engine (optional)
    public function dropdownEngines(Request $request)
    {
        return response()->json(
            $this->searchService->getEngines($request->car_brand, $request->car_type, $request->year, $request->category_id)
        );
    }
}