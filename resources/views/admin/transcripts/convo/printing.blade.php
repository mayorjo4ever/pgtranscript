<?php use Carbon\Carbon; use App\Models\Programme;  ?>
@extends('layouts.print_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{ $page_info['title']}} @endsection
@section('content')

<style>
				
        #background{
        position:absolute;
        z-index:0;
        background:white;
        display:block;
        min-height:50%; 
        min-width:50%;
        color:yellow;
        }

        #content{
                position:absolute;
                z-index:1;
        }

        #bg-text
        {
                color:lightgrey;
                font-size:60px;
                transform:rotate(300deg);
                -webkit-transform:rotate(300deg);
                text-transform:uppercase;
        }
				
        table tr, table thead td, table td, table thead th, table th {
                  border:1px solid #fff; margin:5px; padding:5px; 
                   line-height:5px; background:transparent; 
        }
        .border-lines tr th,.border-lines tr td, .border-lines tr { border:1px solid #000; }

        /** 
            * Define the width, height, margins and position of the watermark.
            **/
			
        /***** adding water mark *********/
        #watermark {
            position: fixed;

            /** 
                Set a position in the page for your image
                This should center it vertically
            **/
            left:300px; 
            top:600px;

            /** Change image dimensions**/


            /** Your watermark should be behind every content**/
            z-index:  -1000;
        }

        .align-vertical{
                writing-mode: vertical-lr;
                text-orientation: upright;
        }
			
</style>

<?php ## show watermark 
     if($printout->type=="student") {  ?> 
    <div id="watermark">
        <img src="{{asset('img/data/watermark.png')}}" style="width:800%;" />
   </div>
<?php } ## end watermark  ?>

<section class="content notify-printed">
    <div class="row">
<div class="col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3 col-sm-12" > 

<div class="" style="height:270px;">&nbsp;</div>
    <p class="text-center"  style="font-family:Berlin Sans FB Regular; font-size:30px; font-weight:bold;"> 
        POSTGRADUATE STUDENT’S ACADEMIC TRANSCRIPT
   </p> 
   
   <input type="hidden" id="print-id" value="{{$printout->id}}" />
   
   <div class="mytable  " style="font-family:Tahoma; border:0px  background:transparent;" >						
        <table class="mytable1 nogap font-15 " style="font-weight:500; font-size:14px;  border:1px solid #fff;">
                <tr class="text-capitalize" >
                        <th style="width:25%;"> name </th>
                        <?php if($report->dob == ""): ?>
                        <td style="width:30%; line-height:13px;">{{surname($report->name)}} <br/> <small> <i>(Surname)</i></small></td>
                        <td style="width:40%; line-height:13px;">{{othername($report->name)}} <br/> <small> <i>(othername)</i></small> </td>
                <?php else:  ?>
                        <td style="width:45%; line-height:13px;">{{surname($report->name)}} &nbsp;  {{othername($report->name)}} <br/> <small> <i>(Surname)</i></small></td>
                        <td style="width:35%; line-height:13px;"> <strong>Date of Birth: </strong> {{surname($report->dob)}}  </td>

                <?php endif;  ?>
                </tr>
                <tr class="text-capitalize" >
                        <th style="width:30%; line-height: 17px;"> matriculation number: </th>
                        <td style="width:30%;"> {{$report->regno}} </td>
                        <td style="width:40%; line-height:17px;"><b> date of first Reg..</b>: {{$report->first_reg_date}} </td>
                </tr>

                <tr class="text-capitalize" >
                        <th style="width:30%;"> faculty : </th>
                        <td style="width:30%; line-height:17px;">{{fact_name($report->fact_id)}} </td>
                        <td style="width:40%;  line-height:17px;">  <b>Department : </b> {{dept_name($report->dept_id)}} </td>
                </tr>

                <tr class=""><!-- text-capitalize -->
                        <th style="width:30%;"> Degree Awarded:</th>
                        <td style="width:30%; line-height:17px;"> {{formatProgrammeName($report->programme)}}</td>
                        <td style="width:40%;"> <b>Approve Date </b>: {{$report->approve_date}} </td>
                </tr>
        </table>
</div>  
   
<div class=" " style="font-family:Tahoma; margin-bottom:0em;  padding-bottom:0em;">
    <center>
    <table class=" mytable2 font-13 thick-bolder" style=" width:90%; font-size:13px; font-weight:500; padding-bottom:0em; margin-bottom:0em;">

    <tr class="bold text-capitalize">																	
        <td colspan="2"> course code &amp; status</td>
        <td style="width:46%"> course title </td>
        <td style="width:12%; text-align:center"> credits </td> 
        <!--  <td> status </td>  -->
        <td style="width:12%; text-align:center"> mark obtained (%) </td> 
  </tr>

    <tbody class="border-lines" style="margin-bottom:0em;">
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
        <th style="width:12%;text-align:left;"> {{ $subject->code }} @if($subject->starred==1) <span style="font-size:18px">*</span>  @endif 
        </th>
        <th align="center" style="width:5%;text-align:center">{{ substr($subject->type, 0, 1)  }}</th>
        <td align="left"> {{ $subject->title }} </td>        
        <td align="center">{{ $subject->units }}</td> 
        <td align="center">{{ $subject->score }} </td> 
      </tr>
      @endforeach
</tbody>

</table>
</center>
</div> <!--close table div -->   
   

    <p class="small text-center" style="margin-top:0em; font-size:14px;">  <i class="text-black bold" style=" font-size:16px;">*</i>&nbsp;&nbsp; Prescribed courses not taken into account in obtaining Weighted Average Mark.</p>
    <p class=" bold text-center"> Weighted Average Mark for the Degree Awarded = @if($tco > 0)
                        {{ number_format(($wgp - $uwgp) / ($tco - $tcuu),2) }}
                        @else 0.00
                        @endif % </p>
    <p class=" bold text-center" style=" font-size:12px;"> Special Remarks (if any):  <i class="text-black" style=" font-size:16px;">*</i> =   <?php echo ($stars==0)?" Nill ":$stars; ?>	&nbsp; &nbsp; &nbsp; 	Key: C = Compulsory, R = Required, E = Elective, O = Optional </p>
    <p style="height:10px;"> &nbsp; </p> 	

    <div class="col-md-10 mt-4 pt-4 col-md-offset-1 col-xs-offset-1 col-sm-offset-1">
            <table class="table nogap" style="font-size:14px;">
                <tbody>
                    <tr> 
                        <td style="width:40%; border-top:1px solid #FFF;"> <p> <b> {!! officials_name($printout->dean_id)!!} </b> <br/>  Dean, Postgraduate School </br/>  <b> Date: </b> {{Carbon::parse($printout->created_at)->toDateString()}} </p> </td>
                        <td style="width:20%; border-top:1px solid #FFF;"> </td>
                        <td style="width:40%; border-top:1px solid #FFF;">  <b>{!! officials_name($printout->sec_id)!!} </b><br/> Deputy Registrar/Secretary  <br/> <b> Date: </b> {{Carbon::parse($printout->created_at)->toDateString()}}  <br/>  </td>
                    </tr> 
                </tbody>									
            <table>
    </div>   
   
</div>   <!-- /.col-md-12 -->
    </div>
</section>			  
      
@endsection