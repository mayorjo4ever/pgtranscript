@props([
  'id' => 'modalId',
  'title' => '',
  'size' => '', // Accepts: '', 'sm', 'lg', 'xl'
  'centered' => true
])

@php
  $sizeClass = match($size) {
    'sm' => 'modal-sm',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    default => '',
  };

  $dialogClass = trim("modal-dialog {$sizeClass} " . ($centered ? 'modal-dialog-centered' : ''));
@endphp

<!-- Trigger Button (Optional) -->
@if ($attributes->has('trigger'))
  {{ $attributes->get('trigger') }}
  @else <!-- no modal
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $id }}">
    {{ $button ?? '' }}
  </button>  -->
@endif

<!-- Modal -->
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  <div class="{{ $dialogClass }}">
    <div class="modal-content">
      
      @if($title || $header)
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        {{ $header ?? '' }}
        <button type="button" class="btn-close btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons md-48 text-danger">close</span></button>
      </div>
      @endif

      <div class="modal-body">
        {{ $slot }}
      </div>

      @isset($footer)
      <div class="modal-footer">
        {{ $footer }}
      </div>
      @endisset

    </div>
  </div>
</div>
