<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::namespace('API')->group(function () {
    foreach (File::allFiles(base_path('routes/api')) as $file) {
        require $file->getRealPath();
    }
});
