<?php

declare(strict_types=1);

namespace Tests\Driver\Helper;

use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class CookieHelperTest extends TestCase
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

    public function testCookieCanBeSet(): void
    {
        $this->driver->start();
        $this->driver->getClient()->getCookieJar()->clear();
        $this->driver->visit('/cookie.php');
        $this->driver->setCookie('cookie', 'randomNewValue');

        static::assertSame('randomNewValue', $this->driver->getCookie('cookie'));
    }

    public function testCookieCanBeAccessed(): void
    {
        $this->driver->start();
        $this->driver->getClient()->getCookieJar()->clear();
        $this->driver->visit('/cookie.php');
        $cookie = $this->driver->getSpecificCookie('barcelona', '/cookie.php', '127.0.0.1');

        static::assertNotNull($cookie);
    }
}
