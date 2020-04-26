<?php

declare(strict_types=1);

namespace Tests\Extension;

use PantherExtension\Driver\PantherDriver;
use PantherExtension\Extension\PantherFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherFactoryTest extends TestCase
{
    public function testFactoryIsConfigured(): void
    {
        $factory = new PantherFactory();

        static::assertSame('panther', $factory->getDriverName());
        static::assertTrue($factory->supportsJavascript());
    }

    public function testDefinitionIsBuilt(): void
    {
        $factory = new PantherFactory();
        $definition = $factory->buildDriver(['driver' => 'chrome']);

        static::assertInstanceOf(Definition::class, $definition);
        static::assertSame(PantherDriver::class, $definition->getClass());
    }

    public function testDefinitionIsBuiltWithSelenium(): void
    {
        $factory = new PantherFactory();
        $definition = $factory->buildDriver([
            'driver' => 'chrome',
            'selenium' => [
                'hub_url' => 'http://127.0.0.1:4444/wd/hub',
            ],
        ]);

        static::assertInstanceOf(Definition::class, $definition);
        static::assertSame(PantherDriver::class, $definition->getClass());
    }
}
