<?php use Illuminate\Support\Facades\Session; ?>
<div>
   <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{asset('img/apple-icon.png')}}">
  <link rel="icon" type="image/png" href="{{asset('img/favicon.png')}}">
  <!--     Fonts and icons     -->
 
<link href="{{ asset('./fonts/material-icon-round.css') }}" rel="stylesheet">
<link href="{{ asset('./fontawesome/css/all.min.css') }}" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="{{asset('css/material-dashboard.css?v=3.1.0')}}" rel="stylesheet" />
 <link rel="stylesheet" href="{{ asset('css/notyf.min.css')}}">

 <link rel="stylesheet" href="{{asset('css/ladda-themeless.css')}}"> 
 <link rel="stylesheet" href="{{asset('css/dataTables.bootstrap4.min.css')}}">  
 <link rel="stylesheet" href="{{asset('css/select2.min.css')}}"> 
 <link rel="stylesheet" href="{{ asset('css/notyf.min.css')}}">
 <link rel="stylesheet" href="{{ asset('css/flatpickr.min.css')}}">
 <link rel="stylesheet" href="{{asset('css/ladda-themeless.css')}}"> 
 
  
@if(in_array(Session::get('tab'),["upload_certs_data","import_users","general_page","upload-courses"])) 
<link rel="stylesheet" href="{{url('css/dropzone5.9.css')}}"> 
@endif
 
</div>