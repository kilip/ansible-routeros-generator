<?php


namespace Tests\RouterOS\Generator;


use Symfony\Component\DependencyInjection\ContainerInterface;

trait UseContainerTrait
{
    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getKernel()
    {
        if(is_null(static::$kernel)){
            self::$kernel = self::createKernel();
            self::$kernel->boot();
        }
        return static::$kernel;
    }
}