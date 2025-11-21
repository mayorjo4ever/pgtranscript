$(function(){
    
    $('.dataTable').dataTable();
    $('.select2').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select options',
        closeOnSelect: false
      });
    // showpop("how are you");
    enableCertBtns();
    initDatePicker();
});

    
    
 function initDatePicker(){

    flatpickr('.datepicker', {weekNumbers: true, altInput: true,
    altFormat: "F j, Y",
    dateFormat: "Y-m-d H:i",
    // enableTime: true,
    // time_24hr: true
     });

     flatpickr('.datetimepicker', {weekNumbers: true, altInput: true,
        altFormat: "F j, Y H:i",
        dateFormat: "Y-m-d H:i",
         enableTime: true,
         time_24hr: false
         });
    }

    function checkStatus(code){
        if(code===419){
            swal.fire('Your Active Session Has Expired ','You have to login again','error').then((result) => {        
               window.location = "/portal/login";             
             });
        }
    }
    
    function checkAll(){
        // $('.checkbox').prop('checked', true);
        let allChecked = $('.checkbox:checked').length === $('.checkbox').length;
        $('.checkbox').prop('checked', !allChecked); 
        var icon = " <span class='material-icons font-24'>select_all</span> &nbsp; ";
        $('button.checkAll').html(allChecked ? icon+' Select All' :icon+' Unselect All');        
        enableCertBtns();         
    }
    
    function enableCertBtns(){
        var countChecks = $('.checkbox:checked').length; 
        $('span.count-checks').text(countChecks);
        
        if(countChecks > 0) {
            $(".btn-normalize-cert,.btn-generate-cert,.btn-finalize-cert,.btn-reverse-cert").prop('disabled',false); 
            
        } else {
            $(".btn-normalize-cert,.btn-generate-cert,.btn-finalize-cert,.btn-reverse-cert").prop('disabled',true);
        }
    }
    
    function normalize_cert_names(){
        // get ids of all selected students 
        var students = []; var btn = ".btn-normalize-cert";
        var cur_page = $('input.cur_page').val(); 
        var toSwap = 'no'; 
        if($("input[name='toswap']").prop('checked')===true){
            toSwap = 'yes'; 
        }
         $.each($("input[name='students[]']:checked"),function(){
            students.push($(this).val());
         });
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/normalize-cert-names',
               data:{students:students,toSwap:toSwap},
               beforeSend:function(){
                    startLoader(btn);
               },
               success:function(resp){ // alert(resp);    
                  stopLoader(btn);       
                  showpop(resp.message,resp.type);
                  load_student_by_programmes(cur_page);
               }, 
                   error:function(jhx,textStatus,errorThrown){   stopLoader(btn);                         
                    
                    checkStatus(jhx.status); 
                   }
           });
    }
    
    function finalize_cert_names(){
        // get ids of all selected students 
        var students = []; var btn = ".btn-finalize-cert";
        var cur_page = $('input.cur_page').val(); 
         $.each($("input[name='students[]']:checked"),function(){
            students.push($(this).val());
         });
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/finalize-cert-names',
               data:{students:students},
               beforeSend:function(){
                    startLoader(btn);
               },
               success:function(resp){ // alert(resp);    
                  stopLoader(btn);       
                  showpop(resp.message,resp.type);
                  load_student_by_programmes(cur_page); 
               }, 
                   error:function(jhx,textStatus,errorThrown){   stopLoader(btn);                                             
                    checkStatus(jhx.status); 
                   }
           });
    }

    function definalize_cert_names(){
        // get ids of all selected students 
        var students = []; var btn = ".btn-reverse-cert";
        var cur_page = $('input.cur_page').val(); 
         $.each($("input[name='students[]']:checked"),function(){
            students.push($(this).val());
         });
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/definalize-cert-names',
               data:{students:students},
               beforeSend:function(){
                    startLoader(btn);
               },
               success:function(resp){ // alert(resp);    
                  stopLoader(btn);       
                  showpop(resp.message,resp.type);
                  load_student_by_programmes(cur_page); 
               }, 
                   error:function(jhx,textStatus,errorThrown){   stopLoader(btn);                                             
                    checkStatus(jhx.status); 
                   }
           });
    }


    var spin = "<i class='fa fa-spin fa-spinner  text-info' style='font-size:40px'></i>";
    var check = "<i class='material-icons  text-success' style='font-size:40px'>done_all</i>";
    var warn = "<i class='material-icons text-danger' style='font-size:40px'>warning</i>";
        
        
    $(document).on('click','.sync_transcript_request',function(){  
       
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/sync-transcript-requests',
               data:{status:'latest'},
               beforeSend:function(){
                    $('.sync_transcript_request').prop('disabled',true);
                    $('.import_transcript_request').prop('disabled',true);
                    $('.counts').html(spin);   $('input#counts').val(0);
                    $('.report').html(spin + " &nbsp; Getting Updates ");
               },
               success:function(resp){ // alert(resp);    
                  $('.counts').html(resp);                    
                  $('input#counts').val(resp);
                  $('.sync_transcript_request').prop('disabled',false);
                  $('.import_transcript_request').prop('disabled',false);
                  $('.report').html(check + "<br/>"+ resp + "&nbsp; New Request(s) Found ");
                  if(resp > 0 ){
                      setTimeout(function(){
                          $('.import_transcript_request').click(); 
                      },4000);
                  }
               }, 
                   error:function(jhx,textStatus,errorThrown){                         
                    $('.sync_transcript_request').prop('disabled',false);
                    $('.import_transcript_request').prop('disabled',false);
                     $('.counts').html(0);  
                    $('.report').html(warn + "&nbsp; <br/> "+ errorThrown + "&nbsp; ");
                    checkStatus(jhx.status); 
                   }
           });
    });
   
    $(document).on('click','.import_transcript_request',function(){   
        var maxno = $('input#counts').val();         
        if(maxno == "" || maxno <=0 ){
            alert("No Data To Inport, Click On Refresh ");
             exit; 
        } else 
        {
           
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/import-latest-transcript-requests',
               data:{status:'latest', maxno:maxno },
               beforeSend:function(){
                    // $('.sync_transcript_request').prop('disabled',true);
                    $('.import_transcript_request').prop('disabled',true);
                    $('.report').html(spin + " &nbsp; Importing New Requests ");
               },
               success:function(resp){  
                   // alert(resp);    
                   $('.sync_transcript_request').trigger('click');
                   // $('.import_transcript_request').prop('disabled',false);
                   // showToastPosition('bottom-right','Successful',"<span class='font-16 bold text-uppercase'>NOW "+resp['status']+"</span>",'success');
                   $('.report').html(check + "<br/>" + "&nbsp; Import Successful ");
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                    $('.import_transcript_request').prop('disabled',false);
                    $('.report').html(warn + "&nbsp; <br/> "+ errorThrown + "&nbsp; ");
                    checkStatus(jhx.status); 
                   }
           });
       } // end if 
    });
 
    function renamePassport(elem){     
        var $btn = elem;
        var oldName = $btn.data('old');
        var newName = $btn.data('new');
        var imgSelector = "."+$btn.data('img');        
        var loader = "."+$btn.data('id');
        $.ajax({
             headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
            //url: "{{ route('certificates.renamePassport') }}",
            url: "/admin/renamePassport",
            type: "POST",
            beforeSend:function(){ startLoader(loader); },
            data: {
                old_name: oldName,
                new_name: newName,                
            },
            success: function (response) {
                 showpop(response.message,response.type); 
                  stopLoader(loader);
                 $(imgSelector).html(response.view);                                       
            },
            error: function (response) {
               showpop(response.message,response.type); 
               stopLoader(loader);
            }
        });
    } 
 
    //processing certificate 
    function load_uploaded_cert_programmes(){   
        var approval_date_id = $('input#approval_date_id').val();         
        
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/load-uploaded-cert-programmes',
               data:{  approval_date_id:approval_date_id },
               beforeSend:function(){
                   $('.all_programmes').html(spin + " &nbsp; Loading Programmes ");
               },
               success:function(resp){  
                   // alert(resp);    
                   $('.all_programmes').html(resp.view);
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                     checkStatus(jhx.status); 
                   }
           });
       }  
    
     function load_completed_cert_programmes(){   
        var approval_date_id = $('input#approval_date_id').val();                
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/load-completed-cert-programmes',
               data:{  approval_date_id:approval_date_id },
               beforeSend:function(){
                   $('.completed_programmes').html(spin + " &nbsp; Loading Programmes ");
               },
               success:function(resp){  
                   // alert(resp);    
                   $('.completed_programmes').html(resp.view);
                   setTimeout(analyze_certificates(),2000); 
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                     checkStatus(jhx.status); 
                   }
           });
       }  
    
    function analyze_certificates(){
        // showpop('ready to calculate'); 
        let totalCerts = 0;
        let completedCerts = 0;
        let toPrint = 0;
        let remains = 0;

        $(".certificate-analyzer tbody tr").each(function(){
            const checkbox = $(this).find('input[name="programes[]"]');
            const total = parseInt($(this).find('input[name="total_certs[]"]').val()) || 0;
            const completed = parseInt($(this).find('input[name="total_completed_certs[]"]').val()) || 0;
             totalCerts += total;
             completedCerts += completed;
            
            if (checkbox.is(":checked")) {
               const printing =  parseInt($(this).find('input[name="total_completed_certs[]"]').val()) || 0;
               toPrint += printing; 
            }
        });
         
        remains = totalCerts - completedCerts; 

        $("span.total_certs").text(totalCerts);
        $("span.total_completed_certs").text(completedCerts + " [ Remains "+ remains+" ]" );
        $("span.total_printing_certs").text(toPrint); // Change formula if needed
    }

    
    function load_uploaded_student_groups(){   
        var approval_date_id = $('input#approval_date_id').val();         
        
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/load-uploaded-cert-student-groups',
               data:{  approval_date_id:approval_date_id },
               beforeSend:function(){
                   $('.final_loaded_student').html("");
                   $('.all_users_programme').html(spin + " &nbsp; Loading Programmes ");
               },
               success:function(resp){  
                   // alert(resp);    
                    $('.all_users_programme').html(resp.view);
                    $('.final_loaded_student').html("");
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                    checkStatus(jhx.status); 
                   }
           });
       }  
    
    var cur_page = $('input.cur_page').val(); 
    function load_student_by_programmes(page = cur_page) {
            var approval_date_id = $('input#approval_date_id').val();         
            var selected_programme = $('select#loaded_student_programmes').val();
            var accuracy = $('input#accuracy').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  
                },
                type: 'POST',
                url: '/admin/load-uploaded-cert-student-by-programme?page=' + page, // ✅ Send the page here
                data: {
                    approval_date_id: approval_date_id,
                    selected_programme: selected_programme,
                    accuracy:accuracy
                },
                beforeSend: function() {
                    $('.final_loaded_student').html(spin + " &nbsp; Loading Students ");
                },
                success: function(resp) {
                    $('.final_loaded_student').html(resp.view);
                    setTimeout(function(){enableCertBtns();},3000);
                },
                error: function(jhx, textStatus, errorThrown) {  
                    checkStatus(jhx.status); 
                }
            }); 
        }
        
     function set_cur_page(value){  
          $('input.cur_page').val(value); 
     }
     
     function set_default_cert_approval_date(current){
         // alert(current); 
         var btn = ".date_btn_"+current;
         $.ajax({
                headers: {   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')    },
                type: 'POST',
                url: '/admin/set-default-cert-approval-date',
                data: {  current_date: current },
                beforeSend: function() { startLoader(btn); },
                success: function(resp) {
                   showpop(resp.message,resp.type); 
                   stopLoader(btn); 
                   if(resp.type==='success') {window.location.reload();  }
                },
                error: function(jhx, textStatus, errorThrown) {  
                    checkStatus(jhx.status); stopLoader(btn); 
                }
            }); 
         
     }
    function startLoader(elem='',addBtn=false){
        if(elem===""){
          elem = '.ajaxLoader'; 
        }
       if($(elem).length >0){
             var l = Ladda.create(document.querySelector(elem));  
              if(addBtn===true){ $(elem).addClass(' btn p-4 '); }
              l.start(); 
        } 
    }
    
    function stopLoader(elem='',addBtn=false){
        if(elem===""){
          elem = '.ajaxLoader'; 
        }
       if($(elem).length >0){
             var l = Ladda.create(document.querySelector(elem));  
              if(addBtn===true){ $(elem).removeClass(' btn p-4 '); }
              l.stop(); 
        } 
    }
    
  function showpop(messages,  types='success'){
        const notyf = new Notyf({
        position: {
            x: 'center',
            y: 'top',
        } ,
        duration:5000,  dismissible:false, icon: true
        });

        notyf.open({
          type: types,
          message: '<span class="font-weight-700">'+messages+'</span>'
        });
    }

    function toggleModal(id, show = true) {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.display = show ? 'flex' : 'none';
      }
     
     function setValues(value,elem){
         $('span'+elem).html(value); // eg. .cert-regno || .cert-name
         $('input'+elem).val(value); 
         // for datepicker, call init
         initDatePicker();
     }
     function setRef(value){        
         if($('input.update-ref').length > 0) { $('input.update-ref').val(value); }         
     }
     // onclick="setValues('{{$student['record']['name']}}','.cert-name'), setRef('{{$student['record']['id']}}')"
     function update_cert_param(param_type){
         // alert(param_type);
         var value = $('input.cert-'+param_type).val(); 
         var update_ref =  $('input.update-ref').val();          
         var btn = ".update-cert-"+param_type+"-btn"; // regno || name || programme
         var cur_page = $('input.cur_page').val(); 
         
         if(value===""){
             showpop("form must not be empty ","error");
         }
         else {
             $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/modify-uploaded-cert-data',
               data:{  value:value, ref:update_ref,param_type:param_type },
               beforeSend:function(){
                   startLoader(btn);
               },
               success:function(resp){ stopLoader(btn);  
                   showpop(resp.message,resp.type);
                   
                   setTimeout(function(){ $('.close-btn').click(); load_student_by_programmes(cur_page); },2000);
               }, 
                error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                    checkStatus(jhx.status); 
                   }
           });
         } // end else
          
     }
     
     function save_cert_approve_date(){
         // alert(param_type);
         var value = $('input.approve-date').val(); 
         var update_ref =  $('input.update-ref').val(); 
         var btn = ".cert-approve-date-btn"; //
         // alert(value + " - "+update_ref); exit; 
         
         if(value===""){  showpop("Date Must Not Be Empty ","error");  }
         else {
             $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/add-update-cert-approve-date',
               data:{  value:value, ref:update_ref },
               beforeSend:function(){  startLoader(btn);  },
               success:function(resp){ stopLoader(btn);  
                   showpop(resp.message,resp.type);
                   if(resp.type==='success'){
                        setTimeout(function(){ $('.close-btn').click(); window.location.reload(); },2000);
                   }                  
               }, 
                error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                    checkStatus(jhx.status); 
                   }
           });
         } // end else          
     }
     
     function checkProgrammeCompatibility(programme){
         $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/check-programme-compatibility',
                data:{  programme:programme },
                beforeSend:function(){  $('.prog-setup-view').html("<span class='fa fa-spin fa-spinner fa-3x'></span>");  },
                success:function(resp){ // stopLoader();  
                    // showpop(resp.message,resp.type);
                    $('.prog-setup-view').html(resp.view);
                    if(resp.type==='success'){
                         // setTimeout(function(){ $('.close-btn').click(); window.location.reload(); },2000);
                    }                  
                }, 
                 error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
           });
         $('span.raw-programme').html(programme); 
         $('input.keeper').val(programme); 
     }
     
     function create_programme_template(deg_id,name){
         // showpop(deg_id+" = "+name); 
         btn = ".programme-template-btn";
         $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/create-programme-template',
                data:{  deg_id:deg_id,name:name },
                beforeSend:function(){  startLoader(btn);  },
                success:function(resp){ // stopLoader();  
                      showpop(resp.message,resp.type); stopLoader(btn);
                   //  $('.prog-setup-view').html(resp.view);
                    if(resp.type==='success'){
                        setTimeout(function(){ 
                           // checkProgrammeCompatibility($('span.raw-programme').text());
                            checkProgrammeCompatibility($('input.keeper').val());
                        },1000);                       
                    }                  
                }, 
                 error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
           });
     }
     
     function configure_programme_template(prog_id,prog_name){
          // showpop(deg_id+" = "+name); 
         btn = ".programme-template-config-btn";
         // showpop(prog_id+" = "+prog_name); exit; 
         $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/configure-programme-template',
                data:{  prog_id:prog_id,prog_name:prog_name },
                beforeSend:function(){  startLoader(btn);  },
                success:function(resp){ // stopLoader();  
                    showpop(resp.message,resp.type); stopLoader(btn);                   
                    if(resp.type==='success'){
                        setTimeout(function(){ 
                            // checkProgrammeCompatibility($('span.raw-programme').text());
                            checkProgrammeCompatibility($('input.keeper').val());
                        },1000);                       
                    }                  
                }, 
                 error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });
     }
     
     function download_cert_data(data_type="excel"){
         // showpop('you are downloading '+data_type);
         // count all programmes selected for downlod 
         var btn = "."+data_type+"-cert-btn";
         var programmes = []; // var btn = ".btn-normalize-cert";       
         $.each($("input[name='programes[]']:checked"),function(){
            programmes.push($(this).val());
         });
         
         if(programmes.length ===0){
             showpop('No Programme has been selected for download ','error');
         }
         else{            
             // submit request
              $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/download-certificate-data',
               data:{  programmes:programmes,data_type:data_type },
               beforeSend:function(){ /* startLoader(btn);*/  },
               success:function(resp){ /*stopLoader(btn); */ 
                    showpop(resp.message,resp.type); stopLoader(btn);                   
                    if (resp.file_url) {
                           window.location.href = resp.file_url; // triggers browser download
                        }                                                     
                }, 
                 error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });
         }// endelse
     } // end fuction 
     
     
     function download_uncompleted_data(data_type="excel"){
         // showpop('you are downloading '+data_type);
         // count all programmes selected for downlod          
             // submit request
              $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/download-uncompleted-data',
               data:{ data_type:data_type },
               beforeSend:function(){ /* startLoader(btn);*/  },
               success:function(resp){ /*stopLoader(btn); */ 
                    showpop(resp.message,resp.type); // stopLoader(btn);                   
                    if (resp.file_url) {
                           window.location.href = resp.file_url; // triggers browser download
                        }                                                     
                }, 
                 error:function(jhx,textStatus,errorThrown){  stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });         
     } // end fuction 
     
    // when processing transcript
    function search_this_course(code=""){        
         var elem = $(".courses-container");  
         var regno = $("#regno").val();
         btn = $(".loader"); var process = "<span class='fa fa-spin fa-spinner fa-3x'></span>";
         if(code==="") elem.html(""); 
         else {
             // search 
             $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/search-this-course',
               data:{ code:code,regno:regno },
               beforeSend:function(){ btn.html(process);  },
               success:function(resp){btn.html("");                                     
                    if (resp.type==="success") {
                         elem.html(resp.view);
                        }                                                     
                }, 
                 error:function(jhx,textStatus,errorThrown){  btn.html("");   
                     checkStatus(jhx.status); 
                    }
                }); 
         }
               
     } // end fuction 
     
     function add_this_code(elem){
         var row = $(elem).closest("tr.transcript-courses");
         var score = row.find(".course-score").val();
         var code = row.find(".course-code").val();
         var starred = row.find(".course-starred").is(":checked");
         var regno = $("input#regno").val(); 
         var approve_date = $("input#approve_date").val(); 
         var btn = ".btn-"+code;
         if(score===""){
             showpop('Enter The Score','error'); exit; 
         }  
         // submit 
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/add-this-course',
               data:{ code:code,score:score,starred:starred,regno:regno,approve_date:approve_date },
               beforeSend:function(){ startLoader(btn);  },
               success:function(resp){ stopLoader(btn);                                     
                   showpop(resp.message,resp.type);
                   load_my_transcript(regno,approve_date); 
                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });          
         // showpop('you score '+score +' in '+code+" and the star is "+starred);
     }
     
      function remove_this_code(elem){
         var row = $(elem).closest("tr.transcript-courses");        
         var code = row.find(".course-code").val();       
         var regno = $("input#regno").val(); 
         var approve_date = $("input#approve_date").val(); 
         var btn = ".btn-"+code;
          
          if(confirm("Are you sure you want to delete this course "+code)){
         // submit 
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/remove-this-course',
               data:{ code:code,regno:regno,approve_date:approve_date },
               beforeSend:function(){ startLoader(btn);  },
               success:function(resp){ stopLoader(btn);                                     
                   showpop(resp.message,resp.type);
                   load_my_transcript(regno,approve_date); 
                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });      
            } // end confirm
         // showpop('you score '+score +' in '+code+" and the star is "+starred);
     }
     
     
      function load_my_transcript(regno,approve_date){
         var elem = $('.transcript-summary'); var process = "<span class='fa fa-spin fa-spinner fa-3x'></span>";
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/load-my-transcript',
               data:{ regno:regno,approve_date:approve_date },
               beforeSend:function(){ elem.html(process);  },
               success:function(resp){                                    
                   elem.html(resp.view);
                   setTimeout(function(){ initDatePicker(); 
                   $("select#faculty").trigger('change'); 
                   },3000);
                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });          
         // showpop('you score '+score +' in '+code+" and the star is "+starred);
     }
     
     // for transcript request 
     function search_my_transcript(regno){  
        var elem = $('.search-result'); var process = "<span class='fa fa-spin fa-spinner fa-3x'></span>";
         var regno = $("#regno").val();   
         var request_id = $("#request_id").val(); 
         var request_type = $("#request_type").val(); 
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/search-my-transcript',
               data:{ regno:regno, request_id:request_id,request_type:request_type },
               beforeSend:function(){ elem.html(process);  },
               success:function(resp){                                    
                   elem.html(resp.view);
                   setTimeout(function(){ initDatePicker(); 
                   $("select#faculty").trigger('change'); 
                   },3000);
                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });  
        
     }
     
     function search_my_phd_transcript(regno){  
        var elem = $('.search-phd-result'); var process = "<span class='fa fa-spin fa-spinner fa-3x'></span>";
         var regno = $("#regno").val();   
         var request_id = $("#request_id").val(); 
         var request_type = $("#request_type").val(); 
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/search-my-phd-transcript',
               data:{ regno:regno, request_id:request_id,request_type:request_type },
               beforeSend:function(){ elem.html(process);  },
               success:function(resp){                                    
                   elem.html(resp.view);
                   setTimeout(function(){ initDatePicker(); 
                   $("select#faculty").trigger('change'); 
                   },3000);
                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });  
        
     }
     
      function search_general_transcript(form){  
        var elem = $('.search-result'); var process = "<span class='fa fa-spin fa-spinner fa-3x'></span>";
         var form = form.serialize();   
            $.ajax({
               headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')   },
               type:'post',
               url:'/admin/transcript-search',
               data:form,
               beforeSend:function(){ elem.html(process);  },
               success:function(resp){     
                   if(resp.type==="success") {
                        elem.html(resp.view);
                   }
                  if(resp.type==="error") {
                        elem.html(resp.message);
                   }
                  

                }, 
                 error:function(jhx,textStatus,errorThrown){ stopLoader(btn);   
                     checkStatus(jhx.status); 
                    }
                });          
     }
     
     function AddNewStudent(){
     let form = $('#new_student_form').serialize();
     let btn = ".new-student-btn";    
     $.ajax({
            headers:{
              'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
            },
            type:'post',
            url:'/admin/add-new-student',
            data:form,
            beforeSend:function(){  startLoader(btn); },
            success:function(resp){  
                stopLoader(btn);
                 if(resp.type==="success"){ 
                 showpop(resp.message);  
             }
                if(resp.type==="error"){
                  var msg = "";
                  $.each(resp.errors,function(prefix,val){
                       msg+="- <span>"+val[0]+"</span><br/> ";
                  });
                 showpop(msg,'error');  
             }
            }, 
                error:function(jhx,textStatus,errorThrown){  
                  checkStatus(jhx.status); stopLoader(btn);
                }
           });
     }
     

     //processing transcript  
    function load_departments(fact_id){   
        elem = $("#fact_department"); 
        var user_dept = $("#user_dept").val();
        $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/load-departments',
               data:{ fact_id:fact_id ,user_dept:user_dept  },
               beforeSend:function(){
                 elem.html("<option value=''>Loading..</option>");
               },
               success:function(resp){  
                   // alert(resp);    
                   elem.html(resp.view);
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                     checkStatus(jhx.status); 
                   }
           });
       }  
    
    function transcriptPrintingNotification() {
        if($(".notify-printed").length > 0){
              var id = $('#print-id').val();
              $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/update-transcript-printout/'+id,
               data:{  id:id }, 
               success:function(resp){
                  showpop(resp.message,resp.type);
               }, 
                   error:function(jhx,textStatus,errorThrown){  
                     checkStatus(jhx.status); 
                   }
           });              
         }
          
    }
    
    function myAfterPrintFunction() {
         alert("Print finished or cancelled.");
    }
    
    $(window).on("afterprint", transcriptPrintingNotification);
    // $(window).on("beforeprint", myAfterPrintFunction);

    // Detect Ctrl+P
//    $(document).on("keydown", function (e) {
//        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "p") {
//            console.log("Ctrl+P detected");
//            // e.preventDefault(); // uncomment if you want to block native print
//            myBeforePrintFunction();
//        }
//    });
   
    // prevent input text from auto submit
    $(document).on("keydown", "form input[type='text']", function(e) {
    if (e.key === "Enter") {
        e.preventDefault(); // stop form from submitting
        $(this).trigger("change"); // manually fire change event
    }
    });
   
     
     $(function(){
         $("input#course_finder").on('change',function(){
             var code = $(this).val(); 
             search_this_course(code); 
         });
         
           
         if($('.transcript-summary').length > 0){
             var regno = $("input#regno").val(); 
             var approve_date = $("input#approve_date").val(); 
             load_my_transcript(regno,approve_date); 
         }
         
             
         if($('#faculty').length > 0){
                $('#faculty').trigger('change');
          }
         
         $('form#transcript_submission').on('submit',function(e){
            e.preventDefault(); 
             alert('subitted'); 
         });
         
     });
     
     
     
     /// 3213-7045-5083 