<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Students @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')
 <x-admin.alert></x-admin.alert>
<div class="container-fluid py-4">
       <div class="row">
           <div class="col-md-12">  
                <x-admin.card header="Database Backup and Restore">
                    
                     <x-admin.card id="database" >
                         <div class="row">                             
                             <div class="col-md-6 col-sm-12 col-xl-6">
                                 <p class="text-danger mb-4" style="font-size:1.3rem">Select Operation </p>
                                  <div class="form-group mb-3">
                                        <div class="radio-wrapper-8 float-md-start">
                                            <label class="control-label radio-wrapper-8"  style="font-size: 1rem">
                                                <input class="data-operations form-radio w-50" type="radio" value="backup" name="operation" checked=""/>
                                            <span>Backup </span></label>
                                        </div>
                                       &nbsp; &nbsp; &nbsp; &nbsp; 
                                       <div class="radio-wrapper-8 float-md-end">
                                        <label class="control-label radio-wrapper-8" style="font-size: 1rem">
                                            <input class="data-operations form-radio" type="radio" value="restore" name="operation" />
                                        <span>Restore </span></label>      
                                       </div>
                                   </div>
                                 <p>&nbsp;</p>
                                 
                                 <div class="backup-div">
                                     <form method="post" action="{{url('admin/backup-db')}}">@csrf
                                     <h5 class="bg-light p-2"> Back-Up Database</h5>
                                      <div class="input-group"> 
                                          <input class="form-control form-control-lg border border-1 border-dark" style="height:55px; font-size: 1.5rem;" type="password" name="backup_key" placeholder="Backup Key" >
                                         <button class="btn btn-primary btn-lg p-3" type="submit">Back-Up</button>
                                        </div>
                                     </form>
                                 </div>
                                
                                 <div class="restore-div" style="display:none"> 
                                     <form method="post" action="{{url('admin/restore-sql')}}" enctype="multipart/form-data">@csrf
                                     
                                     <h5 class="bg-light p-2"> Restore Database</h5>
                                       <div class="">
                                           <div class="form-group">
                                            <label>Restore File (.ZIP): </label><br>
                                            <input class="form-control form-control-lg border border-1 border-dark" type="file" name="sql_file" required>
                                           </div>
                                           <div class="input-group mt-3">
                                           <input class="form-control form-control-lg border border-1 border-dark" style="height:55px;  font-size: 1.5rem;" type="password" name="restore_key" placeholder="Restore Key" >
                                           <button class="btn btn-primary btn-lg p-3" type="submit">Restore</button>
                                        </div>
                                       </div>
                                     </form>
                                 </div>
                             </div><!-- ./ col-md-6  -->
                             
                         </div>
                          
                         
                                   
                                
                         
                         <form action="{{ url('admin/google-id-card-upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                              
                             </div>
                        </form>
                    </x-admin.card>
                    
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      
       
</div>


<!-- In your <head> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet" />

<!-- Before your closing </body> tag -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    
   Dropzone.autoDiscover = false;

let myDropzone = new Dropzone("#myDropzone", {
    paramName: "file",
    maxFiles: 1,
    acceptedFiles: ".xlsx,.xls",
    method: "post",
    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
    init: function() {
        this.on("sending", function(file, xhr) {
            xhr.responseType = 'blob';   // ✅ important
        });

        this.on("success", function(file, response) {
            let blob = response; // already blob
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "converted_dates.xlsx";
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        });
    }
});
</script>
    
    

<style>
    body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .upload-area {
            margin-bottom: 30px;
        }
        
        .dropzone {
            border: 2px dashed #0087F7;
            border-radius: 10px;
            background: #f9f9f9;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dropzone:hover {
            border-color: #005cbf;
            background: #f0f8ff;
        }
        
        .dropzone .dz-message {
            font-size: 18px;
            color: #666;
            margin: 0;
        }
        
        .uploaded-images {
            margin-top: 30px;
        }
        
        .image-preview {
            display: inline-block;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            position: relative;
        }
        
        .image-preview img {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }
        
        .image-info {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>

@endsection