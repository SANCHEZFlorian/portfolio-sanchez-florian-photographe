<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <!-- dynamic title handled by Inertia -->
        <title inertia>{{ config('app.name', 'Florian Sanchez Photographie') }}</title>

        <!-- SEO Meta Tags -->
        <meta name="robots" content="index, follow">
        <meta name="author" content="Florian Sanchez">
        <meta name="description" content="Florian Sanchez — Photographe professionnel spécialisé dans le portrait, l'événementiel et le reportage. Découvrez mon portfolio et mes prestations.">
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="Florian Sanchez | Photographe Professionnel">
        <meta property="og:description" content="Capturer l'instant, révéler l'émotion. Découvrez l'univers photographique de Florian Sanchez.">
        <meta property="og:image" content="{{ asset('favicon.png') }}">
        <meta property="og:site_name" content="Florian Sanchez Photographie">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="Florian Sanchez | Photographe Professionnel">
        <meta name="twitter:description" content="Capturer l'instant, révéler l'émotion. Découvrez l'univers photographique de Florian Sanchez.">
        <meta name="twitter:image" content="{{ asset('favicon.png') }}">

        <!-- Favicons -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
