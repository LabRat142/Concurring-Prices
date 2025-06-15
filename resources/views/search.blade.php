<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f3f6fd;
        }

        .header-bar {
            background: linear-gradient(90deg, #5c6bc0, #3f51b5);
            color: white;
            border-radius: 0 0 10px 10px;
            padding: 1rem 2rem;
            margin-bottom: 1.5rem;
        }

        .header-bar h4 {
            margin: 0;
            font-weight: bold;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
        }

        .price {
            color: #e53935;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .old-price {
            text-decoration: line-through;
            color: #888;
            font-size: 0.9rem;
            margin-left: 0.4rem;
        }

        .product-label {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            margin-bottom: 0.4rem;
            display: inline-block;
        }

        .product-label.in-stock {
            background-color: #d4edda;
            color: #155724;
        }

        .product-label.limited {
            background-color: #fff3cd;
            color: #856404;
        }

        .product-label.out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }

        .sidebar-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .rating-stars {
            color: #fbc02d;
        }

        .nav-tabs .nav-link.active {
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 10px 10px 0 0;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #3f51b5;
        }
    </style>

</head>
<body>

<!-- Header -->
<div class="container header-bar text-center py-3 mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div><strong>Total:</strong> {{ $products->total() }} products</div>
        <h4>Concurring-Prices</h4>
        <div><span class="text-light small">Last updated: Today 09:45</span></div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar (1/3) -->
        <div class="sidebar-box col-md-3">
            <!-- Search Form -->
            <form action="{{ url('/') }}" method="GET" class="mb-3">
                {{-- Preserve existing filters --}}
                @foreach(request()->except(['stores', 'categories', 'page']) as $name => $value)
                    @if(is_array($value))
                        @foreach($value as $val)
                            <input type="hidden" name="{{ $name }}[]" value="{{ $val }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach


                <!-- Search Input -->
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="query" value="{{ request('query') }}" placeholder="Search products...">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>

                <!-- Reset Button -->
                <a href="{{ url('/') }}" class="btn btn-secondary mt-2 w-100">Reset Filters</a>



                <!-- Price Range Slider Container -->
                <div class="mb-3">
                    <label class="form-label">
                        Price Range (From <span id="minPriceValue">{{ $minPriceInput }}</span> to <span id="maxPriceValue">{{ $maxPriceInput }}</span> MKD)
                    </label>

                    <!-- Wrapper div with proper structure -->
                    <div class="range-slider-container" style="position: relative; height: 50px; margin: 0 15px;">
                        <!-- Background track -->
                        <div style="position: absolute; height: 4px; background: #ddd; width: 100%; top: 50%; transform: translateY(-50%); z-index: 1;"></div>

                        <!-- Active range track -->
                        <div id="rangeTrack" style="position: absolute; height: 4px; background: #0d6efd; top: 50%; transform: translateY(-50%); z-index: 2;"></div>

                        <!-- Min Slider - comes FIRST in DOM -->
                        <input type="range" class="form-range position-absolute w-100" id="minPrice" name="min_price"
                               min="{{ $minPrice }}" max="{{ $maxPrice }}"
                               value="{{ $minPriceInput }}" step="1"
                               style="top: 50%; transform: translateY(-50%); z-index: 3; -webkit-appearance: none; height: 0;">

                        <!-- Max Slider - comes SECOND in DOM -->
                        <input type="range" class="form-range position-absolute w-100" id="maxPrice" name="max_price"
                               min="{{ $minPrice }}" max="{{ $maxPrice }}"
                               value="{{ $maxPriceInput }}" step="1"
                               style="top: 50%; transform: translateY(-50%); z-index: 4; -webkit-appearance: none; height: 0;">
                    </div>
                </div>




                <!-- Stores Filter -->
                <div class="mb-3">
                    <label class="form-label">Stores</label>
                    @foreach($allStores as $store)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="stores[]"
                                   value="{{ $store }}"
                                   id="store_{{ $loop->index }}"
                                   {{ in_array($store, request('stores', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="store_{{ $loop->index }}">{{ $store }}</label>
                        </div>
                    @endforeach

                </div>

                <!-- Brands Filter -->
                <div class="mb-3">
                    <label class="form-label">Brands</label>
                    @foreach($products->pluck('category')->unique() as $category)
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="categories[]"
                                   value="{{ $category }}"
                                   id="category_{{ $loop->index }}"
                                   {{ in_array($category, request('categories', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()"> {{-- Trigger submit on change --}}
                            <label class="form-check-label" for="category_{{ $loop->index }}">{{ $category }}</label>
                        </div>
                    @endforeach
                </div>

            </form>
        </div>

        <!-- Main Content (2/3) -->
        <div class="col-md-9">
            <!-- Category Nav -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request('category') == 'smartphone' ? 'active' : '' }}"
                       href="{{ url()->current() . '?' . http_build_query(['category' => 'smartphone']) }}">
                        Phones
                    </a>
                </li>
                <li class="nav-item">
                    @php
                        $queryParams = request()->all();
                    @endphp
                    <a class="nav-link {{ request('category') == 'computers' ? 'active' : '' }}"
                       href="{{ url()->current() . '?' . http_build_query(['category' => 'computers']) }}">
                        Computers
                    </a>
                </li>
                <li class="nav-item">
                    @php
                        $queryParams = request()->all();
                    @endphp

                    <a class="nav-link {{ request('category') == 'laptop' ? 'active' : '' }}"
                       href="{{ url()->current() . '?' . http_build_query(['category' => 'laptop']) }}">
                        Laptops
                    </a>
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
                                    <p class="card-text"><strong>{{ $product->price }} MKD</strong></p>
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

<script>
    const minSlider = document.getElementById('minPrice');
    const maxSlider = document.getElementById('maxPrice');
    const minDisplay = document.getElementById('minPriceValue');
    const maxDisplay = document.getElementById('maxPriceValue');
    const rangeTrack = document.getElementById('rangeTrack');
    const form = minSlider.closest('form');

    const priceGap = 1;
    const minValue = parseInt(minSlider.min);
    const maxValue = parseInt(minSlider.max);

    function updateDisplayAndTrack() {
        // Update display values
        minDisplay.textContent = minSlider.value;
        maxDisplay.textContent = maxSlider.value;

        // Update track visualization
        const minPercent = ((minSlider.value - minValue) / (maxValue - minValue)) * 100;
        const maxPercent = ((maxSlider.value - minValue) / (maxValue - minValue)) * 100;
        rangeTrack.style.left = minPercent + '%';
        rangeTrack.style.width = (maxPercent - minPercent) + '%';
    }

    function handleMinChange() {
        if (parseInt(minSlider.value) > parseInt(maxSlider.value) - priceGap) {
            minSlider.value = parseInt(maxSlider.value) - priceGap;
        }
        updateDisplayAndTrack();
    }

    function handleMaxChange() {
        if (parseInt(maxSlider.value) < parseInt(minSlider.value) + priceGap) {
            maxSlider.value = parseInt(minSlider.value) + priceGap;
        }
        updateDisplayAndTrack();
    }

    // Event listeners
    minSlider.addEventListener('input', handleMinChange);
    maxSlider.addEventListener('input', handleMaxChange);

    // Auto-submit when released
    minSlider.addEventListener('change', () => form.submit());
    maxSlider.addEventListener('change', () => form.submit());

    // Initialize
    updateDisplayAndTrack();

    // Force thumb visibility (critical for Safari/WebKit)
    const style = document.createElement('style');
    style.textContent = `
input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    background: transparent;
    pointer-events: auto;
    position: relative;
    z-index: 4;
}

/* WebKit thumbs (Chrome, Safari) */
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    background: #0d6efd;
    border-radius: 50%;
    cursor: pointer;
    margin-top: -20px;
    position: relative;
    z-index: 6;
}

/* Firefox thumbs */
input[type="range"]::-moz-range-thumb {
    width: 16px;
    height: 16px;
    background: #0d6efd;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    z-index: 6;
}

/* IE / Edge */
input[type="range"]::-ms-thumb {
    width: 16px;
    height: 16px;
    background: #0d6efd;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    position: relative;
    z-index: 6;
}

/* Note: ::after may not be supported for ::-ms-thumb, so visual line might not show in old Edge */
`;

    document.head.appendChild(style);
</script>


</body>
</html>


