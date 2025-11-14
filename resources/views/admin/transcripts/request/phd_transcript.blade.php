<?php   use Carbon\Carbon; use App\Models\Programme; use Illuminate\Support\Facades\Session; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{  'PHD TRANSCRIPT REQUEST - '. $info[0]   }} @endsection

@section('content')
 <div class="container-fluid py-4">
     
      <x-admin.alert></x-admin.alert>
    
      <div class="row">
           <div class="col-md-12">   
                <x-admin.card header="{{$info[0]}}  - PHD TRANSCRIPT - {{$info[3]}} REQUEST ">
                    <form method="post" onsubmit="submit_transcript_request()">@csrf
                    <table class="table table-responsive">       
                        <thead>
                            <tr> <td colspan="2"><strong>Programme</strong>: <span class="text-primary font-weight-bold">   {{--$report->programme --}} </span>
                                    &nbsp; &nbsp; 
                                    <strong>Approved Date </strong>: <span class="text-primary font-weight-bold">  {{-- Carbon::parse($report->approve_date)->format('D, jS F, Y') --}}</span>
                                    <input type="hidden" name="regno" id="regno" value="{{$info[0]}}"/>
                                    <!--<input type="hidden" name="approve_date" id="approve_date" value="{{--$report->approve_date--}}"/>-->
                                    <input type="hidden" name="request_id" id="request_id" value="{{$info[2]}}"/>
                                </td>             
                            </tr>                            
                        </thead>
                        <tbody>                           
                            <tr><td  class="text-uppercase" style="width:30%"> <label class="font-weight-bold mt-3 "> Student Matric Number </label>  </td>
                                <td><input placeholder="REGNO" name="" id="" value="{{$info[0] }}" type="text" class="mt-3 form-control-lg font-weight-bold w-60 text-uppercase" style="font-size: 1rem;" /></td>
                            </tr>                               
                            <tr><td class=""> <label class="font-weight-bold mt-3 "> Student's Name </label>  </td>
                                <td><input placeholder="Student Name : As appeared on the certificate" name="name" id="stud_name" value="{{$report->name??''}}" type="text" class="mt-3 form-control-lg font-weight-bold w-90 text-cpitalize" style="font-size: 1rem;" /></td>
                            </tr>
                            <tr><td class=""> <label class="font-weight-bold mt-3 "> Faculty </label>  </td>
                                <td>
                                    <select name="faculty" id="faculty" required="" onchange="load_departments($(this).val())" class="form-control-lg p-3 select2 rounded" style="width: 90%; font-size: 1.2rem">
                                        <option value="">...</option>
                                        @foreach(Session::get('faculties') as $faculty)
                                        <option value="{{$faculty->fact_id}}" @if(!empty($report)) @selected($faculty->fact_id == $report->fact_id ??'' ) @endif >{{$faculty->name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            
                              <tr><td class=""> <label class="font-weight-bold mt-3 "> Department </label>  </td>
                                <td>
                                    <input type="hidden" name="user_dept" id="user_dept" value="{{$report->dept_id??''}}"/>
                                    <select name="department" required="" id="fact_department" class="form-control-lg p-3 select2 rounded" style="width: 90%; font-size: 1.2rem">
                                        <option value="">...</option>             
                                    </select>
                                </td>
                            </tr>
                            
                              <tr><td class=""> <label class="font-weight-bold mt-3 "> Programme </label>  </td>
                                <td>                                   
                                    <select name="programme" required="" id="programme" class="form-control-lg p-3 select2 rounded" style="width: 90%; font-size: 1.2rem">
                                        <option value="">...</option>       
                                        @foreach($phd_progs as $prog)
                                        <option value="Ph.D. {{$prog->name}}" @if(!empty($report)) @selected("Ph.D. ".$prog->name == $report->programme ??'' ) @endif > Ph.D. {{$prog->name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            
                              <tr><td class=""> <label class="font-weight-bold mt-3 "> Senate Approval Date </label>  </td>
                                <td>                                   
                                    <input type="text" name="approve_date" value="{{$report->approve_date ?? ''}}" class="form-control-lg border border-1 border-dark datepicker font-weight-bold w-90"/>
                                </td>
                            </tr>
                            
                            
                            @if($student_request->reference_number !="")
                               <tr><td class="text-uppercase"> <label class="font-weight-bold mt-3 "> WES REF No:</label>  </td>
                                 <td><input placeholder="NAME" name="wes_ref_no" id="wes_ref_no" value="{{-- $student_request->cover_letter->wes_ref_no ?? $student_request->reference_number --}}" type="text" class="mt-3 form-control-lg font-weight-bold w-90 text-uppercase" style="font-size: 1rem;" /></td>
                               </tr>
                            @endif
                            <tr><td class="text-uppercase"> <label class="font-weight-bold mt-3 "> Receiving Body </label>  </td>
                                <td><textarea placeholder="RECEIVING BODY " rows="5" name="receiving_body" class="mt-3 form-control-lg font-weight-bold w-90" style="font-size: 1rem;">{{$student_request->cover_letter->destination_address ?? $student_request->destination_address }}</textarea></td>
                            </tr> 
                        </tbody>
                    </table>
                        
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary btn-lg p-3 w-100"> SUBMIT PHD TRANSCRIPT </button>
                         </div>
                    </div> 
                    
                    <x-admin.card>
                        <div class="row">
                            <div class="col-md-12">
                            </div>
                        </div>
                    </x-admin.card>
               
               </form>
             </x-admin.card>   
           </div> <!--./ col-md-12 --> 
            
       </div><!-- ./ row -->
       
       
 <p class="mt-3 pt-3">&nbsp;</p>
 
<x-admin.card>
    <h5 class=" bg-dark  text-white text-capitalize p-2 rounded">printout summary</h5>
    <table class="table table-sm">
        <tr class="text-capitalize">
            <th>purpose</th>           
            <th>created by</th>           
            <th>secretary </th>
            <th>last printed</th>
            <th>total print</th>
        </tr>
        @if(!empty($printouts->toarray()))
        @foreach($printouts as $printout)
        <tr>
            <td class="text-capitalize">{{$printout->request->request_purpose ?? ""}} <br/> {{$printout->type}}'s copy</td>          
            <td class="text-capitalize">{{$printout->created_by}} <br/>
                {{Carbon::parse($printout->created_at)->format('D, jS M, Y - h:i:A')}}
            <br/> {{ Carbon::parse($printout->created_at)->diffForHumans()}}
            </td>             
            <td>{!! officials_name($printout->sec_id)!!} </td>
            <td>Printed By : {{$printout->printed_by}} <br/>
                @if($printout->printed_by !="")
                {{Carbon::parse($printout->updated_at)->format('D, jS M, Y - h:i:A')}}
                <br/> {{ Carbon::parse($printout->updated_at)->diffForHumans()}}
                @endif
            </td>
            <td>{{$printout->print_count}}</td>
        </tr>
        @endforeach
        @endif
    </table>
</x-admin.card>
       
 </div>
@endsection