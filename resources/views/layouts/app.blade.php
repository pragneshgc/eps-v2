<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Electronic Shipping Application') }}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    {{-- <link href="{{ mix('/css/app.css') }}" rel="stylesheet" type="text/css"> --}}
    @vite(['resources/assets/sass/app.scss'])
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i" rel="stylesheet">
    <script>
        let userInfo = {
            id: {!! auth()->user()->id !!},
            name: "{!! auth()->user()->name !!}",
            pharmacy_id: "{!! auth()->user()->pharmacy_id !!}",
            surname: "{!! auth()->user()->surname !!}",
            role: "{!! auth()->user()->role !!}",
            token: "{!! auth()->user()->token !!}"
        }
    </script>
</head>

<body>
    <div id="app">
        <app-layout></app-layout>
    </div>
    <!-- Scripts -->
    <script type="text/javascript" src="mdb/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="mdb/js/jquery-ui.js"></script>
    <!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
    {{-- <script type="text/javascript" src="{{ mix('/js/app.js') }}"></script> --}}
    @vite(['resources/assets/js/app.js'])
    <!--
        <script type="text/javascript" src="mdb/js/popper.min.js"></script>
        <script type="text/javascript" src="mdb/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="mdb/js/mdb.min.js"></script>
        <script type="text/javascript" src="/js/d3.min.js"></script>
        <script type="text/javascript" src="/js/topojson.min.js"></script>
        <script type="text/javascript" src="/js/datamaps.world.min.js"></script> -->
</body>

</html>
