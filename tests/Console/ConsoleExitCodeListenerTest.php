<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Generator;
use PHPUnit\Framework\Assert;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleExitCodeListenerTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @return int[][]|\Generator
	 */
	public function logErrorDataProvider(): Generator
	{
		yield 'exit code lower than 255' => [
			'exitCode' => 123,
			'expectedExitCode' => 123,
		];

		yield 'max exit code is 255' => [
			'exitCode' => 999,
			'expectedExitCode' => 255,
		];
	}

	/**
	 * @dataProvider logErrorDataProvider
	 *
	 * @param int $exitCode
	 * @param int $expectedExitCode
	 */
	public function testLogError(
		int $exitCode,
		int $expectedExitCode
	): void
	{
		$commandName = 'hello:world';

		$logLevel = LogLevel::DEBUG;
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::once())
			->method('log')
			->with($logLevel, Assert::logicalAnd(
				Assert::stringContains($commandName),
				Assert::stringContains((string) $expectedExitCode)
			));

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

		$listener = new ConsoleExitCodeListener($logger, $logLevel);
		$listener->onConsoleTerminate($event);
	}

	public function testZeroExitCodeDoesNotLog(): void
	{
		$commandName = 'hello:world';
		$exitCode = 0;

		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects(self::never())
			->method('log');

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);

		$listener = new ConsoleExitCodeListener($logger, LogLevel::DEBUG);
		$listener->onConsoleTerminate($event);
	}

}
