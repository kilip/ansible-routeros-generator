<?php


namespace App;


use App\RouterOS\Resource;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Dumper\YamlDumper;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment as Twig;

class Compiler
{
    /**
     * @var Resource[]
     */
    private $resources;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Twig
     */
    private $twig;

    public function __construct($resources, LoggerInterface $logger, Twig $twig, $targetDir)
    {
        $this->resources = $resources;
        $this->targetDir = $targetDir;
        $this->logger = $logger;
        $this->twig = $twig;
    }

    public function compile()
    {
        $resources = $this->resources;
        $logger = $this->logger;
        foreach($resources as $resource){
            $logger->notice("compiling {0}", [$resource->name]);

            if(!$resource->validated) continue;
            $this->renderResource($resource);
            $this->renderModule($resource);
            $this->renderTest($resource);
        }

        $this->renderSubset();
        $this->runTox();
    }

    private function renderResource(Resource $resource)
    {
        $name = $resource->name;
        $fileName = $resource->fileName;
        $package = $resource->package;
        $logger = $this->logger;
        $target = $this->targetDir;
        $suffix = "resources/{$package}/{$fileName}.py";
        $target = "{$target}/plugins/module_utils/{$suffix}";
        $template = $resource->type.".py.twig";
        $twig = $this->twig;

        if(!is_dir($dir = dirname($target))){
            mkdir($dir, 0775, true);
            $logger->alert("created {0}", [$dir]);
        }
        if(!is_file($initPyFile = $dir."/__init__.py")){
            touch($initPyFile, 0775);
        }

        $resourceTemplate = "{$name}.py.twig";
        if(is_file(__DIR__.'/../templates/'.$resourceTemplate)){
            $template = $resourceTemplate;
        }

        $output = $twig->render(
            $template,[
                "resource" => $resource
            ]
        );
        $output = strtr($output,[
            "\\/" => "/"
        ]);
        file_put_contents($target, $output, LOCK_EX);

        $logger->alert("Compiled {0} to {1}", [$name, $suffix]);
    }

    private function renderModule(Resource $resource)
    {
        $module_name = $resource->module_name;
        $twig = $this->twig;
        $template = "module.py.twig";
        $target = $this->targetDir."/plugins/modules/{$module_name}.py";
        $documentation = $this->getDocumentation($resource);
        $logger = $this->logger;
        
        if(!is_dir($dir = dirname($target))){
            mkdir($dir, 0777, true);    
        }
        if(!is_file($initPy=$dir."/__init__py")){
            touch($initPy);
        }

        $output = $twig->render(
            $template,
            [
                "resource" => $resource,
                "documentation" => $documentation
            ]
        );

        file_put_contents($target, $output, LOCK_EX);
        $logger->alert("Compiled {0} to {1}", [$module_name, $target]);
    }

    private function getDocumentation(Resource $resource)
    {
        $documentation = $resource->documentation;
        $command = $resource->command_root;
        $module = $resource->module_name;

        $documentation["module"] = $resource->module_name;
        $documentation["version_added"] = "1.0.0";
        $documentation["author"] = "Anthonius Munthi (@kilip)";
        $documentation["options"]["config"]["description"] = "A dictionary for L({$resource->module_name})";

        if($resource->type == "plural"){
            $documentation["options"]["config"]["type"] = "list";
            $documentation["options"]["config"]["elements"] = "dict";
            $documentation["options"]["state"] = [
                "choices" => ["merged", "replaced","overridden","deleted"],
                "default" => "merged",
                "description" => [
                    "I(merged) M({$module}) will update existing C({$command}) configuration, or create new C({$command}) when resource not found",
                    "I(replaced) M({$module}) will restore existing C({$command}) configuration to it's default value, then update existing resource with the new configuration. If the resource C({$command}) not found, M({$module}) will create resource in C({$command})",
                    "I(overridden) M({$module}) will remove any resource in C({$command}) first, and then create new C({$command}) resources.",
                    "I(deleted) M({module}) when found module will delete C({$command})",
                ]
            ];
        }else{
            $documentation["config"]["type"] = "list";
            $documentation["state"] = [
                "choices" => ["present", "reset"],
                "default" => "present",
                "description" => [
                    "I(present) will update C({$command}) config with passed argument_spec values.",
                    "I(reset) will restore C({$command}) to it's default values",
                ]
            ];
        }

        uksort($documentation["options"]["config"], function($key1, $key2){
            $order = ["suboptions", "elements", "type", "description"];
            return ((array_search($key1, $order) < array_search($key2, $order)) ? 1 : -1);
        });

        uksort($documentation["options"],function($key1, $key2){
            $order = ["config","state"];
            return ((array_search($key1, $order) < array_search($key2, $order)) ? 1 : -1);
        });

        uksort($documentation, function($key1, $key2){
            $order = ["options", "author", "version_added","description","short_description","module"];
            return ((array_search($key1, $order) < array_search($key2, $order)) ? 1 : -1);
        });

        $yaml = Yaml::dump(
            $documentation,
            6,
            4,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
        $yaml = strtr($yaml,[
            "'" => "",
            "\\" => "",
        ]);
        $yaml = preg_replace("#^(?:[\t ]*(?:\r?\n|\r))+#im","", $yaml);
        $yaml = preg_replace("#[ \t]+(\r?$)#im","", $yaml);
        return $yaml;
    }

    private function renderTest(Resource $resource)
    {
        if(!$resource->generate_test) return;
        $module_name = $resource->module_name;
        $target = $this->targetDir."/tests/unit/modules/test_{$module_name}.py";
        $template = "module_test.py.twig";
        $twig = $this->twig;

        if(is_file($target)){
            return;
        }
        
        $output = $twig->render(
            $template,
            ["resource" => $resource]
        );
        file_put_contents($target, $output, LOCK_EX);
    }

    private function runTox()
    {
        $commands = [
            "/home/toni/.pyvenv/ansible-dev/bin/tox"
        ];
        $this->logger->notice("running tox",[$commands]);

        $process = new Process($commands,$this->targetDir);
        $process->run(function($type, $buffer){
            echo 'OUT >> '.$buffer;
        });

        $commands = [
            '/home/toni/.pyvenv/ansible-dev/bin/ansible-test',
            'units',
            '--python',
            '3.8'
        ];

        $process = new Process($commands,$this->targetDir);
        $process->run(function($type, $buffer){
            echo $buffer;
        });
    }

    private function renderSubset()
    {
        $target = $this->targetDir."/plugins/module_utils/resources/subset.py";
        $template = "subset.py.twig";
        $twig = $this->twig;
        $logger = $this->logger;

        $output = $twig->render($template,["resources" => $this->resources]);

        file_put_contents($target, $output, LOCK_EX);
        $logger->notice("Compiled subset to {0}",[$target]);
    }
}