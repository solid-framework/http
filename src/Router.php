<?php

/**
 * Copyright (c) 2016 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Solid\Http;

use ReflectionMethod;
use Solid\Config\Config;
use Solid\Config\ConfigSection;
use Solid\Container\Container;
use Solid\Kernel\ResourceResponseInterface;
use Solid\Kernel\ResourceNotFoundException;
use Solid\Kernel\ControllerNotFoundException;
use Solid\Kernel\ControllerMethodNotFoundException;
use Psr\Http\Message\RequestInterface;

/**
 * @package Solid\Http
 * @author Martin Pettersson <martin@solid-framework.com>
 * @since 0.1.0
 */
class Router
{
    /**
     * @internal
     * @since 0.1.0
     * @var Container
     */
    protected $container;

    /**
     * @internal
     * @since 0.1.0
     * @var Config
     */
    protected $config;

    /**
     * @internal
     * @since 0.1.0
     * @var Request
     */
    protected $request;

    /**
     * @api
     * @since 0.1.0
     * @param Container $container The application DI container.
     * @param Config    $config    The application configuration.
     */
    public function __construct(Container $container, Config $config)
    {
        $this->container = $container;
        $this->config = new ConfigSection('http', $config);
    }

    /**
     * @api
     * @since 0.1.0
     * @param RequestInterface
     * @return ResourceResponseInterface
     * @throws ResourceNotFoundException
     */
    public function routeRequest(RequestInterface $request): ResourceResponseInterface
    {
        $this->request = $request;

        // extract, filter out empty values and reindex
        $pathArray = array_values(array_filter(explode('/', $request->getRequestTarget())));

        // sort out valid path and parameters from the path array
        $validPathArray = $this->getValidPathArray($pathArray);
        $parameters = $this->diffPathArray($validPathArray, $pathArray);

        $controllerClass = $this->findController($validPathArray, $parameters);

        // sort out valid path and parameters from the new parameters array
        $validPathArray = $this->getValidPathArray($parameters);
        $parameters = $this->diffPathArray($validPathArray, $parameters);

        $methodName = $this->findControllerMethod($controllerClass, $validPathArray, $parameters);

        // resolve the controller through the container
        $this->container->bind($controllerClass, null, true);
        $this->container->alias($controllerClass, 'controller');

        // decode urlencoded parameters
        $parameters = array_map('urldecode', array_map('urldecode', $parameters));

        return $this->container->resolve('controller')->{$methodName}(...$parameters);
    }

    /**
     * @internal
     * @since 0.1.0
     * @param array $pathArray  An array of url paths.
     * @param array $parameters An array of parameters.
     * @return string Returns the fully qualified controller class name.
     * @throws ControllerNotFoundException
     */
    protected function findController(array $pathArray, array &$parameters): string
    {
        $i = count($pathArray);

        while ($i--) {
            $controllerClassParts = array_map(function ($part) {
                $parts = explode('-', $part);

                return implode('', array_map('ucfirst', str_replace('-', '', $parts)));
            }, $pathArray);
            $potentialControllerClass = 'Solid\\App\\Controllers\\' .
                implode('', $controllerClassParts) .
                'Controller';

            if ($this->validateController($potentialControllerClass)) {
                return $potentialControllerClass;
            }

            array_unshift($parameters, array_pop($pathArray));
        }

        // try fallback controller
        $controllerClass = 'Solid\\App\\Controllers\\HomeController';

        if ($this->validateController($controllerClass)) {
            return $controllerClass;
        }

        throw new ControllerNotFoundException;
    }

    /**
     * @internal
     * @since 0.1.0
     * @param string $controllerClass The controller class to validate.
     * @return bool
     */
    protected function validateController($controllerClass): bool
    {
        return class_exists($controllerClass);
    }

