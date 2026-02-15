@extends('layouts.admin_layout')
@section('page_title') Admin Dashboard @endsection
@section('content')

    <x-admin.dashboard 
        :active-admins="$activeAdmins" 
        :inactive-admins="$inactiveAdmins" 
    />
    
@endsection