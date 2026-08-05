<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://www.roistore.shop/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://www.roistore.shop/shop</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach($categories as $category)
    <url>
        <loc>https://www.roistore.shop/shop?category={{ $category->slug }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach
    @foreach($products as $product)
    <url>
        <loc>https://www.roistore.shop/shop/{{ $product->slug }}</loc>
        @if($product->updated_at)<lastmod>{{ $product->updated_at->toAtomString() }}</lastmod>@endif
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
</urlset>
