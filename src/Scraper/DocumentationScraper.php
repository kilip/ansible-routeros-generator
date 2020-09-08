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

namespace RouterOS\Scraper;

use Doctrine\Inflector\InflectorFactory;
use RouterOS\Contracts\SubMenuManagerInterface;
use RouterOS\Event\ProcessEvent;
use RouterOS\Model\Property;
use RouterOS\Model\SubMenu;
use RouterOS\Util\YamlConfigLoader;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapter;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;

class DocumentationScraper
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var CacheAdapter
     */
    private $cache;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var SubMenuManagerInterface
     */
    private $manager;

    /**
     * DocumentationScraper constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param CacheAdapter             $cache
     * @param ConfigurationInterface   $configuration
     * @param SubMenuManagerInterface  $manager
     * @param string                   $configDir
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheAdapter $cache,
        ConfigurationInterface $configuration,
        SubMenuManagerInterface $manager,
        string $configDir
    ) {
        $this->dispatcher = $dispatcher;
        $this->cache = $cache;
        $this->configuration = $configuration;
        $this->manager = $manager;
        $this->configDir = $configDir;
    }

    public function start()
    {
        $configuration = $this->configuration;
        $configDir = $this->configDir;
        $dispatcher = $this->dispatcher;
        $loader = new YamlConfigLoader();
        $config = $loader->process($configuration, 'routeros.pages', $configDir);
        $pages = $config['pages'];

        $event = new ProcessEvent('Start Scraping Web Pages', [], \count($pages));
        $dispatcher->dispatch($event, ProcessEvent::EVENT_LOG);
        $i = 1;
        foreach ($pages as $name => $page) {
            $event
                ->setCurrent($i)
                ->setMessage('Processing submenu {0}')
                ->setContext([$name]);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);
            $this->scrapSubMenu($name, $page);
            ++$i;
        }
    }

    /**
     * @param array $config
     * @param mixed $name
     */
    private function scrapSubMenu($name, array $config)
    {
        $manager = $this->manager;
        $subMenu = $manager->findOrCreate($name);
        $inflector = InflectorFactory::create()->build();
        $url = $config['generator']['url'];

        // preparing sub menu
        foreach ($config as $name => $value) {
            if ('properties' == $name) {
                continue;
            }
            $method = 'set'.ucfirst($inflector->camelize($name));
            \call_user_func([$subMenu, $method], $value);
        }

        foreach ($config['properties'] as $name => $value) {
            $property = $subMenu->getProperty($name);
            foreach ($value as $key => $propConfig) {
                $method = 'set'.ucfirst($inflector->camelize($key));
                \call_user_func([$property, $method], $propConfig);
            }
        }

        $tableParser = TableParser::fromSubMenu($subMenu);
        $propertyParser = new PropertyParser();
        $page = $this->getPageContents($url);
        $rows = $tableParser->parse($page);

        foreach ($rows as $columns) {
            $propertyParser->parse($subMenu, $columns[0], $columns[1]);
        }

        $this->setDefaultProperty($subMenu);

        $manager->update($subMenu);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function getPageContents(string $url): string
    {
        $cache = $this->cache;
        $id = md5($url);
        $page = $cache->getItem($id);

        if (!$page->isHit()) {
            $dispatcher = $this->dispatcher;
            $event = new ProcessEvent('Loading html page from : {0}', [$url]);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOG);

            $client = HttpClient::create();
            $r = $client->request(Request::METHOD_GET, $url);
            $content = $r->getContent();
            $page->set($content);
            $cache->save($page);
        }

        return $page->get();
    }

    private function setDefaultProperty(SubMenu $subMenu)
    {
        if (
            !$subMenu->hasProperty('disabled')
            && !$subMenu->hasOption(SubMenu::OPTIONS_NON_DISABILITY_SUBMENU)
        ) {
            $property = new Property();
            $property
                ->setName('disabled')
                ->setType(Property::TYPE_BOOL)
                ->setDescription('Interface disability');
            $subMenu->addProperty($property);
        }

        if (
            !$subMenu->hasProperty('comment')
            && !$subMenu->hasOption(SubMenu::OPTIONS_NON_COMMENTED_SUBMENU)
        ) {
            $property = new Property();
            $property
                ->setName('comment')
                ->setType('string')
                ->setDescription('Description for this resource');
            $subMenu->addProperty($property);
        }
    }
}
