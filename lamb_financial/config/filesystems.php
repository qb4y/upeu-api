<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 'minio'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
 
        'minio-lamb' => [
            'driver' => 's3',
            'endpoint' => 'https://lamb-files.upeu.edu.pe',
            'use_path_style_endpoint' => true,
            'key' => env('LAMB_MINIO_KEY'),
            'secret' => env('LAMB_MINIO_SECRET'),
            'region' => env('LAMB_MINIO_REGION'),
            'bucket' => env('LAMB_MINIO_BUCKET'),
        ],
        'minio-academic' => [
            'driver' => 's3',
            'endpoint' => env('LAMB_ACADEMIC-MINIO_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'key' => env('LAMB_ACADEMIC-MINIO_KEY'),
            'secret' => env('LAMB_ACADEMIC-MINIO_SECRET'),
            'region' => env('LAMB_ACADEMIC-MINIO_REGION'),
            'bucket' => env('LAMB_ACADEMIC-MINIO_BUCKET'),
        ],
        'minio-talent' => [
            'driver' => 's3',
            'endpoint' => env('TALENT_MINIO_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'key' => env('TALENT_MINIO_KEY'),
            'secret' => env('TALENT_MINIO_SECRET'),
            'region' => env('TALENT_MINIO_REGION'),
            'bucket' => env('TALENT_MINIO_BUCKET'),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
        'minio' => [
            'driver' => 's3',
            'endpoint' => env('MINIO_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

    ],

];
