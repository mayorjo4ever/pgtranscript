<?php use Illuminate\Support\Facades\Session; ?>

  <script src="{{asset('js/jquery-3.7.1.js')}}"></script>
  <script src="{{asset('js/core/popper.min.js')}}"></script>
  <script src="{{asset('js/core/bootstrap.min.js')}}"></script>
  <script src="{{asset('js/plugins/perfect-scrollbar.min.js')}}"></script>
          
  <script src="{{asset('js/plugins/smooth-scrollbar.min.js')}}"></script>
  <script src="{{asset('js/plugins/chartjs.min.js')}}"></script>
  <script src="{{asset('js/material-dashboard.min.js?v=3.1.0')}}"></script>
  <script src="{{asset('js/custom.js')}}"></script>
  <script src="{{asset('js/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('js/select2.min.js')}}"></script>
  <script src="{{ asset('js/notyf.min.js')}}"></script>

  <script src="{{asset('./fontawesome/js/all.min.js') }}"></script>
  <script src="{{asset('js/spin.js')}}"></script>    
  <script src="{{asset('js/ladda.js')}}"></script>    
  <script src="{{asset('js/flatpickr.min.js')}}"></script>    
      
  
  @if(in_array(Session::get('tab'),["upload_certs_data","import_users","upload-courses"])) 
    <script src="{{asset('js/dropzone5.9.js')}}"></script>
@endif
 