<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Certificate @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12"> <?php $current_approval = get_current_approve_date(); ?>
                <x-admin.card header=" Search Student Transcript">
                    <form method="post"  action="javascript:void(0)" onsubmit="search_general_transcript($(this))"> @csrf
                    <table class="table">                         
                        <tbody>
                            <tr>                                
                                <th class="bg-gray font-bold h3 w-50"> 
                                    <input type="text" name="regno" id="regno" class="form-control border border-dark p-3 font-24"  placeholder="Search ( Name, Regno, e.t.c ) " style="font-size:1.2rem" />
                                </th>
                                <th class="bg-gray w-30"> <button type="submit" class="btn btn-primary p-3"> Search &nbsp; <i class="material-icons md-24 opacity-10">search</i> </button> </th>
                            </tr>
                        </tbody>
                    </table>                  
                     </form>
                     
                    <div class="search-result mt-3 bg-gray"></div>
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row --> 
       
</div> 
@endsection