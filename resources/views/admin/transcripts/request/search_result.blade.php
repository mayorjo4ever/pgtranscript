<?php use App\Models\Programme; use App\Models\Degree; ?>
@if(!empty($reports->toarray()))
<div class="">
    <h6 class="card-title bg-light p-2 text-center"> RESULTS : {{count($reports)}}</h6>
    <div class="table"> 
        <table class="table"> 
            
            @foreach($reports as $report)
            <?php # print_r($printout->toarray()); <pre></pre> ?>
            @if(!empty($printout))
            <?php $url = base64_encode($printout->regno."|".$printout->approve_date."|".$printout->id);?>
            <a href="{{url('admin/print-transcript/'.$url)}}" target="_blank" class="btn btn-primary "> PRINT {{ $printout->type.' Transcript ' }}   [ {{ $printout->print_count }} ]</a>
                <!-- include cover letter -->
                @if(!empty($cover_letter))                      
                <?php $memo_url = base64_encode($cover_letter->regno."|".$cover_letter->id); ?>
                 &nbsp; &nbsp; <a href="{{url('admin/print-memo/'.$memo_url)}}" target="_blank" class="btn btn-primary "> PRINT Covering  Memo  [ {{ $cover_letter->print_count }} ]</a>
                 @endif
            @else
             <tr> 
                <th> Name </th>
                <th>Programme </th>
                <th> Duration </th>
            </tr>
            <tr> 
                <td> {{$report->name}}</td>
                <td> {{$report->programme}}</td>
                <td> {{$report->first_reg_date}} To 
                    {{$report->approve_date}}</td>
            </tr> 
            
            @endif
              
         @php 
           $degree = extractDegreeInfo($report->programme);
           $progid = program_available($degree['id'],$degree['field']);
            #  print_r($degree); var_dump($progid);  exit; 
           @endphp 
           
           @if($progid['available']==false)
                <tr>
                <th colspan="3">
                    <strong class="text-danger">Reconfigure Student's Programme: </strong>  <br/>
                    <label>Select from Existing Programmes </label>
                    <div class="input-group">                    
                    <select name="" id="" class="form-control border border-dark select2">
                        <?php $progs = Programme::whereNotIn('degree_id',[14])->get(); ?>
                        @foreach($progs as $prog)
                        <option value="{{ Degree::name($prog->degree_id)." ".$prog->name}}">
                            {{ Degree::name($prog->degree_id)." ".$prog->name}}
                        </option>
                        @endforeach
                    </select>
                    <button class="btn btn-info btn-lg"> Update </button>
                    </div>
                    
                </th>
            </tr>
            <tr>
                <th colspan="3"><form id="new-prog" method="post" onsubmit="create_new_programme($(this))" action="javascript:void(0)">@csrf
                    <strong class="text-danger">Or Create New Programme: </strong>  <br/>                    
                    <div class="input-group">                    
                        <select style="width:20%" name="deg_id" id="deg_id" class="form-control p-3 border border-dark select2">
                        <?php $degrees = Degree::all(); ?>
                        @foreach($degrees as $degree)
                        <option value="">
                            {{ Degree::name($degree->id)}}
                        </option>
                        @endforeach
                    </select>
                        <input style="width:50%" name="name" id="name" type="text" class="form-control p-3 border border-dark"/>
                             
                        <button type="submit" class="btn btn-success btn-lg"> Create </button>
                    </div>
                    </form>
                </th>
            </tr>
           @else
            
            <tr>
                <td colspan="3">
                    @php $regno = base64_encode($report->regno);
                        $purpose = base64_encode("Request"); # convocation / request
                        $approve_date = base64_encode($report->approve_date);
                        $request_id = base64_encode($request_id);
                        $type = base64_encode($request_type); # student / official
                        $url = base64_encode($regno."|".$purpose."|".$approve_date."|".$request_id."|".$type);
                    @endphp
                    
                    <a target="_blank" class="btn btn-success btn-lg p-3" href="{{url('admin/schedule-transcript-request/'.$url)}}">  View Transcript </a>
                    &nbsp; &nbsp; 
                    @if($request_type=="OFFICIAL")
                    <a target="_blank" class="btn {{empty($cover_letter) ? 'btn-warning':'btn-success'}}  btn-lg p-3" href="{{url('admin/schedule-transcript-memo/'.$url)}}">Official Memo </a>
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="3">
                   </td>
            </tr>
            @endif
           @endforeach
        </table>
    </div>
</div>

@else 

<div class="text-warning" style="font-size: 1.5rem">
        No Transcript Found 
    </div>
@endif
    

