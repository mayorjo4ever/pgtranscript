<?php use Carbon\Carbon; 
use Illuminate\Support\Facades\Http; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Students @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

 <x-admin.alert></x-admin.alert>
 
<div class="container-fluid py-4">
       <div class="row">
           <div class="col-md-12">  
                <x-admin.card header="{{$page_info['title']}}">
                     <div class="row">                                
                       <div class="col-md-8"> 
                           <button class="btn btn-dark btn-lg p-3 sync_id_card_request" type="submit">Refresh &nbsp; <i class="fa fa-refresh" style="font-size: 18px;"></i> </button>
                           &nbsp; &nbsp; 
                           <span style="font-size:2rem;" class="counts text-xl font-24 font-weight-bold">0</span>
                           <span class="response text-lg font-weight-bold"> New Request Found </span>
                            
                           <button style="display: none" class="btn btn-primary import_id_card_request"> Import <i class="material-icons">download</i> </button>
                          <input type="hidden" class="form-control" name="counts" id="counts" />
                          
                        </div>
                     </div>
                         
                </x-admin.card>
               
               <x-admin.card >
                     <div class="row">                                
                       <div class="col-md-12"> 
                           <p class="table-light h4 p-2 text-dark text-justify-center">RECENT ID CARD REQUESTS  &nbsp; &nbsp; 
                               <button id="downloadSelected" class="btn btn-dark p-3"> Download Passports & Signatures </button>
                           </p>
                           <!--<form method="POST" action="{{-- route('idcards.fetch.files') --}}">-->
                            @csrf

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><div class="form-check form-switch d-flex align-items-center mb-3">
                                                <input onchange="" class="form-check-input" type="checkbox" id="checkAll"  >
                                               <label class="form-check-label mb-0 ms-3" for="checkAll"></label>
                                             </div></th>
                                        <th>PSP & SIGN</th>
                                        <th>Reg No</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($records as $record)
                                    <tr>
                                        <td>
                                            
                                            {{ $loop->iteration + ($records->currentPage() - 1) * $records->perPage() }}
                                            &nbsp; &nbsp; 
                                            
                                            <div class="form-check form-switch d-flex align-items-center mb-3">
                                                <input 
                                                    data-passport='@json($record->passport)'
                                                    data-signature='@json($record->signature)'
                                                    class="form-check-input record-checkbox" type="checkbox" id="id-card-{{$record['id']}}"  name="selected[]" value="{{ $record->id }}" >
                                               <label class="form-check-label mb-0 ms-3" for="id-card-{{$record['id']}}"></label>
                                             </div>
                                        </td>
                                        <td> 
                                            <a target="_blank" class="btn  btn-secondary" href="{{ $record->passport }}"><i class="fa fa-download" style="font-size:18px;"></i> &nbsp; Passport</a>
                                            &nbsp; &nbsp; <br/>
                                            <a target="_blank"  class="btn btn-secondary"  href="{{ $record->signature }}"><i class="fa fa-download" style="font-size:18px;"></i> &nbsp; Signature</a>
                                         </td>
                                        
                                        
                                        <td>{{ $record->entry_session  }} Set <br/>{{ $record->regno }} <br/> 
                                            {{ $record->fullname }}<br/>
                                            {{ \Carbon\Carbon::parse(excelDate($record['request_time']))->diffForHumans() ?? " --:--"}}
                                        <br/> <span class="material-icons pb-2 mb-2">alarm</span>&nbsp; <span class="mt-0 pt-0">{{$record['request_time']}}</span>
                                      
                                        </td>
                                        
                                        
                                        <td>{{ $record->degree }}<br/>{{ $record->programme }} 
                                        <br/> {{ $record->faculty }}<br/> {{ $record->department }} 
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            
                        <!--</form>-->

                       </div>
                     </div>
                         
                   {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $records->links('vendor.pagination.material') }}
                    </div>  
                   
                </x-admin.card>
               
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      
       
</div>

  
@endsection