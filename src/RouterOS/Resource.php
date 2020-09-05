<?php


namespace App\RouterOS;

use Twig\Environment as Twig;
use Doctrine\Inflector\InflectorFactory;

class Resource
{
    /**
     * @var string
     */
    private $versionAdded;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $type = "config";

    /**
     * @var bool
     */
    private $validated = false;

    /**
     * @var string
     */
    private $command = "";

    /**
     * @var Option[]
     */
    private $options = [];

    /**
     * @var array
     */
    private $generator = [];

    private $module = [
        "keys" => ["name"],
        "supports" => [],
        "states" => ["merged", "replaced", "overridden", "deleted"],
        "default_state" => "merged",
    ];

    /**
     * @var null|string
     */
    private $moduleName;

    /**
     * @var null|string
     */
    private $moduleNamespace;

    private $examples = [];

    /**
     * @var null|string
     */
    private $className;

    /**
     * @var array
     */
    private $documentations = [
        "author" => "Anthonius Munthi (@kilip)",
        "version_added" => "1.0.0",
    ];

    /**
     * @var array
     */
    private $tests = [];
    
    /**
     * @param string $name
     * @return Option
     */
    public function getOption($name)
    {
        $translated = strtr($name, [
            "-" => "_",
            "/" => "",
        ]);

        if(!$this->hasOption($translated)){
            $option = new Option();
            $option->setName($translated);
            $option->setRosKey($name);
            $this->options[$translated] = $option;
            ksort($this->options);
        }
        return $this->options[$translated];
    }

    public function setOptions(array $options)
    {
        foreach($options as $name => $config){
            $option = $this->getOption($name);
            foreach($config as $key => $value){
                $setter = 'set'.$key;
                call_user_func([$option,$setter], $value);
            }
        }
    }

    public function toArray()
    {
        $options = [];

        foreach($this->options as $option){
            $options[$option->getName()] = $option->toArray();
        }

        ksort($options);
        return [
            "name" => $this->name,
            "package" => $this->package,
            "type" => $this->type,
            "validated" => $this->validated,
            "command" => $this->command,
            "module" => $this->module,
            "examples" => $this->examples,
            "options" => $options,
        ];
    }

    /**
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * @return array
     */
    public function getDocumentations(): array
    {
        return $this->documentations;
    }

    /**
     * @param array $documentations
     * @return static
     */
    public function setDocumentations(array $documentations)
    {
        $this->documentations = array_merge($documentations, $this->documentations);
        return $this;
    }

    /**
     * @return string
     */
    public function getVersionAdded():string
    {
        return $this->versionAdded;
    }

    /**
     * @param mixed $versionAdded
     * @return static
     */
    public function setVersionAdded($versionAdded)
    {
        $this->versionAdded = $versionAdded;
        return $this;
    }


    public function setExamples(array $examples)
    {
        $this->examples = $examples;
    }

    public function getExamples()
    {
        return $this->examples;
    }

    /**
     * @return array
     */
    public function getGenerator(): array
    {
        return $this->generator;
    }

    /**
     * @param array $generator
     * @return static
     */
    public function setGenerator(array $generator)
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * @param bool $validated
     * @return static
     */
    public function setValidated(bool $validated)
    {
        $this->validated = $validated;
        return $this;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    /**
     * @return string|null
     */
    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    /**
     * @param string $moduleName
     * @return static
     */
    public function setModuleName(string $moduleName)
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * @return array
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @param array $tests
     * @return static
     */
    public function setTests(array $tests)
    {
        $this->tests = array_merge($this->tests, $tests);
        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return static
     */
    public function setClassName(string $className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return array
     */
    public function getModule(): array
    {
        return $this->module;
    }

    /**
     * @param array $module
     * @return static
     */
    public function setModule(array $module)
    {
        $this->module = array_merge($this->module, $module);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModuleNamespace(): ?string
    {
        return $this->moduleNamespace;
    }

    /**
     * @param string|null $moduleNamespace
     * @return static
     */
    public function setModuleNamespace(string $moduleNamespace)
    {
        $this->moduleNamespace = $moduleNamespace;
        return $this;
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param array $states
     * @return static
     */
    public function setStates(array $states)
    {
        $this->states = $states;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPackage(): ?string
    {
        return $this->package;
    }

    /**
     * @param string $package
     * @return static
     */
    public function setPackage(string $package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type)
    {
        $this->type = $type;
        if($type == "setting"){
            $this->module['keys'] = [];
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * @param string $command
     * @return static
     */
    public function setCommand(string $command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}