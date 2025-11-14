<!DOCTYPE html>
<html lang="en">
    <head>       
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <x-admin.cssloader></x-admin.cssloader>
        <title> Administrator Login   </title>
    </head>
    
    <body> 
        
        @yield('content')
        
        <x-admin.jsloader></x-admin.jsloader>
    </body>
</html>