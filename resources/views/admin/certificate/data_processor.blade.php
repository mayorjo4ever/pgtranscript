<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Certificate @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12"> <?php $current_approval = get_current_approve_date(); ?>
              
                <x-admin.card header="Processing Certificate For : {{Carbon::parse( $current_approval->app_date)->format('D, jS F, Y') }} Senate Approval">                     
                 <div class="tab-container">
                    <div class="tabs">
                      <div class="tab active" data-tab="dashboard">
                        <span class="material-icons">school</span>
                        <span> Uploaded Programmes</span>
                      </div>
                      <div class="tab" data-tab="profile">
                        <span class="material-icons">group</span>
                        <span> Names & Photographs </span>
                      </div>
                      <div class="tab" data-tab="settings">
                        <span class="material-icons">download</span>
                        <span>Download Processed Data </span>
                      </div>
                    </div>                    
                    
                    <input name="approval_date_id" id="approval_date_id" type="hidden" value="{{$current_approval->id}}" />
                     
                    <div class="tab-content active" id="dashboard">
                      <div class="card-body">                          
                          <button type="button" class="btn btn-primary p-3 load_uploaded_cert_programmes" onclick="load_uploaded_cert_programmes()"> Show All Programmes &nbsp; <span class="material-icons font-24">replay</span> </button>
                          <div class="all_programmes"></div>
                      </div>
                    </div>

                    <div class="tab-content" id="profile">
                      <div class="card-body">                          
                          <table class="table">
                              <tr>
                                  <td style="width:30%"><button type="button" class="btn btn-primary p-3 load_uploaded_cert_programmes" onclick="load_uploaded_student_groups()"> Show Available Programmes &nbsp; <span class="material-icons font-24">replay</span> </button> </td>
                                  
                                  <td style="width:20%"><input type="text" class="cur_page form-control p-3 font-weight-bold border border-dark" placeholder="Current Page" value="1" style="max-width: 100px; font-size: 1.2rem" /> <label>Current Page </label></td>
                                  
                                  <td style="width:50%" class="p-1" rowspan="2">   <div class="all_users_programme"></div> </td>
                              </tr>
                              <tr>
                                  <td colspan="2">
                                      <div class="ps-0 font-weight-bold">
                                        <input class="form-range" onchange="$('span.accuracy').text($(this).val())"  type="range" name="accuracy" id="accuracy" value="80" step="1" min="20" max="100">                                        
                                     </div>
                                      <label class="w-80 mb-0  font-weight-bold" for=""> Passport's Accuracy: &nbsp;&nbsp; <span class="accuracy">80</span> % </label>
                                  </td>
                                  
                              </tr>
                          </table> 
                          <div class="final_loaded_student"></div>
                      </div>
                    </div>

                    <div class="tab-content" id="settings">
                      <div class="card-body"> 
                         <button type="button" class="btn btn-info p-3 load_completed_cert_programmes" onclick="load_completed_cert_programmes()"> Show All Programmes &nbsp; <span class="material-icons font-24">replay</span> </button>
                         <button type="button" class="btn btn-success float-end p-3  m-2 excel-cert-btn ladda-button" data-style="expand-right" onclick="download_cert_data('excel-convo')"> <span class="material-icons font-24">download</span>  Convocation &nbsp; ( <span class="count-checks">0</span> )</button>
                         <button type="button" class="btn btn-success float-end p-3  m-2 excel-cert-btn ladda-button" data-style="expand-right" onclick="download_cert_data('excel')"> <span class="material-icons font-24">download</span>  Excel &nbsp; ( <span class="count-checks">0</span> )</button>
                         <button type="button" class="btn btn-primary float-end p-3  m-2 passport-cert-btn ladda-button" data-style="expand-right" onclick="download_cert_data('passport')"> <span class="material-icons font-24">download</span>  Passports  &nbsp; </button>
                         <button type="button" class="btn btn-dark float-end p-3  m-2 graduation-status-btn ladda-button" data-style="expand-right" onclick="download_cert_data('graduation-status-update')"> <span class="material-icons font-24">upload</span> Update Grads Uploaded  &nbsp; ( <span class="count-checks">0</span> )</button>
                         <button type="button" class="btn btn-warning float-end p-3  m-2 passport-cert-btn ladda-button" data-style="expand-right" onclick="download_uncompleted_data('excel')"> <span class="material-icons font-24">download</span>  Uncompleted  &nbsp; ( <span class="count-checks">0</span> )</button>
                         <button type="button" class="btn btn-light float-end p-3 m-2 mr-2 checkAll" onclick="checkAll(),analyze_certificates()"> <span class="material-icons font-24">select_all</span> &nbsp;  Select All </button>                                                  
                         <div class="completed_programmes"></div>
                      </div>
                      </div>
                    </div>
                  </div>  
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
                       
       </div><!-- ./ row -->      
       
</div>

<x-admin.tabcontent></x-admin.tabcontent>
 

@endsection