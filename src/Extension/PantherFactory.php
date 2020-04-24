<?php

declare(strict_types=1);

namespace PantherDriver\Extension;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use PantherDriver\Driver\PantherDriver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherFactory implements DriverFactory
{
    private const DRIVER_NAME = 'panther';

    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return self::DRIVER_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsJavascript()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('driver')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        return new Definition(PantherDriver::class, [
            $config['driver'],
        ]);
    }
}
