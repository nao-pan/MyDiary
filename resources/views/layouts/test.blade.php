<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <title>@yield('title')</title>
</head>

<body>
  <header style="background-color: #2467aa; padding: 20px; text-align: center;">
    <h1>Test Layout</h1>
  </header>
  <div class="content">
    @yield('content')
  </div>
  <footer style="background-color: #2467aa; padding: 10px; text-align: center; color: white;">
    <p>&copy; {{ date('Y') }} My Application</p>
  </footer>
</body>
</html>