<?php


namespace Tests\RouterOS\Generator\Concerns;


use Symfony\Component\Yaml\Yaml;

trait InteractsWithYaml
{
    public function parseYamlFile($file)
    {
        $file = __DIR__.'/../Fixtures/'.$file;
        return Yaml::parseFile($file);
    }
}