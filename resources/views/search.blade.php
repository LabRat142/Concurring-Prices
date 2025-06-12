<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Header -->
<div class="container bg-light py-3 mb-3">
    <div class="row text-center align-items-center">
        <div class="col-md-4 text-start ps-4">
            <strong>Total:</strong> {{ $products->total() }}
        </div>
        <div class="col-md-4">
            <h4 class="m-0">Concurring-Prices</h4>
        </div>
        <div class="col-md-4 text-end pe-4">
            {{-- <strong>Last updated:</strong> {{ $lastUpdated ?? 'N/A' }} --}}
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar (1/3) -->
        <div class="col-md-3">
            <!-- Search Form -->
            <form action="{{ url('/') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="query" value="{{ request('query') }}" placeholder="Search products...">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>

            <!-- Price Range -->
            <div class="mb-3">
                <label for="priceRange" class="form-label">Price Range</label>
                <input type="range" class="form-range" min="0" max="2000" name="price" id="priceRange">
            </div>

            <!-- Stores Filter -->
            <div class="mb-3">
                <label class="form-label">Stores</label>
                @foreach($products->pluck('store')->unique() as $store)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="stores[]" value="{{ $store }}" id="store_{{ $loop->index }}">
                        <label class="form-check-label" for="store_{{ $loop->index }}">{{ $store }}</label>
                    </div>
                @endforeach
            </div>

            <!-- Brands Filter (from 'category') -->
            <div class="mb-3">
                <label class="form-label">Brands</label>
                @foreach($products->pluck('category')->unique() as $category)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category }}" id="category_{{ $loop->index }}">
                        <label class="form-check-label" for="category_{{ $loop->index }}">{{ $category }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content (2/3) -->
        <div class="col-md-9">
            <!-- Category Nav -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request('category') == 'smartphone' ? 'active' : '' }}" href="{{ url('/?category=smartphone') }}">Phones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('category') == 'computers' ? 'active' : '' }}" href="{{ url('/?category=computers') }}">Computers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('category') == 'laptop' ? 'active' : '' }}" href="{{ url('/?category=laptop') }}">Laptops</a>
                </li>
            </ul>

            <!-- Product Cards -->
            @if($products->isEmpty())
                <p>No products found for your search query.</p>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 d-flex flex-column">
                                <img src="{{ $product->imgURL }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: contain; height: 200px;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text"><strong>${{ $product->price }}</strong></p>
                                    <p class="card-text">
                                        <small class="{{ $product->available ? 'text-success' : 'text-danger' }}">
                                            {{ $product->available ? 'Available' : 'Out of stock' }}
                                        </small><br>
                                        <small>Store: {{ $product->store }}</small><br>
                                        <small>Updated: {{ \Carbon\Carbon::parse($product->updated_at)->diffForHumans() }}</small>
                                    </p>
                                    <div class="mt-auto">
                                        <a href="{{ $product->url }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">View Product</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-5 text-center">
                    {{ $products->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>

            @endif
        </div>
    </div>
</div>

</body>
</html>
