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
        $selectedStores = $request->input('store');

        // Get existing data from the database
        $products = Products::query()
            ->where('name', 'LIKE', "%{$query}%")
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($selectedStores, fn($q) => $q->whereIn('store', $selectedStores))
            ->orderBy('price')
            ->paginate(20);

        // Dispatch a job to scrape updated data in the background
        ScrapeProductData::dispatch($query, Config::get('stores.stores'));

        return view('search', ['products' => $products]);
    }
}
