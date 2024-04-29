<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Toolbox' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma-extensions@6.2.7/dist/css/bulma-extensions.min.css">
    <link rel="stylesheet" href="{{ URL::asset('css/app.css')}}">

</head>

<body class="is-fullheight">
    <section class="section">
        <div class="container">
            <h1 class="title has-text-centered">
                @yield('header')
            </h1>
        </div>
    </section>
    <section class="section is-fullheight">
        <div class="container">
            @yield('body')
           <p/> <a href="/" class="button is-link">Do another test</a>
        </div>
    </section>

    <footer class="has-text-centered">
        &copy;2024
    </footer>

</body>

</html>
