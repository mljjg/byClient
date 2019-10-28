<?php

namespace Jjg\Console\Commands;

use Illuminate\Console\Command;
use Jjg\Lib\Baidu\NetworkDisk\NetworkDiskClient;

class ByUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'by:upload {localPath} {relativePath?} {remoteRootDir?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '百度网盘 - 大文件上传';

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
     */
    public function handle()
    {
        //
        $localPath = $this->argument('localPath');
        $relativePath = $this->argument('relativePath') ?? '/';
        if ($lastChar = substr($relativePath, -1)) {
            if ($lastChar != '/')
                $relativePath .= '/';

        }

        $remoteRootDir = $this->argument('remoteRootDir') ?? config('netdisk.remote.root');
        if ($lastRootChar = substr($remoteRootDir, -1)) {
            if ($lastRootChar != '/')
                $remoteRootDir .= '/';
        }

        $remoteDir = $relativePath == '/' ? $remoteRootDir : $remoteRootDir . $relativePath;

        $this->warn('localPath:' . $localPath);
        $this->warn('remoteDir:' . $remoteDir);

        try {
            #@todo 上传目录
            ## 判断要求上传的是本地的一个文件还是目录（文件夹），若上传的是目录，则要遍历目录及其子目录的文件

            # 1)预上传
            $preCreateResponse = $this->networkDiskClient->preCreate($localPath, $remoteDir);

            # 2）分片上传
            $bar = $this->output->createProgressBar(count($preCreateResponse->getBlockList()));//开启进度
            $block_list_uploaded = [];//已上传的分片

            $blocks = $preCreateResponse->getBlockList() ?: [0];
            foreach ($blocks as $partSeq) {
                $shardUploadResponse = $this->networkDiskClient->shardUpload($localPath, $preCreateResponse->getRemotePath(), $preCreateResponse->getUploadId(), $partSeq);
                $block_list_uploaded[] = $shardUploadResponse->getMd5();
                $bar->advance(1);//增量进度
                ## 内存占用
//                $this->showMemory();
            }

            $bar->finish();//结束进度

            # 3) 创建文件
            $res = $this->networkDiskClient->createFile($localPath, $preCreateResponse->getRemotePath(), $preCreateResponse->getUploadId(), $block_list_uploaded);
            $this->info('');
            $this->info($res->getPath());

        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

    }


    private function showMemory()
    {
        $this->warn('used memory:' . (memory_get_usage() / 1024 / 1024) . ' MB');
        $this->warn('used memory_get_peak_usage:' . (memory_get_peak_usage() / 1024 / 1024) . ' MB');
        $this->warn('used memory real:' . (memory_get_usage(true) / 1024 / 1024) . ' MB');
        $this->warn('used memory_get_peak_usage real:' . (memory_get_peak_usage(true) / 1024 / 1024) . ' MB');
    }
}
