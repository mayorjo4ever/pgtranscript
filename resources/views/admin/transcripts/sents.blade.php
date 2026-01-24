<?php use Illuminate\Support\Facades\Session; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') Completed Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
       
 <div class="row">
        <div class="col-12">
            <x-admin.card header="Sent Transcript Requests">
                 <div class="row mb-4 mt-0"> 
                    <div class="col-md-7">
                        <form method="post" action="{{url('admin/sent-transcript-requests')}}">@csrf
                        <div class="input-group p-3 pt-0  m-3 mt-0">
                            <input type="text" name="search" class="form-control-lg  p-3 font-weight-bold w-75 col-md-4"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem" placeholder="Search Student Matric / Name " />
                            <button type="submit" class="btn btn-lg btn-info p-3">Search &nbsp; </button>
                        </div>
                        </form>    
                    </div>  
                     <div class="col-md-4 ">
<!--                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_show_only_pg_apps($(this))" class="form-check-input" type="checkbox" id="show-only-pg-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="show-only-pg-apps">SHOW ONLY PG REQUESTS</label>
                          </div>-->
                         
<!--                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_hide_copleted_apps($(this))" class="form-check-input" type="checkbox" id="hide-completed-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="hide-completed-apps">HIDE COMPLETED REQUESTS</label>
                          </div>-->
                     </div>
                     
                   </div>
                
                
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
                  @foreach($sents as $sent)
                  <tr id="{{$sent['id']}}" data-body="{{$sent['bodies']}}" class="{{($sent['bodies']=="undergraduate")?"table-danger":""}}  {{($sent['request_status']=="Sent")?"table-success":""}} {{($sent['request_status']=="Duplicate")?"table-warning":""}} {{($sent['request_status']=="No-Payment")?"table-danger":""}} m-4 ">
                        <td class="align-middle text-center"> {{ $loop->iteration + ($sents->currentPage() - 1) * $sents->perPage() }}</td>
                      <td>
                        <div class="d-flex px-2 py-1">                        
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><strong class="text-lg">{{$sent['surname']." ".$sent['middle_name']}} </strong></h6>
                            <p class="text-xs text-dark mb-0">{{$sent['applicant_email']}} <br/> <strong class="text-lg">{{$sent['regno']}} </strong> 
                                <br/> Request Time:  {{ \Carbon\Carbon::parse($sent['request_time'])->diffForHumans() ?? " --:--"}}  &nbsp; | &nbsp; {{$sent['request_time']}}
                                <br/>Last Updates :{{ \Carbon\Carbon::parse($sent['updated_at'])->diffForHumans() ?? " --:--"}}&nbsp; | &nbsp; {{$sent['updated_at']}}                               
                                <br/> By : 
                            </p>
                          </div>
                        </div>
                          
<!--                          <div class="progress-wrapper">
                                <span class="fa fa-spinner fa-spin"></span>
                                <div class="progress"  style="height:15px">
                                    <div class="progress-bar" style="width:{{$sent['progression']}}%;  height:100%;"></div>
                                </div>
                                <span class="progress-text font-weight-bold">{{$sent['progression']}}%</span>
                            </div>-->
                      </td>
                                            
                                            
                      <td>
                        <p class="text-md font-weight-bold mb-0">{{$sent['request_purpose']}} - {{$sent['request_type']}}</p>                       
                        <span class="text-secondary text-xs font-weight-bold"> {{$sent->degree_awarded}} </span><br/>     
                         @if(!empty($sent->printout))
                          <?php $url = $sent->printout->regno."|".$sent->printout->approve_date ??'';
                          $url .= "|".$sent->printout->id; ?>
                         {{-- $sent->printout->id."|".$sent->printout->regno."|".$sent->printout->approve_date ??''--}}
                             <a href="{{url('admin/print-transcript/'.base64_encode($url))}}" target="_blank" class="btn {{ ($sent->printout->print_count >0)?"btn-light":"btn-primary"}} "> PRINT {{ $sent->printout->type.' Transcript ' }}   [ {{ $sent->printout->print_count }} ]</a>
                          @endif
                          
                           @if(!empty($sent->cover_letter))  
                           <br/>
                            <?php $memo_url = base64_encode($sent->cover_letter->regno."|".$sent->cover_letter->id); ?>
                             &nbsp; &nbsp; <a href="{{url('admin/print-memo/'.$memo_url)}}" target="_blank" class="btn {{ ($sent->cover_letter->print_count >0)?"btn-light":"btn-primary"}} "> PRINT Covering  Memo  [ {{ $sent->cover_letter->print_count }} ]</a>
                           @endif

                      </td>
                      
                      <td class="align-middle text-sm-right text-sm">
                          @php $url = base64_encode($sent->id."|".$sent->regno);  @endphp
                          <a href="{{url('admin/process-transcript-requests/'.$url)}}" target="_blank" class="btn {{($sent['request_status']=='Sent')?'btn-success':'btn-primary'}} p-3"> @if($sent['request_status']=="created") Start Process @elseif($sent['request_status']=="Treated") Send e-Mail  @else {{ $sent['request_status']}} @endif </a>    
                          <br/>
                          <span class="text-grey">Last View: <strong>{{strtoupper($sent['last_viewer'])}}</strong></span>   @if($sent['last_viewed']!="")  <br/> {{\Carbon\Carbon::parse($sent['last_viewed'])->diffForHumans()}} @else --:-- @endif
                      </td>                                          
                    </tr> 
                    
                    @endforeach
                    
                  </tbody>
                </table>
              </div>
                
                  {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $sents->links('vendor.pagination.material') }}
                    </div>                     
            </x-admin.card>                    
            
        </div>
      </div>
 </div>
@endsection