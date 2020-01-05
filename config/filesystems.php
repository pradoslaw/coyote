<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "s3", "rackspace"
    |
    */

    'default' => 'public',

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

    'cloud'   => 's3',
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks'   => [
        'public_fs'     => [
            'driver' => 'local',
            'root'   => public_path() . '/uploads',
        ],
        'local_fs'        => [
            'driver' => 'local',
            'root'   => storage_path() . '/app'
        ],

        'local'     => [
            'driver' => 's3',
            'use_path_style_endpoint' => true,
            'key' => env('AWS_ACCESS_KEY_ID', docker_secret('AWS_ACCESS_KEY_FILE')),
            'secret' => env('AWS_SECRET_ACCESS_KEY', docker_secret('AWS_SECRET_ACCESS_KEY_FILE')),
            'region' => 'us-east-1',
            'bucket' => 'local',
            'endpoint' => env('MINIO_ENDPOINT', docker_secret('MINIO_ENDPOINT_FILE')),
            'disable_asserts' => true
        ],
        'public'     => [
            'driver' => 's3',
            'use_path_style_endpoint' => true,
            'key' => env('AWS_ACCESS_KEY_ID', docker_secret('AWS_ACCESS_KEY_FILE')),
            'secret' => env('AWS_SECRET_ACCESS_KEY', docker_secret('AWS_SECRET_ACCESS_KEY_FILE')),
            'region' => 'us-east-1',
            'bucket' => 'public',
            'endpoint' => env('MINIO_ENDPOINT', docker_secret('MINIO_ENDPOINT_FILE')),
            'disable_asserts' => true,
            'url' => env('AWS_URL', docker_secret('AWS_URL_FILE')),

            'cache' => [
                'store' => 'redis',
                'expire' => 600,
                'prefix' => 's3',
            ]
        ],
        'log'        => [
            'driver' => 'local',
            'root'   => storage_path() . '/logs'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed files
    | --------------------------------------------------------------------------
    |
    | List of allowed extensions
    */
    'upload_mimes' => 'jpg,jpeg,gif,png,zip,rar,txt,pdf,doc,docx,xls,xlsx,cpp,7z,7zip,patch,webm',

    /*
    | --------------------------------------------------------------------------
    | Max file size
    | --------------------------------------------------------------------------
    |
    | Max allowed file size (in Mb)
    */
    'upload_max_size' => '20'

];
