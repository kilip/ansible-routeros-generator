<?php


namespace App;


use App\RouterOS\Resource;
use Doctrine\Inflector\InflectorFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
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

    private $typos = [
        "estabilished" => "established",
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $ignores = [];

    public function __construct(
        Resource $resource,
        $cacheDir,
        LoggerInterface $logger
    )
    {
        foreach($resource->getGenerator() as $method => $value){
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

        if(
            !$resource->hasOption("comment")
            && $resource->getType() !== "setting"
        ){
            $resource->getOption("comment")
                ->setType("str")
                ->setDescription("Give notes for this resource");
        }

        $inflector = InflectorFactory::create()->build();


        if(is_null($resource->getClassName())){
            $class = $inflector->classify($resource->getName())."Resource";
            $resource->setClassName($class);
        }

        $resource->setModuleName("ros_".$resource->getName());
        $resource->setModuleNamespace("kilip.routeros");

        // configure required option
        $module = $resource->getModule();
        if(isset($module['keys'])){
            $keys = $module['keys'];
            foreach($keys as $key){
                $resource->getOption($key)->setRequired('True');
            }
        }

        $this->configureCustomProps();
        $this->configureTests();

        $this->dump();
        return $configured;
    }

    public function dump()
    {
        $resource = $this->resource;
        $yaml = Yaml::dump(
            $resource->toArray(),
            6,
            4,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
        );
        $target = __DIR__."/../var/resources/{$resource->getPackage()}/{$resource->getName()}.yml";
        if(!is_dir($dir=dirname($target))){
            mkdir($dir, 0775, true);
        }
        file_put_contents($target, $yaml, LOCK_EX);
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

        $params = [];
        $resource = $this->resource;
        $logger = $this->logger;

        $crawler->filter("td")->each(function($crawler, $index) use(&$params){
            $converter = new HtmlConverter([
                "strip_tags" => true,
                "use_autolinks" => false,
            ]);

            $html = $crawler->html();
            /*$html = preg_replace(
                "#(.{1,80})( +|$\n?)|(.{1,80})#im",
                "\\1\\2\n\\3",
                $html
            );*/
            $converted = $converter->convert($html);
            $params[$index] = $converted;
        });

        if(false !== strpos($params[0],"read-only")){
            return;
        }

        $name = $this->parseName($params[0]);

        if(in_array($name, $this->ignores)){
            return;
        }

        $option = $resource->getOption($name);
        $option->setRosKey($name);
        $option
            ->fromText($params[0])
            ->setDescription($params[1])
        ;
    }

    private function configureCustomProps()
    {
        $resource = $this->resource;

        $customProps = [];
        foreach($resource->getOptions() as $key => $option)
        {
            $rosKey = $option->getRosKey();
            $test = str_replace("_", "-", $key);
            if($rosKey != $test){
                $customProps[$key]['ros_key'] = $rosKey;
            }
        }

        $resource->setCustomProps($customProps);
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

    private function parseName($text)
    {
        preg_match("#\*\*(\S+)\*\*#im", $text, $matches);
        if(isset($matches[1])){
            return trim($matches[1]);
        }
    }

    private function configureTests()
    {
        $resource = $this->resource;
        $tests = $resource->getTests();
        $examples = $resource->getExamples();
        $fixtureKey = strtr($resource->getExportCommand(),[
            " " => "_",
            "/" => "",
        ]);
        $fixtures = isset($tests["fixtures"]) ? $tests["fixtures"]:[];
        $fixtures = isset($fixtures[$fixtureKey]) ? $fixtures[$fixtureKey]:"";

        foreach($resource->getExamples() as $index => $config){
            if(!isset($config["pre"])){
                $examples[$index]['pre'] = $fixtures;
            }
        }

        $resource->setExamples($examples);
    }


}