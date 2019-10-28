# 百度网盘对接
- [百度网盘API文档](https://pan.baidu.com/union)


## 生成配置

```
php artisan vendor:publish

选择：jjg-baidu-netdisk
```
## 使用手册

- 授权自己的百度网盘
```
php artisan by:info

Please visit:
https://openapi.baidu.com/oauth/2.0/authorize?response_type=code&client_id=ukp6O2V0Pzvxfkaj9qTYWGmp&redirect_uri=oob&scope=basic,netdisk&display=page&qrcode=1&force_login=1
And authorize this app
Paste the Authorization Code here within 10 minutes.

 Press [Enter] when you are done:
 > 3ced93d242f0fa59d506d65dcb3a692d

you enter code:3ced93d242f0fa59d506d65dcb3a692d
{"code":100000,"msg":"\u64cd\u4f5c\u6210\u529f","success":true,"data":{"access_token":"21.226c45076d66758ca494ea83dc97e3373.2592000.1574603471.643027510-17332190","expires_in":2592000,"scope":"basic netdisk","refresh_token":"22.578bb618478b190446155dee0fea5c28.315360000.1887371471.643027510-17332190"}}

```

- 使用上传命令
```
php artisan by:upload {localPath} {relativePath?} {remoteRootDir?}
```

- 内存使用情况
```
used memory:16.064987182617 MB
used memory_get_peak_usage:26.901359558105 MB
used memory real:18 MB
used memory_get_peak_usage real:28 MB
```


