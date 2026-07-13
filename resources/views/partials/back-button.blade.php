@php
    $fallbackUrl = $fallback ?? (auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : route('shop.index'));
@endphp
<button type="button" class="back-button" onclick="goBack(this)" data-fallback="{{ $fallbackUrl }}" aria-label="Go back">&lsaquo;</button>
