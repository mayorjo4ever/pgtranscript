<?php # print_r($uploaded_programmes->toArray()); ?>

<div class="row form-group">
    <div class="col-sm-12">
        
        <button type="button" onclick="load_student_by_programmes($('.cur_page').val())" class="btn btn-sm btn-info  pt-3 pb-3 font-weight-bold"> Reload &nbsp; <span class="fa fa-refresh fa-2x"></span></button>
        <button onclick="checkAll()" class="btn btn-secondary  pt-3 pb-3 btn-sm checkAll">Select All &nbsp; <span class="material-icons  md-36">select_all</span> </button>

        <button onclick="normalize_cert_names()" class="btn btn-info pt-3 pb-3 btn-normalize-cert ladda-button" data-style="expand-right" disabled="">
            Normalize  <span class="count-checks"></span> Names   &nbsp; <span class="material-icons md-36">shuffle</span>
        </button>     
        
        <button onclick="finalize_cert_names()" class="btn btn-success pt-3 pb-3 btn-finalize-cert ladda-button" data-style="expand-right"  disabled="">
            Finalize <span class="count-checks"></span> Certificates  &nbsp; <span class="material-icons  md-36">check_circle</span>
        </button>        
        
        <button onclick="definalize_cert_names()" class="btn btn-danger pt-3 pb-3 btn-reverse-cert ladda-button" data-style="expand-right"  disabled="">
            Reverse <span class="count-checks"></span>  Finalization
        </button>  
        
        <div class="form-check form-switch ps-0">
        <input class="form-check-input ms-auto " type="checkbox" name="toswap" value="1" checked >
           <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="toswap"> Swap Student Names </label>
         </div>
        
        
    </div> 
</div>
 
<table class="table border-2 border-dark table-responsive dataTable mt-4" style="font-size:14px;">
    <thead class="table-dark">
        <tr> 
            <th></th>
            <th>S/N</th>            
            <th>Finalized</th>            
            <th>Passport </th>
            <th>Normalized Name </th>
            <th>RegNo</th>
            <th>Original Name</th>
            
        </tr>
    </thead>
    <tbody>
        @foreach($processed as $k=>$student)
        <tr>
            <td>
                 <div class="form-check form-switch ps-0">
                     <input class="form-check-input ms-auto checkbox" type="checkbox" name="students[]" value="{{$student['record']['id']}}" onchange="enableCertBtns()" @if($student['matched']) checked @endif >
                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault"></label>
                      </div>
            </td>
            <td>{{$k+1}} </td>
            <td> @if($student['record']['completed']==1)
                <span class="material-icons text-success text-4xl">check_circle</span>
                @else
                 <span class="material-icons text-danger text-4xl">warning</span>
                @endif
            </td>
            <td> <div class="new-img-{{$student['record']['id']}}"> 
                    @if(file_exists(public_path($img_dir . $student['photo'])))
                    <img src="{{asset($img_dir . $student['photo'])}}" id="passport-img-{{ $student['record']['id'] }}" title="{{asset($img_dir . $student['photo'])}}"  style="width: 80px; height: 80px" />
                    @endif
                <br/>
                 @if($student['matched'])
                 <small> <span class="material-icons text-success">check_circle</span></small>
                    @else
                      @if(!$student['matched'] && count($student['suggestions'])) 
                            @foreach($student['suggestions'] as $expected => $suggestion)
                               <div class="mb-3">
                                   @php $matches = text_diff($suggestion,$student['record']['pix_name']) @endphp
                                   @php #$matches = highlightDifference($suggestion,$student['record']['pix_name'].".jpg") @endphp
                                   
                                   <strong>Expected: </strong> {!! $matches['new'] !!}<br> 
                                   <strong>Found: </strong> {!! $matches['old'] !!}<br> <!-- ['new'] -->
                               
                                <button onclick="renamePassport($(this))"
                                    class="btn btn-sm btn-primary btn-img-{{$student['record']['id']}} rename-btn mt-1 ladda-button" data-style="expand-right"
                                    data-old="{{ $suggestion }}"
                                    data-new="{{ $student['record']['pix_name'].".jpg" }}"
                                    data-img="new-img-{{$student['record']['id']}}"
                                    data-id="btn-img-{{$student['record']['id']}}"
                                    
                                >Rename</button>
                            </div>                                
                            @endforeach         
                         @endif 
                    @endif
                </div> <span class="rename-result text-success ms-2"></span>
            </td>            
            <td><a data-bs-toggle="modal" data-bs-target="#update_name_modal" href="#" href="#" onclick="setValues('{{$student['record']['name']}}','.cert-name'), setRef('{{$student['record']['id']}}')" >{{$student['record']['name']}}&nbsp; <span class="material-icons">edit</span> </a> 
                <br><br> <strong>Pix Name </strong><br> 
                {{$student['record']['pix_name'].".jpg"}}
                <br><br> <strong>Portal Name </strong><br> 
                {!!$student['record']['first_name']." ".$student['record']['middle_name']." ".strtoupper($student['record']['last_name'])."<br/> Phone: ".$student['record']['phone']."<br/> Programme: ".$student['record']['programme']!!}
            </td>
            <td><a data-bs-toggle="modal" data-bs-target="#update_regno_modal" href="#" onclick="setValues('{{$student['record']['regno']}}','.cert-regno'),setRef('{{$student['record']['id']}}')">{{$student['record']['regno']}} &nbsp; <span class="material-icons">edit</span> </a>
            <br/> {{$student['record']['raw_programme']}}</td>
            <td>{{$student['record']['raw_name']}}</td>                     
        </tr>
        
        @endforeach
    </tbody>
