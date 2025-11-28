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
                                    <input type="text" name="regno" id="regno" class="form-control border border-dark p-3 " style="font-size:1.1rem"  placeholder="Search ( Name, Regno, e.t.c ) " style="font-size:1.1rem" />
                                </th>
                                <th class="bg-gray w-50"> 
                                    <button type="submit" class="btn btn-primary p-3"> Search &nbsp; <i class="material-icons md-24 opacity-10">search</i> </button> 
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#add_new_graduate"  class="btn btn-info p-3"> Add New Student &nbsp; <i class="material-icons md-24 opacity-10">add</i> </button> 
                                
                                </th>
                            </tr>
                        </tbody>
                    </table>                  
                     </form>
                     
                    <div class="search-result mt-3 bg-gray"></div>
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
             
       </div><!-- ./ row --> 
       
       <x-admin.modal id="add_new_graduate" title="Add New Student" size="md">       
           <form method="post" id="new_student_form">@csrf
           <div class="row m-1 p-1"> 
               <div class="col-sm-12 mb-3">                   
                   <input placeholder="Matric / Regno" type="text" class="form-control border border-dark p-3 " style="font-size:1.1rem" name="regno" />
               </div>
               
               <div class="col-sm-12 mb-3">                   
                   <input placeholder="Surname, Other Names" type="text" class="form-control border border-dark p-3" style="font-size:1.1rem" name="fullname" />
               </div>
               
               <div class="col-sm-12 mb-3">                   
                   <input placeholder="Approval Date" type="text" class="form-control border border-dark datepicker p-3" style="font-size:1.2rem" name="approve_date" />
               </div>
               
                <div class="col-sm-12 mb-3">                  
                   <select style="width: 100%; font-size:1.2rem" class="form-control border border-dark p-3" name="programme">
                       <option value="">Programme</option>
                       @foreach($programmes as $programme)
                       <option value="{{$programme->id}}">{{formatProgrammeName($programme->degree->short_name ." ". $programme->name)}}</option>
                       @endforeach
                   </select>
               </div>
               
           </div> <!-- ./ row -->
                
                <x-slot name="footer"> <span class="ajaxLoader"></span> &nbsp;                                     
                    <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal"> Close </button>
                    <button type="button" onclick="AddNewStudent()" class="btn btn-primary new-student-btn ladda-button" data-style="expand-right"> Add New Student </button>

                </x-slot>
           </form>
        </x-admin.modal>

       
</div> 
@endsection