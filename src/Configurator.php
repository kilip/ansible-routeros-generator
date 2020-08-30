<?php


namespace App;


use App\RouterOS\Resource;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * 
 */
class Configurator
{
    private $url;
    private $tableIndex;
    private $resource;
    private $cacheDir;
    private $ignores;

    private $typos = [
        "estabilished" => "established",
    ];
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Resource $resource,
        $cacheDir,
        LoggerInterface $logger
    )
    {
        foreach($resource->generator as $method => $value){
            $this->$method = $value;
        }
        $this->resource = $resource;
        $this->cacheDir = $cacheDir;
        $this->logger = $logger;
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function configure()
    {
        $configured = true;
        $url = $this->url;
        $page = $this->getPage($url);
        $crawler = new Crawler($page, $url);
        $resource = $this->resource;

        $crawler
            ->filter("table")
            ->each(\Closure::fromCallable([$this, "parseTable"]));

        if(!isset($resource->options["comment"])){
            $resource->options['comment'] = [
                'type' => 'str'
            ];
        }
        $resource->configure();

        return $configured;
    }

    public function parseTable(Crawler $crawler, $index)
    {
        if(
            is_array($this->tableIndex)
            && !in_array($index, $this->tableIndex)
        ){
            return;
        }
        elseif(!is_array($this->tableIndex) && $index !== $this->tableIndex){
            return;
        }

        $crawler
            ->filter("tr")
            ->each(\Closure::fromCallable([$this,"parseRow"]));
    }

    public function parseRow(Crawler $crawler, $index)
    {
        if($index == 0) return;

        $filters = ["numbers", "time"];
        $params = [];
        $resource = $this->resource;

        $crawler->filter("td")->each(function($crawler, $index) use(&$params){
            $converter = new HtmlConverter([
                "strip_tags" => true,
            ]);
            $converted = $converter->convert($crawler->html());
            $params[$index] = strtr($converted,[
                "**" => "",
                "*" => ""
            ]);
        });

        if(!isset($params[0])){
           return;
        }

        if(false !== strpos($params[0],"read-only")){
            return;
        }

        // set defaults
        $property = strtolower($params[0]);
        $name = $this->parseName($property);
        $options = isset($resource->options[$name]) ? $resource->options[$name]:[];
        $type = isset($options['type']) ? $options['type']:null;
        $ignore = isset($options["ignore"]) ? $options["ignore"]:false;

        // generate property config values
        $pattern = "#\((.+)[\)|+]#im";
        preg_match($pattern, $property, $matches);
        if(!isset($matches[1])){
            throw new \Exception($crawler->html());
        }

        $propConfig = $matches[1];
        $default = $this->parseDefault($propConfig);
        $choices = $this->parseChoices($propConfig);

        if(!is_null($default) && is_null($type)){
            if(is_string($default) && strlen($default) > 0 ){
                $type = "str";
            }
        }
        
        if(is_array($choices) && is_null($type)){
            if(is_string($choices[0])){
                $type = "str";
            }
        }

        if(isset($this->resource->options[$name]["type"])){
            $type = $this->resource->options[$name]["type"];
        }

        if(is_null($type)){
            try{
                $type = $this->parseType($propConfig);
            }catch(\Exception $e){
                throw $e;
            }
            
        }
        
        if(in_array($name, $filters)) return;
        
        $description = $this->parseDescription($params[1], $type);
        $required = in_array($name, $resource->resource_keys);

        if($type && !$ignore){
            $options['type'] = $type;
        }
        if($default && !$required && $type != "list" && !$ignore){
            $options['default'] = $default;
        }
        if($choices && !$ignore){
            $negation = isset($options['negation_symbol']) ? $options['negation_symbol']:false;
            if($negation){
                foreach($choices as $choice){
                    $choices[] = $negation.$choice;
                }
            }

            if($type == 'list' && !is_null($default)){
                if(!in_array($default, $choices) && false === strpos($default, ",")){
                    $choices[] = $default;
                }
                if(false !== strpos($default, ',')){
                    $default = explode(",", $default);
                }else{
                    $default = [$default];
                }
                $options['default'] = $default;
            }

            $options['choices'] = $choices;
            if(!isset($options["default"])){
                $options["default"] = "None";
            }
        }

        if($required){
            $options["required"] = "True";
        }

        $options["description"] = "\n$description";
        $resource->options[$name] = $options;
        $resource->documentation["options"]["config"]["suboptions"][$name] = $options;

        $logger = $this->logger;
        $logger->info("configured options {0} {1}", [$resource->name, $options]);
    }

    private function parseDescription($description)
    {
        $description = strtr($description,[
            "`Read more >>`" => "Read more"
        ]);
        // strip links
        //$pattern = "#\[([^\]]+)\]\(([^\|^\s)]+)([^\)]+)\)#im";
        $pattern = "#\[([^\]]+)\]\(([^\|^\s)]+)(\s+\".+\")\)#im";
        $description = preg_replace($pattern,"L(\\1,\\2)", $description);
        $pattern = "#\[([^\]]+)\]\(([^\|^\s)]+)\)#im";
        $description = preg_replace($pattern,"L(\\1,\\2)", $description);

        // strip code value
        $pattern = "#\`([^\`]+)\`#im";
        $description = preg_replace($pattern, "C(\\1)", $description);

        return $description;
    }

    private function parseChoices($config)
    {
        if(false === strpos($config, "|")){
            return false;
        }

        $filters = [
            'ip/mask | ipv6 prefix',
            'ip/netmask | ip range',
            'integer[/time],integer,dst-address | dst-port | src-address[/time]',
            'dns name | ip address/netmask | ip-ip',
        ];

        $exp = explode(";", $config);
        $choices = $exp[0];
        $choices = str_replace("\\","",$choices);
        $choices = strtr($choices,[
            "\\/" => "/",
            "\\" => "",
            "unreachabl" => "unreachable"
        ]);
        if(in_array(strtolower($choices), $filters)){
            return false;
        }
        if(false !== strpos($choices, "[", 0)){
            preg_match("#\[(.*)\]#im", $choices, $matches);
            if(isset($matches[1])){
                return $matches[1];
            }
        }

        $choices = preg_replace("#(\:.*|\S+\,)#","", $choices);
        $choices = strtr($choices, $this->typos);

        preg_match_all("#([^\||^\s]+)#im", $choices, $matches);
        if(isset($matches[1])){
            return $matches[1];
        }

        return false;
    }

    private function parseName($property)
    {
        preg_match("#(\S+)#", $property, $matches);
        if($matches[1]){
            $name = trim($matches[1]);
            $name = str_replace("/","", $name);
            $name = str_replace("-", "_", $name);
            $name = str_replace(".", "_", $name);
            return $name;
        }
        throw \Exception("can not find name in {$property}.");
    }

    /**
     * @param $url
     * @return false|string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function getPage($url)
    {
        $cacheDir = $this->cacheDir.'/routeros-help';
        $cache = $cacheDir.'/'.md5($url).'.html';

        if(!is_dir($cacheDir)){
            mkdir($cacheDir, 0777, true);
        }

        if (!is_file($cache)){
            $client = HttpClient::create();
            $r = $client->request(Request::METHOD_GET, $url);
            $content = $r->getContent();
            file_put_contents($cache, $content);
            return $content;
        }

        return file_get_contents($cache);
    }

    private function parseDefault($config)
    {
        $filters = ["none"];
        $config = str_replace("\_","_", $config);
        preg_match("#default: (\S+)$#im", $config, $matches);
        if(isset($matches[1])){
            $default = $matches[1];
            $default = strtr($default, [
                '"' => "",
            ]);
            if(!in_array($default, $filters)){
                return $default;
            }
        }
        return null;
    }

    private function parseType($config)
    {
        $knownTypes = [
            "num" => "int",
            "bool" => "bool",
            "string" => "str",
            "mac address" => "str",
            "time" => "str",
            "integer" => "int",
            "text" => "str",
            "mac" => "str",
            "list of rates" => "str",
            "name" => "str",
            "yes" => "str",
            "valuestohash" => "str",
            "ip" => "str",
            "list" => "str",
            "script" => "str",
            "byte" => "bytes",
            "default" => "str",
            "start" => "str",
            "10mhz" => "str",
            "bridge" => 'str',
        ];

        preg_match("#^(".implode("|", array_keys($knownTypes)).")#im", $config, $matches);
        if(isset($matches[1])){
            $match = $matches[1];
            return $knownTypes[$match];
        }

        $capsman = [
            "list; default:",
            "; default:",
            "; default: ap",
            "; default: yes",
        ];
        if(in_array(trim($config), $capsman)){
            return "str";
        }

        throw new \Exception("unknown type in {$config}");
    }
}