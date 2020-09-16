<?php

namespace Tests\RouterOS\Generator\Fixtures;

use Symfony\Component\HttpClient\Response\MockResponse;

class HtmlPages
{
    private $pages = [];

    public function __construct()
    {
        $this->configure();
    }

    public function handleRequest($method, $url, $options)
    {
        $pages = $this->pages;
        $page = isset($pages[$url]) ? $pages[$url]:null;

        if(null === $page){
            throw new \Exception("Url: {$url} not exists in test pages");
        }

        $content = file_get_contents($page);
        return new MockResponse($content);
    }

    private function configure()
    {
        $this->pages = [
            'https://wiki.mikrotik.com/wiki/Manual:Interface' => __DIR__.'/pages/interface.html',
        ];
    }
}