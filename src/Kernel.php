<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.{yaml,php}');
        $container->import('../config/{packages}/'.$this->environment.'/*.{yaml,php}');

        $container->import('../config/services.yaml');
        $container->import('../config/{services}_'.$this->environment.'.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');
    }

    public function getBuildDir(): string
    {
        if (isset($_SERVER['APP_BUILD_DIR'])) {
            return $_SERVER['APP_BUILD_DIR'].'/'.$this->environment;
        }

        return $this->getProjectDir().'/var/build/'.$this->environment;
    }
}
