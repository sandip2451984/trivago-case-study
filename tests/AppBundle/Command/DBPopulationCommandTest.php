<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\DBPopulationCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DBPopulationCommandTest extends KernelTestCase {

    private $application;
    private $command;
    private $commandTester;
    private $doctrineManager;

    private $GenericRepository;

    private $entitiesToCheck = [
        "AppBundle:TopicAlias",
        "AppBundle:Topic",
        "AppBundle:Emphasizer",
        "AppBundle:Criteria",
        "AppBundle:Review"
    ];

    public function testExecute() : void {
        $this->initializations();

        if ($this->GenericRepository->areTablesEmpty($this->entitiesToCheck)) {
            $this->commandTester->execute(['command' => $this->command->getName()]);
            $this->assertEquals("Success.\n", $this->commandTester->getDisplay());

            foreach ($this->entitiesToCheck as $entityName) { 
                $this->GenericRepository->truncateTable($entityName);
            }
        } else {
            $this->commandTester->execute(['command' => $this->command->getName()]);
            $this->assertNotEquals("Success.\n", $this->commandTester->getDisplay());
        }

    }

    private function initializations() : void {
        self::bootKernel();
        $this->application = new Application(static::$kernel);
        $this->application->add(new DBPopulationCommand());
        $this->command = $this->application->find('db:populate');
        $this->commandTester = new CommandTester($this->command);

        $this->doctrineManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->GenericRepository = static::$kernel->getContainer()->get('AppBundle.GenericRepository');
    }

}
