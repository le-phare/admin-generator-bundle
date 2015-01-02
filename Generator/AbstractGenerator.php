<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractGenerator
{
    protected $kernel;
    protected $bundle;
    protected $parameters;

    public function __construct(KernelInterface $kernel, BundleInterface $bundle, array $parameters)
    {
        $this->kernel = $kernel;
        $this->bundle = $bundle;
        $this->parameters = $parameters;
    }

    protected function getEntityInfo(\DomDocument $metadata)
    {
        $domList = $metadata->getElementsByTagName('entity');
        $entity = str_replace('\\', '/', $domList->item(0)->getAttribute('name'));

        $items = array_reverse(explode('/', $entity));

        return [ 'Entity' === $items[1] ? null : $items[1], $items[0] ];
    }

    protected function findXsl($name, $model = 'default')
    {
        $xsl = new \DOMDocument();
        $xsl->load($this->kernel->locateResource(sprintf('@LephareAdminGeneratorBundle/Resources/stylesheets/%s/%s.xsl', $model, $name)));

        return $xsl;
    }

    protected function get($parameter)
    {
        return $this->parameters[$parameter];
    }
}
