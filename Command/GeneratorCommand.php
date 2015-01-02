<?php

namespace Lephare\Bundle\AdminGeneratorBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Lephare\Bundle\AdminGeneratorBundle\Generator\GeneratorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class GeneratorCommand extends Command
{
    protected $kernel;
    protected $orm;
    protected $parameters;

    public function __construct(KernelInterface $kernel, ObjectManager $orm, array $parameters)
    {
        $this->kernel = $kernel;
        $this->orm = $orm;
        $this->parameters = $parameters;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('lephare:admin:generate')
            ->setDefinition([
                new InputArgument('bundle', InputArgument::REQUIRED, 'The destination bundle name'),
                new InputOption('filter', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'A string pattern used to match entities that should be processed.'),
            ])
        ;
    }

    public function execute(InputInterface $in, OutputInterface $out)
    {
        $app = $this->getApplication();
        $finder = new Finder();

        $destPath = $this->get('dest-path');
        shell_exec('rm -f ' . $destPath . '/*.orm.xml');

        $input = [
            'command' => 'doctrine:mapping:convert',
            'to-type' => 'xml',
            'dest-path' => $destPath,

            '--force' => true,
            '--filter' => $in->getOption('filter') ? $in->getOption('filter') : $this->get('filter'),
        ];

        if (0 === $app->doRun(new ArrayInput($input), $out)) {
            shell_exec("sed -i 's/<doctrine-mapping .*>/<doctrine-mapping>/g' app/Resources/metadata/*.xml");
            $out->writeln('Schema files sucessfully generated ...');
        }

        $files = $finder->files()->in($destPath)->name('*.xml');
        $bundle = $this->kernel->getBundle($in->getArgument('bundle'));

        $controllerGenerator = GeneratorFactory::create('controller', $this->kernel, $bundle, $this->parameters);
        $formGenerator = GeneratorFactory::create('form', $this->kernel, $bundle, $this->parameters);
        $formViewGenerator = GeneratorFactory::create('formView', $this->kernel, $bundle, $this->parameters);
        $routingGenerator = GeneratorFactory::create('routing', $this->kernel, $bundle, $this->parameters);
        $roleGenerator = GeneratorFactory::create('role', $this->kernel, $bundle, $this->parameters);
        $menuGenerator = GeneratorFactory::create('menu', $this->kernel, $bundle, $this->parameters);

        foreach ($files as $file) {
            $metadata = new \DOMDocument();
            $metadata->load($file->getPathname());

            $controllerGenerator->generate($metadata);
            $formGenerator->generate($metadata);
            $formViewGenerator->generate($metadata);
            $routingGenerator->generate($metadata);
            $roleGenerator->generate($metadata);
            $menuGenerator->generate($metadata);

            list($namespace, $name) = $this->getEntityInfo($metadata);
            $out->writeln('Admin interface sucessfully generated for <comment>' . $name . '</comment>.');
        }
    }

    protected function getEntityInfo(\DomDocument $metadata)
    {
        $domList = $metadata->getElementsByTagName('entity');
        $entity = str_replace('\\', '/', $domList->item(0)->getAttribute('name'));

        $items = array_reverse(explode('/', $entity));

        return [ 'Entity' === $items[1] ? null : $items[1], $items[0] ];
    }

    protected function get($parameter)
    {
        return $this->parameters[$parameter];
    }
}