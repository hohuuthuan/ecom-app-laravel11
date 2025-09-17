<!doctype html>
<html lang="vi">
  <head>
    @include('partials.head')
    <title>@yield('title','Khách')</title>
  </head>
  <body class="min-h-screen bg-gradient-to-br from-sky-50 to-white">
    <main class="px-4 sm:px-6 lg:px-8 py-10">
      <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-900">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9.5 8.5-.7.8L20 11.7V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-8.3l-1.8 1.6-.7-.8z"/></svg>
          <span class="text-lg font-semibold">Ecom</span>
        </a>
      </div>

      @include('partials.flash-toasts')
      @yield('content')
    </main>

    @include('partials.script')
  </body>
</html>