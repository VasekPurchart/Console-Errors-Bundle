<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class ConsoleExitCodeListener
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

	public function onConsoleTerminate(ConsoleTerminateEvent $event): void
	{
		$statusCode = $event->getExitCode();
		$command = $event->getCommand();

		if ($statusCode === 0) {
			return;
		}

		if ($statusCode > 255) {
			$statusCode = 255;
			$event->setExitCode($statusCode);
		}

		$this->logger->log($this->logLevel, sprintf(
			'Command `%s` exited with status code %d',
			$command->getName(),
			$statusCode
		));
	}

}
