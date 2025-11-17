<?php use Illuminate\Support\Facades\Session; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title')Pending Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
     
      <x-admin.alert></x-admin.alert>
     
 <div class="row">
        <div class="col-12">
            <x-admin.card header="Transcript Requests">
                 <div class="form-row mb-4 mt-0"> 
                    <div class="col-md-8">
                        <form method="post" action="{{url('admin/pending-transcript-requests')}}">@csrf
                        <div class="input-group p-3 pt-0  m-3 mt-0">
                            <input type="text" name="search" class="form-control-lg  p-3 font-weight-bold w-75 col-md-4"  value="{{Session::get('transcript_search')}}" style="font-size:1.2rem" placeholder="Search Student Matric / Name " />
                            <button type="submit" class="btn btn-lg btn-info p-3">Search &nbsp; </button>
                        </div>
                        </form>    
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
                  <tr class="{{($pending['request_status']=="Treated")?"table-success":""}} {{($pending['request_status']=="Duplicate")?"table-warning":""}} {{($pending['request_status']=="No-Payment")?"table-danger":""}} ">
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
                      </td>
                      <td>
                        <p class="text-md font-weight-bold mb-0">{{$pending['request_purpose']}} - {{$pending['request_type']}}</p>                       
                        <span class="text-secondary text-xs font-weight-bold"> {{$pending['degree_awarded']}} </span><br/>
                        <span class="text-secondary text-xs font-weight-bold"> From : &nbsp; {{$pending['year_of_entry']}}   &nbsp;To &nbsp; {{$pending['year_of_graduation']}} </span><br/>                                                
                        <span class="font-weight-bold text-xs">RRR: {{$pending['rrr']}}</span> <br/>
                      </td>
                      
                      <td class="align-middle text-sm-right text-sm">
                          @php $url = base64_encode($pending->id."|".$pending->regno);  @endphp
                          <a href="{{url('admin/process-transcript-requests/'.$url)}}" target="_blank" class="btn {{($pending['request_status']=='Treated')?'btn-success':'btn-primary'}} p-3"> {{($pending['request_status']=="created")?"process":$pending['request_status'] }}  </a>                           
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