<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

interface GeneratorInterface
{
    public function generate(\DomDocument $metadata);
}
