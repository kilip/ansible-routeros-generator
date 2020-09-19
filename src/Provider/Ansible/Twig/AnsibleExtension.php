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

namespace RouterOS\Generator\Provider\Ansible\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AnsibleExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $modulePrefix;
    /**
     * @var string
     */
    private $moduleCompletePrefix;

    public function __construct(
        $modulePrefix = 'ros_',
        $moduleCompletePrefix = 'kilip.routeros'
    ) {
        $this->modulePrefix = $modulePrefix;
        $this->moduleCompletePrefix = $moduleCompletePrefix;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('ansible_normalize_output', [$this, 'normalizeOutput']),
            new TwigFilter('resource_base_import', [$this, 'resourceBaseImport']),
            new TwigFilter('ansible_module_name', [$this, 'moduleName']),
        ];
    }

    public function normalizeOutput($output)
    {
        // fix on/off value
        $output = preg_replace('#([\-|\:]+)\s(on|off)#', '\\1 "\\2"', $output);

        // fix hex value
        $output = preg_replace('#(0x8100|0x88a8|0x9100)#', '"\\1"', $output);

        return preg_replace('#\`(.+)\`#im', 'C(\\1)', $output);
    }

    public function resourceBaseImport($package)
    {
        $exp = explode('.', $package);
        $prefix = str_repeat('.', \count($exp));

        return "from {$prefix}.base import ResourceBase";
    }

    public function moduleName($name, $complete = false)
    {
        $modulePrefix = $this->modulePrefix;
        $moduleCompletePrefix = $this->moduleCompletePrefix;

        $moduleName = "{$modulePrefix}{$name}";
        if ($complete) {
            $moduleName = "{$moduleCompletePrefix}.{$moduleName}";
        }

        return $moduleName;
    }
}
