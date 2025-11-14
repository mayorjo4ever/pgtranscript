<?php use Carbon\Carbon; ?>
@extends('layouts.print_layout')
@section('bedcrumb') Certificate @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
<style>
    @media print {
  .table-responsive {
    overflow: visible !important;
  }
}
</style> 
 
 <div class="container-fluid py-4 px-4">
  <div class="row">
      
           <div class="col-md-12">
                <x-admin.card>
                    <p class="text-justify-center text-center bg-white  font-weight-bolder">
                        <span style="font-weight:bold; font-size: 2.5rem;"> UNIVERSITY OF ILORIN, NIGERIA </span><br/>
                        <span style="font-weight:bold; font-size: 2.2rem;"> POSTGRADUATE SCHOOL </span> <br/>
                    <img src="{{asset('img/unilorin.png')}}" height="80" width="150"/>
                    <br/>
                    <span style="font-weight:bold; font-size: 1.8rem;">List of Masters and PGD. Graduands For 2025 Convocation</span> </p>
                    
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Passport</th>
                                <th>Name</th>          
                            </tr>
                        </thead>
                        <tbody> <?php $n=0; $produced = 0; $pending = 0;  ?>
                            @foreach($students as $student) 
                            <?php $n++; ?>
                                <tr>
                                    <th>{{ $n }}</th>
                                    <td>
                                        <img src="{{ $student->passport_path }}" 
                                             alt="Passport" 
                                             width="100" height="100"
                                             style="object-fit: cover; border-radius: 10px;">
                                    </td>
                                    <td><strong style="font-size:1.6rem">{{ smartSwapName($student->name) }}  </strong>
                                        <br/>
                                        <small> {{$student->degree->short_name}} {{ $student->raw_programme ?? $student->programme->name ?? 'N/A' }}
                                              <br/>
                                              {{ $student->regno }} <br/>
                                              Approved :
                                        {{ $student->app_date->app_date ?? '' }} <br/>
                                        Transcript :  @if($student->transcript_produced) @php $produced++; @endphp 
                                                <span class="badge bg-success">Produced</span>
                                                
                                                &nbsp; &nbsp; Printing : &nbsp; 
                                                  @if($student->transcript_printed)
                                                    <span class="badge bg-success">Printed </span>
                                                    @else
                                                  <span class="badge bg-secondary">Not Printed </span>
                                                    @endif
                                                
                                                
                                            @else  @php $pending++; @endphp 
                                                <span class="badge bg-danger">Pending</span>
                                            @endif 
                                            
                                           
                                        </small>   
                                    </td>
                                                                       
                                </tr>
                            @endforeach
                            <tr>
                                 <td>&nbsp;</td>
                                <td></td>
                                <td> Transcript  Produced : <strong> {{$produced}}, </strong>   Pending : <strong>{{$pending}} </strong></td>
                               
                            </tr>
                        </tbody>
                    </table>
                    
                    <p class=" font-weight-bold">                        
                        <img src="{{asset('img/sec-pgs.jpg')}}" width="300" height="150" class="mb-0 pb-0" />
                        <br/>
                        <b>Secretary, Postgraduate School
                        <br/> <br/>Date : {{Carbon::now()->toDateString()}} </b>
                    </p>
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
  </div><!-- ./ row --> 
 </div>
 
@endsection