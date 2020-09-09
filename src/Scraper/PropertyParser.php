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

use RouterOS\Generator\Model\Property;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Util\Text;

class PropertyParser
{
    private $mapTypes = [
        'integer' => Property::TYPE_INTEGER,
        'string' => Property::TYPE_STRING,
        'bool' => Property::TYPE_BOOL,
        'mac address' => Property::TYPE_STRING,
        'mac' => Property::TYPE_STRING,
        'time' => Property::TYPE_STRING,
        'text' => Property::TYPE_STRING,
        'name' => Property::TYPE_STRING,
        'bridge interface' => Property::TYPE_STRING,
        'default' => Property::TYPE_STRING,
        'auto' => Property::TYPE_STRING,
        'list' => Property::TYPE_LIST,
    ];

    public function parse(SubMenu $subMenu, $info, $description)
    {
        if (
            false !== strpos($info, 'read-only')
        ) {
            return;
        }

        $rosName = $this->parseName($info);
        $name = Text::normalizeName($rosName);

        $property = $subMenu->getProperty($name);

        $property->setDescription($description);
        $property->setOriginalName($name);

        $this->parseChoices($property, $info);
        $this->parseType($property, $info);
        $this->parseDefault($property, $info);
    }

    private function parseName($text)
    {
        preg_match("#\*\*(\S+)\*\*#im", $text, $matches);
        if (isset($matches[1])) {
            return trim($matches[1]);
        }
    }

    private function parseType(Property $property, $text)
    {
        if (
            null !== $property->getType()
        ) {
            return;
        }

        $choices = $property->getChoices();
        $text = strtolower($text);
        $match = false;
        $map = $this->mapTypes;

        if (
            false !== strpos($text, 'list of')
            || false !== strpos($text, 'comma separated list')
        ) {
            $property->setType(Property::TYPE_LIST);

            return;
        }

        // detect type by choices value
        if (isset($choices[0])) {
            $choice = $choices[0];
            if (\in_array($choice, ['yes', 'no'], true)) {
                $property->setType(Property::TYPE_STRING);

                return;
            }

            if (\is_string($choice)) {
                $property->setType(Property::TYPE_STRING);

                return;
            }
        }

        if (0 != preg_match('#\(\*([^\:|^\*]+)#im', $text, $matches)) {
            $match = $matches[1];
        }

        foreach ($map as $key => $type) {
            if (false !== strpos($match, $key)) {
                $property->setType($type);

                return;
            }
        }

        if (!isset($map[$match])) {
            throw new \Exception("Unknown Type {$property->getName()}: {$match} = {$text}");
        }
        $property->setType($map[$match]);
    }

    private function parseChoices(Property $property, $text)
    {
        if ($property->hasOption(Property::OPTION_IGNORE_CHOICES)) {
            return $this;
        }

        $choices = [];
        $ignores = [
            'name of the country',
            'default | integer',
            'comma separated',
            'integer',
            'string',
        ];

        if (0 == preg_match("#\(\*(.+)\*\;#im", $text, $matches)) {
            return $this;
        }

        $strChoice = $matches[1];

        if (false === strpos($strChoice, '|') && false === strpos($strChoice, ',')) {
            return $this;
        }

        $strChoice = preg_replace("#(list.+\()(.+)(\))#im", '\\2', $strChoice);
        $strChoice = preg_replace("#(list.+\[)(.+)(\])#im", '\\2', $strChoice);
        $strChoice = preg_replace(("#(\s+\\\[\S+\\\])#im"), '', $strChoice);
        // remove spaces
        $strChoice = preg_replace("#(\s+)(\|)(\s+)#im", '\\2', $strChoice);
        $strChoice = preg_replace("#(\,)(\s+)#im", '\\1', $strChoice);
        $strChoice = trim($strChoice, '\\');

        foreach ($ignores as $ignore) {
            if (false !== strpos(strtolower($strChoice), $ignore)) {
                return $this;
            }
        }

        if (0 !== preg_match_all("#([^\||^\,]+)#im", $strChoice, $matches)) {
            $choices = $matches[1];
        }

        sort($choices);
        $property->setChoices($choices);

        return $this;
    }

    private function parseDefault(Property $property, $text)
    {
        if (
            $property->hasOption(Property::OPTION_IGNORE_DEFAULT)
        ) {
            return;
        }
        $type = $property->getType();
        $default = null;
        $choices = $property->getChoices();
        $text = strtr($text, [
            '*' => '',
            '**' => '',
        ]);

        if (0 !== preg_match("#\s+default\:\s+?(.+)\)#im", $text, $matches)) {
            $default = $matches[1];
        }

        if (null !== $default) {
            $default = strtr($default, [
                '\\' => '',
            ]);
        }

        if (null !== $default) {
            if (Property::TYPE_INTEGER == $type) {
                $default = (int) $default;
            }
        }

        if ('list' == $type && !empty($choices)) {
            $default = preg_replace("#(\;)(\s+)#im", '\\1', $default);
            if (0 !== preg_match_all("#([^\;]+)#im", $default, $matches)) {
                $default = $matches[1];
            }
        }

        $filters = ['empty'];
        if (\in_array($default, $filters, true)) {
            return;
        }

        if (\is_string($default)) {
            $default = strtr($default, [
                'regulatory_domain' => 'regulatory-domain',
            ]);
        }

        if (!empty($choices) && !\is_array($default) && !\in_array($default, $choices, true)) {
            return;
        }

        if (null !== $default) {
            $property->setDefaultValue($default);
        }
    }
}
