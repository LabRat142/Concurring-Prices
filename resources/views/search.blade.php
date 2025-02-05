<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <!-- Add bootstrap-->
</head>
<body>
<h1>Search Results</h1>

<!-- Search Form -->
<form action="{{ url('/') }}" method="GET">
    <input type="text" name="query" value="{{ request('query') }}" placeholder="Search products...">
    <button type="submit">Search</button>
</form>

<!-- Display Products -->
@if($products->isEmpty())
    <p>No products found for your search query.</p>
@else
    <ul>
        @foreach($products as $product)
            <li>
                <h3>{{ $product->name }}</h3>
                <p>Price: ${{ $product->price }}</p>
            </li>
        @endforeach
    </ul>
@endif

<!-- Optionally, you could include pagination here if you want to display a lot of products -->
{{-- {{ $products->links() }} --}}
</body>
</html>
