<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RSTExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter("example_yaml", [$this,'formatYaml']),
            new TwigFilter("example_ssh", [$this,'formatSSH'])
        ];
    }

    public function formatYaml($config, $moduleName, $example)
    {
        $lines = explode("\n", $config);
        $argspec = [];
        foreach($lines as $line){
            $argspec[] = "    ".$line;
        }
        $argspec = implode("\n", $argspec);

        $output = <<<EOC

.. code-block:: yaml+jinja

    - name: {$example["name"]}
      kilip.routeros.{$moduleName}:
{$argspec}
      
EOC;

        return $output;
    }


    public function formatSSH($commands, $spacing=0)
    {
        if(!is_array($commands)){
            $commands = explode("\n", $commands);
        }

        $space = str_repeat("  ", $spacing);
        $cmds = [];
        foreach($commands as $cmd){
            $cmds[] = $space.$cmd;
        }
        $cmds = implode("\n", $cmds);
        $output = <<<EOC

.. code-block:: ssh

{$cmds}

EOC;

        return $output;
    }
}