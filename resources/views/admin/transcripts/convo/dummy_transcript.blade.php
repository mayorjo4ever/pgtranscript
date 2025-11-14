<?php use Illuminate\Support\Facades\Session; use Carbon\Carbon; ?>

<table class="table  table-sm  table-responsive w-100">    
<tr class="table-dark">     
    <td class="p-2"><strong>S/N</strong></td>   
    <td class="p-2"><strong>Type  </strong></td>
    <td class="p-2"><strong>Code</strong></td>
    <td class="p-2"><strong>Score</strong></td>  
    <td class="p-2"><strong>Starred </strong></td>
    <td class="p-2"><strong>Action </strong></td>      
    <td class="p-2"><strong>Last Editor </strong></td>      
</tr> @php $n = 1; 
           $tco = 0; $tcuu = 0;  $tcu = 0; $tsr = 0;
           $wgp = 0;  $uwgp = 0; $cgp = 0; 
@endphp
    @foreach($courses as $course)
    @php 
        $tco += $course->units; 
        $wgp += $course->units * $course->score; 
        if($course->starred==true) :
            $tcuu += $course->units;
               $uwgp += $course->units * $course->score; 
            endif; 
        
    @endphp
    <tr class="transcript-courses"> 
    <td>{{ $n }}</td>   
    <td><strong>{{$course->type}} | {{$course->units}} Units  </strong></td>             
     <td><strong>{{$course->code}} <br/> </strong>
        <small>{{$course->title}}</small>   
        <input type="hidden" class="course-code" value="{{$course->code}}" />
    </td>
    <td><strong>{{$course->score}}</strong></td>
    
    <td>
        {!! ($course->starred==true)?"<span class='text-danger fa fa-star'>*</span>":"" !!}      
    </td>  
    <td> <button type="button" onclick="remove_this_code($(this))" class="btn btn-md btn-danger mt-1 code-add ladda-button btn-{{$course->code}}" data-t="style="expand-right"> Remove </button></td>
    <td class="p-2">{{$course->created_by}}</td>
</tr> @php $n++; @endphp
@endforeach

</table>
<hr/> 
            
<div class="row">
    <div class="col-5 m-2 " style="border-radius: 15px;">
        <p class="font-weight-bold mt-0 mb-0  bg-light p-2 d-flex justify-content-between">
          <span >Total Credit Offered</span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">{{$tco}}</span>
        </p>
        
        <p class="font-weight-bold mt-0 mb-0  bg-white p-2 d-flex justify-content-between">
          <span >Total Credit Used </span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">{{$tco - $tcuu}}</span>
        </p>
       
        <p class="font-weight-bold  mt-0 mb-0 bg-light p-2 d-flex justify-content-between">
          <span>Weighted Grade Point </span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">{{$wgp - $uwgp}}</span>
        </p>
      
        <p class="font-weight-bold  mt-0 mb-0 bg-white p-2 d-flex justify-content-between">
          <span>Cumulative Grade Point </span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">
             @if($tco > 0)
                {{ number_format(($wgp - $uwgp) / ($tco - $tcuu),2) }}
                @else 0.00
                @endif
          </span>
        </p>
        
        <p class="font-weight-normal mt-0 mb-0 bg-light p-2 d-flex justify-content-between">
          <span>Unused Weighted Grade Point </span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">{{$uwgp}}</span>
        </p>
        
         <p class="font-weight-normal mt-0 mb-0 bg-white p-2 d-flex justify-content-between">
          <span>Expected Weighted Grade Point </span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">{{$wgp}}</span>
        </p>
        
        <p class="font-weight-normal mt-0 mb-0 bg-light p-2 d-flex justify-content-between">
          <span>Expected Cumulative Grade Point</span>
          &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;  
          <span class="pull-right">
             @if($tco !=0)
                {{number_format(($wgp / $tco),2)}}
                @else 0.00
                @endif
          </span>
        </p>
        
      <div class="form-group mb-3 mt-3">
           <label class="control-label">Correct Name </label> <br/>
           <input type="text" name="stud_name" value="{{smartSwapName($programme['name'])}}" class="form-control-lg bg-white border border-1 border-dark font-weight-bold w-100"/>
       </div>                
        
    </div>
    
   <div class="col-5 m-2 " style="border-radius: 15px;">
       <h6 class="p-2 bg-light mb-1 text-center"> DATES & TYPE </h6>
        <div class="form-group mb-3  mt-4">
            <label class="control-label">First Registration Date </label>
            <input required="" value="{{$report->first_reg_date??'' }}"  name="first_reg_date" type="text" class="form-control-lg border border-1  border-dark datepicker font-weight-bold"/>
        </div>
       <div class="form-group mb-3">
           <label class="control-label">Senate Approval Date </label>
           <input type="text" value="{{$approve_date}}" class="form-control-lg border border-1 border-dark datepicker font-weight-bold"/>
       </div>
       
        <div class="form-group mb-3">
            <div class="radio-wrapper-8 float-md-start">
                <label class="control-label radio-wrapper-8"  style="font-size: 1rem">
                <input type="radio" value="student" @if(Session::get('type')=='student') checked @endif name="transcript_type"/>
                <span>Student Copy </span></label>
            </div>
           &nbsp; &nbsp; &nbsp; &nbsp; 
           <div class="radio-wrapper-8 float-md-end">
            <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                <input class="form-radio" type="radio" value="official"  @if(Session::get('type')=='official') checked @endif name="transcript_type"/>
            <span>Official Copy </span></label>      
           </div>
       </div>
        <div class="form-group mb-3 ">
           <label class="control-label">Transcript Purpose </label> <br/>
           <input readonly="" type="text" name="purpose" value="{{ucwords(Session::get('purpose'))}}" class="form-control-lg bg-white border border-1 border-dark font-weight-bold w-100 bg-white"/>
           <input value="{{Session::get('request_id')??""}}" type="hidden" class="form-control font-weight-bold border border-1 border-dark form-control-lg" name="request_id" id="request_id" style="font-size:1rem" />
       </div>
    </div>    
