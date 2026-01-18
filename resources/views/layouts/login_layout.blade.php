<!DOCTYPE html>
<html lang="en">
    <head>       
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <x-admin.cssloader></x-admin.cssloader>
        <title> Administrator Login   </title>
    </head>
    <body class="bg-gradient-secondary">
        <!--style="background-image: url('{{ asset('img/logo.png') }}'); background-repeat: repeat;"-->
        @yield('content')
        
        <x-admin.jsloader></x-admin.jsloader>
        
    </body>
</html>