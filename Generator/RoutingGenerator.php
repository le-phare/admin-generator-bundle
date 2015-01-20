<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class RoutingGenerator extends AbstractGenerator
{
    protected $dumper;
    protected $yaml;

    public function __construct(KernelInterface $kernel, BundleInterface $bundle, array $parameters)
    {
        $this->dumper = new Dumper();
        $this->yaml = new Parser();

        parent::__construct($kernel, $bundle, $parameters);
    }

    public function generate(\DomDocument $metadata)
    {
        $routingConfig = sprintf('%s/Resources/config/routing.yml', $this->bundle->getPath());
        $routing = $this->yaml->parse(file_get_contents($routingConfig));
        $routing = !$routing ? [] : $routing;
        list($namespace, $name) = $this->getEntityInfo($metadata);

        $resourceIndex = sprintf(
            '%s_%s',
            str_replace('_bundle', '', Container::underscore($this->bundle->getName())),
            Container::underscore($namespace)
        );
        $routing = array_merge($routing, [
            $resourceIndex => [
                'resource' => sprintf(
                    '@%s/Resources/config/routing/%s.yml',
                    str_replace('\\', '/', $this->bundle->getName()),
                    Container::underscore($namespace)
                ),
            ]
        ]);
        file_put_contents($routingConfig, $this->dumper->dump($routing, 2));

        //
        $dirname = sprintf('%s/Resources/config/routing', $this->bundle->getPath());
        $filename = sprintf('%s.yml', Container::underscore($namespace));
        $path = sprintf('%s/%s', $dirname, $filename);
        $routing = is_file($path) ? $this->yaml->parse(file_get_contents($path)) : [];

        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        $resourceIndex = sprintf('%s_%s', str_replace('_bundle', '', Container::underscore($this->bundle->getName())), Container::underscore($name));
        $resourceListIndex = $resourceIndex . '_list';
        $resourceEditIndex = $resourceIndex . '_edit';
        $resourceConfirmDeleteIndex = $resourceIndex . '_confirm_delete';
        $resourceDeleteIndex = $resourceIndex . '_delete';
        $resourceNewIndex = $resourceIndex . '_new';
        $resourceSearchIndex = $resourceIndex . '_search';

        $routingControllerIndex = sprintf('%s:%s/%s:', $this->bundle->getName(), $namespace, $name);
        $routing = array_merge($routing, [
            $resourceListIndex => [
                'path' => sprintf('/%s.{_format}', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'list',
                    '_format' => 'html',
                ]
            ],
            $resourceEditIndex => [
                'path' => sprintf('/%s/{id}', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'edit',
                ],
                'requirements' => [
                    'id' => '\d+',
                    '_method' => 'GET|POST',
                ]
            ],
            $resourceConfirmDeleteIndex => [
                'path' => sprintf('/%s/{id}/delete', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'confirmDelete',
                ],
                'requirements' => [
                    'id' => '\d+',
                    '_method' => 'GET',
                ]
            ],
            $resourceDeleteIndex => [
                'path' => sprintf('/%s/{id}', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'delete',
                ],
                'requirements' => [
                    'id' => '\d+',
                    '_method' => 'DELETE',
                ]
            ],
            $resourceNewIndex => [
                'path' => sprintf('/%s/new', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'new',
                ],
            ],
            $resourceSearchIndex => [
                'path' => sprintf('/%s/search', Container::underscore($name)),
                'defaults' => [
                    '_controller' => $routingControllerIndex . 'search',
                ],
            ],
        ]);
        file_put_contents($path, $this->dumper->dump($routing, 2));
    }
}
