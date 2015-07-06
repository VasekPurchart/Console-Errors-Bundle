<?php

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleErrorListener;

class ConsoleErrorsExtensionErrorsTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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

}
