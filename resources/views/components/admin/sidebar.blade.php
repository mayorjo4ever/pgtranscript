<?php use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Session;
?>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="{{url('admin/profile')}}" >
        <!--<img src="../assets/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo" />-->
        <span class="ms-1 font-weight-bold text-white"> {{admin_info(Auth::id())['fullname']}} </span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white @if(Session::get('page')=="dashboard") active bg-gradient-primary @endif" href="{{url('admin/dashboard')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons  md-24 opacity-10">house</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>    
        
           <li class="nav-item  @if(Session::get('page')=="users") active @endif">
            <a class="nav-link text-white  @if(Session::get('page')=="users") active bg-gradient-primary @endif" data-bs-toggle="collapse" href="#usersMenu" role="button" aria-expanded="false" aria-controls="usersMenu">
              <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons md-24 opacity-10">group</i>
              </div>
              <span class="nav-link-text ms-1">Students</span>
            </a>
            <div class="collapse @if(Session::get('page')=="users") show @endif" id="usersMenu">
              <ul class="navbar-nav ms-4">
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="import_users") active bg-primary @endif" href="{{url('admin/import-users')}}">
                    <span class="nav-link-text ms-1">Import Students</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="view_users") active bg-primary @endif" href="{{url('admin/students')}}">
                    <span class="nav-link-text ms-1">All Students </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="process_certs") active bg-primary @endif" href="{{url('admin/cert-data-processing')}}">
                    <span class="nav-link-text ms-1">Certificate Processing</span>
                  </a>
                </li>
               
              </ul>
            </div>
          </li>
      
        <li class="nav-item  @if(Session::get('page')=="transcripts") active @endif">
            <a class="nav-link text-white  @if(Session::get('page')=="transcripts") active bg-gradient-primary @endif" data-bs-toggle="collapse" href="#transcriptMenu" role="button" aria-expanded="false" aria-controls="transcriptMenu">
              <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons md-24 opacity-10">account_box</i>
              </div>
              <span class="nav-link-text ms-1">Transcripts</span>
            </a>
            <div class="collapse @if(Session::get('page')=="transcripts") show @endif" id="transcriptMenu">
              <ul class="navbar-nav ms-4">
                
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="import") active bg-primary @endif" href="{{url('admin/import-transcript-requests')}}">
                    <span class="nav-link-text ms-1">Import New Requests</span>
                  </a>
                </li>
                
                 <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="transcript_search") active bg-primary @endif" href="{{url('admin/transcript-search')}}">
                    <span class="nav-link-text ms-1">Search Transcript </span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">search</i>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="pending") active bg-primary @endif" href="{{url('admin/pending-transcript-requests')}}">
                    <span class="nav-link-text ms-1">Transcript Requests</span>
                  </a>
                </li> 
                
                  <li class="nav-item">
                    <a class="nav-link text-white @if(Session::get('tab')=="pending-grads") active bg-primary @endif" href="{{url('admin/pending-graduation-transcripts')}}">
                      <span class="nav-link-text ms-1">Pending Grad. Transc.</span>
                    </a>
                  </li> 
                  <li class="nav-item">
                    <a class="nav-link text-white @if(Session::get('tab')=="completed-grads") active bg-primary @endif" href="{{url('admin/completed-graduation-transcripts')}}">
                      <span class="nav-link-text ms-1">Completed Grad. Transc.</span>
                    </a>
                  </li>                
                
              </ul>
            </div>
          </li>
          
        <li class="nav-item  @if(Session::get('page')=="courses") active @endif">
            <a class="nav-link text-white  @if(Session::get('page')=="courses") active bg-gradient-primary @endif" data-bs-toggle="collapse" href="#coursesMenu" role="button" aria-expanded="false" aria-controls="coursesMenu">
              <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons md-24 opacity-10">book</i>
              </div>
              <span class="nav-link-text ms-1">Courses</span>
            </a>
            <div class="collapse @if(Session::get('page')=="courses") show @endif" id="coursesMenu">
              <ul class="navbar-nav ms-4">
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="upload-courses") active bg-primary @endif" href="{{url('admin/upload-courses')}}">
                    <span class="nav-link-text ms-1">Upload Courses</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="courses") active bg-primary @endif" href="{{url('admin/courses')}}">
                    <span class="nav-link-text ms-1">View All Courses</span>
                  </a>
                </li>  
              </ul>
            </div>
          </li>
         
          <li class="nav-item">
          <a class="nav-link text-white " href="../pages/billing.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons  md-24 opacity-10">event_note</i>
            </div>
            <span class="nav-link-text ms-1">Memos</span>
          </a>
        </li>
         
        <li class="nav-item  @if(Session::get('page')=="certificates") active @endif">
            <a class="nav-link text-white  @if(Session::get('page')=="certificates") active bg-gradient-primary @endif" data-bs-toggle="collapse" href="#certificateMenu" role="button" aria-expanded="false" aria-controls="certificateMenu">
              <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons md-24 opacity-10">school</i>
              </div>
              <span class="nav-link-text ms-1">Certificates</span>
            </a>
            <div class="collapse @if(Session::get('page')=="certificates") show @endif" id="certificateMenu">
              <ul class="navbar-nav ms-4">
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="cert_setup") active bg-primary @endif" href="{{url('admin/cert-settings')}}">
                    <span class="nav-link-text ms-1">Certificate Setups</span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">settings</i>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="upload_certs_data") active bg-primary @endif" href="{{url('admin/cert-data-upload')}}">
                    <span class="nav-link-text ms-1"> Data Uploading </span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">upload</i>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white @if(Session::get('tab')=="process_certs") active bg-primary @endif" href="{{url('admin/cert-data-processing')}}">
                    <span class="nav-link-text ms-1">Data Processing</span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">cached</i>
                  </a>
                </li>
                 
                <li class="nav-item">
                    <a target="_blank" class="nav-link text-white @if(Session::get('tab')=="master-graduands") active bg-primary @endif" href="{{url('admin/master-graduands')}}">
                    <span class="nav-link-text ms-1">All Master Graduands </span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">users</i>
                  </a>
                </li>
                
                <li class="nav-item">
                    <a target="_blank" class="nav-link text-white @if(Session::get('tab')=="phd-graduands") active bg-primary @endif" href="{{url('admin/phd-graduands')}}">
                    <span class="nav-link-text ms-1">All Ph.D. Graduands </span>&nbsp;&nbsp;
                    <i class="material-icons md-24 opacity-10">users</i>
                  </a>
                </li>
                
               
              </ul>
            </div>
          </li>
         
  
      
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8"> ---   &nbsp; others &nbsp; ---</h6>
        </li>
        
         <li class="nav-item">
          <a class="nav-link text-white @if(Session::get('page')=="general_page") active bg-gradient-primary @endif " href="{{url('admin/date-conversion')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">calendar_month</i>
            </div>
            <span class="nav-link-text ms-1">Date Conversion </span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-white @if(Session::get('page')=="manage_password") active bg-gradient-primary @endif " href="{{url('admin/manage-password')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">lock</i>
            </div>
            <span class="nav-link-text ms-1">Password Management</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-white @if(Session::get('page')=="id_card") active bg-gradient-primary @endif " href="{{url('admin/google-id-card')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">badge</i>
            </div>
            <span class="nav-link-text ms-1">Google ID Card</span>
          </a>
        </li>
        
            <li class="nav-item">
          <a class="nav-link text-white @if(Session::get('page')=="database") active bg-gradient-primary @endif " href="{{url('admin/data-backup-restore')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">storage</i>
            </div>
            <span class="nav-link-text ms-1">Database Backup</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-white" href="{{url('portal/logout')}}">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">logout</i>
            </div>
            <span class="nav-link-text ms-1">Sign Out</span>
          </a>
        </li>
       
      </ul>
    </div>
     
  </aside>