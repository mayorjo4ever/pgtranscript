<?php

use Illuminate\Support\Facades\Session;

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <x-admin.transcriptcss></x-admin.transcriptcss>
        <title> @yield('page_title') </title>
    </head>

   <body class="hold-transition skin-blue sidebar-mini" style="font-family:Tahoma;">

        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

             @yield('content')

         </main>
        <x-admin.transcriptjs></x-admin.transcriptjs>
    </body>
</html>