<?php


namespace App\Command;


use App\Compiler;
use App\RouterOS\Resource;
use App\Configurator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Twig\Environment as Twig;

class GenerateResourceCommand extends Command implements ContainerAwareInterface
{
    protected static $defaultName = "app:generate";

    private $cacheDir;

    private $twig;

    private $debugMode = false;

    private $logger;

    private $targetDir;

    public function __construct(LoggerInterface $logger, $name = null)
    {
        parent::__construct($name);
        $this->addOption('debug-mode', null, InputOption::VALUE_NONE);
        $this->logger = $logger;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->cacheDir = $container->getParameter('kernel.cache_dir');
        $this->twig = $container->get("twig");
        $this->targetDir = $container->getParameter("target_dir");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debugMode = $input->getOption("debug-mode");

        $resources = $this->getResources($output);

        $target = realpath(__DIR__.'/../../var/generated');
        if(!$this->debugMode){
            $target = $this->targetDir;
        }
        
        foreach($resources as $resource){
            $output->writeln("<info>Configuring </info> <comment>{$resource->getName()}</comment>");
            $parser = new Configurator(
                $resource,
                $this->cacheDir,
                $this->logger
            );
            $parser->configure();
        }
        //$this->renderSubset($this->twig, $resources, $target);

        $compiler = new Compiler($resources, $this->logger, $this->twig, $target);
        $compiler->compile();
        return Command::SUCCESS;
    }

    /**
     * @return Resource[]
     */
    private function getResources(OutputInterface $output)
    {
        $resources = [];
        $dir = __DIR__.'/../Resources/packages';
        if($this->debugMode){
            $dir = __DIR__.'/../Resources/debug';
        }
        $finder = Finder::create()
            ->in($dir)
        ;
        /* @var SplFileInfo $file */
        foreach($finder->files() as $file){
            $output->writeln("Parsing yaml: {$file->getRealPath()}");
            $data = Yaml::parseFile($file->getRealPath());
            foreach($data as $name => $config){
                $resource = new Resource();
                $resource->setName($name);
                foreach($config as $key=>$value){
                    $method = 'set'.$key;
                    call_user_func([$resource, $method], $value);
                }
                $resources[$name] = $resource;
            }
        }
        return $resources;
    }

    private function generateResources(Resource $resource, OutputInterface $output)
    {
        $url = $resource->url;
        if(is_null($url)) return;
        $page = $this->getPage($url);
        $crawler = new Crawler($page, $url);
        $id = $resource->html_id;
        $next = $crawler->filter("h1 span#{$id}");
        if($next->count() == 0){
            $next = $crawler->filter("h2 span#{$id}");
        }

        $next = $next->parents();
        /* @var \Symfony\Component\DomCrawler\Crawler $next */
        /* @var \DOMElement[] $trs */
        $i = 0;
        $continue = true;
        $code = "";
        $description = "";
        $params = [];
        while($continue){
            $next = $next->nextAll();
            $class = $next->attr("class");

            $node = $next->filter("#shbox code");
            $table = $next->filter("tbody");
            
            if($node->count()>0 && $code==""){
                $code = $node->text();
            }
            elseif($class == 'styled_table' || $table->count() > 0){
                $params = $this->parseTable($output, $next->outerHtml());
            }
            else{
                $node = $next->filter("p");
                if($node->count()){
                    $description .= $node->outerHtml();
                }
            }
        
            if("styled_table" == $class || $i > 15){
                $continue = false;
            }else{
                $i++;
            }
        }

        if(is_null($resource->command_root)){
            $resource->command_root = $code;
        }
        $resource->properties = $params;
        if(is_null($resource->description)){
            $resource->description = $description;
        }
    }

    private function parseTable(OutputInterface $output, $html)
    {
        $crawler = new Crawler($html);
        $trs = $crawler->filter("table tr");
        $params = [];

        $trs->each(function(Crawler $node, $index) use ($output, &$params, $crawler){
            if($index == 0){
                return;
            }
            $param = [];
            $node->filter("td")->each(function($node, $index) use(&$param){
                $key = $index == 0 ? "property":"description";
                $param[$key] = $index==0 ? $node->text():$node->html();
            });
            $params[] = $param;
        });
        return $params;
    }

    private function getPage($url)
    {
        $cacheDir = $this->cacheDir.'/ROS';
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

}