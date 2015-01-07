<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Generator;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class RoleGenerator extends AbstractGenerator
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
        $rolesConfig = sprintf('%s/config/security_roles.yml', $this->get('kernel.root_dir'));
        $roles = $this->yaml->parse(file_get_contents($rolesConfig));
        list($namespace, $name) = $this->getEntityInfo($metadata);


        $roles['security']['role_hierarchy']['ROLE_ADMIN'] = isset($roles['security']['role_hierarchy']['ROLE_ADMIN'])
            ? $roles['security']['role_hierarchy']['ROLE_ADMIN']
            : [];

        $resourceIndex = [
            'resource' => sprintf('@%s/Resources/config/roles.yml', $this->bundle->getName()),
        ];
        $roleBundleIndex = strtoupper(sprintf('ROLE_ADMIN_%s', Container::underscore($this->bundle->getName())));

        if (isset($roles['imports'])
            && (false !== ($key = array_search($resourceIndex, $roles['imports'])))) {
            unset($roles['imports'][$key]);
        }
        $roles['imports'][] = $resourceIndex;
        $roles['imports'] = array_values($roles['imports']);
        $roles['security']['role_hierarchy']['ROLE_ADMIN'] = array_values(array_unique(array_merge(
            $roles['security']['role_hierarchy']['ROLE_ADMIN'],
            [ $roleBundleIndex ]
        )));
        file_put_contents($rolesConfig, $this->dumper->dump($roles, 4));

        //
        $dirname = sprintf('%s/Resources/config', $this->bundle->getPath());
        $filename = 'roles.yml';
        $path = sprintf('%s/%s', $dirname, $filename);
        $roles = is_file($path) ? $this->yaml->parse(file_get_contents($path)) : [];

        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        $roles['security']['role_hierarchy'][$roleBundleIndex] = isset($roles['security']['role_hierarchy'][$roleBundleIndex])
            ? $roles['security']['role_hierarchy'][$roleBundleIndex]
            : [];
        $roleIndex = sprintf('ROLE_ADMIN_%s', strtoupper(Container::underscore($name)));
        $roles['security']['role_hierarchy'][$roleIndex] = isset($roles['security']['role_hierarchy'][$roleIndex]) ? $roles['security']['role_hierarchy'][$roleIndex] : [];
        $roles['security']['role_hierarchy'] = array_merge($roles['security']['role_hierarchy'], [
            $roleBundleIndex => array_values(array_unique(array_merge($roles['security']['role_hierarchy'][$roleBundleIndex], [ $roleIndex ]))),
            $roleIndex => array_values(array_unique(array_merge($roles['security']['role_hierarchy'][$roleIndex], [
                sprintf('%s_LIST', $roleIndex),
                sprintf('%s_EDIT', $roleIndex),
                sprintf('%s_NEW', $roleIndex),
                sprintf('%s_DELETE', $roleIndex),
            ]))),
        ]);
        ksort($roles['security']['role_hierarchy']);
        sort($roles['security']['role_hierarchy'][$roleBundleIndex]);

        file_put_contents($path, $this->dumper->dump($roles, 5));
    }
}
