<?php

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class ConsoleErrorListener
{

	/** @var \Psr\Log\LoggerInterface */
	private $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function onConsoleTerminate(ConsoleTerminateEvent $event)
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

		$message = 'Command `%s` exited with status code %d';
		$level = LogLevel::ERROR;
		if (ConsoleInternalApplicationRun::isInternalApplicationRun($event->getCommand()->getApplication())) {
			$level = LogLevel::DEBUG;
			$message = '[Application run internally] ' . $message;
		}

		$this->logger->log($level, sprintf(
			$message,
			$command->getName(),
			$statusCode
		));
	}

}
