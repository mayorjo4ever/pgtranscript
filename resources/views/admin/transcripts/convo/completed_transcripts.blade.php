<?php use Carbon\Carbon; use Illuminate\Support\Facades\Session; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
 
<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">   
                <x-admin.card header="{{ $page_info['title']}}">
                    <div class="form-row mb-4"> <form method="post" action="{{url('admin/completed-graduation-transcripts')}}">@csrf
                        <div class="input-group">
                            <select class="form-control-lg font-weight-bold select2 col-md-4" name="approve_date_id"  style="font-size:1rem">
                                <option value="">...</option>
                                @foreach($approve_dates as $date)
                                <option value="{{$date->id}}" @selected($date->id == Session::get('approveDateId')) >{{ Carbon::parse($date->app_date)->format('D, jS F, Y') }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="search" class="form-control-lg  p-3 font-weight-bold w-50 col-md-4"  value="{{Session::get('transcript_search')}}" style="font-size:1rem" placeholder="Search Student Matric / Name " />
                            <button type="submit" class="btn btn-lg btn-info p-3">Search &nbsp; </button>
                        </div>
                     </form>
                    </div>
                    <table class="table table-responsive">       
                        <thead>
                            <tr>
                                <th>SN</th>
                                 <th>ACTIONS</th> 
                                <th>NAME</th>                                                       
                                <th>TOTAL PRINTOUTS</th>             
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php $regno = base64_encode($student->regno);
                                 $purpose = base64_encode("convocation");
                                 $approve_date = base64_encode($student->approve_date_id);
                                 $url = base64_encode($regno."|".$purpose."|".$approve_date);
                            @endphp
                            <tr>   
                                <td>{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                                 <td><a href="{{url('admin/transcript-processing/'.$url)}}" class="btn btn-success p-3" target="_blank">View Transcript</a> </td>
                                <td>{{$student->regno}} <br/> {{$student->raw_name}} <br/> {{$student->raw_programme}} <br/> <small> {{ get_full_approve_date($student->approve_date_id)}}</small></td>                                
                                <td> @if(!empty($student->printouts))
                                        @foreach($student->printouts as $printout)
                                        <?php $url = base64_encode($printout->regno."|".$printout->approve_date."|".$printout->id);?>
                                        <a  href="{{url('admin/print-transcript/'.$url)}}" target="_blank" class="btn {{ ($printout->print_count >0)?"btn-light":"btn-primary"}}"> {{ $printout->purpose }} | {{ $printout->type.' Copy ' }}  </a>
                                        <a href="#" title="Total Printouts" class="btn btn-lg {{ ($printout->print_count >0)?"btn-light":"btn-primary"}}"> {{ $printout->print_count }}</a>
                                        <br/>
                                        @endforeach
                                    @endif
                                </td>                               
                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    

                    {{-- Pagination links --}}
                    <div class="d-flex justify-content-center">
                        {{ $students->links('vendor.pagination.material') }}
                    </div> 
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      
       
</div>

    
    

@endsection