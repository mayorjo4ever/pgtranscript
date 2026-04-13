<?php use Carbon\Carbon; ?>
@extends('layouts.guest_layout')
@section('bedcrumb') Date Conversion @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">  
                <x-admin.card header=" Date Conversion Tool">
                    <table class="table">                         
                        <tbody>
                            <tr>
                                <th class="bg-gray w-30"> What To Upload :  </th>
                                <th class="bg-gray font-bold h3 text-danger"> 
                                     Excel Dates  </th>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <p class="text-dark text-wrap text-capitalize">
                                        Upload only the Date Column That you want to convert, the 
                                        system will automatically convert the dates to a clean format and make it available for download.                                       
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                     <x-admin.card id="cert-excel" >
                        <p> &nbsp; &nbsp;
                             <img class="img img-thumbnail" src="{{asset('img/data/date-conversion.png')}}" />
                             <img class="img img-thumbnail" src="{{asset('img/data/excel.png')}}" width="80" height="80" />
                         </p>                         
                         <form method="post" action="{{ url('download-clean-dates') }}" 
                                class=""  enctype="multipart/form-data">
                              @csrf
                              <div class="input-group">
                                <input type="file" name="file" accept=".xls,.xlsx" required class="m-2 form-control border border-dark"/>
                                <button type="submit" class="btn btn-primary m-2" btn-lg>Upload</button>
                              </div>
                          </form>
                    </x-admin.card>
                    
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->      
       
</div>

@endsection