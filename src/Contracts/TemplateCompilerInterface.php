<?php


namespace RouterOS\Generator\Contracts;


interface TemplateCompilerInterface
{
    public function compile($template, $target, array $context);
}