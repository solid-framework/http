<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\App\Controllers;

use Solid\Http\Tests\Fixtures\ResourceStringResponse;
use Solid\Kernel\ResourceResponseInterface;

/**
 * @package Solid\App\Controllers
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class HomeController
{
    /**
     * @api
     * @since 0.1.0
     * @return void
     */
    public function allIndex(): ResourceResponseInterface
    {
        return new ResourceStringResponse('HomeController::allIndex');
    }
}
