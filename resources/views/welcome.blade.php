<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title> {{ config('app.name', 'Teebox') }}</title>
        <link href="{{ mix('css/argon.css') }}" rel="stylesheet">
        <script>window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token()]);?></script>
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
        <script>
            WebFont.load({
                google: {
                    "families": ["Poppins:300,400,500,600,700", "Roboto:300,400,500,600,700"]
                },
                active: function() {
                    sessionStorage.fonts = true;
                }
            });
        </script>
    </head>
    <body>
        <div id="app">
            <template v-cloak>
                @include('partials.header')
                @yield('body')
                @include('partials.footer')
                @stack('modals')
            </template>
        </div>
        <script type="text/javascript" src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
