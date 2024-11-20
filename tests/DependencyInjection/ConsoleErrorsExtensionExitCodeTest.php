<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Generator;
use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleExitCodeListener;

class ConsoleErrorsExtensionExitCodeTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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
	public function errorsEnabledDataProvider(): Generator
	{
		yield 'errors enabled by default' => [
			'configuration' => [],
		];

		yield 'errors enabled by configuration' => [
			'configuration' => [
				'exit_code' => [
					'enabled' => true,
				],
			],
		];
	}

	/**
	 * @dataProvider errorsEnabledDataProvider
	 *
	 * @param mixed[][] $configuration
	 */
	public function testErrorsEnabled(
		array $configuration
	): void
	{
		$this->load($configuration);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exit_code_listener', ConsoleExitCodeListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exit_code_listener', 'kernel.event_listener', [
			'event' => 'console.terminate',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	public function testErrorsDisabled(): void
	{
		$this->load([
			'exit_code' => [
				'enabled' => false,
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.console_errors.console.console_exit_code_listener');

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
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
			'expectedParameterValue' => Configuration::DEFAULT_EXIT_CODE_LISTENER_PRIORITY,
		];

		yield 'default log_level' => [
			'configuration' => [],
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
			'expectedParameterValue' => Configuration::DEFAULT_EXIT_CODE_LOG_LEVEL,
		];

		yield 'configure listener_priority' => [
			'configuration' => [
				'exit_code' => [
					'listener_priority' => 123,
				],
			],
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
			'expectedParameterValue' => 123,
		];

		foreach ($this->logLevelDataProvider() as $caseName => $caseData) {
			yield 'configure log_level - ' . $caseName => [
				'configuration' => [
					'exit_code' => [
						'log_level' => $caseData['inputLogLevel'],
					],
				],
				'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
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
			'exit_code' => [
				'log_level' => $inputLogLevel,
			],
		]);
	}

}
