<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductsController extends Controller
{
    public function search(Request $request): View|Factory|Application
    {
        $query = $request->input('query');
        $category = $request->input('category');
        $selectedStores = $request->input('stores');
        $selectedCategories = $request->input('categories');
        $globalMinPrice = Price::query()->min('price');
        $globalMaxPrice = Price::query()->max('price');
        $minPriceInput = $request->input('min_price', $globalMinPrice);
        $maxPriceInput = $request->input('max_price', $globalMaxPrice);
        $sort = $request->input('sort');
        $hasDiscount = $request->boolean('has_discount');
        $isAvailable = $request->boolean('is_available');
        $brands = $request->input('brands');

        // Get all distinct stores and categories
        $allStores = Store::query()->select('name')->distinct()->pluck('name');
        $allCategories = Product::query()->select('category')->distinct()->pluck('category');

        $product_columns = Schema::getColumnListing('products');

        // Get existing data from the database
        $products = Product::query()
            ->selectRaw('products.*, MIN(prices.price) as lowest_price')
            ->leftJoin('prices', 'products.id', '=', 'prices.product_id')
            ->with([
                'prices' => function ($query) use ($isAvailable) {
                    if ($isAvailable) {
                        $query->where('available', 1); // Filter only available prices
                    }
                },
                'prices.store' // Still eager load the store relation
            ])
            ->when($query, function ($q) use ($query) {
                $words = collect(explode(' ', $query))
                    ->filter() // remove empty values
                    ->map(fn($word) => trim($word));

                foreach ($words as $word) {
                    $q->where('products.name', 'LIKE', '%' . $word . '%');
                }
            })
            ->when($category, fn($q) => $q->where('products.category', $category))
            ->when($selectedStores, fn($q) => $q->whereHas('prices.store', fn($s) =>
                $s->whereIn('name', $selectedStores)))
            ->when($selectedCategories, fn($q) => $q->whereIn('products.category', $selectedCategories))
            ->when($minPriceInput, fn($q) => $q->whereHas('prices', fn($p) =>
                $p->where('price', '>=', $minPriceInput)))
            ->when($maxPriceInput, fn($q) => $q->whereHas('prices', fn($p) =>
                $p->where('price', '<=', $maxPriceInput)))
            ->when($request->boolean('has_discount'), fn($q) => $q->where('discount_price', '>', 0))
            //sort by brands
            ->when($brands, fn($q) =>
                $q->where(function($query) use ($brands) {
                    foreach ($brands as $brand) {
                        $query->orWhere('name', 'LIKE', "%$brand%");
                    }
                })
            )
            // sort low → high
            ->when($sort === 'price_asc', fn($q) => $q->orderBy('lowest_price', 'asc'))
            // sort high → low
            ->when($sort === 'price_desc', fn($q) => $q->orderBy('lowest_price', 'desc'))
            // sort by largest % discount (only if has_discount is on)
            ->when($sort === 'discount_desc' && $hasDiscount, fn($q) =>
            $q->orderByRaw('MAX( (prices.price - prices.discount_price) / prices.price ) DESC'))
            // fallback default sort
            ->when(!$sort, fn($q) => $q->orderBy('lowest_price', 'asc'))
            ->groupBy(...collect($product_columns)->map(fn($col) => "products.$col")->all())
            ->paginate(20);

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
