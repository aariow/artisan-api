<?php

namespace Artisan\Api;

use Artisan\Api\Controllers\RunCommandController;
use Illuminate\Support\Str;

/**
 * This class is responsible to add routes dynamiccaly and perform related
 * actions on routes.
 */
class Router
{

    /**
     * Adapter to translate each commad's attributes into readable string
     * for Laravel routing system
     *
     * @var RouteAdapter
     */
    protected RouteAdapter $adapter;

    /**
     * Default HTTP method; can be set within config/artisan.php
     *
     * @var string|array
     */
    protected string|array $method;

    /**
     * All added routes generated by pacakage
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Routes that are not commands but should do specific actions
     *
     * @var array
     */
    protected array $staticRoutes = [
        '/all',     // Get all available commands via REST APIs by this package
        '/command', // Provide route to client to add binded command within HTTP request
    ];

    /**
     * These routes will NOT be added to routes; They can be set within config/artisan.php
     *
     * @var array
     */
    protected array $forbiddenRoutes = [];

    /**
     * Initialize necessary parameters
     *
     * @param RouteAdapter $adapter
     * @return self
     */
    public function __construct(RouteAdapter $adapter)
    {
        $this->adapter = $adapter;

        $this->method = config('artisan.api.method');
        $this->prefix = config('artisan.api.prefix');

        $this->forbiddenRoutes = config('artisan.forbidden-routes');

        // $this->setForbiddenCommands();

        return $this;
    }

    /**
     * Generate routes by dynamic command's attributes; uses RouteAdapter to convert
     * command's attributes into readable string for Laravel routing system.
     *
     * @param boolean $withHiddens
     * @return void
     */
    public function generate(bool $withHiddens = false)
    {
        $routeConfig = [
            'prefix' => $this->prefix
        ];

        app('router')->group($routeConfig, function ($router) use ($withHiddens) {

            $namePrefix = 'artisan.api.';

            // Add static routes
            foreach ($this->getStaticRoutes() as $route) {
                $router->addRoute($this->method, $route, $this->getAction())->name($namePrefix . $route);
            }

            // Add dynamic routes for each command
            foreach ($this->adapter->getCommands()->all() as $command) {

                $commandName = $command->getName();

                foreach ($this->forbiddenRoutes as $route) {
                    if (Str::is($route, $commandName)) {
                        // Lead the flow to first loop, and exit from forbidden loop
                        continue 2;
                    }
                }

                // Prevents empty routes to be added from hidden commands
                if (!$uri = $this->adapter->getUri($command, $withHiddens))
                    continue;

                $name = $namePrefix . $this->adapter->toRouteName($command);

                $route = $router->addRoute($this->method, $uri, $this->getAction())->name($name);

                array_push($this->routes, $route->uri);
            }
        });
    }

    /**
     * Get action to be run when route reached
     *
     * @param $command
     * @return array
     */
    protected function getAction()
    {
        /**
         * Here we return controller to do actions for cleaner code,
         * we can still use a Closure function to do actions.
         */
        return [RunCommandController::class, 'run'];
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return array
     */
    public function getStaticRoutes(): array
    {
        return $this->staticRoutes;
    }
}
