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
    public function getFilters()
    {
        return [
            new TwigFilter('ansible_normalize_output', [$this, 'normalizeOutput']),
            new TwigFilter('resource_base_import', [$this, 'resourceBaseImport']),
        ];
    }

    public function normalizeOutput($output)
    {
        // replace quoted links
        $pattern = '#(\[\s+]?)([^\]]+)\]\((.+)(\s+?\".+\")([\s+]?\))#im';
        $output = preg_replace($pattern, 'L(\\2, \\3)', $output);

        $pattern = '#(\[[\s+]?)([^\]]+)\]\(([^\s?\)]+)([\s+]?\))#im';
        $output = preg_replace($pattern, 'L(\\2, \\3)', $output);

        $pattern = '#L\((.+)(\s+\".+\")\)#im';
        $output = preg_replace($pattern, 'L(\\1)', $output);

        // decorize code output
        $output = preg_replace('#\<var>([^<\/]+)<\/var>#im', 'C(\\1)', $output);

        return preg_replace('#\`(.+)\`#im', 'C(\\1)', $output);
    }

    public function resourceBaseImport($package)
    {
        $exp = explode(".", $package);
        $prefix = str_repeat('.', count($exp));
        $contents = "from {$prefix}.base import ResourceBase";
        return $contents;
    }
}
