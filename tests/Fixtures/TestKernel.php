<?php


namespace Tests\RouterOS\Generator\Fixtures;


use RouterOS\Generator\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpClient\MockHttpClient;

class TestKernel extends Kernel
{
    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TestPass());
    }

}