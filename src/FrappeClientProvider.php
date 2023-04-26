<?php 

namespace Rombituon\FrappeClient;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Console\AboutCommand;

final class FrappeClientProvider extends ServiceProvider 
{

    public function boot(): void
    {
        $this->bootPublishing();
        AboutCommand::add('Frappe Client', fn () => ['Version' => '1.0.0']);
        
    }


    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/frappe.php', 'frappe');
    }

   
    private function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/frappe.php' => $this->app->configPath('frappe.php'),
            ], 'frappe');
        }
    }

}