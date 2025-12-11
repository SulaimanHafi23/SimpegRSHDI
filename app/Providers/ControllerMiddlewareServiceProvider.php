<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class ControllerMiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // Register middleware resolver
        $router->matched(function ($event) {
            $controller = $event->route->getController();
            
            if ($controller && method_exists($controller, 'getMiddlewareDefinitions')) {
                $definitions = $controller->getMiddlewareDefinitions();
                
                foreach ($definitions as $definition) {
                    $middleware = $definition->getMiddleware();
                    $only = $definition->getOnly();
                    $except = $definition->getExcept();
                    
                    $action = $event->route->getActionMethod();
                    
                    // Check if middleware should be applied
                    $shouldApply = true;
                    
                    if (!empty($only) && !in_array($action, $only)) {
                        $shouldApply = false;
                    }
                    
                    if (!empty($except) && in_array($action, $except)) {
                        $shouldApply = false;
                    }
                    
                    if ($shouldApply) {
                        $event->route->middleware($middleware);
                    }
                }
            }
        });
    }
}
