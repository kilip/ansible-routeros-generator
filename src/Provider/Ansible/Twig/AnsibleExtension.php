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

    public function __construct(
        string $modulePrefix
    ) {
        $this->modulePrefix = $modulePrefix;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('full_module_name', [$this, 'formatModuleName']),
        ];
    }

    public function formatModuleName($module)
    {
        return "{$this->modulePrefix}.{$module}";
    }
}
