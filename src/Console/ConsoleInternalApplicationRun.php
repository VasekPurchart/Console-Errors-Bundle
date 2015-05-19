<?php

namespace VasekPurchart\ConsoleErrorsBundle\Console;

use ReflectionClass;

use Symfony\Component\Console\Application;

class ConsoleInternalApplicationRun
{

	/**
	 * @see https://github.com/symfony/symfony/pull/14288 there is currently no proper way to reason about the Application state
	 *
	 * @param \Symfony\Component\Console\Application $application
	 * @return boolean
	 */
	public static function isInternalApplicationRun(Application $application)
	{
		$applicationReflection = new ReflectionClass($application);
		while ($applicationReflection->getName() !== Application::class) {
			$applicationReflection = $applicationReflection->getParentClass();
		}
		$autoExitProperty = $applicationReflection->getProperty('autoExit');
		$autoExitProperty->setAccessible(true);
		$catchExceptionsProperty = $applicationReflection->getProperty('catchExceptions');
		$catchExceptionsProperty->setAccessible(true);

		return $autoExitProperty->getValue($application) === false
			&& $catchExceptionsProperty->getValue($application) === false;
	}

}
