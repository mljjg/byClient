<?php

namespace Jjg\Console\Commands;

use Illuminate\Console\Command;
use Jjg\Lib\Baidu\NetworkDisk\NetworkDiskClient;

class ByInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'by:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '百度网盘 - 授权码模式(authorization code)';

    /**
     * @var NetworkDiskClient
     */
    private $networkDiskClient;


    /**
     * Create a new command instance.
     *
     * @param NetworkDiskClient $networkDiskClient
     */
    public function __construct(NetworkDiskClient $networkDiskClient)
    {
        parent::__construct();

        $this->networkDiskClient = $networkDiskClient;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        //
        # 1)获取服务端code
        $code = $this->getCode();
        $this->info('you enter code:' . $code);

        # 2) 用户使用返回的 url 进行授权，复制授权码，按照提示输入
        $result = $this->networkDiskClient->getAccessToken($code);
        $this->info($result);

    }

    /**
     * 获取服务端的code和交互提示
     */
    private function getCode()
    {
        $getCodeUrl = $this->networkDiskClient->getServerCode();
        $this->info("Please visit:");
        $this->info($getCodeUrl);
        $this->info('And authorize this app');
        $this->info('Paste the Authorization Code here within 10 minutes.');
        $code = $this->ask('Press [Enter] when you are done');
        return $code;
    }
}
