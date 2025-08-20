<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleExceptionListener;

class ConsoleErrorsExtensionExceptionsTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @return mixed[][]|\Generator
	 */
	public function exceptionsEnabledDataProvider(): Generator
	{
		yield 'exceptions enabled by default' => [
			'configuration' => [],
		];

		yield 'exceptions enabled by configuration' => [
			'configuration' => [
				'exceptions' => [
					'enabled' => true,
				],
			],
		];
	}

	/**
	 * @dataProvider exceptionsEnabledDataProvider
	 *
	 * @param mixed[][]|array $configuration
	 */
	public function testExceptionsEnabled(
		array $configuration
	): void
	{
		$container = ConsoleErrorsExtensionExitCodeTest::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		ConsoleErrorsExtensionExitCodeTest::loadRegisteredExtension($container, $configuration);

		$serviceId = 'vasek_purchart.console_errors.console.console_exception_listener';
		ConsoleErrorsExtensionExitCodeTest::assertContainerHasService($container, $serviceId);
		ConsoleErrorsExtensionExitCodeTest::assertContainerServiceIsOfType(
			$container,
			$serviceId,
			ConsoleExceptionListener::class
		);
		ConsoleErrorsExtensionExitCodeTest::assertContainerServiceHasTagWithAttributes(
			$container,
			$serviceId,
			'kernel.event_listener',
			[
				'event' => 'console.error',
				'priority' => '%vasek_purchart.console_errors.exception.listener_priority%',
			]
		);

		$container->compile();
	}

	public function testExceptionsDisabled(): void
	{
		$container = ConsoleErrorsExtensionExitCodeTest::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		ConsoleErrorsExtensionExitCodeTest::loadRegisteredExtension($container, [
			'exceptions' => [
				'enabled' => false,
			],
		]);

		ConsoleErrorsExtensionExitCodeTest::assertContainerDoesNotHaveService(
			$container,
			'vasek_purchart.console_errors.console.console_exception_listener'
		);

		$container->compile();
	}

	/**
	 * @return mixed[]|\Generator
	 */
	public function logLevelDataProvider(): Generator
	{
		yield 'lowercase error (as PSR-3 uses)' => [
			'inputLogLevel' => 'error',
			'normalizedValueLogLevel' => 'error',
		];
		yield 'lowercase debug (as PSR-3 uses)' => [
			'inputLogLevel' => 'debug',
			'normalizedValueLogLevel' => 'debug',
		];
		yield 'uppercase error (as Monolog uses)' => [
			'inputLogLevel' => 'ERROR',
			'normalizedValueLogLevel' => 'error',
		];
		yield 'integer based on Monolog value' => [
			'inputLogLevel' => 100,
			'normalizedValueLogLevel' => 100,
		];
		yield 'arbitrary integer' => [
			'inputLogLevel' => 999,
			'normalizedValueLogLevel' => 999,
		];
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function configureContainerParameterDataProvider(): Generator
	{
		yield 'default listener_priority' => [
			'configuration' => [],
			'parameterName' => 'vasek_purchart.console_errors.exception.listener_priority',
			'expectedParameterValue' => Configuration::DEFAULT_EXCEPTION_LISTENER_PRIORITY,
		];

		yield 'default log_level' => [
			'configuration' => [],
			'parameterName' => 'vasek_purchart.console_errors.exception.log_level',
			'expectedParameterValue' => Configuration::DEFAULT_EXCEPTION_LOG_LEVEL,
		];

		yield 'configure listener_priority' => [
			'configuration' => [
				'exceptions' => [
					'listener_priority' => 123,
				],
			],
			'parameterName' => 'vasek_purchart.console_errors.exception.listener_priority',
			'expectedParameterValue' => 123,
		];

		foreach ($this->logLevelDataProvider() as $caseName => $caseData) {
			yield 'configure log_level - ' . $caseName => [
				'configuration' => [
					'exceptions' => [
						'log_level' => $caseData['inputLogLevel'],
					],
				],
				'parameterName' => 'vasek_purchart.console_errors.exception.log_level',
				'expectedParameterValue' => $caseData['normalizedValueLogLevel'],
			];
		}
	}

	/**
	 * @dataProvider configureContainerParameterDataProvider
	 *
	 * @param mixed[][] $configuration
	 * @param string $parameterName
	 * @param mixed $expectedParameterValue
	 */
	public function testConfigureContainerParameter(
		array $configuration,
		string $parameterName,
		$expectedParameterValue
	): void
	{
		$container = ConsoleErrorsExtensionExitCodeTest::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		ConsoleErrorsExtensionExitCodeTest::loadRegisteredExtension($container, $configuration);

		ConsoleErrorsExtensionExitCodeTest::assertContainerHasParameter($container, $parameterName);
		Assert::assertSame($expectedParameterValue, $container->getParameter($parameterName));

		$container->compile();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function invalidLogLevelDataProvider(): Generator
	{
		yield 'nonexistent log level as lowercase string' => [
			'inputLogLevel' => 'lorem',
		];
		yield 'nonexistent log level as uppercase string' => [
			'inputLogLevel' => 'LOREM',
		];
		yield 'float value' => [
			'inputLogLevel' => 100.0,
		];
		yield 'null value' => [
			'inputLogLevel' => null,
		];
	}

	/**
	 * @dataProvider invalidLogLevelDataProvider
	 *
	 * @param string|int $inputLogLevel
	 */
	public function testConfigureLogLevelInvalidValues($inputLogLevel): void
	{
		$container = ConsoleErrorsExtensionExitCodeTest::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		$this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

		ConsoleErrorsExtensionExitCodeTest::loadRegisteredExtension($container, [
			'exceptions' => [
				'log_level' => $inputLogLevel,
			],
		]);
	}

}
