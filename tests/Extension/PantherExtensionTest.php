<?php

declare(strict_types=1);

namespace Tests\Extension;

use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use PantherExtension\Extension\PantherExtension;
use PantherExtension\Extension\PantherFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherExtensionTest extends TestCase
{
    public function testExtensionIsConfigured(): void
    {
        static::assertSame('panther', (new PantherExtension())->getConfigKey());
    }

    public function testDriverCannotBeRegistered(): void
    {
        $minkExtension = $this->createMock(MinkExtension::class);
        $minkExtension->expects(static::never())->method('getConfigKey')->willReturn('mink');
        $extensionManager = new ExtensionManager([]);

        $minkExtension->expects(static::never())->method('registerDriverFactory')
            ->with(static::callback(function ($factory): bool {
                return $factory instanceof PantherFactory;
            }))
        ;

        (new PantherExtension())->initialize($extensionManager);
    }

    public function testDriverIsRegistered(): void
    {
        $minkExtension = $this->createMock(MinkExtension::class);
        $minkExtension->expects(static::once())->method('getConfigKey')->willReturn('mink');
        $extensionManager = new ExtensionManager([$minkExtension]);

        $minkExtension->expects(static::once())->method('registerDriverFactory')
            ->with(static::callback(function ($factory): bool {
                return $factory instanceof PantherFactory;
            }))
        ;

        (new PantherExtension())->initialize($extensionManager);
    }
}
