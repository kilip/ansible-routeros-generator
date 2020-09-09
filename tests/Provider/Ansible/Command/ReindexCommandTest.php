<?php

namespace Tests\RouterOS\Generator\Provider\Ansible\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\TesterTrait;
use Tests\RouterOS\Generator\UseContainerTrait;

class ReindexCommandTest extends KernelTestCase
{
    use UseContainerTrait, TesterTrait;

    public function testExecute()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);

        $command = $application->find("ansible:reindex-module");
        $tester = new CommandTester($command);


        $this->prepare();
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertRegExp('#Start Reindexing Modules#', $output);
        $this->assertRegExp('#Processing bridge_settings#', $output);
        $this->assertRegExp('#3/3#', $output);
    }

    private function prepare()
    {
        $this->initOutput([]);
        $output = $this->getOutput();

        $listener = $this->getContainer()->get('routeros.event.console_process_subscriber');
        $listener->setOutput($output);

        $progressBar = $listener->getProgressBar();
        $progressBar->setRedrawFrequency(1);
        $progressBar->maxSecondsBetweenRedraws(0);
        $progressBar->minSecondsBetweenRedraws(0);
        $scraper = $this->getContainer()->get('routeros.scraper.documentation');
        $scraper->start();
    }
}
