<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

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
	 * @return mixed[][]
	 */
	public function defaultConfigurationValuesDataProvider(): array
	{
		return [
			[
				ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
				Configuration::DEFAULT_EXIT_CODE_LISTENER_PRIORITY,
			],
			[
				ConsoleErrorsExtension::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
				Configuration::DEFAULT_EXIT_CODE_LOG_LEVEL,
			],
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
	 * @return mixed[][]
	 */
	public function logLevelDataProvider(): array
	{
		return [
			['error', 'error'],
			['debug', 'debug'],
			['ERROR', 'error'],
			[100, 100],
			[999, 999],
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
	 * @return mixed[][]
	 */
	public function invalidLogLevelDataProvider(): array
	{
		return [
			['lorem'],
			['LOREM'],
			[100.0],
			[null],
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
