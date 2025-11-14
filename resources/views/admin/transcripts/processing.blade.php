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
                                <h6 class="card-title text-uppercase"> Request Information </h6>
                                <table class="table text-dark">
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
                                        <td>{{$request->degree_awarded}}  </td>
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
                                        <td style="">{{$request->receiving_body_email}}  </td>
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
                                   <div class="col-md-12">
                                       <x-admin.card >
                                            <h6 class="text-uppercase">REQUEST :: &nbsp; {{$request->request_purpose}}&nbsp;{{$request->request_type}} </h6>
                                            <?php $preUrl = "https://login.remita.net/remita/exapp/api/v1/send/api/print/billsvc/biller/".$request->rrr."/printrecieptRequest.pdf"; ?>
                                            <a href="{{ $preUrl }}" target="_blank"  class="btn btn-primary btn-lg" > Download Receipt &nbsp; <i class="fa fa-print"></i> </a>
                                            <!--<a href="{{empty($request->rrr_receipt_url)?"https://login.remita.net/remita/auto-receipt/receipt.reg":$request->rrr_receipt_url}}" target="_blank"  class="btn btn-primary btn-lg" > Download Receipt &nbsp; <i class="fa fa-print"></i> </a>-->
                                            <a href="{{"https://login.remita.net/remita/onepage/biller/".$request->rrr."/payment.spa"}}" target="_blank"  class="btn btn-primary btn-lg" > Verify Receipt &nbsp; <i class="fa fa-print"></i> </a>
                                            <a href="{{$request->certificate_url}}" target="_blank" class="btn btn-primary btn-lg" > Print Certificate  &nbsp; <i class="fa fa-print"></i> </a>
                                            @if($request->courier_receipt_url !="")
                                                 <a href="{{$request->courier_receipt_url}}" target="_blank" class="btn btn-primary btn-lg" > Print Courier Waybill  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif

                                            @if($request->pgschool_receipt_url !="")
                                                 <a href="{{$request->pgschool_receipt_url}}" target="_blank" class="btn btn-primary btn-lg" > Print PG Receipt  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif
                                            @if($request->applicant_dob_cert !="")
                                                 <a href="{{$request->applicant_dob_cert}}" target="_blank" class="btn btn-primary btn-lg" > DOB Cert  &nbsp; <i class="fa fa-print"></i> </a>
                                            @endif
                                        </x-admin.card>
                                   </div><!-- col-md-12 -->
                                   
                                   <div class="col-md-12">
                                       <x-admin.card >   <form method="post" onsubmit="search_my_transcript()" action="javascript:void(0)" >@csrf
                                               <h6 class="text-uppercase">Masters - PGD. Transcript </h6>
                                           <div class="input-group"> 
                                               <input  value="{{strtoupper($request->regno)}}" type="text" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="regno" id="regno" style="font-size:1rem" />
                                               <input value="{{$request->id}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_id" id="request_id" style="font-size:1rem" />
                                               <input value="{{$request->request_purpose}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_type" id="request_type" style="font-size:1rem" />
                                               <button type="submit" class="btn btn-info btn-lg ladda-button " data-style="expand-right"> Search </button>
                                        </div>
                                             </form>
                                            <p>&nbsp;</p>
                                            <div class="search-result"></div>
                                          
                                    </x-admin.card>
                                   </div><!-- col.md-12 -->
                                   
                                   <!-- phd transcript  -->
                                   <div class="col-md-12">
                                       <x-admin.card >   <form method="post" onsubmit="search_my_phd_transcript()" action="javascript:void(0)" >@csrf
                                               <h6 class="text-uppercase">Ph.D Transcript </h6>
                                           <div class="input-group"> 
                                               <input  value="{{strtoupper($request->regno)}}" type="text" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="regno" id="regno" style="font-size:1rem" />
                                               <input value="{{$request->id}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_id" id="request_id" style="font-size:1rem" />
                                               <input value="{{$request->request_purpose}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_type" id="request_type" style="font-size:1rem" />
                                               <button type="submit" class="btn btn-success btn-lg ladda-button " data-style="expand-right"> Search </button>
                                        </div>
                                             </form>
                                            <p>&nbsp;</p>
                                            <div class="search-phd-result"></div>
                                          
                                    </x-admin.card>
                                   </div><!-- col.md-12 -->
                                   
                               </div><!-- row -->
                            </div><!-- col-md-5 -->
                           
                        
                    </div>
                    
                 
                    
                </x-admin.card>
           </div>
       </div>
 
 </div>
@endsection