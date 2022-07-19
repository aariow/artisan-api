<?php

/*
 * This file is part of the Artisan-Api package.
 *
 * (c) Alireza Farhanian <aariow01@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @link https://github/aariow/artisan-api
 */

namespace Artisan\Api;


class ArtisanApiManager
{

    const VERSION = "1.0.0";

    protected Router $router;

    public function __construct()
    {
        Adapter::init(CommandsCollection::getIntance());

        $this->router = new Router;

        return $this;
    }

    /**
     * Get Router instance
     *
     * @return Router
     */
    public function router(): Router
    {
        return $this->router;
    }
}
