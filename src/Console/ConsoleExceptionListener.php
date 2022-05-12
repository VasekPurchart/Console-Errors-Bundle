<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleErrorEvent;

class ConsoleExceptionListener
{

	/** @var \Psr\Log\LoggerInterface */
	private $logger;

	/** @var string|int */
	private $logLevel;

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param string|int $logLevel
	 */
	public function __construct(
		LoggerInterface $logger,
		$logLevel
	)
	{
		$this->logger = $logger;
		$this->logLevel = $logLevel;
	}

	/**
	 * Keeping this method for BC (of this package) - was renamed to onConsoleError() as part of a bugfix
	 *
	 * @param \Symfony\Component\Console\Event\ConsoleErrorEvent $event
	 */
	public function onConsoleException(ConsoleErrorEvent $event): void
	{
		$this->onConsoleError($event);
	}

	public function onConsoleError(ConsoleErrorEvent $event): void
	{
		$command = $event->getCommand();
		$exception = $event->getError();

		$message = sprintf(
			'%s: %s (uncaught exception) at %s line %s while running console command `%s`',
			get_class($exception),
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine(),
			$command->getName()
		);

		$this->logger->log($this->logLevel, $message, [
			'exception' => $exception,
		]);
	}

}
