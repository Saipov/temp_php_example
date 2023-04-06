<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use function dirname;

/**
 * Class AppKernel
 *
 * @package Src
 */
class AppKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/app/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . "/var/cache/app"; // TODO: Change the autogenerated stub
    }

    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/app/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // Experimental common config
        $container->import('../config/common/{packages}/*.yaml');
        $container->import('../config/common/{packages}/' . $this->environment . '/*.yaml');

        $container->import('../config/app/{packages}/*.yaml');
        $container->import('../config/app/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/app/services.yaml')) {
            $container->import('../config/app/{services}.yaml');
            $container->import('../config/app/{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = dirname(__DIR__) . '/config/app/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/app/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/app/{routes}/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/app/routes.yaml')) {
            $routes->import('../config/app/{routes}.yaml');
        } elseif (is_file($path = dirname(__DIR__) . '/config/app/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}