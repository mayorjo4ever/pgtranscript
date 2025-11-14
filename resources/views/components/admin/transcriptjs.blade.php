
<script src="{{asset('bower/js/jquery.min.js')}}"></script>    
<script src="{{asset('bower/js/bootstrap.min.js')}}"></script>    

<script src="{{ asset('js/notyf.min.js')}}"></script>

<script>
    
    
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
         //
          if($(".notify-memo-printed").length > 0){              
              var id = $('#print-id2').val();
              $.ajax({
               headers:{
                 'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')  
               },
               type:'post',
               url:'/admin/update-memo-printout/'+id,
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
 
    $(window).on("afterprint", transcriptPrintingNotification);
    // $(window).on("beforeprint", myAfterPrintFunction);
    
    
    
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
    
    function checkStatus(code){
        if(code===419){
            swal.fire('Your Active Session Has Expired ','You have to login again','error').then((result) => {        
               window.location = "/portal/login";             
             });
        }
    }
        
    </script>
            