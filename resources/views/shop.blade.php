<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Shop</title>

    <!-- Performance hints -->
    <link rel="preconnect" href="http://localhost:8080" crossorigin>
    <link rel="dns-prefetch" href="//localhost">
    <!-- Якщо є CDN для картинок/шрифтів — додай тут -->
    <!-- <link rel="preconnect" href="https://cdn.example.com" crossorigin> -->

    <!-- Приклад preload (під себе): -->
    <!-- <link rel="preload" as="font" href="/fonts/Inter-Variable.woff2" type="font/woff2" crossorigin> -->


    @viteReactRefresh
    @vite('resources/js/shop/main.tsx')
</head>
<body class="bg-gray-50 text-gray-900">
<div id="shop-root"></div>
</body>
</html>
