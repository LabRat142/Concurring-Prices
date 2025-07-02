<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - {{ $product->name }}</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4">{{ $product->name }}</h2>

    @php
        // Use the same image selection logic as the search page
        $img = $product->prices
            ->first(fn($price) => $price->imgURL && !str_contains($price->imgURL, 'anhoch'))
            ?->imgURL;
    @endphp

    <div class="text-center mb-4">
        @if($img)
            <img src="{{ $img }}"
                 alt="{{ $product->name }}"
                 class="mb-4"
                 style="object-fit: contain; max-width: 500px; max-height: 400px; width: auto; height: auto;">
        @else
            <img src="{{ asset('images/fallback_image.jpg') }}"
                 alt="No image available"
                 class="mb-4"
                 style="object-fit: contain; max-width: 500px; max-height: 400px; width: auto; height: auto;">
        @endif
    </div>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Store</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Available</th>
            <th>Link</th>
        </tr>
        </thead>
        <tbody>
        @foreach($product->prices as $p)
            <tr>
                <td>{{ $p->store->name }}</td>
                <td>{{ number_format($p->price, 2) }} MKD</td>
                <td>
                    @if($p->discount_price && $p->discount_price < $p->price)
                        <span class="text-danger">{{ number_format($p->discount_price, 2) }} MKD</span>
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($p->available)
                        <span class="text-success">Yes</span>
                    @else
                        <span class="text-danger">No</span>
                    @endif
                </td>
                <td>
                    @if(!empty($p->url))
                        <a href="{{ $p->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">View</a>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back to results</a>
</div>

</body>
</html>
