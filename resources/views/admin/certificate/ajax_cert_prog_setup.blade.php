<table class="table table-responsive">
    <tbody>
        <tr>
            <th class="table-info w-35">Selected Programme</th>
            <td><span class="raw-programme">{{$data['programme']}}</span></td>
        </tr>
        <tr>
            <th class="table-info">Matched Degree: </th>
            <td><span class="">{{$info['acronymn']}}</span></td>
        </tr>
        <tr>
            <th class="table-info">Degree ID: </th>
            <td><span class="">{{$info['id']}}</span></td>
        </tr>
        <tr>
            <th class="table-info">Degree Name: </th>
            <td><span class=""></span>{{$info['name']}}</td>
        </tr>
        <tr>
            <th class="table-info">Programme Name: </th>
            <td><span class=""></span>{{$info['field']}}</td>
        </tr>
        <tr>
            <th class="table-info">Degree Setup Available ?: </th>
            <td><?php $temp_info = program_available($info['id'],$info['field']); ?>
                @if($temp_info['available'])
                <span class="text-success"> Degree Available &nbsp;  <i class="material-icons">check_circle</i></span> &nbsp; [ {{$info['id']}} ]
                <br/> 
                @else 
                <span class="text-danger"> Template Not Available &nbsp;  <i class="material-icons">warning</i></span>
                <br class=""/> 
                <button onclick="create_programme_template('{{$info['id']}}','{{$info['field']}}')" type="submit" class="btn btn-success mt-3 pt-2 programme-template-btn ladda-button" data-style="expand-right"> Create The Template </button>
                @endif
            </td>
        </tr>
         @if($temp_info['available'])
          <tr>
            <th class="table-info">Programme Configured ?: </th>
            <td>
               @if(programmeConfigured($data['programme']))     
                 <span class="text-success"> Programme Configured &nbsp;  <i class="material-icons">check_circle</i></span> &nbsp; [ {{$temp_info['id']}} ]
               @else 
                  <span class="text-danger"> Not Configured &nbsp;  <i class="material-icons">warning</i></span>
                   <br class=""/> 
                <button onclick="configure_programme_template('{{$temp_info['id']}}','{{$data['programme']}}')" type="submit" class="btn btn-success mt-3 pt-2 programme-template-config-btn ladda-button" data-style="expand-right"> Configure </button>                  
               @endif
            </td>
          </tr>
         @endif
    </tbody>
</table>

