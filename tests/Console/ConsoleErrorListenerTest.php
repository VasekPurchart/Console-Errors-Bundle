<?php

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleErrorListenerTest extends \PHPUnit\Framework\TestCase
{

	public function testLogError()
	{
		$commandName = 'hello:world';
		$exitCode = 123;

		$logLevel = LogLevel::DEBUG;
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects($this->once())
			->method('log')
			->with($logLevel, $this->logicalAnd(
				$this->stringContains($commandName),
				$this->stringContains((string) $exitCode)
			));

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

		$listener = new ConsoleErrorListener($logger, $logLevel);
		$listener->onConsoleTerminate($event);
	}

	public function testLogErrorExitCodeMax255()
	{
		$commandName = 'hello:world';
		$exitCode = 999;

		$logLevel = LogLevel::DEBUG;
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects($this->once())
			->method('log')
			->with($logLevel, $this->logicalAnd(
				$this->stringContains($commandName),
				$this->stringContains((string) 255)
			));

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

		$listener = new ConsoleErrorListener($logger, $logLevel);
		$listener->onConsoleTerminate($event);
	}

	public function testZeroExitCodeDoesNotLog()
	{
		$commandName = 'hello:world';
		$exitCode = 0;

		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects($this->never())
			->method('log');

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

		$listener = new ConsoleErrorListener($logger, LogLevel::DEBUG);
		$listener->onConsoleTerminate($event);
	}

}
