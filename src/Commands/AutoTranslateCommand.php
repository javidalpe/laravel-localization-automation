<?php

namespace Javidalpe\LaravelLocalizationAutomation\Commands;

use Illuminate\Console\Command;

class AutoTranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uses a third party service to translate your localization files.';

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
        echo "Test";
    }
}
