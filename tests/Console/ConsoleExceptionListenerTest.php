<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Closure;
use Generator;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleExceptionListenerTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @return mixed[]|\Generator
	 */
	public function eventMethodProvider(): Generator
	{
		yield 'onConsoleError' => [
			'methodCallback' => static function (ConsoleExceptionListener $listener, ConsoleErrorEvent $event): void {
				$listener->onConsoleError($event);
			},
		];

		yield 'onConsoleException (for BC)' => [
			'methodCallback' => static function (ConsoleExceptionListener $listener, ConsoleErrorEvent $event): void {
				$listener->onConsoleException($event);
			},
		];
	}

	/**
	 * @dataProvider eventMethodProvider
	 *
	 * @param \Closure $methodCallback
	 */
	public function testLogError(Closure $methodCallback): void
	{
		$commandName = 'hello:world';
		$message = 'Foobar!';
		$exception = new \Exception($message);

		$logLevel = LogLevel::DEBUG;
		$logger = $this->createMock(LoggerInterface::class);
		$logger
			->expects($this->once())
			->method('log')
			->with($logLevel, $this->logicalAnd(
				$this->stringContains($commandName),
				$this->stringContains($message)
			), $this->contains($exception, true));

		$command = new Command($commandName);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$event = new ConsoleErrorEvent($input, $output, $exception, $command);

		$methodCallback(
			new ConsoleExceptionListener($logger, $logLevel),
			$event
		);
	}

}
