<?php

namespace App\Console\Commands;

use App\Services\MinIO\MinIOService;
use Illuminate\Console\Command;

class InitMainBucket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:init-main-bucket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация системного бакета в MinIO';

    public function __construct(
        protected MinIOService $storage_service
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Инициализация системного бакета...');
        $this->storage_service->initMainBucket();
        $this->info('Инициализация системного бакета [OK]');

        return 0;
    }
}
