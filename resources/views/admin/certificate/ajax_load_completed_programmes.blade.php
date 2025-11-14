<?php # print_r($uploaded_programmes->toArray());
use Illuminate\Support\Facades\Session;?>

    @if(Session::get('missingPsp'))
    <pre> <h6>Missing Passports</h6>
        <?php print_r(Session::get('missingPsp')); ?>
    </pre>
    @endif

<table class="table dataTable certificate-analyzer">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Download</th>
            <th>Programmes</th>           
            <th>Total Completed </th>
            <th>Remaining</th>
            
        </tr>
    </thead>
    <tbody>
        @if(empty($uploaded_programmes))
        <tr>
            <td colspan="6"> No Data Available </td>
        </tr>
        @endif
        
        @foreach($uploaded_programmes as $k=>$programme)
        <tr class="@if($programme['percentage_completed'] < 100) table-danger @endif
            @if($programme['percentage_uploaded'] == 100) table-success @endif ">
            <td>{{$k+1}}</td>
              <td> 
               @if(programmeConfigured($programme['raw_programme']))                                
                 &nbsp; 
                  <div class="form-check form-switch ps-0">
                     <input class="form-check-input ms-auto checkbox" type="checkbox" name="programes[]" value="{{$programme['raw_programme']}}" onchange="enableCertBtns(), analyze_certificates()" checked >
                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault"></label>
                      </div>
               @else &nbsp; 
               <span class="text-danger font-weight-bold">Not Configured </span> &nbsp;
                 <button onclick="checkProgrammeCompatibility('{{$programme['raw_programme']}}')" type="button" data-bs-toggle="modal" data-bs-target="#normalize_uploaded_programme" class="btn btn-info"><span class="material-icons">settings</span> Setup </button>
               @endif
            </td>      
            <td style="max-width:30%"><strong>{{$programme['raw_programme']}}</strong>
<!--                <pre> 
                    @php ## print_r(extractDegreeInfo($programme['raw_programme'])); @endphp
                </pre>-->
            </td>
            <td>{{$programme['total_completed']}}  /  {{$programme['total']}} &nbsp; <input type="hidden" name="total_certs[]" value="{{$programme['total']}}" /></td>            
            <td>{{$programme['total'] - $programme['total_completed'] }}   <input type="hidden" name="total_completed_certs[]" value="{{$programme['total_completed']}}" /></td>                            
        </tr>
        
        @endforeach
    </tbody>
    <tfoot>
        <tr class="table-light">
            <th colspan="2">Total Certificates : &nbsp; <span class="total_certs"></span> </th>
            
            <th colspan="2">Completed Certificates  : &nbsp; <span class="total_completed_certs"></span>  </th>
            
            <th colspan="2">Total Printing : &nbsp; <span class="total_printing_certs"></span> </th>
        </tr>
    </tfoot>
</table> 
<!--Modal To Upload Programme -->

<x-admin.modal id="normalize_uploaded_programme" title="Normalize Programmes" size="lg">       
    
    <div class="row m-1 p-1 border border-1 prog-setup-view"></div> <!-- ./ row -->
       
        <x-slot name="footer"> <span class="ajaxLoader"></span> &nbsp;                                     
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal"> Close </button>
             
        </x-slot>
    
</x-admin.modal>



