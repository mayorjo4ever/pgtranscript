<?php

use Illuminate\Support\Facades\Session;

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <x-admin.cssloader></x-admin.cssloader>
        <title> @yield('page_title') </title>
    </head>

   <body class="g-sidenav-show  bg-gray-300"> 
      <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
         @yield('content')
      </main>
      <x-admin.jsloader></x-admin.jsloader>
    </body>
</html>