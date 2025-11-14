<?php use Carbon\Carbon; use App\Models\Programme;  ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
 
<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">   
                <x-admin.card header="{{$transcript_info['regno']}} | {{$transcript_info['convocation']['raw_name']}} | {{$transcript_info['purpose']}}'s  Transcript ">
                    <form method="post" onsubmit="submit_transcript()">@csrf
                    <table class="table table-responsive">       
                        <thead>
                            <tr> <td><strong>Programme</strong>: {{$transcript_info['convocation']['raw_programme'] }} 
                                    &nbsp; &nbsp; 
                                    <strong>Approved Date </strong>:  {{get_full_approve_date($transcript_info['approve_date_id'])}}
                                    <input type="hidden" name="regno" id="regno" value="{{$transcript_info['regno']}}"/>
                                    @php $approve_date = get_full_approve_date($transcript_info['approve_date_id'],'normal') @endphp
                                    <input type="hidden" name="approve_date" id="approve_date" value="{{$approve_date}}"/>
                                </td>             
                            </tr>                            
                        </thead>
                        <tbody>                           
                            <tr><td><div class="">
                                        <label class="font-weight-bold"> SEARCH COURSES OFFERED </label>
                                        <input placeholder="CODE" name="course_finder" id="course_finder" value="" type="text" class="mt-3 form-control-lg font-weight-bold text-uppercase" style="font-size: 1rem;" />
                                        &nbsp; &nbsp; <span class="loader"></span>
                                    </div>                                         
                                </td>
                            </tr>                               
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="courses-container">
                                <span class="p-5"></span>
                            </div>
                         </div>
                    </div> 
                    
                    <x-admin.card>
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="card-title bg-light p-2">SUMMARY</h6>
                                <div class="transcript-summary"></div>
                            </div>
                        </div>
                    </x-admin.card>
 
                </x-admin.card>
               
               </form>
                    
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      <script>
      $(function(){
          alert('active');
      });    
      </script>
       
</div>

@endsection