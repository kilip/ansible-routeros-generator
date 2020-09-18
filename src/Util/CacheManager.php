<?php

/*
 * This file is part of the RouterOS project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace RouterOS\Generator\Util;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapter;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CacheManager implements CacheManagerInterface
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var CacheAdapter
     */
    private $adapter;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var string
     */
    private $kernelProjectDir;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheAdapter $adapter,
        HttpClientInterface $httpClient,
        string $cacheDir,
        string $kernelProjectDir,
        $debug = false
    ) {
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
        $this->adapter = $adapter;
        $this->httpClient = $httpClient;
        $this->dispatcher = $dispatcher;
        $this->kernelProjectDir = $kernelProjectDir;
    }

    public function getYamlObject(string $className, string $file): object
    {
        $cacheDir = $this->cacheDir;
        $debug = $this->debug;
        $id = md5($file);
        $cachePath = "{$cacheDir}/yaml-objects/{$id}.dat";
        $cache = new ConfigCache($cachePath, $debug);

        if (!$cache->isFresh()) {
            $config = Yaml::parseFile($file);
            $ob = new $className();

            //@TODO: throws when object doesn't have toArray() method
            $ob->fromArray($config);

            $resources = [new FileResource($file)];
            $contents = serialize([$ob]);
            $cache->write($contents, $resources);
        }

        $data = file_get_contents($cachePath);
        $data = unserialize($data);

        return $data[0];
    }

    public function processYamlConfig(
        ConfigurationInterface $configuration,
        string $path,
        bool $force = false
    ): array {
        $cacheDir = $this->cacheDir;
        $id = md5("{$path}");
        $cachePath = "{$cacheDir}/processed-yml/{$id}.php";
        $cache = new ConfigCache($cachePath, $this->debug);
        $buildTree = $configuration->getConfigTreeBuilder()->buildTree();
        $root = $buildTree->getName();

        if (!$cache->isFresh() || $force) {
            $configs = [];
            $resources = [];
            $finder = Finder::create()->in($path);
            foreach ($finder->files() as $file) {
                $data = $this->parseYaml($file->getRealPath());
                //$configs[$exp[0]][$exp[1]][] = $data;
                $configs[$root][] = $data;
                $resources[] = new FileResource($file->getRealPath());
            }
            $r = new \ReflectionClass(\get_class($configuration));
            $resources[] = new FileResource($r->getFileName());

            $processor = new Processor();
            $processed = $processor->processConfiguration($configuration, $configs);

            $contents = "<?php\n".
                'return '.var_export($processed, true)
                .";\n";
            $cache->write($contents, $resources);
        }

        $data = require $cachePath;

        return $data;
    }

    public function parseYaml(string $file): array
    {
        $kernelProjectDir = $this->kernelProjectDir;

        if (file_exists($test = $kernelProjectDir.'/'.$file)) {
            $file = $test;
        }

        $cacheDir = $this->cacheDir;
        $debug = $this->debug;
        $id = md5($file);
        $cachePath = "{$cacheDir}/yml/{$id}.php";

        $cache = new ConfigCache($cachePath, $debug);
        if (!$cache->isFresh()) {
            $data = Yaml::parseFile($file);
            $resources = [];
            $resources[] = new FileResource($file);

            $contents = "<?php\nreturn "
                .var_export($data, true).';';

            $cache->write($contents, $resources);
        }

        $data = require $cachePath;

        return $data;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getHtmlPage(string $url): string
    {
        $cache = $this->adapter;
        $id = md5($url);
        $page = $cache->getItem($id);

        if (!$page->isHit()) {
            $dispatcher = $this->dispatcher;
            $client = $this->httpClient;
            $event = new ProcessEvent('Loading html page from : {0}', [$url]);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOG);

            $r = $client->request(Request::METHOD_GET, $url);
            $content = $r->getContent();
            $page->set($content);
            $cache->save($page);
        }

        return $page->get();
    }
}
