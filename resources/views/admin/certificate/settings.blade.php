<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Certificate @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
       
       <div class="row">
           <div class="col-md-6">
                <x-admin.card header="List of Approve Dates">
                    <!-- show tables -->  
                    <table class="table table-bordered table-responsive dataTable">
                        <thead><tr>
                           @php $headers = ['id' => 'SN', 'app_date' => 'Date','cur_date'=>'Current Approval']; 
                           $headers2 = ['id' => 'SN','app_date' => 'Date']; 
                           $rows = $approval_dates->toArray(); @endphp                           
                           @foreach ($headers as $header)
                                <th class="px-4 py-2">{{ $header }}</th>
                            @endforeach
                            </tr>
                        </thead>
                      
                        <tbody class="divide-y divide-gray-100">
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="px-2 py-2">   {{ $row['id'] }}  </td>
                                        <td class="px-2 py-2"> <a data-bs-toggle="modal" data-bs-target="#add_new_approve_date" href="#" onclick="setValues('{{$row['app_date']}}','.approve-date'),setRef('{{$row['id']}}')" > 
                                                {{ Carbon::parse($row['app_date'])->format('D, jS F, Y')}} 
                                        &nbsp; 
                                        <span class="material-icons md-36">edit</span></a>
                                        </td>                                      
                                        <td class="px-2 py-2">
                                             <button  onclick="set_default_cert_approval_date('{{$row['id']}}')" name="cur_date" type="button" class="btn @if($row['cur_date']==1) btn-primary @else btn-secondary @endif ladda-button {{"date_btn_".$row['id']}}" data-style="expand-right">
                                                 @if($row['cur_date']==1) Current @else Set Current @endif   
                                             </button>
                                            </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($headers2) }}" class="text-center px-4 py-3 text-gray-500">
                                            No data available
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                    </table>
                    
                                    
                    <!-- add submit button -->
                    <button onclick="setValues('','.approve-date'),setRef('')" data-bs-toggle="modal" data-bs-target="#add_new_approve_date" type="button" class="mt-3 btn btn-info"> Add New Approve Date </button>
                </x-admin.card>
           </div> <!--./ col-md-6 -->
           
           
           <div class="col-md-6">
                <x-admin.card header="All Degree Programmes">
                    <!-- show tables -->                    
                    <x-admin.table 
                        :headers="['id' => 'SN', 'abbrev' => 'Abbreviation', 'name' => 'Full Name']"
                        :rows="$degrees->toArray()" >                        
                    </x-admin.table>
                    
                    <!-- add submit button -->
                    <button type="button" class="mt-3 btn btn-info"> Add New Approve Date </button>
                </x-admin.card>
           </div> <!--./ col-md-6 -->
           
       </div><!-- ./ row -->
       
       
</div>

<x-admin.modal id="add_new_approve_date" title="Add New / Update Approval Date ">       
        
        <div class="mb-3">
            <!--  <span class="font-weight-bold h6">Initial Date : </span> &nbsp; <span class="approve-date"></span> <br/>-->
            <label for="date" class="mt-1 pt-1 font-weight-bold h6">New Approval Date: </label>
            <input class="p-3 form-control border border-dark datepicker approve-date" name="approve-date" required="" style="font-size: 1.5rem">
            <input type="hidden" class="update-ref" value=""/>
        </div> 
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Cancel</button>
            <button onclick="save_cert_approve_date()" type="submit" class="btn btn-success cert-approve-date-btn ladda-button" data-style="expand-right">Save Date </button>
        </x-slot>
    
</x-admin.modal>

@endsection