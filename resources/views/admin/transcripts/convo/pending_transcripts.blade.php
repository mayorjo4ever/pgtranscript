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
                    <div class="form-row mb-4"> <form method="post" action="{{url('admin/pending-graduation-transcripts')}}">@csrf
                        <div class="input-group">
                            <select class="form-control-lg font-weight-bold select2 col-md-4" name="approve_date_id" >
                                <option value="">...</option>
                                @foreach($approve_dates as $date)
                                <option value="{{$date->id}}" @selected($date->id == Session::get('approveDateId')) >{{ Carbon::parse($date->app_date)->format('D, jS F, Y') }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="search" value="{{Session::get('transcript_search')}}" class="form-control-lg  p-2 font-weight-bold w-50 col-md-4 " style="font-size:1rem" placeholder="Search Student Matric / Name " />
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
                                <th>PROGRAMME</th>             
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
                                 <td><a href="{{url('admin/transcript-processing/'.$url)}}" class="btn btn-primary" target="_blank">Start Process</a> </td>
                                <td>{{$student->regno}} <br/> {{$student->raw_name}}</td>                                
                                <td>{{$student->raw_programme}} <br> <strong><small> {{ get_full_approve_date($student->approve_date_id)}}</small></strong></td>                               
                               
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