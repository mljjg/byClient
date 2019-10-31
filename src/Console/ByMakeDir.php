<?php

namespace Jjg\Console\Commands;

use Illuminate\Console\Command;
use Jjg\Lib\Baidu\NetworkDisk\NetworkDiskClient;

class ByMakeDir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'by:make-dir {relativePath} {remoteRootDir?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '百度网盘API-创建目录操作';

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
     * @throws \Jjg\Lib\Baidu\NetworkDisk\Exceptions\PathException
     */
    public function handle()
    {
        //

        $remoteRootDir = $this->argument('remoteRootDir');
        $relativePath = $this->argument('relativePath') ?? '/';

        $result = $this->networkDiskClient->makeDir($relativePath, $remoteRootDir);
        if ($result->isSuccess()) {
            dump($result->getModel());
        } else
            dump($result->getMsg() . '(' . $result->getCode() . ')');

    }

}
