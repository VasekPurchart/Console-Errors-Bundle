<?php

namespace VasekPurchart\ConsoleErrorsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ConsoleErrorsExtension extends \Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension
{

	const CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY = 'vasek_purchart.console_errors.error.listener_priority';
	const CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY = 'vasek_purchart.console_errors.exception.listener_priority';

	/**
	 * @param mixed[] $mergedConfig
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function loadInternal(array $mergedConfig, ContainerBuilder $container)
	{
		$container->setParameter(
			self::CONTAINER_PARAMETER_ERROR_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_ERRORS][Configuration::PARAMETER_ERROR_LISTENER_PRIORITY]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_EXCEPTION_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_EXCEPTIONS][Configuration::PARAMETER_EXCEPTION_LISTENER_PRIORITY]
		);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yml');
	}

	/**
	 * @param mixed[] $config
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @return \VasekPurchart\ConsoleErrorsBundle\DependencyInjection\Configuration
	 */
	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration(
			$this->getAlias()
		);
	}

}
