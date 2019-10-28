<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 15:19
 */

// 百度网盘（BaiduNetworkDisk）
/**
 * 开发者需要去申请文件上传的资质，否则使用本人的默认配置亦可
 */
return [
    'app_id' => env('BND_APP_ID', '17332190'),
    'api_key' => env('BND_API_KEY', 'ukp6O2V0Pzvxfkaj9qTYWGmp'),
    'secret_key' => env('BND_SECRET_KEY', 'hG7ZbE8PS7PGMpAvCZGqaPbplRCfV4oP'),
    'remote' => [
        'root' => env('BND_REMOTE_ROOT', '/apps/tengyun/')
    ]

];
