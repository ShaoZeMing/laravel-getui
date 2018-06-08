<?php
return [
    // APP_EVN
    'app_env' => env('APP_ENV') == 'production' ? 'production' : 'development',

    // The default default_client name which configured in `development` or `production` section
    'default_client' => 'client_1',


    'development' => [
        'client_1' => [
            'gt_appid' => 'WAqyXNcLpS8OLg4jBywS48',
            'gt_appkey' => 'FkxUuibQsT75FX5Tt5jteA',
            'gt_appsecret' => 'jWtd0iUzdmAvVPhKorrtW1',
            'gt_mastersecret' => '4uCfJsfME99oaF5sT1ZjO',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        'client_2' => [
            'gt_appid' => 'SeldZ6X0Iq8hpj5rGvqAk8',
            'gt_appkey' => '93MPU2THwl9okpeNf4lNI4',
            'gt_appsecret' => 'kzZuSXVMm29M7owpvId979',
            'gt_mastersecret' => '0QCmCdVZSi8lcyMFXLB4e',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],

        // other client_3   ......
    ],
    'production' => [
        'client_1' => [
            'gt_appid' => '6V95sH0t3W6Du1MTiU3679',
            'gt_appkey' => 'n6q8NSAshP77ImKxdhuHV6',
            'gt_appsecret' => '01hGwR1Jdl6vuwBcnvfyD3',
            'gt_mastersecret' => 'daw4hbkFj4Ah3kBlPFfIh2',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],
        'client_2' => [
            'gt_appid' => 'iB7DfaXV6bAf8zlJ0L59A8',
            'gt_appkey' => 'DKKp54s2knA2MaeGBXuF01',
            'gt_appsecret' => 'exTKWC0M1K6O2Bgig5RiC8',
            'gt_mastersecret' => '0cojzBC7yB86mhOiOVHBuA',
            'gt_domainurl' => 'http://sdk.open.api.igexin.com/apiex.htm',
        ],

        // other client_3   ......

    ],
];
