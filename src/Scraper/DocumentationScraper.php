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

namespace RouterOS\Generator\Scraper;

use Doctrine\Inflector\InflectorFactory;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Model\Property;
use RouterOS\Generator\Model\SubMenu;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DocumentationScraper
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var CacheManagerInterface
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
     * @param CacheManagerInterface    $cache
     * @param ConfigurationInterface   $configuration
     * @param SubMenuManagerInterface  $manager
     * @param string                   $configDir
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheManagerInterface $cache,
        ConfigurationInterface $configuration,
        SubMenuManagerInterface $manager,
        string $configDir
    ) {
        $this->dispatcher = $dispatcher;
        $this->configuration = $configuration;
        $this->manager = $manager;
        $this->configDir = $configDir;
        $this->cache = $cache;
    }

    public function start()
    {
        $configuration = $this->configuration;
        $configDir = $this->configDir;
        $dispatcher = $this->dispatcher;
        $cache = $this->cache;
        $config = $cache->processYamlConfig($configuration, 'routeros.pages', $configDir);
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
        $cache = $this->cache;
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
        $page = $cache->getHtmlPage($url);
        $rows = $tableParser->parse($page);

        foreach ($rows as $columns) {
            $propertyParser->parse($subMenu, $columns[0], $columns[1]);
        }

        $this->setDefaultProperty($subMenu);

        $manager->update($subMenu);
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
