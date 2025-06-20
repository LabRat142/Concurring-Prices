<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concurring Prices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

</head>
<body>

<!-- Header -->
<div class="container header-bar text-center py-3 mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div><strong>Вкупно:</strong> {{ $products->total() }} производи</div>
        <h4>Concurring-Prices</h4>
        <div>
        <span class="text-light large">
          Последно ажурирано:
            @php
                $latestProduct = $products->firstWhere('available', true);
            @endphp
            {{ $latestProduct
               ? $latestProduct->updated_at->format('d/m/Y')
               : 'N/A'
            }}
        </span>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar (1/3) -->
        <div class="sidebar-box col-md-3">
            <form action="{{ url('/') }}" method="GET">

                {{-- preserve filters --}}
                @foreach(request()->except(['stores','categories','page','has_discount']) as $name=>$value)
                    @if(is_array($value))
                        @foreach($value as $val)
                            <input type="hidden" name="{{ $name }}[]" value="{{ $val }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach

                <!-- SEARCH -->
                <div class="sidebar-section">
                    <h5 class="section-title">Пребарувај</h5>
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            name="query"
                            value="{{ request('query') }}"
                            placeholder="Пребарувај производи…">
                        <button class="btn btn-primary" type="submit">🔍</button>
                    </div>
                </div>

                <div class="sidebar-section">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="has_discount"
                            id="hasDiscount"
                            value="1"
                            {{ request('has_discount') ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label class="form-check-label" for="hasDiscount">
                            На попуст
                        </label>
                    </div>
                </div>

                <!-- RESET -->
                <button type="reset" onclick="location.href='{{ url('/') }}';" class="btn btn-secondary w-100"
                        style="margin-bottom:40px">
                    Ресетирај филтри
                </button>


                <!-- PRICE -->
                <div class="sidebar-section">
                    <h5 class="section-title">Цена (MKD)</h5>
                    <label class="d-block mb-2">
                        од <strong><span id="minPriceValue">{{ $minPriceInput }}</span></strong>
                        до <strong><span id="maxPriceValue">{{ $maxPriceInput }}</span></strong>
                    </label>
                    <div class="range-slider-container mb-3">
                        <div class="track-bg" style="top: 45% !important;"></div>
                        <div id="rangeTrack" style="top: 45% !important;"></div>
                        <input type="range" id="minPrice" name="min_price" min="{{ $minPrice }}" max="{{ $maxPrice }}"
                               value="{{ $minPriceInput }}">
                        <input type="range" id="maxPrice" name="max_price" min="{{ $minPrice }}" max="{{ $maxPrice }}"
                               value="{{ $maxPriceInput }}">
                    </div>
                </div>

                <!-- STORES -->
                <div class="sidebar-section">
                    <h5 class="section-title">Продавници</h5>
                    @foreach($allStores as $store)
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="stores[]"
                                value="{{ $store }}"
                                id="store_{{ $loop->index }}"
                                {{ in_array($store, request('stores',[])) ? 'checked' : '' }}
                                onchange="this.form.submit()">
                            <label class="form-check-label" for="store_{{ $loop->index }}">
                                {{ $store }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <!-- BRANDS -->
                <div class="sidebar-section">
                    <h5 class="section-title">Брендови</h5>
                    @if (request()->query('category') == 'smartphone')
                        @php
                            // Define an array of phone brands
                            $brands = ['Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Sony', 'LG', 'OnePlus', 'Nokia', 'Motorola', 'Oppo'];
                        @endphp
                        @foreach ($brands as $brand)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="brands[]"
                                    value="{{ $brand }}"
                                    id="brand_{{ $loop->index }}"
                                    {{ in_array($brand, request('brands',[])) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label" for="brand_{{ $loop->index }}">
                                    {{ $brand }}
                                </label>
                            </div>
                        @endforeach
                    @elseif (request()->query('category') == 'computers')
                        @php
                            // Define the computer types
                            $brands = ['Gaming', 'Desktop'];
                        @endphp

                        @foreach ($brands as $brand)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="computer_types[]"
                                    value="{{ $brand }}"
                                    id="computer_{{ $brand }}"
                                    {{ in_array($brand, request('computer_types', [])) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label" for="computer_{{ $brand }}">
                                    {{ $brand }}
                                </label>
                            </div>
                        @endforeach
                    @elseif (request()->query('category') == 'laptop')
                        @php
                            // Define the computer types
                            $brands = ['Dell', 'HP', 'Lenovo', 'Macbook'];
                        @endphp

                        @foreach ($brands as $brand)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="laptop[]"
                                    value="{{ $brand }}"
                                    id="laptop_{{ $brand }}"
                                    {{ in_array($brand, request('laptop', [])) ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label" for="laptop_{{ $brand }}">
                                    {{ $brand }}
                                </label>
                            </div>
                        @endforeach
                    @endif
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

            {{-- 1) separator line --}}
            <hr class="my-4">

            {{-- 2) flex-row with some padding and margin --}}
            <div class="d-flex justify-content-between align-items-center mb-5 px-3">

                {{-- actual pagination --}}
                <div>
                    {!! $products->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') !!}
                </div>

                {{-- empty dropdown for future filters --}}
                <form id="sortForm" method="GET" class="ms-auto d-flex align-items-center">
                    {{-- 1) carry over all current GET params except page & sort --}}
                    @foreach(request()->except(['page','sort']) as $name => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $name }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    {{-- 2) the actual select --}}
                    <select
                        name="sort"
                        class="form-select"
                        style="width: auto;"
                        onchange="document.getElementById('sortForm').submit()"
                    >
                        <option value="">Сортирај по...</option>
                        <option value="price_asc" {{ request('sort')=='price_asc'  ? 'selected':'' }}>
                            Цена: Ниска → Висока
                        </option>
                        <option value="price_desc" {{ request('sort')=='price_desc' ? 'selected':'' }}>
                            Цена: Висока → Ниска
                        </option>

                        {{-- 3) only when “On sale only” is checked --}}
                        @if(request()->boolean('has_discount'))
                            <option value="discount_desc" {{ request('sort')=='discount_desc' ? 'selected':'' }}>
                                Највисок попуст %
                            </option>
                        @endif
                    </select>
                </form>

            </div>

            <!-- Product Cards -->
            @if($products->isEmpty())
                <p>Не се пронајдени производи за вашето пребарување.</p>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 d-flex flex-column">
                                @if ($product->store == 'Anhoch')
                                    <img src="{{ url('/proxy-image?imageUrl=' . urlencode($product->imgURL)) }}"
                                         class="card-img-top" alt="{{ $product->name }}"/>
                                @else
                                    <img src="{{ $product->imgURL }}" class="card-img-top" alt="{{ $product->name }}"
                                         style="object-fit: contain; height: 200px;">
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text"><strong>{{ $product->price }} MKD</strong></p>
                                    <p class="card-text">
                                        <small class="{{ $product->available ? 'text-success' : 'text-danger' }}">
                                            {{ $product->available ? 'На залиха' : 'Нема на залиха' }}
                                        </small><br>
                                        <small>Продавница: {{ $product->store }}</small><br>
                                    </p>
                                    {{-- only show this when there’s a discount --}}
                                    @if($product->discount_price > 0)
                                        <p class="card-text">
                                            <strong class="text-danger">
                                                Попуст: {{ number_format($product->discount_price) }} MKD
                                            </strong>
                                        </p>
                                    @endif
                                    <div class="mt-auto">
                                        <a href="{{ $product->url }}" class="btn btn-outline-primary btn-sm w-100"
                                           target="_blank">Прикажи го производот</a>
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


</script>


</body>
</html>





