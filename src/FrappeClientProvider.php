<?php 

namespace Rombituon\FrappeClient;

use Illuminate\Support\ServiceProvider;

final class FrappeClientProvider extends ServiceProvider 
{

    public function boot(): void
    {
        $this->bootPublishing();
    }


    private function registerConfig(): void
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