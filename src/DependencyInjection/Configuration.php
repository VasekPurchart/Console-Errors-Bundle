<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	public const DEFAULT_ERROR_LISTENER_PRIORITY = 0;
	public const DEFAULT_ERROR_LOG_LEVEL = LogLevel::ERROR;
	public const DEFAULT_EXCEPTION_LISTENER_PRIORITY = 0;
	public const DEFAULT_EXCEPTION_LOG_LEVEL = LogLevel::ERROR;

	public const PARAMETER_ERROR_ENABLED = 'enabled';
	public const PARAMETER_ERROR_LISTENER_PRIORITY = 'listener_priority';
	public const PARAMETER_ERROR_LOG_LEVEL = 'log_level';
	public const PARAMETER_EXCEPTION_ENABLED = 'enabled';
	public const PARAMETER_EXCEPTION_LISTENER_PRIORITY = 'listener_priority';
	public const PARAMETER_EXCEPTION_LOG_LEVEL = 'log_level';

	public const SECTION_ERRORS = 'errors';
	public const SECTION_EXCEPTIONS = 'exceptions';

	/** @var string */
	private $rootNode;

	public function __construct(string $rootNode)
	{
		$this->rootNode = $rootNode;
	}

	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root($this->rootNode);

		$rootNode
			->children()
				->arrayNode(self::SECTION_EXCEPTIONS)
					->addDefaultsIfNotSet()
					->children()
						->scalarNode(self::PARAMETER_EXCEPTION_ENABLED)
							->info('Enable logging for exceptions.')
							->defaultTrue()
							->end()
						->append($this->createLogLevelNode(
							self::PARAMETER_EXCEPTION_LOG_LEVEL,
							'Log level with which exceptions should be logged (accepts string or integer values).',
							self::DEFAULT_EXCEPTION_LOG_LEVEL
						))
						->integerNode(self::PARAMETER_EXCEPTION_LISTENER_PRIORITY)
							->info('Priority with which the listener will be registered.')
							->defaultValue(self::DEFAULT_EXCEPTION_LISTENER_PRIORITY)
							->end()
						->end()
					->end()
				->arrayNode(self::SECTION_ERRORS)
					->addDefaultsIfNotSet()
					->children()
						->scalarNode(self::PARAMETER_ERROR_ENABLED)
							->info('Enable logging for errors (non zero exit codes).')
							->defaultTrue()
							->end()
						->append($this->createLogLevelNode(
							self::PARAMETER_ERROR_LOG_LEVEL,
							'Log level with which errors should be logged (accepts string or integer values).',
							self::DEFAULT_ERROR_LOG_LEVEL
						))
						->integerNode(self::PARAMETER_ERROR_LISTENER_PRIORITY)
							->info('Priority with which the listener will be registered.')
							->defaultValue(self::DEFAULT_ERROR_LISTENER_PRIORITY)
							->end()
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

	/**
	 * @param string $parameterName
	 * @param string $parameterInfo
	 * @param string|int $defaultValue
	 * @return \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
	 */
	private function createLogLevelNode(string $parameterName, string $parameterInfo, $defaultValue): ScalarNodeDefinition
	{
		$logLevelNode = new ScalarNodeDefinition($parameterName);
		$logLevelNode
			->info($parameterInfo)
			->defaultValue($defaultValue)
			->beforeNormalization()
				->ifString()
				->then(function (string $value): string {
					return strtolower($value);
				})
				->end()
			->validate()
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
				))
				->end()
			->end();

		return $logLevelNode;
	}

}
