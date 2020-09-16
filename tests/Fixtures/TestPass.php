<?php


namespace Tests\RouterOS\Generator\Fixtures;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $htmlPages = new Definition(HtmlPages::class);
        $container->setDefinition('test.html_pages', $htmlPages);

        $container->removeDefinition('http_client');

        $definition = new Definition(MockHttpClient::class);
        $definition->addArgument([$container->findDefinition('test.html_pages'),'handleRequest']);
        $container->setDefinition('http_client', $definition);

        $definitions = $container->findTaggedServiceIds('ansible.build_event');
        foreach($definitions as $id => $tags){
            $container->removeDefinition($id);
        }
    }

}