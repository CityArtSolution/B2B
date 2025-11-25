<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FlashSaleResource;
use App\Http\Resources\ProductResource;
use App\Models\FlashSale;
use App\Repositories\FlashSaleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FlashSaleController extends Controller
{
    /**
     * Get the incoming flash sale.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $incoming = FlashSaleRepository::getIncoming();
        $running = FlashSaleRepository::getRunning();

        return $this->json('incoming and running flash sale', [
            'incoming_flash_sale' => $incoming ? FlashSaleResource::make($incoming) : null,
            'running_flash_sale' => $running ? FlashSaleResource::make($running) : null,
        ]);
    }

    /**
     * Get the flash sale.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(FlashSale $flashSale, Request $request)
    {
        // Check if user has selected a branch
        $user = auth()->user();
        $selectedBranch = session('selected_branch');

        // If no session branch, check cache for API users
        if (!$selectedBranch && $user) {
            $selectedBranch = Cache::get('selected_branch_' . $user->id);
        }

        if (auth()->check() && !$selectedBranch) {
            return $this->json('flash sale details', [
                'flash_sale' => FlashSaleResource::make($flashSale),
                'products' => [
                    'data' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $request->per_page ?? 18
                ]
            ]);
        }

        $categoryId = $request->category_id;

        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 18;
        $skip = ($page * $perPage) - $perPage;

        $selectedBranchId = session('selected_branch');

        $products = $flashSale->products()->where(function ($query) use ($categoryId, $selectedBranchId) {
            $query->when($categoryId, function ($query) use ($categoryId) {
                return $query->whereHas('categories', function ($query) use ($categoryId) {
                    $query->where('id', $categoryId);
                });
            })
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                return $query->whereHas('productBranches', function ($query) use ($selectedBranchId) {
                    return $query->where('branch_id', $selectedBranchId)->where('qty', '>', 0);
                });
            });
        });

        $total = $products->count();

        $products = $products->when($perPage, function ($query) use ($perPage, $skip) {
            return $query->skip($skip)->take($perPage);
        })->get();

        return $this->json('flash sale details', [
            'total_products' => $total,
            'flash_sale' => FlashSaleResource::make($flashSale),
            'products' => ProductResource::collection($products),
        ]);
    }
}
