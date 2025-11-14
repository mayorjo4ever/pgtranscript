<?php use Illuminate\Support\Facades\Session; ?>
@if(Session::has('error_message'))
    <div class="alert alert-warning alert-dismissible text-white fade show" role="alert">
        <span class="material-icons font-weight-bold">warning</span> &nbsp;&nbsp;  {{ Session::get('error_message') }}
        <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
    </div>
  @endif

  @if(Session::has('success_message'))
    <div class="alert alert-success alert-dismissible text-white fade show" role="alert">
         <span class="material-icons font-weight-bold">check</span> &nbsp;&nbsp;  {{ Session::get('success_message') }}
       <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
    </div>
  @endif

   @if($errors->any())
    <div class="alert alert-danger alert-dismissible text-white fade show" role="alert">
    @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
   </div>
   @endif