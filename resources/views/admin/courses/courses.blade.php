<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Courses @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
 
<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">   
                <x-admin.card header=" All Available Coures">
                    
                    <table class="table table-responsive">       
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th style="width:20%">CODE / TITLE</th> 
                                <th>UNITS / TYPE</th>                                   
                                <th>HOST</th>                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                            <tr>   
                                <td>{{ $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage() }}</td>
                                <td>{{$course->code}} <br/> {{$course->title}}</td>
                                <td>{{$course->units}} /  {{$course->type}} / {{$course->level}} 
                                    <br/> {{$course->semester}} Semester </td>                                
                                <td>{{$course->host_department}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    

                    {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $courses->links('vendor.pagination.material') }}
                    </div>
                     
                    
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      
       
</div>

    
    

@endsection