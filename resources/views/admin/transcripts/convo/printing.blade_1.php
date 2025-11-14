<?php use Carbon\Carbon; use App\Models\Programme;  ?>
@extends('layouts.print_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{ $page_info['title']}} @endsection
@section('content')
<style>
    .table {
        color:#000; 
    } 
    p{
         color:#000; 
    }
    table.transcript-table {
   width: 100%;
   border-collapse: collapse;
   }
   .transcript-table {
   font-size: 1rem;    /* shrink font */
   font-family:Tahoma;
   width: 100%;
   table-layout:auto;   /* fixed-  important: prevents auto stretching */
   word-wrap: break-word; /* old support */
   white-space: normal;   /* allow wrapping */
   }
   .transcript-table th {
   color: #000;
   padding: 4px 15px;
   text-align: left;
   vertical-align:middle;
   }
   .transcript-table td {
   color: #000;
   padding: 4px 15px;
   text-align: left;
   vertical-align:middle;
   word-break: break-word;   /* break long words if needed */
   white-space: normal;      /* enable wrapping */
   }
   .vertical-text {
   writing-mode: vertical-rl;  /* modern browsers */
   transform: rotate(360deg);  /* upright instead of upside-down */
   text-align: center;
   vertical-align: middle;
   font-weight: bolder;
   font-size: 1.2rem;
   color: #555;
   white-space: nowrap;
   /* Extra styling for spacing */
   letter-spacing: 12px;   /* gap between characters */
   line-height: 2.2;      /* adjust vertical compactness */
   padding: 5px;
   }
   table.transcript-table tr:last-child td {
   border-bottom: 1px solid #000; /* ensure bottom border */
   }
   .bottom-white {
       border-bottom-color: #fff; 
   }
</style>
<div class="container-fluid py-4 notify-printed" style="background-color:#fff">
   <div class="row">
      <div class="col-md-12">
          <div class="" style="height:290px;"></div> <!-- Berlin Sans FB Regular -->
            <p class="text-center"  style="font-family:Tahoma; font-size:24px; font-weight:bold;">POSTGRADUATE STUDENT’S ACADEMIC TRANSCRIPT</p> 
         <table class="table table-sm text-dark">
             <tbody> <input type="hidden" id="print-id" value="{{$printout->id}}" />
               <tr style="border-bottom:none; border-bottom-color: #fff;">
                  <th  class="">Name:</th>
                  <td style="line-height:1.2"><strong>{{surname($report->name)}}</strong><br/> <small>Surname</small></td>
                  <td style="line-height:1.2"><strong>{{othername($report->name)}}</strong><br/> <small>Other Names</small></td>
               </tr>
               <tr style="border-bottom:none; border-bottom-color: #fff;">
                  <th class="">Matriculation Number:</th>
                  <th class="">{{$report->regno}}</th>
                  <td class="">First Registration Date: &nbsp; <strong>{{$report->first_reg_date}} </strong></td>
               </tr>
               <tr style="border-bottom:none; border-bottom-color: #fff;">
                  <th class="">Faculty:</th>
                  <td class=""><strong>{{fact_name($report->fact_id)}}</strong></td>
                  <td class="">Department:&nbsp; <strong>{{dept_name($report->dept_id)}}</strong></td>
               </tr>
               <tr class="">
                  <th class="">Degree Awarded:</th>
                  <td class=""><strong>{{$report->programme}}</strong></td>
                  <td class="">Senate Approve Date: &nbsp; <strong>{{$report->approve_date}}</strong> </td>
               </tr>
            </tbody>
         </table>
      </div>
      <div class="col-12">
         <table class="table table-bordered border-dark table-sm transcript-table">
            <thead>
               <tr class="">
                  <th  class="text-uppercase" style="width:5%; border-bottom-color: #fff">&nbsp;</th>
                  <th  class="text-uppercase">Code</th>
                  <th class="text-uppercase" style="min-width:30%; width: auto;">Title</th>
                  <th class="text-uppercase">Status</th>
                  <th class="text-uppercase text-center">Units</th>
                  <th class="text-uppercase text-center">Score</th>
               </tr>
            </thead>
            <tbody>
               <!-- Vertical text, rowspan covers all rows -->
               <tr>
                  <td class="vertical-text" style="border-top-color:#fff; font-weight: bolder" rowspan="{{ count($transcripts)+1 }}">
                     &nbsp;&nbsp;<strong>{{strtoupper($printout->type)}} COPY </strong>
                  </td>
               </tr>
                @php $n = 1;  $stars = 0;
                    $tco = 0; $tcuu = 0;  $tcu = 0; $tsr = 0;
                    $wgp = 0;  $uwgp = 0; $cgp = 0; 
                 @endphp
               @foreach($transcripts as $subject)
                    @php 
                        $tco += $subject->units; 
                        $wgp += $subject->units * $subject->score; 
                        if($subject->starred==true) :
                            $tcuu += $subject->units; $stars++;
                               $uwgp += $subject->units * $subject->score; 
                            endif;
                    @endphp
               <tr>
                  <td class="pl-3 table-bordered border-dark font-weight-bold">{{ $subject->code }} &nbsp; @if($subject->starred==1)<small class="fa fa-star"></small> @endif</td>
                  <td class="pl-3 table-bordered border-dark">{{ $subject->title }}</td>
                  <td class="pl-3 table-bordered border-dark text-center">{{ substr($subject->type, 0, 1)  }}</td>
                  <td class="pl-3 table-bordered border-dark text-center">{{ $subject->units }}</td>
                  <td class="pl-3 table-bordered border-dark text-center">{{ $subject->score }}</td>
               </tr>
               @endforeach
               <tr>
                   <td colspan="6" style="border-bottom-color:#fff; border-top-color: #fff; ">&nbsp;</td>
               </tr>
              </tbody>
         </table> 
        
          <p class="small mt-0 pt-0 text-center" style="margin-top:0em; font-size:14px;">  <i class="text-black bold" style=" font-size:16px;">*</i>&nbsp;&nbsp; Prescribed courses not taken into account in obtaining Weighted Average Mark.</p>
          <p class=" font-weight-bold text-center"> Weighted Average Mark for the Degree Awarded =  @if($tco > 0)
                        {{ number_format(($wgp - $uwgp) / ($tco - $tcuu),2) }}
                        @else 0.00
                        @endif % </p>
          <p class=" bold text-center" style=" font-size:12px;"> Special Remarks (if any):  <i class="text-black" style=" font-size:16px;">*</i> =   <?php echo ($stars==0)?" Nill ":$stars; ?>	&nbsp; &nbsp; &nbsp; 	Key: C = Compulsory, R = Required, E = Elective, O = Optional </p>

      </div> <!-- ./ col-md-12 -->
      
      <div class="col-md-12 mt-5">
          <table class="table table-borderless transcript-table text-dark mt-2 pt-2" style="border-bottom-color: #fff; border-top-color: #fff; height: 2px;">
              <tr class="" style="border-bottom-color: #fff; border-top-color: #fff;">
                  <td style="width: 33%; border-bottom-color: #fff; border-top-color: #fff;" class="bottom-white">
                      {!! officials_name($printout->dean_id)!!}<br/>
                      Dean, Postgraduate School <br/>
                      <strong>Date: </strong>{{Carbon::parse($printout->created_at)->toDateString()}}
                  </td>
                  <td style="width: 33%; border-bottom-color: #fff; border-top-color: #fff;" class="bottom-white"></td>
                  <td style="width: 33%; border-bottom-color: #fff; border-top-color: #fff;" class="bottom-white">
                      {!! officials_name($printout->sec_id)!!}<br/>
                      Secretary, Postgraduate School <br/>
                      <strong>Date: </strong> {{Carbon::parse($printout->created_at)->toDateString()}}
                  </td>
              </tr>
          </table>
      </div>
   </div>
   <!-- ./ row -->
</div>
@endsection