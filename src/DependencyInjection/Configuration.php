<?php

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	const DEFAULT_ERROR_LISTENER_PRIORITY = 0;
	const DEFAULT_EXCEPTION_LISTENER_PRIORITY = 0;

	const PARAMETER_ERROR_ENABLED = 'enabled';
	const PARAMETER_ERROR_LISTENER_PRIORITY = 'listener_priority';
	const PARAMETER_EXCEPTION_ENABLED = 'enabled';
	const PARAMETER_EXCEPTION_LISTENER_PRIORITY = 'listener_priority';

	const SECTION_ERRORS = 'errors';
	const SECTION_EXCEPTIONS = 'exceptions';

	/** @var string */
	private $rootNode;

	/**
	 * @param string $rootNode
	 */
	public function __construct($rootNode)
	{
		$this->rootNode = $rootNode;
	}

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
	 */
	public function getConfigTreeBuilder()
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

}
