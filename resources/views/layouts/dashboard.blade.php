<!DOCTYPE html>
<html lang="en">

<head>
    @include('includes.head')
    <title>{{ $title ?? 'Dashboard' }} - Smart School</title>

    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    @include('admin.component.sidebar')

    <!-- Main Content -->
    <div class="flex-1 ml-64">
        <!-- Header -->
        <div class="fixed top-0 left-64 w-[calc(100%-16rem)] bg-white shadow-md z-50">
            @include('admin.component.header')
        </div>
        
        <!-- Content -->
        <div class="p-5 mt-16">
            @yield('content')
        </div>
    </div>
    
</body>

</html>
