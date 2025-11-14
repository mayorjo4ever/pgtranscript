<div>
    <main class="main-content  mt-0">
        @php $img = asset('img/grad-pix.jpg'); @endphp 
    <div class="page-header align-items-start min-vh-100" style="{{'background-image:url('.$img.')'}}">
      <span class="mask bg-gradient-dark opacity-1"></span>
      <div class="container my-auto">
     
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">                        
                      <i class="material-icons">lock</i>                       
                       &nbsp;
                      Reset Password                     
                  </h4>
                   
                </div>
              </div> 
                
              <div class="card-body">
                  
                  <x-admin.alert></x-admin.alert>
                  
                  <form role="form" class="text-start" action="{{url('portal/forgot-password')}}" method="post">@csrf 
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Recovery Email</label>
                    <input type="text" name="email" id="user-email" class="form-control">
                  </div>
                  
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2  login-btn ladda-button" data-style="expand-right" >Sign in</button>
                  </div>
                  <p class="mt-4 text-sm text-center">                      
                      I have Login ID  - 
                      <i class="material-icons">unlock</i> &nbsp;&nbsp;
                      <a href="{{url('portal/login')}}" class="text-primary text-gradient font-weight-bold">Login Here </a>
                  </p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                made with <i class="fa fa-heart" aria-hidden="true"></i> by
                <a href="https://www.creative-tim.com" class="font-weight-bold text-white" target="_blank">Creative Tim</a>
                for a better web.
              </div>
            </div>
            <div class="col-12 col-md-6">
              
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
</div>