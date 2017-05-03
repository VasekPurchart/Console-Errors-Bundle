<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleErrorListener;

class ConsoleErrorsExtensionErrorsTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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

	public function testErrorsEnabledByDefault()
	{
		$this->load();

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_error_listener', ConsoleErrorListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_error_listener', 'kernel.event_listener', [
			'event' => 'console.terminate',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	public function testErrorsDisabled()
	{
		$this->load([
			'errors' => [
				'enabled' => false,
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.console_errors.console.console_error_listener');

		$this->compile();
	}

	public function testErrorsEnabled()
	{
		$this->load([
			'errors' => [
				'enabled' => true,
			],
		]);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_error_listener', ConsoleErrorListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_error_listener', 'kernel.event_listener', [
			'event' => 'console.terminate',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	/**
	 * @return mixed[][]
	 */
	public function defaultConfigurationValuesProvider()
	{
		return [
			[
				ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY,
				Configuration::DEFAULT_ERROR_LISTENER_PRIORITY,
			],
			[
				ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LOG_LEVEL,
				Configuration::DEFAULT_ERROR_LOG_LEVEL,
			],
		];
	}

	/**
	 * @dataProvider defaultConfigurationValuesProvider
	 *
	 * @param string $parameterName
	 * @param mixed $parameterValue
	 */
	public function testDefaultConfigurationValues(string $parameterName, $parameterValue)
	{
		$this->load();

		$this->assertContainerBuilderHasParameter($parameterName, $parameterValue);

		$this->compile();
	}

	public function testConfigureListenerPriority()
	{
		$this->load([
			'errors' => [
				'listener_priority' => 123,
			],
		]);

		$this->assertContainerBuilderHasParameter(ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY, 123);

		$this->compile();
	}

	/**
	 * @return mixed[][]
	 */
	public function logLevelProvider(): array
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
	 * @dataProvider logLevelProvider
	 *
	 * @param string|int $inputLogLevel
	 * @param string|int $normalizedValueLogLevel
	 */
	public function testConfigureLogLevel($inputLogLevel, $normalizedValueLogLevel)
	{
		$this->load([
			'errors' => [
				'log_level' => $inputLogLevel,
			],
		]);

		$this->assertContainerBuilderHasParameter(
			ConsoleErrorsExtension::CONTAINER_PARAMETER_ERROR_LOG_LEVEL,
			$normalizedValueLogLevel
		);

		$this->compile();
	}

	/**
	 * @return mixed[][]
	 */
	public function invalidLogLevelProvider(): array
	{
		return [
			['lorem'],
			['LOREM'],
			[100.0],
			[null],
		];
	}

	/**
	 * @dataProvider invalidLogLevelProvider
	 *
	 * @param string|int $inputLogLevel
	 */
	public function testConfigureLogLevelInvalidValues($inputLogLevel)
	{
		$this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

		$this->load([
			'errors' => [
				'log_level' => $inputLogLevel,
			],
		]);
	}

}
