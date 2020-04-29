<?php

declare(strict_types=1);

namespace Tests\Driver;

use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherDriverTest extends TestCase
{
    public function testDriverCannotBeCreated(): void
    {
        static::expectException(LogicException::class);
        new PantherDriver('test', ['webServerDir' => __DIR__]);
    }

    public function testDriverCanBeCreated(): void
    {
        static::assertInstanceOf(Client::class, (new PantherDriver('chrome', ['webServerDir' => __DIR__]))->getClient());
    }
}
