<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Email @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">
                <x-admin.card header="Send General Email (Official / Student)">
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
                     
                    <div class="search-result mt-3 bg-gray">
                        <div class="col-md-6">
                                       <x-admin.card >   <form method="post" action="{{url('admin/send-transcript-mail')}}" enctype="multipart/form-data" >@csrf
                                               <h6 class="text-uppercase"> 
                                                   <span class=" text-danger">Sending of Request</span>&nbsp; &nbsp; 
                                                   <span class="text-mutted">- Status :  {{($request->request_status=="Treated")?"Ready To Send Email" : $request->request_status }}  </span> 
                                             </h6>    
                                           
                                            @if($request->request_status == "Sent")
                                             <h6 class="text-capitalize"> 
                                                   <span class="text-success">sent by: </span> &nbsp;&nbsp; 
                                                   <span class="text-mutted">{{ $request->sent_by }} </span><br/>
                                                   <span class="text-success">Time Sent: </span> &nbsp;&nbsp; 
                                                   <span class="text-mutted text-capitalize">{{ Carbon::parse($request->date_sent)->diffForHumans()}} - <small>{{ Carbon::parse($request->date_sent)->toDayDateTimeString()}}</small></span><br/>
                                                   <span class="text-success">Destination Email: </span> &nbsp;&nbsp; 
                                                   <span class="text-mutted text-lowercase">{{ $request->last_sent_email }} </span><br/>
                                                   <span class="text-success">Total Sent: </span> &nbsp;&nbsp; 
                                                   <span class="text-mutted">{{ $request->sent_count }} </span><br/>
                                             </h6>    
                                            @endif
                                            
                                            @php $disableReSend = ($request->sent_count > 0 )? "disable=''" : ""; @endphp 
                                            
                                            <input type="hidden" value="{{$request->id}}" name="request_id"/>
                                            <input type="hidden" value="{{$request->sent_count}}" name="total_sent"/>
                                            
                                           <div class="form-group mb-3"> 
                                               <label>Receiving Body Email : </label>
                                               <input {{$disableReSend}} value="{{strtolower($request->receiving_body_email)}}" type="text" class="form-control transcript-email border border-1 border-dark form-control-lg" name="destination_email" id="destination-email" style="font-size:1rem;  height:45px;" />                                              
                                            </div>
                                           <div class="form-group mb-3"> 
                                               <label>CC: (optional) </label>
                                               <input {{$disableReSend}} value="" type="text" class="form-control transcript-email border border-1 border-dark form-control-lg" name="cc" id="cc-email" style="font-size:1rem;  height:45px;" placeholder="cc@example.com | leave blank" />                                              
                                            </div>
                                            
                                           <div class="form-group mb-3"> 
                                               <label>BC: (optional) </label>
                                               <input {{$disableReSend}} value="{{strtolower($request->applicant_email)}}" type="text" class="form-control transcript-email border border-1 border-dark form-control-lg" name="bcc" id="bc-email" style="font-size:1rem;  height:45px;"  placeholder="bcc@example.com | leave blank"  />                                              
                                            </div>
                                            
                                            
                                           <div class="form-group mb-3"> 
                                               <label>Message Title: </label>
                                               <input {{$disableReSend}} value="POSTGRADUATE ACADEMIC TRANSCRIPT FOR : {{surname($request->name).", ". othername($request->name)}}" type="text" class="form-control transcript-email border border-1 border-dark form-control-lg" name="message_title" id="message-title" style="font-size:1rem;  height:45px;" /> 
                                            </div>
                                           <div class="form-group mb-3"> 
                                               <label>Message Body: </label>
                                               <?php $message = "Dear, Kindly find the attached for your Information"; ?>
                                               @if($request->request_type == "TRANSCRIPT" && str_replace(" ","",$request->request_purpose) == "OFFICIAL")
                                               <?php $message = "Kindly find attached Signed and Scanned Academic Transcript for ". surname($request->name).", ". othername($request->name).", With Matriculation Number : ".$request->report_regno.", Awarded The Degree of  ".formatProgrammeName($request->programme). "  To Your Institution / Establishment ";  ?>
                                               @elseif($request->request_type == "TRANSCRIPT" && str_replace(" ","",$request->request_purpose) == "STUDENT")
                                               <?php $message = "Dear ".surname($request->name).", ". othername($request->name)."!  Attached below is your Academic Transcript for ( ".formatProgrammeName($request->programme). " ) As Requested ";  ?>
                                               @endif
                                               <textarea {{$disableReSend}} name="message_body" id="message-body" style="font-size:1rem;" class="form-control transcript-email form-control-lg border border-1 border-dark" rows="5">{{$message}}</textarea>
                                            </div>
                                           
                                           <div class="form-group mb-3"> 
                                               <label>Attachments  &nbsp; <i class="fa fa-clipboard" style="font-size:20px"></i></label>
                                               <input {{$disableReSend}} required="" type="file"  name="attachments[]" multiple="" style="font-size:1rem;" class="form-control transcript-email form-control-lg border border-1 border-dark"  />
                                            </div>
                                           
                                            <div class="form-group mb-3">                                               
                                                <button {{$disableReSend}} type="submit" class="btn btn-info btn-lg transcript-email"> Send &nbsp; <i class="fa fa-envelope-circle-check fa-2x"></i></button>
                                                &nbsp; &nbsp; 
                                                @if($request->request_status=="Sent") 
                                                <div class="form-check form-switch d-flex align-items-center mb-3">
                                                    <input onchange="toggle_email_resend($(this))" class="form-check-input" type="checkbox" id="resend-transcript-email" >
                                                   <label class="form-check-label mb-0 ms-3" for="resend-transcript-email">Resend This Email Again ? </label>
                                                </div>
                                                @endif
                                            </div>
                                           
                                           </form>
                                             
                                    </x-admin.card>
                                   </div><!-- col.md-6 -->
                        
                    </div>
                    
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