<?php

namespace App\Http\Controllers;

use App\Services\ShopifyService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ShopifyService $shopify)
    {
        [$products, $pageInfo] = $shopify->getProducts($request->query('after'));
        return view('products', compact('products', 'pageInfo'));
    }

    public function edit($id, ShopifyService $shopify)
    {
        $product = $shopify->getProductWithVariants($id);
        return view('product-edit', [
            'product'  => $product,
            'variants' => $product->variants->edges ?? [],
        ]);
    }

    public function updateCost(Request $request, ShopifyService $shopify)
{
    $costs = $request->input('inventory_costs', []) ?: $request->json('inventory_costs', []);
    if (empty($costs)) {
        return response()->json(['error' => 'Veri bulunamadı'], 422);
    }

    [$updated, $errors] = $shopify->updateInventoryCosts($costs);

    return empty($errors)
        ? response()->json(['success' => true, 'updated' => $updated])
        : response()->json(['success' => false, 'errors' => $errors], 422);
}

}
