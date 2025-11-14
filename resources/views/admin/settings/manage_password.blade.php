@extends('layouts.admin_layout')
@section('bedcrumb') Account  @endsection
@section('page_title') Password Management  @endsection
@section('content')

<div class="container-fluid py-4">

    <x-admin.alert></x-admin.alert>

    <x-admin.card header="Change Your Password" footer="Always keep your password Secured">

        <form  role="form" class="text-start" method="POST">@csrf

        <div class="row m-3 p-3 pt-1">
            <div class=" col-lg-6 col-md-6">
                    <x-forms.text-input label="Old Password" type="password" name="current_password"></x-forms.text-input>
                    <x-forms.text-input label="New Password" type="password" name="new_password"></x-forms.text-input>
                    <x-forms.text-input label="Confirm Password" name="confirm_password" type="password"></x-forms.text-input>
                    <x-forms.button type="submit">Reset Password &nbsp;  <i class="material-icons">key</i></x-forms.button>
            </div>
        </div>
        </form>
    </x-admin.card>

</div>

@endsection