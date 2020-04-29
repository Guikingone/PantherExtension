<?php

declare(strict_types=1);

namespace PantherExtension\Extension;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use PantherExtension\Driver\PantherDriver;
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
                    ->defaultValue('chrome')
                ->end()
                ->arrayNode('selenium')
                    ->children()
                        ->scalarNode('hub_url')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('screenshots_path')
                    ->defaultValue('')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        if (!\array_key_exists('selenium', $config)) {
            $config['selenium'] = ['hub_url' => null];
        }

        return new Definition(PantherDriver::class, [
            $config['driver'],
            ['config' => [
                'selenium' => $config['selenium'],
            ]],
        ]);
    }
}