</table>
<hr/>
<div class="row">
    <div class="col-sm-12">
        @if ($processed->hasPages())
    <div class="material-pagination" style="text-align: center; margin-top: 30px;">
        {{-- Previous Button --}}
        @if ($processed->onFirstPage())
            <button class="btn btn-light disabled" disabled>❮</button>
        @else
            <button class="btn btn-light" onclick="load_student_by_programmes({{ $processed->currentPage() - 1 }}), , set_cur_page({{ $processed->currentPage() - 1 }})">❮</button>
        @endif

        {{-- Page Numbers --}}
        @for ($i = 1; $i <= $processed->lastPage(); $i++)
            <button class="btn {{ $i == $processed->currentPage() ? 'btn-primary text-white' : 'btn-light' }}" onclick="load_student_by_programmes({{ $i }}), set_cur_page({{ $i }})">
                {{ $i }}
            </button>
        @endfor

        {{-- Next Button --}}
        @if ($processed->hasMorePages())
            <button class="btn btn-light" onclick="load_student_by_programmes({{ $processed->currentPage() + 1 }}), set_cur_page({{ $processed->currentPage() + 1 }})">❯</button>
        @else
            <button class="btn btn-light disabled" disabled>❯</button>
        @endif
    </div>
@endif


    </div>
</div>

<x-admin.modal id="update_name_modal" title="Update Normalized Name">

        <div class="mb-3">
            <span class="font-weight-bold h6">Initial Name : </span> &nbsp; <span class="cert-name"></span> <br/>
            <label for="name" class="mt-1 pt-1 font-weight-bold h6">New Name</label>
            <input class="form-control border border-dark cert-name" name="cert-name" required="" style="font-size: 1rem">
            <input type="hidden" class="update-ref" value=""/>
        </div> 
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Cancel</button>
            <button onclick="update_cert_param('name')" type="submit" class="btn btn-success update-cert-name-btn ladda-button" data-style="expand-right">Update Name </button>
        </x-slot>
    
</x-admin.modal>

<x-admin.modal id="update_regno_modal" title="Update Matric Number">       
        
        <div class="mb-3">
            <span class="font-weight-bold h6">Initial Matric : </span> &nbsp; <span class="cert-regno"></span> <br/>
            <label for="regno" class="mt-1 pt-1 font-weight-bold h6">New Matric No: </label>
            <input class="form-control border border-dark cert-regno" name="cert-regno" required="" style="font-size: 1rem">
            <input type="hidden" class="update-ref" value=""/>
        </div> 
        <x-slot name="footer">
            <button type="button" class="btn btn-secondary close-btn" data-bs-dismiss="modal">Cancel</button>
            <button onclick="update_cert_param('regno')" type="submit" class="btn btn-success update-cert-regno-btn ladda-button" data-style="expand-right">Update Matric </button>
        </x-slot>
    
</x-admin.modal>
