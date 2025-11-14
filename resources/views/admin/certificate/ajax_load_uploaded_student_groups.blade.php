<?php # print_r($uploaded_programmes->toArray()); ?>
 
<div class="input-group">  
                               
    <select multiple="multiple" name="student_programme[]" id="loaded_student_programmes" onchange="load_student_by_programmes($('.cur_page').val())" class="form-control border border-dark select2 font-weight-bold " style="height:200px">
        <optgroup label="Select Programme">
            @foreach($uploaded_programmes as $k=>$programme)
            <option value="{{$programme['raw_programme']}}">
             {{$programme['raw_programme']}}  - ( {{$programme['total_completed'] ." / ".$programme['total']}} )
            </option>
            @endforeach
        </optgroup>
    </select> <button type="button" onclick="load_student_by_programmes()" class="btn btn-sm btn-info mt-2 pt-2 pb-2 border border-dark font-weight-bold"><span class="fa fa-play fa-2x"></span></button>
   </div>