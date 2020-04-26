<?php

declare(strict_types=1);

namespace PantherExtension\Extension;

use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherExtension implements Extension
{
    private const CONFIGURATION_KEY = 'panther';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::CONFIGURATION_KEY;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        $minkExtension = $extensionManager->getExtension('mink');

        if (!$minkExtension instanceof MinkExtension) {
            return;
        }

        $minkExtension->registerDriverFactory(new PantherFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
    }
}
