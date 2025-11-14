<?php   use Carbon\Carbon; use App\Models\Programme;  ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{  " student"  }} @endsection

@section('content')
 <div class="container-fluid py-4">
     
      <x-admin.alert></x-admin.alert>
      
      <div class="row">
           <div class="col-md-12">   
                <x-admin.card header="{{$report->regno}} | {{$report->name}} Official Memo">
                    <form method="post" onsubmit="submit_transcript_request()">@csrf
                    <table class="table table-responsive">       
                        <thead>
                            <tr> <td colspan="2"><strong>Programme</strong>: <span class="text-primary font-weight-bold">   {{$report->programme }} </span>
                                    &nbsp; &nbsp; 
                                    <strong>Approved Date </strong>: <span class="text-primary font-weight-bold">  {{ Carbon::parse($report->approve_date)->format('D, jS F, Y') }}</span>
                                    <input type="hidden" name="regno" id="regno" value="{{$report->regno}}"/>
                                    <input type="hidden" name="approve_date" id="approve_date" value="{{$report->approve_date}}"/>
                                    <input type="hidden" name="request_id" id="approve_date" value="{{$student_request->id}}"/>
                                </td>             
                            </tr>                            
                        </thead>
                        <tbody>                           
                            <tr><td  class="text-uppercase" style="width:30%"> <label class="font-weight-bold mt-3 "> Student Matric Number </label>  </td>
                                <td><input placeholder="REGNO" name="" id="" value="{{$report->regno }}" type="text" class="mt-3 form-control-lg font-weight-bold w-60 text-uppercase" style="font-size: 1rem;" /></td>
                            </tr>                               
                            <tr><td class=""> <label class="font-weight-bold mt-3 "> Student's Name </label>  </td>
                                <td><input placeholder="NAME" name="name" id="stud_name" value="{{$report->name }}" type="text" class="mt-3 form-control-lg font-weight-bold w-90 text-uppercase" style="font-size: 1rem;" /></td>
                            </tr>
                            @if($student_request->reference_number !="")
                               <tr><td class="text-uppercase"> <label class="font-weight-bold mt-3 "> WES REF No:</label>  </td>
                                 <td><input placeholder="NAME" name="wes_ref_no" id="wes_ref_no" value="{{ $student_request->cover_letter->wes_ref_no ?? $student_request->reference_number }}" type="text" class="mt-3 form-control-lg font-weight-bold w-90 text-uppercase" style="font-size: 1rem;" /></td>
                               </tr>
                            @endif
                            <tr><td class="text-uppercase"> <label class="font-weight-bold mt-3 "> Receiving Body </label>  </td>
                                <td><textarea placeholder="RECEIVING BODY " rows="5" name="receiving_body" class="mt-3 form-control-lg font-weight-bold w-90" style="font-size: 1rem;">{{$student_request->cover_letter->destination_address ?? $student_request->destination_address }}</textarea></td>
                            </tr> 
                        </tbody>
                    </table>
                        
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary btn-lg p-3 w-100"> SUBMIT MEMO </button>
                         </div>
                    </div> 
                    
                    <x-admin.card>
                        <div class="row">
                            <div class="col-md-12">
                            </div>
                        </div>
                    </x-admin.card>
               
               </form>
             </x-admin.card>   
           </div> <!--./ col-md-12 --> 
            
       </div><!-- ./ row -->
       
 </div>
@endsection