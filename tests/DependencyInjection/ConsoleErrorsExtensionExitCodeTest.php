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

	public function testErrorsEnabledByDefault(): void
	{
		$this->load();

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

	public function testErrorsEnabled(): void
	{
		$this->load([
			'exit_code' => [
				'enabled' => true,
			],
		]);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exit_code_listener', ConsoleExitCodeListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exit_code_listener', 'kernel.event_listener', [
			'event' => 'console.terminate',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function defaultConfigurationValuesDataProvider(): Generator
	{
		yield 'listener_priority' => [
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
			'parameterValue' => Configuration::DEFAULT_EXIT_CODE_LISTENER_PRIORITY,
		];
		yield 'log_level' => [
			'parameterName' => ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
			'parameterValue' => Configuration::DEFAULT_EXIT_CODE_LOG_LEVEL,
		];
	}

	/**
	 * @dataProvider defaultConfigurationValuesDataProvider
	 *
	 * @param string $parameterName
	 * @param mixed $parameterValue
	 */
	public function testDefaultConfigurationValues(string $parameterName, $parameterValue): void
	{
		$this->load();

		$this->assertContainerBuilderHasParameter($parameterName, $parameterValue);

		$this->compile();
	}

	public function testConfigureListenerPriority(): void
	{
		$this->load([
			'exit_code' => [
				'listener_priority' => 123,
			],
		]);

		$this->assertContainerBuilderHasParameter(ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY, 123);

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
	 * @dataProvider logLevelDataProvider
	 *
	 * @param string|int $inputLogLevel
	 * @param string|int $normalizedValueLogLevel
	 */
	public function testConfigureLogLevel($inputLogLevel, $normalizedValueLogLevel): void
	{
		$this->load([
			'exit_code' => [
				'log_level' => $inputLogLevel,
			],
		]);

		$this->assertContainerBuilderHasParameter(
			ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
			$normalizedValueLogLevel
		);

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
