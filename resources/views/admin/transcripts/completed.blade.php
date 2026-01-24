<?php use Illuminate\Support\Facades\Session; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') Completed Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
       
 <div class="row">
        <div class="col-12">
            <x-admin.card header="Completed Transcript Requests">
                 <div class="row mb-4 mt-0"> 
                    <div class="col-md-7">
                        <form method="post" action="{{url('admin/completed-transcript-requests')}}">@csrf
                        <div class="input-group p-3 pt-0  m-3 mt-0">
                            <input type="text" name="search" class="form-control-lg  p-3 font-weight-bold w-75 col-md-4"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem" placeholder="Search Student Matric / Name " />
                            <button type="submit" class="btn btn-lg btn-info p-3">Search &nbsp; </button>
                        </div>
                        </form>    
                    </div>  
                     <div class="col-md-4 ">
                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_show_only_pg_apps($(this))" class="form-check-input" type="checkbox" id="show-only-pg-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="show-only-pg-apps">SHOW ONLY PG REQUESTS</label>
                          </div>
                         
                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_hide_copleted_apps($(this))" class="form-check-input" type="checkbox" id="hide-completed-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="hide-completed-apps">HIDE COMPLETED REQUESTS</label>
                          </div>
                     </div>
                     
                   </div>
              
                <button disabled="" id="btn-update-sent-requests" onclick="update_sent_requests()" type="button" class="btn btn-info m-2 font-weight-bold"> update   <span class="counts" style="font-size: 1.2rem">0</span>   request as sent </button>
                
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr class="text-dark font-weight-bold">
                      <th class="text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> S/N </th>
                      <th class="text-uppercase text-secondary text-sm font-weight-bolder opacity-10"> Name </th>
                      <th class="text-uppercase text-secondary text-sm  font-weight-bolder opacity-10 ps-2">Request Type</th>                      
                      <th class="text-center text-uppercase text-secondary text-sm font-weight-bolder opacity-10">Status </th>   
                    </tr>
                  </thead>
                  <tbody>                     
                  @foreach($completeds as $completed)
                  <tr id="{{$completed->form_response_id}}" data-body="{{$completed['bodies']}}" class="{{($completed['bodies']=="undergraduate")?"table-danger":""}}  {{($completed['request_status']=="Sent")?"table-success":""}} {{($completed['request_status']=="Duplicate")?"table-warning":""}} {{($completed['request_status']=="No-Payment")?"table-danger":""}} m-4 ">
                        <td class="align-middle text-center"> {{ $loop->iteration + ($completeds->currentPage() - 1) * $completeds->perPage() }}
                        &nbsp; &nbsp; 
                        <!-- indicate maybe it's been sent or not -->
                        <div class="form-check form-switch d-flex align-items-center mb-3" title="Has It Been Sent ?">
                             <input onchange="compile_sent_transcripts($(this))" class="form-check-input completed-requests" type="checkbox" id="input-{{$completed->form_response_id}}" title="{{$completed->form_response_id}}" value="{{$completed->form_response_id}}">
                            <label class="form-check-label mb-0 ms-3" for="transcript-body-{{$completed['id']}}"></label>
                          </div>
                        </td>
                      <td>
                        <div class="d-flex px-2 py-1">                        
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><strong class="text-lg">{{$completed['surname']." ".$completed['middle_name']}} </strong></h6>
                            <p class="text-xs text-dark mb-0">{{$completed['applicant_email']}} <br/> <strong class="text-lg">{{$completed['regno']}} </strong> 
                                <br/> Request Time:  {{ \Carbon\Carbon::parse($completed['request_time'])->diffForHumans() ?? " --:--"}}  &nbsp; | &nbsp; {{$completed['request_time']}}
                                <br/>Last Updates :{{ \Carbon\Carbon::parse($completed['updated_at'])->diffForHumans() ?? " --:--"}}&nbsp; | &nbsp; {{$completed['updated_at']}}                                  
                            </p>
                          </div>
                        </div>
<!--                          
                          <div class="progress-wrapper">
                                <span class="fa fa-spinner fa-spin"></span>
                                <div class="progress"  style="height:15px">
                                    <div class="progress-bar" style="width:{{$completed['progression']}}%;  height:100%;"></div>
                                </div>
                                <span class="progress-text font-weight-bold">{{$completed['progression']}}%</span>
                            </div>-->
                      </td>
                                            
                      <td>
                        <p class="text-md font-weight-bold mb-0">{{$completed['request_purpose']}} - {{$completed['request_type']}}</p>                       
                        <span class="text-secondary text-xs font-weight-bold"> {{$completed->degree_awarded}} </span><br/>     
                         @if(!empty($completed->printout))
                          <?php $url = $completed->printout->regno."|".$completed->printout->approve_date ??'';
                          $url .= "|".$completed->printout->id; ?>
                         {{-- $completed->printout->id."|".$completed->printout->regno."|".$completed->printout->approve_date ??''--}}
                             <a href="{{url('admin/print-transcript/'.base64_encode($url))}}" target="_blank" class="btn {{ ($completed->printout->print_count >0)?"btn-light":"btn-primary"}} "> PRINT {{ $completed->printout->type.' Transcript ' }}   [ {{ $completed->printout->print_count }} ]</a>
                          @endif
                          
                           @if(!empty($completed->cover_letter))  
                           <br/>
                            <?php $memo_url = base64_encode($completed->cover_letter->regno."|".$completed->cover_letter->id); ?>
                             &nbsp; &nbsp; <a href="{{url('admin/print-memo/'.$memo_url)}}" target="_blank" class="btn {{ ($completed->cover_letter->print_count >0)?"btn-light":"btn-primary"}} "> PRINT Covering  Memo  [ {{ $completed->cover_letter->print_count }} ]</a>
                           @endif

                      </td>
                      
                      <td class="align-middle text-sm-right text-sm">
                          @php $url = base64_encode($completed->id."|".$completed->regno);  @endphp
                          <a href="{{url('admin/process-transcript-requests/'.$url)}}" target="_blank" class="btn {{($completed['request_status']=='Sent')?'btn-success':'btn-primary'}} p-3"> @if($completed['request_status']=="created") Start Process @elseif($completed['request_status']=="Treated") Send e-Mail  @else {{ $completed['request_status']}} @endif </a>    
                          <br/>
                          <span class="text-grey">Last View: <strong>{{strtoupper($completed['last_viewer'])}}</strong></span>   @if($completed['last_viewed']!="")  <br/> {{\Carbon\Carbon::parse($completed['last_viewed'])->diffForHumans()}} @else --:-- @endif
                      </td>                                          
                    </tr> 
                    
                    @endforeach
                    
                  </tbody>
                </table>
              </div>
                
                  {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $completeds->links('vendor.pagination.material') }}
                    </div>                     
            </x-admin.card>                    
            
        </div>
      </div>
 </div>
@endsection