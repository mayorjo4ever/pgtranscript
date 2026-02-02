<?php use Illuminate\Support\Facades\Session; use Carbon\Carbon;  ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title')Pending Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
     
      <x-admin.alert></x-admin.alert>
     
 <div class="row">
        <div class="col-12">
            <x-admin.card header="Transcript Requests">
                 <div class="row mb-4 mt-0"> 
                    <div class="col-md-6">
                        <form method="post" action="{{url('admin/pending-transcript-requests')}}">@csrf
                            <label class="font-weight-bold">Search With Students Name, Matric, RRR etc. </label>
                        <div class="input-group p-2 pt-0  m-2 mt-0">
                            <input type="text" name="search" class="form-control-lg  p-2 font-weight-bold w-75 col-md-4"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem" placeholder="Search Student Matric / Name " style="height: 45px;"/>
                            <button type="submit" class="btn btn-md btn-info p-2" style="height: 45px;">Search &nbsp; </button>
                        </div>
                        </form>   
                    </div> 
                     <div class="col-md-6">
                        <form method="post" action="{{url('admin/pending-transcript-requests')}}">@csrf                           
                            <label class="font-weight-bold">Filter By Date of Application: </label>
                            <div class="input-group  pt-0  m-2 mt-0">                            
                            <input type="text" name="datefrom" value="{{  Session::get('datefrom') ??  Carbon::now()}}" class="form-control datepicker border border-dark border-1 p-2 font-weight-bold w-35 col-md-3"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem; border-radius: 15px; height: 45px;" placeholder="Search From " />
                            <input type="text" name="dateto" value="{{Session::get('dateto') ?? Carbon::now()}}" class="form-control datepicker border border-dark  border-1 p-2 font-weight-bold w-35 col-md-3"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem;  border-radius: 15px; height: 45px;" placeholder="To " />
                            <button type="submit" class="btn btn-info p-2"  style="height: 45px;">Search &nbsp; </button>
                        </div>                           
                    </div>  
                     <div class="col-md-6 ">
                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_show_only_pg_apps($(this))" class="form-check-input" type="checkbox" id="show-only-pg-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="show-only-pg-apps">SHOW ONLY PG REQUESTS</label>
                          </div>
                     </div>  
                     <div class="col-md-6 "> 
                         <div class="form-check form-switch d-flex align-items-center mb-3 mt-3">
                             <input onchange="toggle_hide_copleted_apps($(this))" class="form-check-input" type="checkbox" id="hide-completed-apps" checked >
                            <label class="form-check-label mb-0 ms-3 font-weight-bold" for="hide-completed-apps">HIDE COMPLETED REQUESTS</label>
                          </div>
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
                  @foreach($pendings as $pending)
                  <tr id="{{$pending['id']}}" data-body="{{$pending['bodies']}}" class="{{($pending['bodies']=="undergraduate")?"table-danger":""}}  {{($pending['request_status']=="Sent")?"table-success":""}} {{($pending['request_status']=="Duplicate")?"table-warning":""}} {{($pending['request_status']=="No-Payment")?"table-danger":""}} m-4 ">
                        <td class="align-middle text-center"> {{ $loop->iteration + ($pendings->currentPage() - 1) * $pendings->perPage() }}</td>
                      <td>
                        <div class="d-flex px-2 py-1">                        
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><strong class="text-lg">{{$pending['surname']." ".$pending['middle_name']}} </strong></h6>
                            <p class="text-xs text-dark mb-0">{{$pending['applicant_email']}} <br/> <strong class="text-lg">{{$pending['regno']}} </strong> 
                                &nbsp;&nbsp; {{ \Carbon\Carbon::parse($pending['request_time'])->diffForHumans() ?? " --:--"}}
                                <br/> <span class="material-icons pb-2 mb-2">alarm</span>&nbsp; <span class="mt-0 pt-0">{{$pending['request_time']}}</span>
                            </p>
                          </div>
                        </div>
                          
                          <div class="progress-wrapper">
                                <span class="fa fa-spinner fa-spin"></span>
                                <div class="progress"  style="height:15px">
                                    <div class="progress-bar" style="width:{{$pending['progression']}}%;  height:100%;"></div>
                                </div>
                                <span class="progress-text font-weight-bold">{{$pending['progression']}}%</span>
                            </div>
                          
                      </td>
                                            
                      <td>
                        <p class="text-md font-weight-bold mb-0">{{$pending['request_purpose']}} - {{$pending['request_type']}}</p>                       
                        <span class="text-secondary text-xs font-weight-bold"> {{$pending['degree_awarded']}} </span><br/>
                        <span class="text-secondary text-xs font-weight-bold"> From : &nbsp; {{$pending['year_of_entry']}}   &nbsp;To &nbsp; {{$pending['year_of_graduation']}} </span><br/>                                                
                        <span class="font-weight-bold text-xs">RRR: {{$pending['rrr']}}</span> <br/>
                        <!-- indicate maybe it's PG or underG-->
                         <div class="form-check form-switch d-flex align-items-center mb-3">
                             <input onchange="update_transcript_request_body($(this))" class="form-check-input" type="checkbox" id="transcript-body-{{$pending['id']}}" @if($pending['bodies']=='postgraduate') checked @endif >
                            <label class="form-check-label mb-0 ms-3" for="transcript-body-{{$pending['id']}}">PG TRANSCRIPT </label>
                          </div>
                      </td>
                      
                      <td class="align-middle text-sm-right text-sm">
                          @php $url = base64_encode($pending->id."|".$pending->regno);  @endphp
                          <a href="{{url('admin/process-transcript-requests/'.$url)}}" target="_blank" class="btn {{($pending['request_status']=='Sent')?'btn-success':'btn-primary'}} p-3"> @if($pending['request_status']=="created") Start Process @elseif($pending['request_status']=="Treated") Send e-Mail  @else {{ $pending['request_status']}} @endif </a>    
                          <br/>
                          {{ $pending->id."|".$pending->regno}}
                          <br/>
                          <span class="text-grey">Last View: <strong>{{strtoupper($pending['last_viewer'])}}</strong></span>   @if($pending['last_viewed']!="")  <br/> {{\Carbon\Carbon::parse($pending['last_viewed'])->diffForHumans()}} @else --:-- @endif
                      </td>                                          
                    </tr> 
                    
                    @endforeach
                    
                  </tbody>
                </table>
              </div>
                
                  {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $pendings->links('vendor.pagination.material') }}
                    </div>                     
            </x-admin.card>                    
            
        </div>
      </div>
 </div>
@endsection