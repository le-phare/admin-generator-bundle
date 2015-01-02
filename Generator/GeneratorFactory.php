<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class GeneratorFactory
{
    public static function create($name, KernelInterface $kernel, BundleInterface $bundle, array $parameters)
    {
        switch ($name) {
            case 'controller':
                $generator = new ControllerGenerator($kernel, $bundle, $parameters);
                break;
            case 'form':
                $generator = new FormGenerator($kernel, $bundle, $parameters);
                break;
            case 'formView':
                $generator = new FormViewGenerator($kernel, $bundle, $parameters);
                break;
            case 'routing':
                $generator = new RoutingGenerator($kernel, $bundle, $parameters);
                break;
            case 'role':
                $generator = new RoleGenerator($kernel, $bundle, $parameters);
                break;
            case 'menu':
                $generator = new MenuGenerator($kernel, $bundle, $parameters);
                break;
            default:
                throw new \Exception("Unknown generator");
        }

        return $generator;
    }
}
