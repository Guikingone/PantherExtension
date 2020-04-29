<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use PantherExtension\Context\PantherContext;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherContextTest extends TestCase
{
    public function testWaitCannotBeCalledWithoutSession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iWaitForElement('@userList');
    }

    public function testWaitDuringCannotBeCalledWithoutSession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iWaitForElementDuring('@userList', 10);
    }

    public function testWaitDuringEveryCannotBeCalledWithoutSession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iWaitForElementDuringEvery('@userList', 10, 150);
    }

    public function testANewClientCannotBeCreatedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iCreateANewClient('test', 'chrome');
    }

    public function testANewClientCannotBeCreatedWithReservedName(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new PantherContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iCreateANewClient('_root', 'chrome');
    }

    public function testANewClientCanBeCreated(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new PantherContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test', 'chrome');
    }

    public function testASetOfClientsCannotBeCreatedOnEmptySession(): void
    {
        $table = new TableNode([
            0 => ['name', 'driver'],
            1 => ['foo', 'chrome'],
            2 => ['bar', 'chrome'],
        ]);

        $context = new PantherContext();

        static::expectException(\RuntimeException::class);
        $context->iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions($table);
    }

    public function testASetOfClientsCanBeCreated(): void
    {
        $mink = $this->createMinkContext(self::exactly(2));
        $table = new TableNode([
            0 => ['name', 'driver'],
            1 => ['foo', 'chrome'],
            2 => ['bar', 'chrome'],
        ]);

        $context = new PantherContext();
        $context->setMink($mink);

        $context->iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions($table);
    }

    public function testASetOfClientsCanBeCreatedWithExtraOptions(): void
    {
        $mink = $this->createMinkContext(self::exactly(2));
        $table = new TableNode([
            0 => ['name', 'driver', 'options'],
            1 => ['foo', 'chrome', 'port => 9080'],
            2 => ['bar', 'chrome', 'port => 9080'],
        ]);

        $context = new PantherContext();
        $context->setMink($mink);

        $context->iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions($table);
    }

    public function testTheClientCannotBeSwitchedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iSwitchToANewClient('test');
    }

    public function testTheClientCannotBeSwitchedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new PantherContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iSwitchToANewClient('test');
    }

    public function testTheClientCanBeSwitched(): void
    {
        $mink = $this->createMinkContext(self::exactly(2));

        $context = new PantherContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test', 'chrome');
        $context->iSwitchToANewClient('test');
    }

    public function testTheRootClientCannotBeUsedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCannotBeUsedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new PantherContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCanBeUsed(): void
    {
        $mink = $this->createMinkContext(self::exactly(3));

        $context = new PantherContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test', 'chrome');
        $context->iSwitchToANewClient('test');
        $context->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCannotBeRemovedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iRemoveTheClient('_root');
    }

    public function testTheRootClientCannotBeRemoved(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new PantherContext();
        $context->setMink($mink);

        static::expectException(DriverException::class);
        $context->iRemoveTheClient('_root');
    }

    public function testClientsCannotBeCountedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iShouldHave(2);
    }

    public function testClientsCannotBeCountedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new PantherContext();
        $context->setMink($mink);

        static::expectException(LogicException::class);
        $context->iShouldHave(2);
    }

    public function testClientsCanBeCountedOnDefaultClient(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new PantherContext();
        $context->setMink($mink);
        $context->iShouldHave(1);
    }

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
