<?php use App\Models\CertificateApprovalDate; ?>
<div class="col-md-12">
    <table class="table">
        <tr>
            <th>S/N</th>
            <th>Actions</th>
            <th>Regno</th>
            <th>Name</th>
            <th>Programme</th>
           
        </tr>
        @foreach($reports as $k=>$report)
         @php $regno = base64_encode($report->regno);
                $purpose = base64_encode("convocation");
                $date_id = CertificateApprovalDate::firstOrCreate(['app_date'=>$report->approve_date]);
                $approve_date = base64_encode($date_id->id);
                $url = base64_encode($regno."|".$purpose."|".$approve_date);
           @endphp
        <tr>
            <td>{{$k+1}}</td>
            <td><a href="{{url('admin/transcript-reconfiguration/'.$url)}}" target="_blank" class="btn btn-success p-3"l>View</a></td>
            <td>{{$report->regno}}</td>           
            <td>{{$report->name}}</td>    
            <td>{{$report->programme}} <br/> 
            {{$report->approve_date}}
            </td>   
        </tr>
        @endforeach 
       
        
        @foreach($certData as $k=>$report)
         @php $regno = base64_encode($report->regno);
                $purpose = base64_encode("convocation");               
                $approve_date = base64_encode($report->approve_date_id);
                $url = base64_encode($regno."|".$purpose."|".$approve_date);
           @endphp
        <tr>
            <td>{{$k+1}}</td>
            <td><a href="{{url('admin/transcript-processing/'.$url)}}" target="_blank" class="btn btn-primary p-3"l>Start Process</a></td>
            <td>{{$report->regno}}</td>           
            <td>{{$report->name}}</td>    
            <td>{{$report->raw_programme}} <br/> 
            {{$report->app_date->app_date}}
            </td>   
        </tr>
        @endforeach
    </table>
</div>