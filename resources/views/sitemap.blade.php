@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($static as $u)
        <url>
            <loc>{{ $u['loc'] }}</loc>
            @isset($u['changefreq'])<changefreq>{{ $u['changefreq'] }}</changefreq>@endisset
            @isset($u['priority'])  <priority>{{ $u['priority'] }}</priority>@endisset
        </url>
    @endforeach

    {{-- Якщо є окремі сторінки категорій — заміни на свій URL.
         Якщо ні — можна тимчасово використовувати параметр категорії в каталозі --}}
    @foreach ($categories as $c)
        <url>
            <loc>{{ $base }}/?category_id={{ $c->id }}</loc>
            @if($c->updated_at)<lastmod>{{ $c->updated_at->toAtomString() }}</lastmod>@endif
            <changefreq>weekly</changefreq>
        </url>
    @endforeach

    @foreach ($products as $p)
        <url>
            <loc>{{ $base }}/product/{{ urlencode($p->slug) }}</loc>
            @if($p->updated_at)<lastmod>{{ $p->updated_at->toAtomString() }}</lastmod>@endif
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>

