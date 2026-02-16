@extends('layouts.admin_layout')
@section('page_title') Admin Dashboard @endsection
@section('content')

    <x-admin.dashboard 
        :active-admins="$activeAdmins" 
        :inactive-admins="$inactiveAdmins" 
    />
   @push('script')
    <script>
        setInterval(function() {
            fetch('/admin/activity-data')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#active-count').innerText = data.active;
                });
        }, 20000);
        
    </script>
    @endpush
@endsection