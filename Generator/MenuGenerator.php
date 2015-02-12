<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class MenuGenerator extends AbstractGenerator
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
        $menuConfig = sprintf('%s/Resources/config/menu.yml', $this->bundle->getPath());
        $menu = $this->yaml->parse(file_get_contents($menuConfig));
        list($namespace, $name) = $this->getEntityInfo($metadata);

        $rootNode =& $menu['main_nav']['menu']['root'];
        $itemIndex = 'menu.' . Container::underscore($namespace);

        if (!isset($rootNode['children'][$itemIndex])) {
            $rootNode['children'][$itemIndex] = [
                'options' => [
                    'uri' => 'javascript:;',
                    'extras' => [
                        'icon_class' => 'fa fa-question',
                    ],
                    'childrenAttributes' => [
                        'id' => 'lead',
                        'class' => 'acc-menu',
                    ],
                ],
            ];
        }

        $rootNode['append'][$itemIndex] = isset($rootNode['append'][$itemIndex])
            ? $rootNode['append'][$itemIndex]
            : []
        ;
        $rootNode['append'][$itemIndex] = array_merge($rootNode['append'][$itemIndex], [
            sprintf('menu.%s', Container::underscore($name)) => [
                'role' => sprintf('ROLE_ADMIN_%s_LIST', strtoupper($name)),
                'options' => [
                    'route' => sprintf(
                        '%s_%s_list',
                        str_replace('_bundle', '', Container::underscore($this->bundle->getName())),
                        Container::underscore($name)
                    ),
                    'routeParameters' => [
                        'context' => 'none',
                    ],
                ],
            ],
        ]);

        file_put_contents($menuConfig, $this->dumper->dump($menu, 10));
    }
}
