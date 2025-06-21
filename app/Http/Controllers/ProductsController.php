<?php

namespace App\Http\Controllers;

use App\Models\Products;
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
        $globalMinPrice = Products::query()->min('price');
        $globalMaxPrice = Products::query()->max('price');
        $minPriceInput = $request->input('min_price', $globalMinPrice);
        $maxPriceInput = $request->input('max_price', $globalMaxPrice);
        $sort = $request->input('sort');
        $hasDiscount = $request->boolean('has_discount');
        $brands = $request->input('brands');

        // Get all distinct stores and categories
        $allStores = Products::query()->select('store')->distinct()->pluck('store');
        $allCategories = Products::query()->select('category')->distinct()->pluck('category');

        // Get existing data from the database
        $products = Products::query()
            ->when($query, fn($q) => $q->where('name', 'LIKE', "%{$query}%"))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($selectedStores, fn($q) => $q->whereIn('store', $selectedStores))
            ->when($selectedCategories, fn($q) => $q->whereIn('category', $selectedCategories))
            ->when($minPriceInput, fn($q) => $q->where('price', '>=', $minPriceInput))
            ->when($maxPriceInput, fn($q) => $q->where('price', '<=', $maxPriceInput))
            ->when($request->boolean('has_discount'), fn($q) => $q->where('discount_price', '>', 0))
            //sort by brands
            ->when($brands, fn($q) => $q->where(function($query) use ($brands) {
                foreach ($brands as $brand) {
                    $query->orWhere('name', 'LIKE', "%{$brand}%");
                }
            }))
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