    /**
     * @internal
     * @since 0.1.0
     * @param string $controllerClass A controller class string.
     * @param array  $pathArray       An array of url paths.
     * @param array  $parameters      An array of parameters.
     * @return string Returns the controller method name.
     * @throws ControllerMethodNotFoundException
     */
    protected function findControllerMethod(string $controllerClass, array $pathArray, array &$parameters): string
    {
        $method = strtolower($this->request->getMethod());
        $prefix = strtolower($this->config->get("routing.prefixMap.{$method}", $method));

        $i = count($pathArray);

        while ($i--) {
            $methodParts = array_map(function ($part) {
                $parts = explode('-', $part);

                return implode('', array_map('ucfirst', str_replace('-', '', $parts)));
            }, $pathArray);
            $potentialMethodName = $prefix . implode('', $methodParts);

            if (!method_exists($controllerClass, $potentialMethodName)) {
                $potentialMethodName = 'all' . implode('', $methodParts);
            }

            if (method_exists($controllerClass, $potentialMethodName)) {
                if ($this->validateControllerMethod($controllerClass, $potentialMethodName, $parameters)) {
                    return $potentialMethodName;
                }

                throw new ControllerMethodNotFoundException;
            }

            array_unshift($parameters, array_pop($pathArray));
        }

        $potentialMethodName = $prefix . 'Index';

        if (!method_exists($controllerClass, $potentialMethodName)) {
            $potentialMethodName = 'allIndex';
        }

        if (method_exists($controllerClass, $potentialMethodName) &&
            $this->validateControllerMethod($controllerClass, $potentialMethodName, $parameters)
        ) {
            return $potentialMethodName;
        }

        throw new ControllerMethodNotFoundException;
    }

    /**
     * @internal
     * @since 0.1.0
     * @param string $controllerClass The controller class.
     * @param string $methodName      The controller class method.
     * @param array  $parameters      An array of parameters for the method.
     * @return bool
     */
    protected function validateControllerMethod($controllerClass, $methodName, array &$parameters): bool
    {
        $reflection = new ReflectionMethod($controllerClass, $methodName);

        if (
            $reflection->isPublic() &&
            (
                $reflection->isVariadic() ||
                (
                    count($parameters) <= $reflection->getNumberOfParameters() &&
                    count($parameters) >= $reflection->getNumberOfRequiredParameters()
                )
            )
        ) {
            // parse method doc comment
            if (count($parameters) > 0 && $this->config->get('parameterValidation', false)) {
                $methodParameters = $reflection->getParameters();
                $docParameters = [];

                foreach (explode("\n", $reflection->getDocComment()) as $row) {
                    if (preg_match('/@param.+\$(\b\w+\b)(?:\((.+)\))?/', $row, $match)) {
                        $parameterPosition = -1;

                        // get the parameter position
                        foreach ($methodParameters as $methodParameter) {
                            if ($methodParameter->name === $match[1]) {
                                $parameterPosition = $methodParameter->getPosition();
                            }
                        }

                        if ($parameterPosition >= 0) {
                            $docParameters[] = [
                                'index' => $parameterPosition,
                                'name' => $match[1],

                                // make sure the validation regex is wrapped in "/"
                                'validation' => isset($match[2]) ?
                                    (strpos($match[2], '/') !== 0 ? "/{$match[2]}/" : $match[2]) :
                                    ''
                            ];
                        }
                    }
                }

                // validate parameters
                if (!$this->validateParameters($docParameters, $parameters)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @internal
     * @since 0.1.0
     * @param array $docParameters The formatted doc comment parameters.
     * @param array $parameters    The provided parameters.
     * @return bool
     */
    protected function validateParameters(array $docParameters, array $parameters): bool
    {
        foreach ($docParameters as $docParameter) {
            // test the method parameter validator
            if (
                strlen($docParameter['validation']) > 0 &&
                !preg_match($docParameter['validation'], $parameters[$docParameter['index']])
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array sliced at the first value containing a special char
     *
     * @internal
     * @since 0.1.0
     * @param array $pathArray An array of paths.
     * @return array
     */
    protected function getValidPathArray(array $pathArray): array
    {
        if (($firstSpecialChar = $this->getFirstSpecialChar($pathArray)) >= 0) {
            $pathArray = array_slice($pathArray, 0, -(count($pathArray) - $firstSpecialChar));
        }

        return $pathArray;
    }

    /**
     * Returns a diff between the two given arrays
     *
     * It works just like array_diff but keeps duplicate values.
     *
     * @internal
     * @since 0.1.0
     * @param array $validPathArray An array of valid paths.
     * @param array $pathArray      An array of paths.
     * @return array
     */
    protected function diffPathArray(array $validPathArray, array $pathArray): array
    {
        $count = array_count_values($validPathArray);

        return array_values(array_filter($pathArray, function ($path) use (&$count) {
            return !array_key_exists($path, $count) || $count[$path]-- <= 0;
        }));
    }

    /**
     * Returns the index of the first value containing a special char
     *
     * @internal
     * @since 0.1.0
     * @param array $array The array to check.
     * @return int
     */
    protected function getFirstSpecialChar(array $array): int
    {
        foreach ($array as $key => $value) {
            if (!ctype_alpha(str_replace('-', '', $value))) {
                return $key;
            }
        }

        return -1;
    }
}
