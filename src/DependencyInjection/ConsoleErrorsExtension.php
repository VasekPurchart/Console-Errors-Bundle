<?php

declare(strict_types = 1);

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConsoleErrorsExtension extends \Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension
{

	public const CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY = 'vasek_purchart.console_errors.error.listener_priority';
	public const CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL = 'vasek_purchart.console_errors.error.log_level';
	public const CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY = 'vasek_purchart.console_errors.exception.listener_priority';
	public const CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL = 'vasek_purchart.console_errors.exception.log_level';

	/**
	 * @param mixed[] $mergedConfig
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
	{
		$container->setParameter(
			self::CONTAINER_PARAMETER_EXIT_CODE_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_EXIT_CODE][Configuration::PARAMETER_EXIT_CODE_LISTENER_PRIORITY]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_EXIT_CODE_LOG_LEVEL,
			$mergedConfig[Configuration::SECTION_EXIT_CODE][Configuration::PARAMETER_EXIT_CODE_LOG_LEVEL]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_EXCEPTIONS][Configuration::PARAMETER_EXCEPTION_LISTENER_PRIORITY]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_EXCEPTION_LOG_LEVEL,
			$mergedConfig[Configuration::SECTION_EXCEPTIONS][Configuration::PARAMETER_EXCEPTION_LOG_LEVEL]
		);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yaml');
		if ($mergedConfig[Configuration::SECTION_EXCEPTIONS][Configuration::PARAMETER_EXCEPTION_ENABLED]) {
			$loader->load('exception_listener.yaml');
		}
		if ($mergedConfig[Configuration::SECTION_EXIT_CODE][Configuration::PARAMETER_EXIT_CODE_ENABLED]) {
			$loader->load('exit_code_listener.yaml');
		}
	}

	/**
	 * @param mixed[] $config
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @return \VasekPurchart\ConsoleErrorsBundle\DependencyInjection\Configuration
	 */
	public function getConfiguration(array $config, ContainerBuilder $container): Configuration
	{
		return new Configuration(
			$this->getAlias()
		);
	}

}
