@props(['header' => null, 'footer' => null])
<div class="row">
        <div class="col-12">
          <div class="card my-4">
            @isset($header)
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">
                    {{ $header }}
                </h6>
              </div>
            </div>
            @endisset

            <div class="card-body px-3 pb-2">

                {{ $slot }}

            </div><!--./ card-body -->

            @isset($footer)
                <div class="card-footer text-muted bg-light">
                    {{ $footer }}
                </div>
            @endisset
        </div><!--./ card -->
        </div><!--./ col-12 -->
    </div><!--./ row -->