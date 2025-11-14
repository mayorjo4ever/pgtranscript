<?php use Carbon\Carbon; use App\Models\Programme;  ?>
@extends('layouts.print_layout')
@section('bedcrumb') {{$memo->name }} Official Memo @endsection
@section('page_title') Official Memo - {{$memo->name }} @endsection
@section('content')
 			
<style>
        body {
            font-family: "Times New Roman", serif;
            margin: 60px;
            line-height: 1.6;
            font-size: 16px;
        }
        .date-ref {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .address {
            margin-bottom: 25px; font-weight: bold
        }
        .subject {
            font-weight: bold;
            text-decoration: underline;
            margin: 25px 0;
        }
        .signature {
            margin-top: 60px;
        }
        .font-17 {
            font-size:17px;
        }
        .font-18 {
            font-size:18px;
        }
        .font-19 {
            font-size:19px;
        }
        .font-20 {
            font-size:20px;
        }
        .font-17 {
            font-size:22px;
        }
    </style> 
    
    <div class="notify-memo-printed" style="height:240px;">&nbsp;</div>
     <input type="hidden" id="print-id2" value="{{$memo->id}}" />
     
    <div class="date-ref font-18">        
        <div><strong>{{ 'UIL/PGS/147' }}</strong></div>
        <div><strong>{{ \Carbon\Carbon::parse($memo->created_at ?? now())->format('jS F, Y') }}</strong></div>
    </div>
     @if(!empty($memo->wes_ref_no))
     <p class="text-uppercase font-18 mb-3"> <strong>Reference Number: &nbsp; {{$memo->wes_ref_no}}</strong></p>
     @endif
    <div class="address font-18">
        <p class="font-weight-bold">
           {!! $memo->destination_address ?? '' !!}<br>           
        </p>
    </div>

     <p class="font-18"><strong>Dear Sir/Madam,</strong></p>

    <p class="subject font-18">
        RE: REQUEST FOR ACADEMIC POSTGRADUATE TRANSCRIPT  – {{surname($memo->name)}},&nbsp; {{othername($memo->name)}}
        <br> (Matriculation Number: {{ $memo->regno }})
    </p>

    <p class="font-17">
        This is to formally acknowledge your request regarding the 
        academic transcript of the above-named graduate.
    </p> 
    
    <p class="font-17">
        I am directed to inform you that the<strong> Doctoral Programme (Ph.D.) 
            of this University is entirely research-based;</strong> consequently, 
            no academic transcript is issued for the programme.
    </p> 
    
    <p class="font-17">  
        However, I am pleased to confirm that – <strong>{{surname($memo->name)}},&nbsp; {{othername($memo->name)}}</strong>
        with Matriculation Number <strong>{{ $memo->regno }}</strong>, was duly awarded <strong>the Degree of Doctor
        of Philosophy {{rewrite_phd($transcript->programme)}} </strong>  in the Department of {{dept_name($transcript->dept_id)}},
        Faculty of {{fact_name($transcript->fact_id)}} . The award was approved and conferred by the 
        Senate of the University of Ilorin, Ilorin, Nigeria, 
        on {{ Carbon::parse($transcript->approve_date)->format('jS F, Y')}}
            </p> 
    
    <p class="font-17">
        For any further clarification or official correspondence,
        please direct your enquiries to the Secretary, 
        Postgraduate School, via pgschool@unilorin.edu.ng.
        </p>
    <p class="font-17">
        Kindly accept the assurances of the University’s
        highest regards.
    </p>
    
    
    <p class="font-18">Yours faithfully,</p>

    <div class="signature font-20">
        <p><strong>{!! officials_name($memo->sec_id)!!}</strong><br>
        Secretary, Postgraduate School<br>
        For: Registrar</p>
    </div> 
	 
 
@endsection