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

namespace RouterOS\Generator\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessEventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(
        LoggerInterface $logger,
        OutputInterface $output
    ) {
        $this->progressBar = new ProgressBar($output);
        $this->logger = $logger;
    }
}
