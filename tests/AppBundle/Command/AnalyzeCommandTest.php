<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\AnalyzeCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AnalyzeCommandTest extends KernelTestCase {

    private $application;
    private $command;
    private $commandTester;

    public function testExecute() : void {
        /*
            IMPORTANT: Here we are just testing the console command.
            The full tests for the analyzer service are here: /tests/AppBundle/DefaultAnalyzerTest.php
        */
        $this->initializations();

        $this->_testMissingArgument();
        $this->_testValidLibraryArgument();
        $this->_testInvalidLibraryArgument();
    }

    private function initializations() : void {
        self::bootKernel();
        $this->application = new Application(static::$kernel);
        $this->application->add(new AnalyzeCommand());
        $this->command = $this->application->find('app:analyze');
        $this->commandTester = new CommandTester($this->command);
    }

    private function _testMissingArgument() : void {
        try {
            $this->commandTester->execute([
                'command' => $this->command->getName()
            ]);
        } catch (\Exception $e) {
            $this->assertEquals('Not enough arguments (missing: "review").', $e->getMessage());
        }
    }

    private function _testValidLibraryArgument() : void {
        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'review' => 'abc'
        ]);
        $output1 = $this->commandTester->getDisplay();

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'review' => 'abc',
            'library' => 'Default'
        ]);
        $output2 = $this->commandTester->getDisplay();

        $this->assertEquals($output1, $output2);
    }

    private function _testInvalidLibraryArgument() : void {
        $libraryName = 'INVALID_ONE';

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'review' => 'abc',
            'library' => $libraryName
        ]);
        $output = $this->commandTester->getDisplay();

        $this->assertEquals('Invalid library: ' . $libraryName . "\n", $output);
    }

}
