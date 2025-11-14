 @foreach($departments as $dept)
     <option value="{{$dept->dept_id}}" @selected($dept->dept_id==$user_dept)>{{$dept->name}} </option>
 @endforeach