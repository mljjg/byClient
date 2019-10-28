<?php

namespace Jjg\Console\Commands;

use Illuminate\Console\Command;

class By extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'by';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baiduyun Client';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $this->info("欢迎使用暮来科技提供的百度网盘接口服务");
    }
}
