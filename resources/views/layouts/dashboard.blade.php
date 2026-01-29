<!DOCTYPE html>
<html lang="en">

<head>
    @include('includes.head')
    <title>{{ $title ?? 'Dashboard' }} - Smart School</title>

    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Sidebar -->
    @include('admin.component.sidebar')

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64 transition-all duration-300">
        <!-- Header -->
        <div class="fixed top-0 left-0 lg:left-64 right-0 bg-white shadow-sm z-40">
            @include('admin.component.header')
        </div>
        
        <!-- Content -->
        <div class="p-4 sm:p-6 mt-16">
            @yield('content')
        </div>
    </div>
    
</body>

</html>
