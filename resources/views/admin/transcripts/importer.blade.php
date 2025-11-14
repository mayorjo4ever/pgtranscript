@extends('layouts.admin_layout')
@section('bedcrumb') Transcripts @endsection
@section('page_title') Import New Transcript Requests @endsection

@section('content')
 <div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Import New</h6>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
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
                          <button class="btn btn-primary import_transcript_request"> Import <i class="material-icons">download</i> </button>
                          <input type="hidden" class="form-control" name="counts" id="counts" />
                          <button class="btn btn-success sync_transcript_request ladda-button" data-style="expand-right"> Refresh <i class="material-icons">sync</i> </button>
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
            </div>
          </div>
        </div>
     </div>
 </div>
@endsection