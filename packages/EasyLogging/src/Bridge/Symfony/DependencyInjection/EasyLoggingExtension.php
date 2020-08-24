<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\DependencyInjection;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyLoggingExtension extends Extension
{
    /**
     * @var string[]
     */
    protected static $autoConfigs = [
        HandlerConfigProviderInterface::class => BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER,
        LoggerConfiguratorInterface::class => BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR,
        ProcessorConfigProviderInterface::class => BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter(BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL, $config['default_channel'] ?? null);

        foreach (static::$autoConfigs as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)->addTag($tag);
        }
    }
}