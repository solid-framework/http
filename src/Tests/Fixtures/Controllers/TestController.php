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
class TestController
{
    /**
     * @api
     * @since 0.1.0
     * @return ResourceResponseInterface
     */
    public function allIndex(): ResourceResponseInterface
    {
        return new ResourceStringResponse('TestController::allIndex');
    }

    /**
     * @api
     * @since 0.1.0
     * @return ResourceResponseInterface
     */
    public function allNoParameters(): ResourceResponseInterface
    {
        return new ResourceStringResponse('TestController::allNoParameters');
    }

    /**
     * @api
     * @since 0.1.0
     * @param string $one The first parameter.
     * @param string $two The second parameter.
     * @return ResourceResponseInterface
     */
    public function allParameters(string $one, string $two): ResourceResponseInterface
    {
        $oneType = gettype($one);
        $twoType = gettype($two);

        return new ResourceStringResponse(
            "TestController::allParameters({$oneType} {$one}, {$twoType} {$two})"
        );
    }

    /**
     * @api
     * @since 0.1.0
     * @param int    $number(/^(0|[1-9][0-9]*)$/)                         The first parameter.
     * @param string $email(/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i) The second parameter.
     * @return ResourceResponseInterface
     */
    public function allParametersValidation(int $number, string $email): ResourceResponseInterface
    {
        $numberType = gettype($number);
        $emailType = gettype($email);

        return new ResourceStringResponse(
            "TestController::allParametersValidation({$numberType} {$number}, {$emailType} {$email})"
        );
    }

    /**
     * @api
     * @since 0.1.0
     * @return ResourceResponseInterface
     */
    public function getUser()
    {
        return new ResourceStringResponse('TestController::getUser');
    }

    /**
     * @api
     * @since 0.1.0
     * @return ResourceResponseInterface
     */
    public function postUser()
    {
        return new ResourceStringResponse('TestController::postUser');
    }

    /**
     * @api
     * @since 0.1.0
     * @return ResourceResponseInterface
     */
    public function updateUser()
    {
        return new ResourceStringResponse('TestController::updateUser');
    }
}
