<?php use Carbon\Carbon; ?>
@extends('layouts.admin_layout')
@section('bedcrumb') Students @endsection
@section('page_title') {{ $page_info['title']}} @endsection

@section('content')

<div class="container-fluid py-4">
     
       <x-admin.alert></x-admin.alert>
        
       <div class="row">
           <div class="col-md-12">  
                <x-admin.card header=" Import New Student Data">
                    <table class="table">                         
                        <tbody>
                            <tr>
                                <th class="bg-gray w-30"> What To Upload :  </th>
                                <th class="bg-gray font-bold h3 text-danger"> 
                                      Student Data From Portal   </th>
                            </tr>
                        </tbody>
                    </table>
                    
                     <x-admin.card id="cert-excel" >
                         <p class="h6 text-danger">Do Not Modify The Header of The Excel FIle  </p>
                         <p>Download Sample of the Excel File Here To Upload &nbsp; &nbsp;
                             <img class="img img-thumbnail" src="{{asset('img/data/excel.png')}}" width="80" height="80" />
                         </p>
                         
                         <form action="{{ url('admin/upload-new-student-data') }}" class="dropzone"  id="excelDropzone" enctype="multipart/form-data">
                          @csrf
                      </form>
                    </x-admin.card>
                    
                    
                </x-admin.card>
           </div> <!--./ col-md-12 --> 
            
           
       </div><!-- ./ row -->
      
       
</div>


<!-- In your <head> -->
<!--<link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" rel="stylesheet" />-->

<!-- Before your closing </body> tag -->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>-->

<script>
     Dropzone.autoDiscover = false;
     Dropzone.options.passportDropzone = {
        paramName: "files",
        uploadMultiple: true,
        parallelUploads: 10,
        maxFilesize: 5, // MB
        acceptedFiles: '.jpeg,.jpg,.png',
        addRemoveLinks: true,

        init: function () {
            this.on("successmultiple", function (files, response) {
                // ✅ Notification after multiple uploads
                alert(response.message || "Images uploaded successfully!");
            });

            this.on("errormultiple", function (files, response) {
                alert("An error occurred during upload.");
            });

            this.on("error", function (file, response) {
                alert("Error: " + response);
            });
        }
    };
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