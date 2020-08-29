<?php


namespace App\RouterOS;

use Twig\Environment as Twig;
use Doctrine\Inflector\InflectorFactory;

class Resource
{
    const OPT_STR="str";
    const OPT_LIST="list";
    const OPT_DICT="dict";
    const OPT_BOOL="bool";
    const OPT_INT="int";
    const OPT_FLOAT="float";
    const OPT_PATH="path";
    const OPT_RAW="raw";
    const OPT_JSON_ARG="jsonarg";
    const OPT_JSON="json";
    const OPT_BYTES="bytes";
    const OPT_BITS="bits";

    public $url;
    public $html_id;
    public $name;
    public $command_root;
    public $properties = [];
    public $description;
    public $options = [];
    public $ignores = [];
    public $className = "";
    public $states = ["merged","replaced","deleted","overridden"];
    public $relativePath = "";
    public $importPath = "";
    public $type = "plural";

    public $resource_name;
    public $module_name;
    public function configure()
    {
        $this->parseProperties();
        $inflector = InflectorFactory::create()->build();
        $this->className = $inflector->classify($this->name);
        
        if(false !== strpos($this->name, "_")){
            $exp = explode("_", $this->name);
            $package = $exp[0];
            unset($exp[0]);
            $x = implode("_", $exp);
            $this->importPath = $package.".".$x;
        }else{
            $this->importPath = $this->name.".".$this->name;
        }

        // configure resource name
        $this->resource_name = $this->name;

        $name = "ros_".$this->name;
        $name = strtr($name,[
            "ip_" => "",
            "system_" => "",
        ]);
        $this->module_name = $name;
        /*
        $pluralFilters = ["list", "wireless", "address", "table"];
        if($this->type == "plural"){
            $exp  = explode("_",$this->name);
            $last = &$exp[count($exp)-1];
            if(!in_array($last, $pluralFilters)){
                $last = $inflector->pluralize($last);
                $this->resource_name = implode("_", $exp);
                echo $this->resource_name."\n";
            }
        }
        */
    }

    public function render(Twig $twig, $targetDir)
    {
        $this->renderResource($twig, $targetDir);
        $this->renderModule($twig, $targetDir);
    }

    private function renderResource(Twig $twig, $targetDir)
    {
        $exp = explode("_", $this->name);
        $dir = array_shift($exp);
        $file = isset($exp[0]) ? implode("_", $exp):$dir;
        $target = $targetDir."/module_utils/resources/{$dir}";
        if(!is_dir($target)){
            mkdir($target,0777,true);
        }
        if(!is_file($initPY = $target."/__init__.py")){
            touch($initPY);
        }
        $target = $target."/{$file}.py";

        $output = $twig->render($this->type.".py.twig",[
            "resource" => $this,
        ]);
        file_put_contents($target,$output, LOCK_EX);
    }

    private function renderModule(Twig $twig, $targetDir)
    {
        $file = $targetDir."/modules/".$this->module_name.".py";
        if(!is_dir($dir = dirname($file))){
            mkdir($dir, 0777, true);
        }
        $output = $twig->render("module.py.twig",[
            "resource" => $this,
        ]);
        file_put_contents($file, $output, LOCK_EX);
    }

    private function parseProperties()
    {
        $options = $this->options;
        
        foreach($this->properties as $property){
            if(!isset($property['property'])) continue;
            $prop = $property['property'];

            preg_match("/(\S+)/", $prop, $matches);
            $name = $matches[0];
            $name = str_replace("-","_", $name);
            
            if(in_array($name, $this->ignores)){
                continue;
            }
            if(!isset($options[$name])){
                $options[$name] = [];
            }

            if(!isset($options[$name]["type"])){
                preg_match("/\((.*)\)/im", $prop, $matches);
                if(false !== strpos($matches[1],"read-only")){
                    continue;
                }
                if(strpos($matches[1],"|") !== false){
                    $ret = $this->parseChoices($matches[1]);
                    $pyType = $ret[0];
                    $options[$name]["type"] = $pyType;
                    if($pyType!== self::OPT_BOOL){
                        $options[$name]["choices"] = $ret[1];
                    }
                }else{
                    $options[$name]["type"] = $this->parseType($name, $matches);
                }
            }

            $default = $this->parseDefault($matches[1]);
            if(
                !is_null($default)
                && (!isset($options[$name]["default"]) || is_null($options[$name]["default"]))
            ){
                $options[$name]["default"] = $default;
            }

            $options[$name]['description'] = $property["description"];
            if(!isset($options[$name]["type"])){
                throw new \Exception("Unknown type for ".$name);
            }
        }
        $this->options = $options;
        //print_r($options);
    }

    private function parseType($name, $matches)
    {
        $type = $matches[1];
        $type = strtolower($type);
        $known_types = [
            "string" => self::OPT_STR,
            "integer" => self::OPT_INT,
            "ip address" => self::OPT_STR,
            "ip\/netmask" => self::OPT_STR,
            "ip\/netmaks" => self::OPT_STR,
            "name" => self::OPT_STR,
            "mac address" => self::OPT_STR,
            "time" => self::OPT_STR,
            "text" => self::OPT_STR,
            "mac" => self::OPT_STR,
            "ip" => self::OPT_STR,
            "script" => self::OPT_STR,
        ];
        $pyType = null;
        $pattern = "^(".implode("|",array_keys($known_types)).")";
        preg_match("#{$pattern}#im", $type, $finds);
        if($finds){
            $match = $finds[0];
            $match = str_replace("/","\/", $match);
            $pyType = $known_types[$match];
        }

        if(is_null($pyType)){
            echo "{$this->name} {$this->command_root} {$name} = {$matches[1]}\n";
        }
        return $pyType;
    }

    private function parseChoices($type)
    {

        $exp = explode(";", $type);
        $choices = preg_replace("#(\s+)#","", $exp[0]);
        if(false !== strpos($choices, "yes|no")){
            $pyType = "bool";
        }else{
            $pyType = "str";
        }
        $choices = explode("|", $choices);
        foreach($choices as $choice){
            if(false !== strpos($choice,',')){
                return [$pyType,[]];
            }
            if(false !== strpos($choice,"/")){
                return [$pyType,[]];
            }
        }
        
        return [$pyType,$choices];
    }

    private function parseDefault($match)
    {
        $exp = explode(";", $match);
        if(!isset($exp[1])){
            return null;
        }
        $content = trim(strtolower($exp[1]));
        preg_match("#^default\:\s+(\S+)#im", $content, $matches);
        if($matches){
            $default = $matches[1];
            if(false !== strpos($default, '"')) return null;
            if($default == "yes" || $default == "no"){
                return $matches == "yes" ? "True":"False";
            }
            return $default;
        }
        return null;
    }
}