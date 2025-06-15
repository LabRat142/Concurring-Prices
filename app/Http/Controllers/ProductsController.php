<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeProductData;
use App\Models\Products;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ProductsController extends Controller
{
    public function search(Request $request): View|Factory|Application
    {
        $query = $request->input('query');
        $category = $request->input('category');
        $selectedStores = $request->input('stores');
        $selectedCategories = $request->input('categories');
        $globalMinPrice = Products::min('price');
        $globalMaxPrice = Products::max('price');

        $minPriceInput = $request->input('min_price', $globalMinPrice);
        $maxPriceInput = $request->input('max_price', $globalMaxPrice);

        // Get all distinct stores and categories
        $allStores = Products::select('store')->distinct()->pluck('store');
        $allCategories = Products::select('category')->distinct()->pluck('category');

        // Get existing data from the database
        $products = Products::query()
            ->when($query, fn($q) => $q->where('name', 'LIKE', "%{$query}%"))
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($selectedStores, fn($q) => $q->whereIn('store', $selectedStores))
            ->when($selectedCategories, fn($q) => $q->whereIn('category', $selectedCategories))
            ->when($minPriceInput, fn($q) => $q->where('price', '>=', $minPriceInput))
            ->when($maxPriceInput, fn($q) => $q->where('price', '<=', $maxPriceInput))
            ->orderBy('price')
            ->paginate(20);

        // Dispatch a job to scrape updated data in the background
        ScrapeProductData::dispatch($query, Config::get('stores.stores'));


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
