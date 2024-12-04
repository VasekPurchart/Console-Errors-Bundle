<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	public const DEFAULT_EXIT_CODE_LISTENER_PRIORITY = 0;
	public const DEFAULT_EXIT_CODE_LOG_LEVEL = LogLevel::ERROR;
	public const DEFAULT_EXCEPTION_LISTENER_PRIORITY = 0;
	public const DEFAULT_EXCEPTION_LOG_LEVEL = LogLevel::ERROR;

	public const PARAMETER_EXIT_CODE_ENABLED = 'enabled';
	public const PARAMETER_EXIT_CODE_LISTENER_PRIORITY = 'listener_priority';
	public const PARAMETER_EXIT_CODE_LOG_LEVEL = 'log_level';
	public const PARAMETER_EXCEPTION_ENABLED = 'enabled';
	public const PARAMETER_EXCEPTION_LISTENER_PRIORITY = 'listener_priority';
	public const PARAMETER_EXCEPTION_LOG_LEVEL = 'log_level';

	public const SECTION_EXIT_CODE = 'exit_code';
	public const SECTION_EXCEPTIONS = 'exceptions';

	/** @var string */
	private $rootNode;

	public function __construct(string $rootNode)
	{
		$this->rootNode = $rootNode;
	}

	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder($this->rootNode);
		$rootNode = $treeBuilder->getRootNode();

		$rootNode->children()->append($this->createExceptionsNode(self::SECTION_EXCEPTIONS));
		$rootNode->children()->append($this->createExitCodeNode(self::SECTION_EXIT_CODE));

		return $treeBuilder;
	}

	private function createExceptionsNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->addDefaultsIfNotSet();
		$node->children()->append($this->createEnabledNode(
			self::PARAMETER_EXCEPTION_ENABLED,
			'Enable logging for exceptions.'
		));
		$node->children()->append($this->createLogLevelNode(
			self::PARAMETER_EXCEPTION_LOG_LEVEL,
			'Log level with which exceptions should be logged (accepts string or integer values).',
			self::DEFAULT_EXCEPTION_LOG_LEVEL
		));
		$node->children()->append($this->createListenerPriorityNode(
			self::PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			self::DEFAULT_EXCEPTION_LISTENER_PRIORITY
		));

		return $node;
	}

	private function createExitCodeNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->addDefaultsIfNotSet();
		$node->children()->append($this->createEnabledNode(
			self::PARAMETER_EXIT_CODE_ENABLED,
			'Enable logging for non-zero exit codes.'
		));
		$node->children()->append($this->createLogLevelNode(
			self::PARAMETER_EXIT_CODE_LOG_LEVEL,
			'Log level with which exit codes should be logged (accepts string or integer values).',
			self::DEFAULT_EXIT_CODE_LOG_LEVEL
		));
		$node->children()->append($this->createListenerPriorityNode(
			self::PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
			self::DEFAULT_EXIT_CODE_LISTENER_PRIORITY
		));

		return $node;
	}

	private function createEnabledNode(string $nodeName, string $info): ScalarNodeDefinition
	{
		$node = new ScalarNodeDefinition($nodeName);
		$node->info($info);
		$node->defaultTrue();

		return $node;
	}

	/**
	 * @param string $nodeName
	 * @param string $info
	 * @param string|int $defaultValue
	 * @return \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
	 */
	private function createLogLevelNode(string $nodeName, string $info, $defaultValue): ScalarNodeDefinition
	{
		$node = new ScalarNodeDefinition($nodeName);
		$node->info($info);
		$node->defaultValue($defaultValue);
		$node->beforeNormalization()
			->ifString()
			->then(function (string $value): string {
				return strtolower($value);
			});
		$node->validate()
			->ifTrue(function ($value): bool {
				switch (true) {
					case is_int($value):
						return false;
					case is_string($value) && defined(LogLevel::class . '::' . strtoupper($value)):
						return false;
					default:
						return true;
				}
			})
			->thenInvalid(sprintf(
				'Invalid log level value "%%s". Must be either value from %s or an integer.',
				LogLevel::class
			));

		return $node;
	}

	private function createListenerPriorityNode(string $nodeName, int $defaultValue): IntegerNodeDefinition
	{
		$node = new IntegerNodeDefinition($nodeName);
		$node->info('Priority with which the listener will be registered.');
		$node->defaultValue($defaultValue);

		return $node;
	}

}
