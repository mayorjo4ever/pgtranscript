<div>
    <main class="main-content  mt-0">
       
    <div class="page-header align-items-start min-vh-100" >
      <span class="mask bg-gradient-secondary opacity-8"></span>
      <div class="container my-auto">

        <div class="row">
          <div class="col-md-5" style="border-radius:20px;  min-height:500px;  background:url({{asset('img/unilorin.jpg')}}); background-repeat: no-repeat; background-size:100% 120%;"> </div>
          <div class="col-lg-5 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-info shadow-info border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-0 mb-0">
                      <i class="material-icons">lock</i>
                       &nbsp;
                      Login
                  </h4>
                </div>
              </div>
              <div class="card-body">

                  <x-admin.alert></x-admin.alert>
                  <center><b>Unilorin Postgraduate School</b></center>
                  
                  <form id="loginForm" role="form" class="text-start" action="javascript:void(0);" method="post">@csrf
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="username" id="username" class="form-control" style="font-size: 20px">
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="user-password"  class="form-control" style="font-size: 20px">
                  </div>
                  <div class="form-check form-switch d-flex align-items-center mb-3">
                    <input class="form-check-input" onclick="toggleShowPassword()" type="checkbox" id="rememberMe" >
                    <label class="form-check-label mb-0 ms-3" for="rememberMe">Show Password</label>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-info w-100 mt-1 mb-3 login-btn ladda-button" data-style="expand-right" >Sign in</button>
                  </div>
                  <p class="mt-2 text-sm text-center"> 
                       <i class="material-icons">unlock</i> &nbsp;&nbsp;
                      <a href="{{url('portal/forgot-password')}}" class="text-primary text-gradient font-weight-bold">Reset Password </a>
                  </p>
                </form>
                 <hr/>
                 <div class="row">
                 <div class="col-12 align-items-center">
                     <center>
                        <a href="https://docs.google.com/forms/d/e/1FAIpQLSetCIFtMjXAAke8L-sDeeos3iTDrSX2yDUWhcaKPhjk17S6KQ/viewform" class="font-weight-bold text-white btn-sm btn btn-success align-items-center" target="_blank">PG Transcript Form </a> 
                        &nbsp;  &nbsp;  &nbsp;
                        <a href="https://login.remita.net/remita/onepage/2757205950/service.spa" class="font-weight-bold text-white btn btn-sm btn-info align-items-center" target="_blank">Pay for Transcript </a>
                     </center>
                 </div>
                    
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>
     
    </div>
  </main>
</div>