<?php
/**
 * Created by PhpStorm.
 * User: JJG
 * Date: 2019/10/25
 * Time: 12:04
 */

namespace Jjg\Providers;


use Illuminate\Support\ServiceProvider;
use Jjg\Console\Commands\By;
use Jjg\Console\Commands\ByInfo;
use Jjg\Console\Commands\ByUpload;

class ByClientServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        By::class,
        ByInfo::class,
        ByUpload::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if ($this->app->runningInConsole()) {
            ## 配置文件
            $this->publishes([__DIR__ . '/../config/netdisk.php' => config_path('netdisk.php')], 'jjg-baidu-netdisk');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        //注册命令
        $this->commands($this->commands);
    }

}