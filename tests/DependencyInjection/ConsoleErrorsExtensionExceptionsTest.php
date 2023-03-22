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

	public function testExceptionsEnabledByDefault(): void
	{
		$this->load();

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

	public function testExceptionsEnabled(): void
	{
		$this->load([
			'exceptions' => [
				'enabled' => true,
			],
		]);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exception_listener', ConsoleExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exception_listener', 'kernel.event_listener', [
			'event' => 'console.error',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function defaultConfigurationValuesDataProvider(): Generator
	{
		yield [
			ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			Configuration::DEFAULT_EXCEPTION_LISTENER_PRIORITY,
		];
		yield [
			ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL,
			Configuration::DEFAULT_EXCEPTION_LOG_LEVEL,
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
			'exceptions' => [
				'listener_priority' => 123,
			],
		]);

		$this->assertContainerBuilderHasParameter(ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY, 123);

		$this->compile();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function logLevelDataProvider(): Generator
	{
		yield ['error', 'error'];
		yield ['debug', 'debug'];
		yield ['ERROR', 'error'];
		yield [100, 100];
		yield [999, 999];
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
			'exceptions' => [
				'log_level' => $inputLogLevel,
			],
		]);

		$this->assertContainerBuilderHasParameter(
			ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL,
			$normalizedValueLogLevel
		);

		$this->compile();
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function invalidLogLevelDataProvider(): Generator
	{
		yield ['lorem'];
		yield ['LOREM'];
		yield [100.0];
		yield [null];
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
