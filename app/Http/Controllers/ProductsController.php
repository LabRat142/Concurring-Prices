<?php

namespace App\Http\Controllers;

use App\Models\Prices;
use App\Models\Products;
use App\Models\Stores;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function search(Request $request): View|Factory|Application
    {
        $query = $request->input('query');
        $category = $request->input('category');
        $selectedStores = $request->input('stores');
        $selectedCategories = $request->input('categories');
        $globalMinPrice = Prices::query()->min('price');
        $globalMaxPrice = Prices::query()->max('price');
        $minPriceInput = $request->input('min_price', $globalMinPrice);
        $maxPriceInput = $request->input('max_price', $globalMaxPrice);
        $sort = $request->input('sort');
        $hasDiscount = $request->boolean('has_discount');
        $brands = $request->input('brands');

        // Get all distinct stores and categories
        $allStores = Stores::query()->select('name')->distinct()->pluck('name');
        $allCategories = Products::query()->select('category')->distinct()->pluck('category');

        // Get existing data from the database
        $products = Prices::query()
            ->with(['product:id,name', 'store:id,name'])
            ->when($query, fn($q) => $q->whereHas('product', fn($p) => $p->where('name', 'LIKE', "%{$query}%")))
            ->when($category, fn($q) => $q->whereHas('product', fn($p) => $p->where('category', $category)))
            ->when($selectedStores, fn($q) => $q->whereHas('store', fn($s) => $s->whereIn('name', $selectedStores)))
            ->when($selectedCategories, fn($q) => $q->whereHas('product', fn($p) => $p->whereIn('category', $selectedCategories)))
            ->when($minPriceInput, fn($q) => $q->where('price', '>=', $minPriceInput))
            ->when($maxPriceInput, fn($q) => $q->where('price', '<=', $maxPriceInput))
            ->when($request->boolean('has_discount'), fn($q) => $q->where('discount_price', '>', 0))
            //sort by brands
            ->when($brands, fn($q) =>
                $q->whereHas('product', fn($p) =>
                    $p->where(function($query) use ($brands) {
                        foreach ($brands as $brand) {
                            $query->orWhere('name', 'LIKE', "%{$brand}%");
                        }
                    })
                )
            )
            // sort low → high
            ->when($sort === 'price_asc', fn($q) => $q->orderBy('price', 'asc'))
            // sort high → low
            ->when($sort === 'price_desc', fn($q) => $q->orderBy('price', 'desc'))
            // sort by largest % discount (only if has_discount is on)
            ->when($sort === 'discount_desc' && $hasDiscount, function($q) {
                // (price - discount_price) / price DESC
                return $q->orderByRaw('(price - discount_price) / price DESC');
            })
            // fallback default sort
            ->when(!$sort, fn($q) => $q->orderBy('price', 'asc'));
//            ->orderBy('price')
//            ->paginate(20);

        $products = $products->paginate(20);


        return view('search', [
            'products' => $products,
            'allStores' => $allStores,
            'allCategories' => $allCategories,
            'minPrice' => $globalMinPrice,
            'maxPrice' => $globalMaxPrice,
            'minPriceInput' => $minPriceInput,
            'maxPriceInput' => $maxPriceInput,
        ]);


    }
}
