<?php


namespace App\Twig;


use App\RouterOS\Resource;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\Yaml\Yaml;

class DocExtension extends AbstractExtension
{

    /**
     * @var TwigEnvironment
     */
    private $environment;

    public function __construct(TwigEnvironment $environment)
    {

        $this->environment = $environment;
    }

    public function getFilters()
    {
        return [
            new TwigFilter("rst_subtitle", [$this, "formatSubTitle"]),
            new TwigFilter("rst_title", [$this, "formatTitle"]),
            new TwigFilter("doc", [$this, 'formatDoc']),
            new TwigFilter("format_yaml", [$this, "formatYaml"]),
            new TwigFilter("yaml_comment", [$this, "formatYamlComment"]),
            new TwigFilter("console", [$this, "formatConsole"]),
        ];
    }

    public function formatConsole($text, Resource $resource, int $spacing=0, $comment=false)
    {
        return $resource->formatConsoleOutput($text, $spacing, $comment);
    }

    public function formatTitle($text)
    {
        $length = strlen($text);
        $mark = str_repeat("=", $length);
        $output = <<<EOC

{$mark}
{$text}
{$mark}

EOC;
        return $output;

    }

    public function formatSubTitle($text)
    {
        $length = strlen($text);
        $mark = str_repeat("-", $length);
        $output = <<<EOC

{$mark}
{$text}
{$mark}

EOC;
        return $output;

    }

    public function formatDoc($text)
    {
        $lines = explode("\n",$text);
        $contents = [];
        $indent = "            ";
        foreach($lines as $index => $line){
            $line = trim($line);
            $prefix = "";
            if($index > 0){
                $prefix = $indent;
            }
            $line = $this->wrapDoc($line, $prefix);
            $line = $this->convertTags($line);
            $line = $prefix.$line;
            $contents[] = $line;
        }

        $contents  = implode("\n", $contents);
        // remove blank lines
        $contents = preg_replace("#^(?:[\t ]*(?:\r?\n|\r))+#im","", $contents);

        // fix link
        $contents = preg_replace("#(L\(\s+)(.*)#im", "L(\\2", $contents);
        $contents = preg_replace("#L\((.+)(\s+)(.+)\)#im", "L(\\1 \\3)", $contents);
    
        // remove \_ to _
        $contents = strtr($contents, [
            "\\" => "",
        ]);
        return $contents;
    }

    public function formatYaml(array $config)
    {
        $output =  Yaml::dump($config, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $contents = [];
        $lines = explode("\n", $output);
        $prefix = "";
        foreach($lines as $line){
            if(trim($line) == "-"){
                $prefix = $line." ";
                continue;
            }

            if($prefix !== ""){
                $contents[] = "    ".$prefix.trim($line);
            }else{
                $contents[] = "    ".$line;
            }

            if($prefix !== ""){
                $prefix = "";
            }
        }


        return implode("\n",$contents);
    }

    public function formatYamlComment($text)
    {
        $lines = explode("\n", $text);
        $contents = [];

        foreach($lines as $line){
            $content = "#  ".$line;
            $contents[] = trim($content);
        }
        return implode("\n",$contents);
    }

    private function wrapDoc($text)
    {
        $prefix = "            ";
        if(0 === strpos($text, "-")){
            $prefix .= "  ";
        }
        $text = preg_replace(
            "#(.{1,80})( +|$\n?)|(.{1,80})#im",
            "\\1\\2\n{$prefix}\\3",
            $text
        );
        return $text;
    }

    private function convertTags($text)
    {
        $text = preg_replace("#^(?:[\t ]*(?:\r?\n|\r))+#im","", $text);

        // remove blank lines
        $text = preg_replace("#[ \t]+(\r?$)#im","", $text);

        // quote !local like values
        $text = preg_replace("#(\!\S+)#im", "\"\\1\"", $text);

        // normalize links
        $pattern = "#\[([^\]]+)\]\(([^\|^\s)]+)(\s+\".+\")\)#im";
        $text = preg_replace($pattern,"L(\\1,\\2)", $text);
        $pattern = "#\[([^\]]+)\]\(([^\|^\s)]+)\)#im";
        $text = preg_replace($pattern,"L(\\1,\\2)", $text);

        // strip code value
        $pattern = "#\`([^\`]+)\`#im";
        $text = preg_replace($pattern, "C(\\1)", $text);
        return $text;
    }

}