<?php use Illuminate\Support\Facades\Session; use Carbon\Carbon;  ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{  $request->regno. " - ". $request->surname. "  "  }} @endsection

@section('content')

<style>
    .table {
        width:100%;
        table-layout:fixed;
        border-collapse:collapse;
    }
    .table td, .table th{
        padding: 6px; 
        white-space: normal;
        word-wrap:break-word;
        word-break: break-all;
        overflow-wrap:break-word;
        hyphens: auto;
        max-width: 0; overflow:;
    }
      
         
     </style>

 <div class="container-fluid py-4">
     
      <x-admin.alert></x-admin.alert>
      
       <div class="row">
           <div class="col-md-12">   
                <x-admin.card header="{{ $page_info['title']}}">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <x-admin.card >
                                <h6 class="card-title text-uppercase"> Request Information &nbsp; &nbsp; 
                                    <span class="text-mutted">  - Status :  
                                        {{($request->request_status=="Treated")?"Ready To Send Email" : "" }}  </span> 
                                        {{($request->request_status=="created")?" To Start Processing" : "" }}  </span> 
                                </h6>
                                <table class="table text-dark" style="font-size:14px;">
                                    <tr>
                                        <th style="width:40%;">Name::</th>
                                        <th style="width:60%;">{{$request->surname}}&nbsp;{{$request->middle_name}}</th>
                                    </tr>
                                     <tr>
                                        <th>Matric No::</th>
                                        <th>{{strtoupper($request->regno)}}</th>
                                    </tr>
                                     <tr>
                                        <th>Request Date::</th>
                                        <td>{{$request->request_time}} &nbsp;- &nbsp;  
                                        {{ Carbon::parse($request->request_time)->diffForHumans()}}
                                        </td>
                                    </tr>
                                    <tr class=" @if(count_rrr($request->rrr)>1) table-danger @endif ">
                                        <th>RRR::</th>
                                        <td style="">{{$request->rrr}} : [ <strong>{{count_rrr($request->rrr)}}</strong> ]  </td>
                                    </tr>
                                     <tr>
                                        <th>Applicant Email::</th>
                                        <td>{{$request->applicant_email}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Email Used:: </th>
                                        <td>{{$request->request_email}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Year of Entry:: </th>
                                        <td>{{$request->year_of_entry}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Year of Graduation:: </th>
                                        <td>{{$request->year_of_graduation}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Degree Awarded:: </th>
                                        <td><h5>{{$request->degree_awarded}} </h5> </td>
                                    </tr>
                                     <tr>
                                        <th>Faculty:: </th>
                                        <td>{{$request->faculty}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Department::</th>
                                        <td>{{$request->department}}  </td>
                                    </tr>
                                     <tr>
                                        <th>REQUEST TYPE::</th>
                                        <th>{{$request->request_purpose}}&nbsp;{{$request->request_type}}  </th>
                                    </tr>
                                     <tr>
                                        <th>WES Reference No::</th>
                                        <td>{{$request->reference_number}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Destination Address::</th>
                                        <td style="">{{$request->destination_address}}  </td>
                                    </tr>                                     
                                     <tr>
                                        <th>Mode of Postage::</th>
                                        <td style="">{{$request->mode_of_postage}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Applicant Phone::</th>
                                        <td style="">{{$request->applicant_phone}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Courier Agent::</th>
                                        <td style="">{{$request->courier_agent}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Receiving Body Email::</th>
                                        <td class="text-lowercase">{{$request->receiving_body_email}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Obtained Transcript Before::</th>
                                        <td class="text-uppercase">{{$request->obtained_transcript_before}}  </td>
                                    </tr>
                                     <tr>
                                        <th>Date Obtained::</th>
                                        <td style="">{{$request->date_obtained}}  </td>
                                    </tr>
                                    <tr>
                                        <th>Date of Birth::</th>
                                        <td style="">{{$request->applicant_dob}}  </td>
                                    </tr>
                                      <tr>                                       
                                        <td colspan="2">&nbsp; </td>
                                    </tr>
                                </table>
                            </x-admin.card>
                        </div>
                        
                           <div class="col-md-6  col-sm-12">
                               <div class="row">
                                   <div class="col-md-12" style="font-size:14px;">
                                       <x-admin.card >
                                            <h6 class="text-uppercase">REQUEST ::&nbsp;{{$request->request_purpose}}&nbsp;{{$request->request_type}} </h6>
                                            <?php 
                                                   # $preUrl = "https://login.remita.net/remita/exapp/api/v1/send/api/print/billsvc/biller/".$request->rrr."/printrecieptRequest.pdf";
                                                   $preUrl = "https://login.remita.net/remita/onepage/invoice.spa?rrr=".$request->rrr; 
                                                   #$preUrl = "https://login.remita.net/remita/exapp/api/v1/send/api/print/billsvc/biller/".$request->rrr."/printrecieptRequest.pdf";
                                                    
                                            ?>
                                            <a href="{{ $preUrl }}" target="_blank"  class="btn btn-primary btn-md" > Print Receipt &nbsp; <i class="fa fa-print"></i> </a>
                                            <!--<a href="{{empty($request->rrr_receipt_url)?"https://login.remita.net/remita/auto-receipt/receipt.reg":$request->rrr_receipt_url}}" target="_blank"  class="btn btn-primary btn-md" > Download Receipt &nbsp; <i class="fa fa-print"></i> </a>-->
                                            <a href="{{"https://login.remita.net/remita/onepage/biller/".$request->rrr."/payment.spa"}}" target="_blank"  class="btn btn-primary btn-md" > Verify Receipt &nbsp; <i class="fa fa-print"></i> </a>
                                            <a href="{{$request->certificate_url}}" target="_blank" class="btn btn-primary btn-md" > Print Certificate  &nbsp; <i class="fa fa-print"></i> </a>
                                            @if($request->courier_receipt_url !="")
                                                 <a href="{{$request->courier_receipt_url}}" target="_blank" class="btn btn-primary btn-md" > Print Courier Waybill  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif

                                            @if($request->pgschool_receipt_url !="")
                                                 <a href="{{$request->pgschool_receipt_url}}" target="_blank" class="btn btn-primary btn-md" > Print PG Receipt  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif
                                            @if($request->applicant_dob_cert !="")
                                                 <a href="{{$request->applicant_dob_cert}}" target="_blank" class="btn btn-primary btn-md" > DOB Cert  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif
                                            <hr class="border border-1 border-dashed border-danger"/>
                                               <!-- indicate maybe it has any issues -->
                                                <div class="form-check form-switch d-flex align-items-center mb-4">
                                                    <input onchange="toggle_transcript_issues($(this))" class="form-check-input" type="checkbox" id="transcript-issue" @checked(in_array($request->request_status,['No-Spreadsheet','No-Payment','Duplicate'])) >
                                                    <label class="form-check-label mb-0 ms-3" for="transcript-issue"><b>THIS REQUEST HAVE ISSUES </b></label>
                                                </div>
                                               <hr class="border border-1 border-dashed border-danger"/>
                                               
                                               <div class="transcript-issue-form-body">
                                                   <div class="form-group mb-4 mt-4">
                                                        <div class="radio-wrapper-8 mb-3 mt-3">
                                                            <label class="control-label radio-wrapper-8"  style="font-size: 1rem">
                                                                <input type="radio" value="Duplicate" name="transcript_status" @checked($request->request_status=='Duplicate') />
                                                            <span>Duplicate Request - So Ignore </span></label>
                                                        </div>
                                                       &nbsp; &nbsp; &nbsp; &nbsp; 
                                                       <div class="radio-wrapper-8 mb-3">
                                                        <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                                                            <input class="form-radio" type="radio" value="No-Spreadsheet" name="transcript_status" @checked($request->request_status=='No-Spreadsheet') />
                                                        <span>Call For Spreadsheet - Spreadsheet Not Found</span></label>      
                                                       </div>
                                                       &nbsp; &nbsp; &nbsp; &nbsp; 
                                                       <div class="radio-wrapper-8 mb-3">
                                                        <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                                                            <input class="form-radio" type="radio" value="No-Transcript-Yet" name="transcript_status" @checked($request->request_status=='No-Transcript-Yet') />
                                                        <span>No Transcript Yet - Transcript Not Yet Prepared  </span></label>      
                                                       </div>
                                                       &nbsp; &nbsp; &nbsp; &nbsp; 
                                                       <div class="radio-wrapper-8 mb-3">
                                                        <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                                                            <input class="form-radio" type="radio" value="Course-Code-Issues" name="transcript_status" @checked($request->request_status=='Course-Code-Issues') />
                                                        <span>Course Codes Not Available  </span></label>      
                                                       </div>
                                                       &nbsp; &nbsp; &nbsp; &nbsp; 
                                                       <div class="radio-wrapper-8 mb-3">
                                                        <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                                                            <input class="form-radio" type="radio" value="No-Payment" name="transcript_status" @checked($request->request_status=='No-Payment') />
                                                        <span>No Payment Found - Did Not Pay </span></label>      
                                                       </div>
                                                   </div>   &nbsp; &nbsp; &nbsp; &nbsp; 
                                                   <input type="hidden" id="form_no" name="form_no" value="{{$request->form_response_id}}"/> 
                                                   <button onclick="submit_transcript_request_issue()" type="submit" class="btn btn-info btn-md ladda-button " data-style="expand-right"> Update Status </button>
                                               </div>
                                               
                                               
                                        </x-admin.card>
                                   </div><!-- col-md-12 -->
                                   
                                   <div class="col-md-12">
                                       <x-admin.card >   <form method="post" onsubmit="search_my_transcript()" action="javascript:void(0)" >@csrf
                                               <h6 class="text-uppercase" style="font-size:14px;">Search Masters Or PGD. Transcript </h6>
                                           <div class="input-group"> 
                                               <input  value="{{strtoupper($request->regno)}}" type="text" class="form-control form-control-lg font-weight-bold border border-1 border-dark" name="regno" id="regno" style="font-size:1rem; height:45px; " />
                                               <input value="{{$request->id}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_id" id="request_id" style="font-size:1rem" />
                                               <input value="{{$request->request_purpose}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_type" id="request_type" style="font-size:1rem" />
                                               <button type="submit" class="btn btn-info btn-md ladda-button " data-style="expand-right"> Search </button>
                                            </div>
                                             </form>
                                          
                                            <div class="search-result"></div>
                                          
                                    </x-admin.card>
                                   </div><!-- col.md-12 -->
                                   
                                   <!-- phd transcript  -->
                                   <div class="col-md-12">
                                       <x-admin.card >   <form method="post" onsubmit="search_my_phd_transcript()" action="javascript:void(0)" >@csrf
                                               <h6 class="text-uppercase">Ph.D Transcript </h6>
                                           <div class="input-group"> 
                                               <input  value="{{strtoupper($request->regno)}}" type="text" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="regno" id="regno" style="font-size:1rem;  height:45px;" />
                                               <input value="{{$request->id}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_id" id="request_id" style="font-size:1rem" />
                                               <input value="{{$request->request_purpose}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_type" id="request_type" style="font-size:1rem" />
                                               <button type="submit" class="btn btn-success btn-md ladda-button " data-style="expand-right"> Search </button>
                                        </div>
                                             </form>
                                            
                                            <div class="search-phd-result"></div>
                                          
                                    </x-admin.card>
                                   </div><!-- col.md-12 -->
                                   
                                   @if(in_array($request->request_status,['Treated','Sent']))
                                  
                                   <div class="col-md-12">
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
                                   </div><!-- col.md-12 -->
                                   
                                   @endif 
                                   
                               </div><!-- row -->
                            </div><!-- col-md-5 -->                           
                        
                    </div>
                    
                 
                    
                </x-admin.card>
           </div>
       </div>
 
 </div>
@endsection