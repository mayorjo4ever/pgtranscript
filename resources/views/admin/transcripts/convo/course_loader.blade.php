<table class="table table-responsive w-100">    
<tr>     
    <td><strong>Units  </strong></td>
    <td><strong>Code</strong></td>
    <td><strong>Starred </strong><br/><small>Select if Starred</small></td>
    <td><strong></strong>Score</td>   
</tr>
    @foreach($courses as $course)
    <tr class="transcript-courses">           
    <td><strong>{{$course->type}} | {{$course->units}} Units  </strong></td>
    <td><strong>{{$course->code}} <br/> </strong>
        <small>{{$course->title}}</small>
        <input type="hidden" class="course-code" value="{{$course->code}}" />
    <td>
         <div class="form-check form-switch ps-0">          
            <input class="form-check-input ms-auto checkbox course-starred" type="checkbox" name="course-starred" value="1"  @if($course->transcripts->isNotEmpty()) {{ ($course->transcripts->first()->starred == true) ? 'checked':''}} @endif >
               <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="flexSwitchCheckDefault"></label>
             </div>
    </td>
    <td>
        <input type="text" placeholder="Score" class="course-score form-control-lg font-weight-bold w-30" style="font-size: 1rem; min-width:80px" value=" @if($course->transcripts->isNotEmpty()){{$course->transcripts->first()->score}}@endif"/>
        <button type="button" onclick="add_this_code($(this))" class="btn btn-lg btn-info mt-1 code-add ladda-button btn-{{$course->code}}" data-t="style="expand-right"> Add </button>
    </td>    
    
</tr>
@endforeach

</table>

