<?php # print_r($uploaded_programmes->toArray()); ?>
<table class="table dataTable">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Programmes</th>
            <th>Certificate Template</th>  
            <th>Total Students </th>
           
        </tr>
    </thead>
    <tbody>
        @if(empty($uploaded_programmes))
        <tr>
            <td colspan="6"> No Data Available </td>
        </tr>
        @endif
        
        @foreach($uploaded_programmes as $k=>$programme)
        <tr>
            <td>{{$k+1}}</td>
            
            <td><strong><a href="#" data-bs-toggle="modal" data-bs-target="#edit_uploaded_programme" onclick="setValues('{{$programme['raw_programme']}}','.cert-programme'), setRef('{{$programme['raw_programme']}}')">{{$programme['raw_programme']}} &nbsp;  <span class="material-icons">edit</span></a></strong>
<!--                <pre>
                    @php ## print_r(extractDegreeInfo($programme['raw_programme'])); @endphp
                </pre>-->
            </td>
             <td> 
               @if(programmeConfigured($programme['raw_programme']))                
                 <span class="text-success font-weight-bold">Configured </span>
                 &nbsp; 
                 <button onclick="checkProgrammeCompatibility('{{$programme['raw_programme']}}')" type="button" data-bs-toggle="modal" data-bs-target="#normalize_uploaded_programme" class="btn btn-success"><span class="material-icons">settings</span> View </button>
               @else &nbsp; 
               <span class="text-danger font-weight-bold">Not Configured </span> &nbsp;
                 <button onclick="checkProgrammeCompatibility('{{$programme['raw_programme']}}')" type="button" data-bs-toggle="modal" data-bs-target="#normalize_uploaded_programme" class="btn btn-info"><span class="material-icons">settings</span> Setup </button>
               @endif
            </td> 
            <td>{{$programme['total']}} &nbsp; / &nbsp; {{$programme['total_completed']}} &nbsp; : &nbsp;  {{$programme['percentage_completed']}}%</td>                     
        </tr>        
        @endforeach
    </tbody>
</table>



<!--Modal To Upload Programme -->
<x-admin.modal id="normalize_uploaded_programme" title="Normalize Programmes" size="lg">       
    <div class="row m-1 p-1 border border-1 prog-setup-view"></div> <!-- ./ row -->
       <x-slot name="footer"> <span class="ajaxLoader"></span>&nbsp;
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal"> Close </button>
            <input type="hidden" class="keeper" value="" />
        </x-slot>    
</x-admin.modal>

<!--Modal To Upload Programme -->
<x-admin.modal id="edit_uploaded_programme" title="Edit Programme" size="md">    
        <span class="font-weight-bold h6">Initial Programme : </span> &nbsp; <span class="cert-programme"></span> <br/>
            <label for="cert-programme" class="mt-1 pt-1 font-weight-bold h6">New Programme Name: </label>
            <input class="form-control border border-dark cert-programme" name="cert-programme" required="" style="font-size: 1rem">
            <input type="hidden" class="update-ref" value=""/>
       <x-slot name="footer"> <span class="ajaxLoader"></span>&nbsp;
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal"> Close </button>
            <button onclick="update_cert_param('programme')" type="submit" class="btn btn-success update-cert-programme-btn ladda-button" data-style="expand-right">Update Programme </button>
        </x-slot>    
</x-admin.modal>


