<?php
return [
    // APP_EVN
    'app_env' => env('APP_ENV') == 'production' ? 'production' : 'development',

    // The default default_client name which configured in `development` or `production` section
    'default_client' => 'client_1',


    'development' => [
        'client_1' => [
            'gt_appid' => '87klYMPe1o515SCcbx7Co5',
            'gt_appkey' => 'dd9XpsgHff89DJgUgvW6L8',
            'gt_appsecret' => 'aKMLyeXLCc8hFpjcuf8gW8',
            'gt_mastersecret' => 'zx85PndZVf8Q1M1Iv9dEy3',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        'client_2' => [
            'gt_appid' => '87klYMPe1o515SCcbx7Co5',
            'gt_appkey' => 'dd9XpsgHff89DJgUgvW6L8',
            'gt_appsecret' => 'aKMLyeXLCc8hFpjcuf8gW8',
            'gt_mastersecret' => 'zx85PndZVf8Q1M1Iv9dEy3',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],

        // other client_3   ......
    ],
    'production' => [
        'client_1' => [
            'gt_appid' => '87klYMPe1o515SCcbx7Co5',
            'gt_appkey' => 'dd9XpsgHff89DJgUgvW6L8',
            'gt_appsecret' => 'aKMLyeXLCc8hFpjcuf8gW8',
            'gt_mastersecret' => 'zx85PndZVf8Q1M1Iv9dEy3',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        'client_2' => [
            'gt_appid' => '87klYMPe1o515SCcbx7Co5',
            'gt_appkey' => 'dd9XpsgHff89DJgUgvW6L8',
            'gt_appsecret' => 'aKMLyeXLCc8hFpjcuf8gW8',
            'gt_mastersecret' => 'zx85PndZVf8Q1M1Iv9dEy3',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],

        // other client_3   ......

    ],
];