</div> 

<div class="row">
    <div class="col-4">
        <label>Faculty</label>
        <select name="faculty" id="faculty" required="" onchange="load_departments($(this).val())" class="form-control-lg p-3" style="width: 99%; font-size: 1.2rem">
            <option value="">...</option>
            @foreach(Session::get('faculties') as $faculty)
            <option value="{{$faculty->fact_id}}" @if(!empty($report)) @selected($faculty->fact_id == $report->fact_id ??'' ) @endif >{{$faculty->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label>Department</label>
        <input type="hidden" name="user_dept" id="user_dept" value="{{$report->dept_id??''}}"/>
        <select name="department" required="" id="fact_department" class="form-control-lg p-3" style="width: 99%; font-size: 1.2rem">
            <option value="">...</option>             
        </select>
    </div>
    <div class="col-4">
        <label>Programme</label><br/>
        <input required="" type="text" value="{{$programme->raw_programme}}" name="programme" class="form-control-lg  font-weight-bold" style="font-size: 1rem" />
    </div>
    
    <div class="col-12 mt-3">
        <button type="submit" class="btn btn-info btn-lg w-100">
            Submit Transcript
        </button>
    </div>
</div>

<p class="mt-3 pt-3"></p>

<x-admin.card>
    <h5 class=" bg-dark  text-white text-capitalize p-2 rounded">printout summary</h5>
    <table class="table table-sm">
        <tr class="text-capitalize">
            <th>purpose</th>           
            <th>created by</th>           
            <th>secretary and dean</th>
            <th>last printed</th>
            <th>total print</th>
        </tr>
        @foreach($printouts as $printout)
        <tr>
            <td class="text-capitalize">{{$printout->purpose}} <br/> {{$printout->type}}'s copy</td>          
            <td class="text-capitalize">{{$printout->created_by}} <br/>
                {{Carbon::parse($printout->created_at)->format('D, jS M, Y - h:i:A')}}
            <br/> {{ Carbon::parse($printout->created_at)->diffForHumans()}}
            </td>             
            <td>{!! officials_name($printout->sec_id)!!} <br/> {!! officials_name($printout->dean_id)!!}</td>
            <td>Printed By : {{$printout->printed_by}} <br/>
                @if($printout->printed_by !="")
                {{Carbon::parse($printout->updated_at)->format('D, jS M, Y - h:i:A')}}
                <br/> {{ Carbon::parse($printout->updated_at)->diffForHumans()}}
                @endif
            </td>
            <td>{{$printout->print_count}}</td>
        </tr>
        @endforeach
    </table>
</x-admin.card>
