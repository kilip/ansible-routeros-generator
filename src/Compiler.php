<?php


namespace App;


use App\RouterOS\Resource;
use App\Twig\DocExtension;
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

    public function __construct(
        $resources,
        LoggerInterface $logger,
        Twig $twig,
        $targetDir
    )
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
        foreach ($resources as $resource) {
            $logger->notice("compiling {0}", [$resource->getName()]);

            if (!$resource->isValidated()) continue;
            $this->renderResource($resource);
            $this->renderModule($resource);
            $this->renderFactTests($resource);
            $this->renderModuleTests($resource);
            $this->renderModuleDoc($resource);
        }

        $this->renderSubset();
    }

    private function renderModuleTests(Resource $resource)
    {
        $examples = $resource->getExamples();
        $tests = $resource->getTests();
        $target = "{$this->targetDir}/tests/unit/modules/fixtures/modules/{$resource->getModuleName()}.yml";

        $config = [];
        $config['module'] = $resource->getModuleName();
        $config['fixtures'] = $tests['fixtures'];
        $moduleTests = [];

        if(empty($examples)){
            return;
        }
        foreach($examples as $example){
            $test = [];

            $moduleTests[] = [
                "commands" => $example["commands"],
                "argument_spec" => $example["argument_spec"]
            ];
        }
        
        $config["tests"] = $moduleTests;
        $yaml = Yaml::dump(
            $config,
            6,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
        file_put_contents($target, $yaml, LOCK_EX);
    }

    private function renderFactTests(Resource $resource)
    {
        $tests = $resource->getTests();
        if(!isset($tests["facts"])){
            return;
        }

        $test = $tests["facts"];
        $target = "{$this->targetDir}/tests/unit/modules/fixtures/facts/{$resource->getName()}.yml";
        $test["resource"] = $resource->getName();
        $test["fixtures"] = $tests["fixtures"];
        $yaml = Yaml::dump(
            $test,
            4,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );

        file_put_contents($target, $yaml, LOCK_EX);
    }

    private function renderResource(Resource $resource)
    {
        $name = $resource->getName();
        $package = $resource->getPackage();
        $logger = $this->logger;
        $target = $this->targetDir;
        $suffix = "resources/{$package}/{$name}.py";
        $target = "{$target}/plugins/module_utils/{$suffix}";
        $twig = $this->twig;

        if(!is_dir($dir = dirname($target))){
            mkdir($dir, 0775, true);
            $logger->alert("created {0}", [$dir]);
        }
        if(!is_file($initPyFile = $dir."/__init__.py")){
            touch($initPyFile, 0775);
        }

        $template = "resource/".$resource->getType().".py.twig";
        $resourceTemplate = "{$name}.py.twig";
        if(is_file(__DIR__.'/../templates/resource/'.$resourceTemplate)){
            $template = "resource/".$resourceTemplate;
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
        $module_name = $resource->getName();
        $twig = $this->twig;
        $template = "module/module.py.twig";
        $target = $this->targetDir."/plugins/modules/{$resource->getModuleName()}.py";
        $logger = $this->logger;
        
        if(!is_dir($dir = dirname($target))){
            mkdir($dir, 0777, true);    
        }
        if(!is_file($initPy=$dir."/__init__.py")){
            touch($initPy);
        }

        $output = $twig->render(
            $template,
            [
                "resource" => $resource,
                "module_name" => $resource->getModuleName(),
            ]
        );

        // remove trailing whitespaces
        $output = preg_replace("#[ \t]+$#im","", $output);

        file_put_contents($target, $output, LOCK_EX);
        $logger->alert("Compiled {0} to {1}", [$module_name, $target]);
    }

    private function renderModuleDoc(Resource $resource)
    {
        $target = $this->targetDir."/docs/kilip.routeros.{$resource->getModuleName()}_module.rst";
        $twig = $this->twig;
        $template = "docs/module.rst.twig";

        $output = $twig->render($template,[
            'resource' => $resource,
            'module_name' => $resource->getModuleName(),
            "docs" => $resource->getDocumentations(),
        ]);

        file_put_contents($target, $output, LOCK_EX);
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