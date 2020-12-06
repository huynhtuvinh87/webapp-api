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

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('API_URL').'/storage',
            'visibility' => 'public',
        ],

        /** The scripts disk will allow you to easily find scripts to run externally */
        'scripts' => [
            'driver' => 'local',
            'root' => storage_path('app/scripts'),
            'visibility' => 'public',
        ],

        's3_us' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_US_REGION'),
            'bucket' => env('AWS_US_BUCKET'),
            'url' => env('AWS_US_URL'),
        ],

        's3_ca' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_CA_REGION'),
            'bucket' => env('AWS_CA_BUCKET'),
            'url' => env('AWS_CA_URL'),
        ],

        // 3rd Party Integrations

        'alc_sftp' => [
            'driver' => 'sftp',
            'host' => env("ALC_SFTP_HOST"),
            'username' => env("ALC_SFTP_USER"),
            'password' => env("ALC_SFTP_PASS"),
            'privateKey' => env("ALC_SFTP_SSH_PRIVATE_KEY_PATH"),
            'permPublic' => 0755,

            'root' => env("ALC_SFTP_FOLDER", '/upload'),
            // 'visibility' => 'public',

            // NOTE: Leaving these settings here in case we need to adjust them
            // Settings for SSH key based authentication...

            // Optional SFTP Settings...
            // 'port' => 22,
            // 'timeout' => 30,
        ]

    ],

    // 10240 = 10MB
    'max_size' => env('MAX_FILE_SIZE', 10240),

];
