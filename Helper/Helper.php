<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Helper;

use Symfony\Component\DependencyInjection\Container;

abstract class Helper
{
    public static function getName($fullname)
    {
        return array_reverse(explode('/', str_replace('\\', '/', $fullname)))[0];
    }

    public static function getNamespace($fullname)
    {
        $arr = array_reverse(explode('/', str_replace('\\', '/', $fullname)));

        return 'Entity' === $arr[1] ? null : $arr[1];
    }

    public static function getRoutePrefix($bundle)
    {
        return str_replace([ '\\', '_bundle' ], [ '_', '' ], Container::underscore($bundle));
    }
}
