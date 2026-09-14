<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Find a rental') — RentOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ url('/') }}" class="text-xl font-semibold tracking-tight text-teal-700">RentOS</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('listings.index') }}" class="hover:text-teal-700">Browse homes</a>
                <a href="{{ url('/owner/login') }}" class="hover:text-teal-700">Owners</a>
                <a href="{{ url('/tenant/login') }}" class="rounded-full bg-teal-700 px-4 py-2 font-medium text-white">Tenant portal</a>
            </nav>
        </div>
    </header>
    @if (session('status'))
        <div class="bg-teal-50 text-teal-900">
            <p class="mx-auto max-w-6xl px-4 py-3 text-sm">{{ session('status') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-800">
            <p class="mx-auto max-w-6xl px-4 py-3 text-sm">{{ $errors->first() }}</p>
        </div>
    @endif
    <main>@yield('content')</main>
    <footer class="mt-16 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-slate-500">
            © {{ date('Y') }} RentOS · Multi-tenant rental operations
        </div>
    </footer>
</body>
</html>
