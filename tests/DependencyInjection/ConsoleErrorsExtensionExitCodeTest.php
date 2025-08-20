<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use VasekPurchart\ConsoleErrorsBundle\Console\ConsoleExitCodeListener;

class ConsoleErrorsExtensionExitCodeTest extends \PHPUnit\Framework\TestCase
{

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
		$container = self::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		self::loadRegisteredExtension($container, $configuration);

		$serviceId = 'vasek_purchart.console_errors.console.console_exit_code_listener';
		self::assertContainerHasService($container, $serviceId);
		self::assertContainerServiceIsOfType($container, $serviceId, ConsoleExitCodeListener::class);
		self::assertContainerServiceHasTagWithAttributes(
			$container,
			$serviceId,
			'kernel.event_listener',
			[
				'event' => 'console.terminate',
				'priority' => '%vasek_purchart.console_errors.error.listener_priority%',
			]
		);

		$container->compile();
	}

	public function testErrorsDisabled(): void
	{
		$container = self::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		self::loadRegisteredExtension($container, [
			'exit_code' => [
				'enabled' => false,
			],
		]);

		self::assertContainerDoesNotHaveService($container, 'vasek_purchart.console_errors.console_exit_code_listener');

		$container->compile();
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
			'parameterName' => 'vasek_purchart.console_errors.error.listener_priority',
			'expectedParameterValue' => Configuration::DEFAULT_EXIT_CODE_LISTENER_PRIORITY,
		];

		yield 'default log_level' => [
			'configuration' => [],
			'parameterName' => 'vasek_purchart.console_errors.error.log_level',
			'expectedParameterValue' => Configuration::DEFAULT_EXIT_CODE_LOG_LEVEL,
		];

		yield 'configure listener_priority' => [
			'configuration' => [
				'exit_code' => [
					'listener_priority' => 123,
				],
			],
			'parameterName' => 'vasek_purchart.console_errors.error.listener_priority',
			'expectedParameterValue' => 123,
		];

		foreach ($this->logLevelDataProvider() as $caseName => $caseData) {
			yield 'configure log_level - ' . $caseName => [
				'configuration' => [
					'exit_code' => [
						'log_level' => $caseData['inputLogLevel'],
					],
				],
				'parameterName' => 'vasek_purchart.console_errors.error.log_level',
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
		$container = self::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		self::loadRegisteredExtension($container, $configuration);

		self::assertContainerHasParameter($container, $parameterName);
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
		$container = self::createContainer();
		$container->registerExtension(new ConsoleErrorsExtension());

		$this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

		self::loadRegisteredExtension($container, [
			'exit_code' => [
				'log_level' => $inputLogLevel,
			],
		]);
	}

	public static function createContainer(): ContainerBuilder
	{
		$container = new ContainerBuilder(new ParameterBag([]));
		$container->getCompilerPassConfig()->setOptimizationPasses([]);
		$container->getCompilerPassConfig()->setRemovingPasses([]);
		$container->getCompilerPassConfig()->setAfterRemovingPasses([]);

		return $container;
	}

	public static function assertContainerHasService(
		ContainerBuilder $container,
		string $serviceId
	): void
	{
		Assert::assertTrue(
			$container->has($serviceId),
			sprintf('Expecting the container to have service `%s`.', $serviceId)
		);
	}

	public static function assertContainerDoesNotHaveService(
		ContainerBuilder $container,
		string $serviceId
	): void
	{
		Assert::assertFalse(
			$container->has($serviceId),
			sprintf('Expecting the container to not have service `%s`.', $serviceId)
		);
	}

	public static function assertContainerServiceIsOfType(
		ContainerBuilder $container,
		string $serviceId,
		string $expectedClassString
	): void
	{
		$serviceDefinition = $container->findDefinition($serviceId);

		Assert::assertSame(
			$expectedClassString,
			$container->getParameterBag()->resolveValue($serviceDefinition->getClass()),
			sprintf('Expecting the service `%s` to be of type `%s`.', $serviceId, $expectedClassString)
		);
	}

	public static function assertContainerHasParameter(
		ContainerBuilder $container,
		string $parameterName
	): void
	{
		Assert::assertTrue(
			$container->hasParameter($parameterName),
			sprintf('Expecting the container to have parameter `%s`.', $parameterName)
		);
	}

	public static function assertContainerServiceHasTag(
		ContainerBuilder $container,
		string $serviceId,
		string $tagName
	): void
	{
		$serviceDefinition = $container->findDefinition($serviceId);

		Assert::assertTrue(
			$serviceDefinition->hasTag($tagName),
			sprintf('Expecting the service `%s` to have tag `%s`.', $serviceId, $tagName)
		);
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param string $serviceId
	 * @param string $tagName
	 * @param mixed[]|array $expectedTagAttributes
	 */
	public static function assertContainerServiceHasTagWithAttributes(
		ContainerBuilder $container,
		string $serviceId,
		string $tagName,
		array $expectedTagAttributes
	): void
	{
		self::assertContainerServiceHasTag($container, $serviceId, $tagName);

		$serviceDefinition = $container->findDefinition($serviceId);
		$allTagAttributes = $serviceDefinition->getTag($tagName);

		if (count($allTagAttributes) === 0) {
			Assert::fail(sprintf('Expecting the tag `%s` to have attributes.', $tagName));
		}

		foreach ($allTagAttributes as $tagAttributes) {
			Assert::assertSame(
				$expectedTagAttributes,
				$tagAttributes,
				sprintf('Expecting the tag `%s` to have expected attributes.', $serviceId)
			);
		}
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param mixed[][]|array $configuration
	 */
	public static function loadRegisteredExtension(
		ContainerBuilder $container,
		array $configuration = []
	): void
	{
		$registeredExtensionsCount = count($container->getExtensions());

		assert(
			$registeredExtensionsCount === 1,
			sprintf('There are %d extensions registered but one is expected', $registeredExtensionsCount)
		);

		self::loadRegisteredExtensionsUsingCommonConfiguration($container, $configuration);
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param mixed[][]|array $configuration
	 */
	private static function loadRegisteredExtensionsUsingCommonConfiguration(
		ContainerBuilder $container,
		array $configuration = []
	): void
	{
		foreach ($container->getExtensions() as $extension) {
			if ($extension instanceof PrependExtensionInterface) {
				$extension->prepend($container);
			}
		}

		foreach ($container->getExtensions() as $extension) {
			$extension->load([$configuration], $container);
		}
	}

}
