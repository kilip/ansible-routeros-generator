<?php

namespace App\RouterOS;


class Option
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

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $elements;

    /**
     * @var array
     */
    private $choices = [];

    /**
     * @var string
     */
    private $default;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $required;

    /**
     * @var bool
     */
    private $choicesIgnored = false;

    /**
     * @var bool
     */
    private $defaultIgnored = false;

    /**
     * @var string
     */
    private $ros_key;

    public function toArray()
    {
        $option = [];
        $option['name'] = $this->name;
        $option['type'] = $this->type;
        $option["ros_key"] = $this->ros_key;

        if(!is_null($this->elements)){
            $option['elements'] = $this->elements;
        }

        if(!empty($this->choices)){
            $option['choices'] = $this->choices;
        }

        if(!is_null($this->default)){
            $option['default'] = $this->default;
        }

        if(!is_null($this->description)){
            $option["description"] = $this->description;
        }

        return $option;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getRosKey(): ?string
    {
        return $this->ros_key;
    }

    /**
     * @param string $roskey
     * @return static
     */
    public function setRosKey(string $roskey)
    {
        $this->ros_key = $roskey;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getElements(): ?string
    {
        return $this->elements;
    }

    /**
     * @param string $elements
     * @return static
     */
    public function setElements(string $elements)
    {
        $this->elements = $elements;
        return $this;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     * @return static
     */
    public function setChoices(array $choices)
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     * @return static
     */
    public function setDefault($default)
    {
        if(is_string($default) && $default == ""){
            $default = null;
        }
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription(string $description)
    {
        // normalize smart quotes
        $description = preg_replace("#[\"“‘”]#im", "'", $description);

        $description = strtr($description,[
            "(/wiki/" => "(https://wiki.mikrotik.com/wiki/"
        ]);
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequired(): ?string
    {
        return $this->required;
    }

    /**
     * @param string $required
     * @return static
     */
    public function setRequired(string $required)
    {
        $this->required = $required;
        return $this;
    }



    /**
     * @param string $text
     * @return static
     * @throws \Exception
     */
    public function fromText($text)
    {
        $this->parseChoices($text);

        if(is_null($this->type)){
            $this->parseType($text);
        }

        $this->parseDefault($text);

        return $this;
    }

    private function parseChoices($text)
    {
        if($this->isChoicesIgnored()){
            return $this;
        }

        $choices = [];
        $ignores = [
            "name of the country",
            "default | integer",
            "comma separated",
            "integer",
            "string"
        ];

        if(0 == preg_match("#\(\*(.+)\*\;#im", $text, $matches)){
            return $this;
        }

        $strChoice = $matches[1];

        if(false === strpos($strChoice, "|") && false === strpos($strChoice, ",")){
            return $this;
        }

        $strChoice = preg_replace("#(list.+\()(.+)(\))#im", "\\2", $strChoice);
        $strChoice = preg_replace("#(list.+\[)(.+)(\])#im", "\\2", $strChoice);
        $strChoice = preg_replace(("#(\s+\\\[\S+\\\])#im"), "", $strChoice);
        // remove spaces
        $strChoice = preg_replace("#(\s+)(\|)(\s+)#im", "\\2", $strChoice);
        $strChoice = preg_replace("#(\,)(\s+)#im", "\\1", $strChoice);
        $strChoice = trim($strChoice, "\\");

        foreach($ignores as $ignore){
            if(false !== strpos(strtolower($strChoice), $ignore)){
                return $this;
            }
        }

        if(0 !== preg_match_all("#([^\||^\,]+)#im", $strChoice, $matches)){
            $choices = $matches[1];
        }

        sort($choices);
        $this->choices = $choices;
        return $this;
    }

    private function parseType($text)
    {
        $choices = $this->choices;
        $text = strtolower($text);
        $map = [
            "string" => self::OPT_STR,
            "integer" => self::OPT_INT,
            "bool" => self::OPT_BOOL,
            "mac address" => self::OPT_STR,
            "mac" => self::OPT_STR,
            "time" => self::OPT_STR,
            "text" => self::OPT_STR,
            "name" => self::OPT_STR,
            "bridge interface" => self::OPT_STR,
            "default" => self::OPT_STR,
            "auto" => self::OPT_STR,
        ];

        $match = false;

        if(
            false !== strpos($text, "list of")
            || false  !== strpos($text, "comma separated list")
        ){
            $this->type = "list";
            $this->elements = "str";
            return;
        }

        // detect type by choices value
        if(isset($choices[0])){
            $choice = $choices[0];
            if(in_array($choice,['yes','no'])){
                $this->type = self::OPT_STR;
                return;
            }

            if(is_string($choice)){
                $this->type = self::OPT_STR;
                return;
            }
        }

        # \(\*([^\:]+)\*\;
        # \(\*(.+)\*\;
        # \(\*([^\:]+)
        if(0 != preg_match('#\(\*([^\:|^\*]+)#im', $text, $matches)){
            $match = $matches[1];
        }

        foreach($map as $key => $type){
            if(false !== strpos($match, $key)){
                $this->type = $type;
                return;
            }
        }



        if(!isset($map[$match])){
            throw new \Exception("Unknown Type: {$match} = {$text}");
        }

        $this->type = $map[$match];
    }

    private function parseDefault($text)
    {
        if($this->defaultIgnored) return $this;
        $type = $this->type;
        $default = null;
        $choices = $this->choices;
        $text = strtr($text, [
            "*" => "",
            "**" => "",
        ]);

        if(0!==preg_match("#\s+default\:\s+?(.+)\)#im", $text, $matches)){
            $default = $matches[1];
        }

        $default = strtr($default, [
            "\\" => "",
        ]);

        if(!is_null($default)){
            if($type == self::OPT_INT){
                $default = (int)$default;
            }
        }

        if ($type == 'list' && !empty($choices)){
            $default = preg_replace("#(\;)(\s+)#im", "\\1", $default);
            if(0 !== preg_match_all("#([^\;]+)#im", $default, $matches)){
                $default = $matches[1];
            }
        }

        $filters = ["empty"];
        if(in_array($default, $filters)){
            return;
        }

        if(is_string($default)){
            $default = strtr($default, [
                "regulatory_domain" => "regulatory-domain",
            ]);
        }

        if(!empty($choices) && !is_array($default) && !in_array($default, $choices)){
            return;
        }

        if(!is_null($default)){
            $this->setDefault($default);
        }
    }

    /**
     * @return bool
     */
    public function isChoicesIgnored(): bool
    {
        return $this->choicesIgnored;
    }

    /**
     * @param bool $choicesIgnored
     * @return static
     */
    public function setChoicesIgnored(bool $choicesIgnored)
    {
        $this->choicesIgnored = $choicesIgnored;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultIgnored(): bool
    {
        return $this->defaultIgnored;
    }

    /**
     * @param bool $defaultIgnored
     * @return static
     */
    public function setDefaultIgnored(bool $defaultIgnored)
    {
        $this->defaultIgnored = $defaultIgnored;
        return $this;
    }
}