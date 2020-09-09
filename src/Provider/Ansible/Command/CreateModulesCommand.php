<?php


namespace RouterOS\Generator\Provider\Ansible\Command;


use Symfony\Component\Console\Command\Command;

class CreateModulesCommand extends Command
{
    protected static $defaultName = 'ansible:create-modules';

    public function __construct(

    )
    {
        parent::__construct(static::$defaultName);

    }

}