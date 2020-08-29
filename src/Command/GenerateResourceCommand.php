<?php


namespace App\Command;


use App\RouterOS\Resource;
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
    protected static $defaultName = "app:generate-resource";

    private $cacheDir;

    private $twig;

    private $useDebug = false;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addOption('use-debug', null, InputOption::VALUE_NONE);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->cacheDir = $container->getParameter('kernel.cache_dir');
        $this->twig = $container->get("twig");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resources = $this->getResources();
        $target = "/home/toni/project/ansible/ansible_collections/kilip/routeros/plugins";
        //$target = realpath(__DIR__.'/../../var/generated');
        
        foreach($resources as $resource){
            $this->generateResources($resource, $output);
            $resource->configure();
            $resource->render($this->twig, $target);
        }
        $this->renderSubset($this->twig, $resources, $target);
        return Command::SUCCESS;
    }

    private function renderSubset(Twig $twig, $resources, $targetDir)
    {
        $target = $targetDir."/module_utils/resources";
        if(!is_dir($target)){
            mkdir($target, 0777, true);
        }
        $target = $target."/subset.py";
        $output = $twig->render("subset.py.twig",[
            "resources" => $resources
        ]);
        file_put_contents($target, $output, LOCK_EX);
    }

    /**
     * @return Resource[]
     */
    private function getResources()
    {
        $resources = [];
        $dir = __DIR__.'/../Resources/resources';
        if($this->useDebug){
            $dir = __DIR__.'/../Resources/debug';
        }
        $finder = Finder::create()
            ->in($dir)
        ;
        /* @var SplFileInfo $file */
        foreach($finder->files() as $file){
            $data = Yaml::parseFile($file->getRealPath());
            foreach($data as $name => $config){
                $resource = new Resource();
                $resource->name = $name;
                $resource->relativePath = $file->getFilenameWithoutExtension();

                foreach($config as $key=>$value){
                    $resource->$key = $value;
                }
                /*
                $resource->url = $config["url"];
                $resource->html_id = $config["html_id"];
                $resource->description = isset($config["description"]) ? $config["description"]:null;
                $resource->options = isset($config["options"]) ? $config["options"]:array();
                $resource->ignores = isset($config["ignores"]) ? $config["ignores"]:array();
                $resource->command_root = isset($config["command_root"]) ? $config["command_root"]:null;
                */
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