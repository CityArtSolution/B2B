<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::latest('id')->paginate(20);

        return view('shop.flashsale.index', compact('flashSales'));
    }

    public function show(FlashSale $flashSale)
    {
        $shop = generaleSetting('shop');

        $dealProducts = $flashSale->products()
            ->where('shop_id', $shop->id)
            ->with(['quantities.branch'])
            ->get();

        $products = $shop->products()
            ->whereNotIn('id', $dealProducts->pluck('id'))
            ->with(['quantities.branch'])
            ->isActive()
            ->get();

        $branches = Branch::all();

        return view('shop.flashsale.show', compact('flashSale', 'products', 'dealProducts', 'branches'));
    }

    public function productStore(FlashSale $flashSale, Request $request)
    {
        $request->validate([
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.branch_id' => ['required', 'exists:branches,id'],
            'products.*.discount_price' => ['required', 'numeric', 'min:0'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $hasAnyErrors = [];

        foreach ($request->products as $productArr) {
            $product = Product::find($productArr['id']);

            if ($product) {
                $productPrice = $product->carton_price > 0 ? $product->carton_price : ($product->discount_price > 0 ? $product->discount_price : $product->price);
                $branchId = $productArr['branch_id'];
                $branchQuantity = $product->quantityBranch($branchId);
                $availableQuantity = (int) ($branchQuantity?->qty ?? 0);

                $discountPercentage = ($productPrice - $productArr['discount_price']) / $productPrice * 100;

                if ($productPrice >= $productArr['discount_price'] && $availableQuantity > 0) {
                    $productQty = min((int) $productArr['quantity'], $availableQuantity);

                    $flashSale->products()->attach($productArr['id'], [
                        'branch_id' => $branchId,
                        'price' => $productArr['discount_price'],
                        'quantity' => $productQty,
                        'discount' => $discountPercentage,
                    ]);
                } else {
                    $hasAnyErrors[] = $product;
                }
            }
        }

        return back()->withSuccess(__('Product added successfully'))->with('hasAnyErrors', $hasAnyErrors);
    }

    public function productRemove(FlashSale $flashSale, Product $product)
    {
        $flashSale->products()->detach($product->id);

        return back()->withSuccess(__('Product removed successfully'));
    }

    public function update(FlashSale $flashSale, Product $product, Request $request)
    {
        $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productPrice = $product->carton_price > 0 ? $product->carton_price : ($product->discount_price > 0 ? $product->discount_price : $product->price);
        $branchId = $request->integer('branch_id');
        $branchQuantity = $product->quantityBranch($branchId);
        $availableQuantity = (int) ($branchQuantity?->qty ?? 0);

        if ($productPrice <= $request->price) {
            return back()->withError(__('Discount price cannot be greater or equal than product price!'));
        }

        if ($request->quantity > $availableQuantity) {
            return back()->withError(__('Quantity cannot be greater than product quantity!'));
        }

        $discountPercentage = (($productPrice - $request->price) / $productPrice) * 100;

        $flashSale->products()->updateExistingPivot($product->id, [
            'branch_id' => $branchId,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'discount' => $discountPercentage,
        ]);

        return back()->withSuccess(__('Updated Successfully'));
    }
}
