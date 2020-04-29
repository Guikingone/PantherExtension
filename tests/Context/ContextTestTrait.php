<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Mink\Mink;
use Behat\Mink\Session;
use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
trait ContextTestTrait
{
    private function createMinkContext(InvokedCount $minkInvocationCount): Mink
    {
        $session = $this->createMock(Session::class);

        $mink = $this->createMock(Mink::class);
        $mink->expects($minkInvocationCount)->method('getSession')->willReturn($session);

        $driver = new PantherDriver('chrome', ['webServerDir' => __DIR__]);
        $driver->setSession($session);
        $session->method('getDriver')->willReturn($driver);

        return $mink;
    }
}
