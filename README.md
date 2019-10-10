# Laravel Or Lumen GeTui  

基于 [个推官方SDK](http://docs.getui.com/getui/server/php/start/)  for Laravel.


---
[![](https://travis-ci.org/ShaoZeMing/laravel-getui.svg?branch=master)](https://travis-ci.org/ShaoZeMing/laravel-getui) 
[![](https://img.shields.io/packagist/v/ShaoZeMing/laravel-getui.svg)](https://packagist.org/packages/shaozeming/laravel-getui) 
[![](https://img.shields.io/packagist/dt/ShaoZeMing/laravel-getui.svg)](https://packagist.org/packages/stichoza/shaozeming/laravel-getui)

## 安装

```shell
$ composer require shaozeming/laravel-getui -v
```
### Laravel



```php
// config/app.php

    'providers' => [
        //...
        ShaoZeMing\GeTui\GeTuiServiceProvider::class,    //This is default in laravel 5.5
    ],
```

And publish the config file: 

```shell
$ php artisan vendor:publish --provider=ShaoZeMing\\GeTui\\GeTuiServiceProvider
```

if you want to use facade mode, you can register a facade name what you want to use, for example `GeTui`: 

```php
// config/app.php

    'aliases' => [
        'GeTui' => ShaoZeMing\GeTui\Facade\GeTui::class,   //This is default in laravel 5.5
    ],
```

### lumen

- 在 bootstrap/app.php 中 82 行左右：
```
$app->register(ShaoZeMing\GeTui\GeTuiServiceProvider::class);
```
将 `vendor/ShaoZeMing/laravel-getui/src/config/getui.php` 拷贝到项目根目录`/config`目录下，并将文件名改成`getui.php`。

### configuration 

```php
// config/getui.php
   // APP_EVN     你的项目当前环境 测试、生产
    'app_env' => env('APP_ENV') == 'production' ? 'production' : 'development',

   
    // The default default_client name which configured in `development` or `production` section
    //默认推送的客户端
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
    
```


## Usage

Gateway instance:

```php
use GeTui;
//针对单个或者多个用户推送
GeTui::push($deviceId, $data,true) //Using default default_client   推送给默认的客户端
GeTui::toClient('CLIENT NAME')->push($deviceId, $data)  // CLIENT NAME is key name of `development` or `production`  configuration.  //自定义发送的客户端  

// 针对整个app所有人推送
GeTui::pushToApp($data,true) ////Using default default_client  
GeTui::toClient('CLIENT NAME')->pushToApp($data)  // GATEWAY NAME is key name of `development` or `production`  configuration.

```


Example:

```php
    $deviceId = 'a1f3e7c11ca97924be6f291f2c09714b';
   // 多个push对象device_id 用数组传入
//   $deviceId = [
//            'a1f3e7c11ca97924be6f291f2c09714b',
//            'ea34a4715b08b1b8d77aabf36c977cba',
//           ];        

   $data = [
                'url' => 'http://test.4d4k.com/push',
                'type' => '点击查看\(^o^)/~',
                'title' => '23232323fdf',
                'content' => '今天天气真好',
                'id' => '3a92y3GR1neZ',
                'merchant_name' => '泽明科技',
                'big_cat' => '电视机',
                'full_address' => '北京市海淀区五道口清华大学',
            ];

 
$res = \GeTui::push($deviceId, $data,true); //Using default default_client

print_r($res);


```

## License

MIT
