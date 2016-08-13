<?php

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleExceptionListener;

class ConsoleErrorsExtensionExceptionsTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	/**
	 * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
	 */
	protected function getContainerExtensions()
	{
		return [
			new ConsoleErrorsExtension(),
		];
	}

	public function testExceptionsEnabledByDefault()
	{
		$this->load();

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exception_listener', ConsoleExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exception_listener', 'kernel.event_listener', [
			'event' => 'console.exception',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	public function testExceptionsDisabled()
	{
		$this->load([
			'exceptions' => [
				'enabled' => false,
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.console_errors.console.console_exception_listener');

		$this->compile();
	}

	public function testExceptionsEnabled()
	{
		$this->load([
			'exceptions' => [
				'enabled' => true,
			],
		]);

		$this->assertContainerBuilderHasService('vasek_purchart.console_errors.console.console_exception_listener', ConsoleExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.console_errors.console.console_exception_listener', 'kernel.event_listener', [
			'event' => 'console.exception',
			'priority' => '%' . ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY . '%',
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
				ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
				Configuration::DEFAULT_EXCEPTION_LISTENER_PRIORITY,
			],
		];
	}

	/**
	 * @dataProvider defaultConfigurationValuesProvider
	 *
	 * @param string $parameterName
	 * @param mixed $parameterValue
	 */
	public function testDefaultConfigurationValues($parameterName, $parameterValue)
	{
		$this->load();

		$this->assertContainerBuilderHasParameter($parameterName, $parameterValue);

		$this->compile();
	}

	public function testConfigureListenerPriority()
	{
		$this->load([
			'exceptions' => [
				'listener_priority' => 123,
			],
		]);

		$this->assertContainerBuilderHasParameter(ConsoleErrorsExtension::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY, 123);

		$this->compile();
	}

}
