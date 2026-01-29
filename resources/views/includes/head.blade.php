<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Default Title')</title>

<!-- CSS Assets -->
@if(app()->environment('production'))
    <!-- Production: Use built assets -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BNZj-whw.css') }}">
    <!-- Fallback CDN Tailwind jika assets tidak tersedia -->
    <script src="https://cdn.tailwindcss.com"></script>
@else
    <!-- Development: Use Vite -->
    @vite('resources/css/app.css')
@endif

<!-- Font Awesome untuk icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Custom CSS for consistency -->
<style>
    /* Ensure Tailwind classes work */
    .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
    .from-blue-50 { --tw-gradient-from: #eff6ff; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(239, 246, 255, 0)); }
    .to-indigo-100 { --tw-gradient-to: #e0e7ff; }
    .backdrop-blur-sm { backdrop-filter: blur(4px); }
    .shadow-xl { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
</style>