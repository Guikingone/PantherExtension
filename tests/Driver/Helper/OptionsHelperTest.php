<?php

declare(strict_types=1);

namespace Tests\Driver\Helper;

use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class OptionsHelperTest extends TestCase
{
    /**
     * @var PantherDriver
     */
    private $driver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->driver = new PantherDriver('chrome', ['webServerDir' => __DIR__.'/../../fixtures']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->driver->stop();
    }

    public function testWindowCanBeResized(): void
    {
        $this->driver->start();
        $this->driver->resizeWindow(100, 100);

        $currentDimensions = $this->driver->getClient()->manage()->window()->getSize();

        static::assertSame(100, $currentDimensions->getWidth());
        static::assertSame(100, $currentDimensions->getHeight());
    }
}
