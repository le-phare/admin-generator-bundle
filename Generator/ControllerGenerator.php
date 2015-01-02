<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ControllerGenerator extends AbstractGenerator
{
    protected $processor;

    public function __construct(KernelInterface $kernel, BundleInterface $bundle, array $parameters)
    {
        $this->processor = new \XSLTProcessor();
        $this->processor->registerPHPFunctions();

        parent::__construct($kernel, $bundle, $parameters);
    }

    public function generate(\DomDocument $metadata)
    {
        $this->processor->importStylesheet($this->findXsl('controller.php'));
        list($namespace, $name) = $this->getEntityInfo($metadata);
        $dirname = sprintf('%s/Controller/%s', $this->bundle->getPath(), $namespace);
        $filename = sprintf('%sController.php', $name);

        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $hdl = fopen(sprintf('%s/%s', $dirname, $filename), 'w');
        $this->processor->setParameter('/', 'bundle', $this->bundle->getNamespace());

        fwrite($hdl, $this->processor->transformToXML($metadata));
        fclose($hdl);
    }
}
