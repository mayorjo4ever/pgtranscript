@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') Import New Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            
            <x-admin.card header="Import New Requests">
              @can('import-new-transcript-request')
               
              @else 
                <!-- <h2 class="danger"> You Are Not Authorized To Import </h2> -->
                @endcan

               <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Found </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                              <span class="material-icons large p-3" style="font-size: 36px">group</span>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                              <span class="mb-0 h6 text-sm"> Found &nbsp;<span class="counts h4">0</span> &nbsp;New Request (s) </span>
                            <p class="text-xs text-secondary mb-0 mt-2">Click on Import Button To Download </p>
                          </div>
                        </div>
                      </td>

                      <td class="align-middle">
                          <button style="display: none" class="btn btn-primary import_transcript_request"> Import <i class="material-icons">download</i> </button>
                          <input type="hidden" class="form-control" name="counts" id="counts" />
                          <button class="btn btn-success sync_transcript_request ladda-button" data-style="expand-right">Import New Ones <i class="material-icons">sync</i> </button>
                      </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="mt3 pt-3">
                            <p class="report text-center h4 alert font-weight-normal"> </p>
                        </td>

                    </tr>

                  </tbody>
                </table>
              </div>
               
            </x-admin.card>
        </div><!-- ./ col-12 -->
     </div>
 </div>
@endsection