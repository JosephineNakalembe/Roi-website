<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('media_url')) {
    function media_url(string $path): string
    {
        if (!$path) {
            return '';
        }

        return Storage::disk('public')->url($path);
    }
}
