<?php

declare(strict_types=1);

namespace Tests\Driver;

use PantherDriver\Driver\Exception\LogicException;
use PantherDriver\Driver\PantherDriver;
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
        new PantherDriver('test');
    }

    public function testDriverCanBeCreated(): void
    {
        static::assertInstanceOf(Client::class, (new PantherDriver('chrome'))->getClient());
    }
}
