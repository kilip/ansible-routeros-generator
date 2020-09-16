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

namespace RouterOS\Generator\Provider\Ansible\Bridge;

use RouterOS\Generator\Provider\Ansible\Event\BuildEvent;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutAnsibleCollection implements EventSubscriberInterface
{
    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var string
     */
    private $gitRepository;

    public function __construct(
        string $gitRepository,
        string $targetDir,
        ProcessHelper $processHelper = null
    ) {
        if (null === $processHelper) {
            $processHelper = new ProcessHelper();
        }

        $this->gitRepository = $gitRepository;
        $this->targetDir = $targetDir;
        $this->processHelper = $processHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::PREPARE => 'onPrepare',
        ];
    }

    public function onPrepare(BuildEvent $event)
    {
        $processHelper = $this->processHelper;
        $repository = $this->gitRepository;
        $targetDir = $this->targetDir;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        if (!is_dir($targetDir.'/.git')) {
            $git = $processHelper->findExecutable('git');
            $commands = [
                $git,
                'clone',
                $repository,
                $targetDir,
            ];
            $processHelper
                ->create($commands)
                ->run();
        }
    }
}
