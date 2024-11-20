<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Generator;
use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleExceptionListener;

class ConsoleErrorsExtensionExceptionsTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	/**
	 * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
	 */
	protected function getContainerExtensions(): array
	{
		return [
			new ConsoleErrorsExtension(),
		];
	}

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
	 * @param mixed[][] $configuration
	 */
	public function testExceptionsEnabled(
		array $configuration
	): void
	{
		$this->load($configuration);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exception_listener', ConsoleExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exception_listener', 'kernel.event_listener', [
			'event' => 'console.error',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	public function testExceptionsDisabled(): void
	{
		$this->load([
			'exceptions' => [
				'enabled' => false,
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.console_errors.console.console_exception_listener');

		$this->compile();
	}

	/**
	 * @return mixed[][]|\Generator
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
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			'expectedParameterValue' => Configuration::DEFAULT_EXCEPTION_LISTENER_PRIORITY,
		];

		yield 'default log_level' => [
			'configuration' => [],
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL,
			'expectedParameterValue' => Configuration::DEFAULT_EXCEPTION_LOG_LEVEL,
		];

		yield 'configure listener_priority' => [
			'configuration' => [
				'exceptions' => [
					'listener_priority' => 123,
				],
			],
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			'expectedParameterValue' => 123,
		];

		foreach ($this->logLevelDataProvider() as $caseName => $caseData) {
			yield 'configure log_level - ' . $caseName => [
				'configuration' => [
					'exceptions' => [
						'log_level' => $caseData['inputLogLevel'],
					],
				],
				'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL,
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
		$this->load($configuration);

		$this->assertContainerBuilderHasParameter($parameterName, $expectedParameterValue);

		$this->compile();
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
		$this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

		$this->load([
			'exceptions' => [
				'log_level' => $inputLogLevel,
			],
		]);
	}

}
