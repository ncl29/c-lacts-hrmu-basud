<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Storage Directory for Supporting Documents and Images
    |--------------------------------------------------------------------------
    |
    | Per C-LACTS file-handling rule, all files live under one base directory.
    | Default: %LOCALAPPDATA%/<AppName>/files
    |
    */
    'base_dir' => env(
        'FILE_BASE_DIR',
        getenv('LOCALAPPDATA')
            ? rtrim(getenv('LOCALAPPDATA'), '\\/') . DIRECTORY_SEPARATOR . 'CLACTS' . DIRECTORY_SEPARATOR . 'files'
            : storage_path('app/files')
    ),

    /*
    |--------------------------------------------------------------------------
    | File Size Limits
    |--------------------------------------------------------------------------
    |
    | Enforced strictly before and during writes.
    |
    */
    'max_image_size_mb' => (int) env('MAX_IMAGE_SIZE_MB', 10),
    'max_document_size_mb' => (int) env('MAX_DOCUMENT_SIZE_MB', 25),
    'max_total_storage_gb' => (int) env('MAX_TOTAL_STORAGE_GB', 50),
];

