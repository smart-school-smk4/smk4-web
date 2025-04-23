<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('includes.head')
    </head>
    <body class="antialiased">
        @include('includes.nav')

        <main>
            @yield('content')
        </main>

        @include('includes.footer')
    </body>
</html>
