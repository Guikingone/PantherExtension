<?php

declare(strict_types=1);

namespace Tests\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\DriverException;
use PantherExtension\Context\ClientContext;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class ClientContextTest extends TestCase
{
    use ContextTestTrait;

    public function testANewClientCannotBeCreatedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new ClientContext())->iCreateANewClient('test');
    }

    public function testANewClientCannotBeCreatedWithReservedName(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new ClientContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iCreateANewClient('_root');
    }

    public function testANewClientCanBeCreated(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new ClientContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test');
    }

    public function testASetOfClientsCannotBeCreatedOnEmptySession(): void
    {
        $table = new TableNode([
            0 => ['name', 'driver'],
            1 => ['foo', 'chrome'],
            2 => ['bar', 'chrome'],
        ]);

        $context = new ClientContext();

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

        $context = new ClientContext();
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

        $context = new ClientContext();
        $context->setMink($mink);

        $context->iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions($table);
    }

    public function testTheClientCannotBeSwitchedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new ClientContext())->iSwitchToANewClient('test');
    }

    public function testTheClientCannotBeSwitchedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new ClientContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iSwitchToANewClient('test');
    }

    public function testTheClientCanBeSwitched(): void
    {
        $mink = $this->createMinkContext(self::exactly(2));

        $context = new ClientContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test');
        $context->iSwitchToANewClient('test');
    }

    public function testTheRootClientCannotBeUsedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new ClientContext())->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCannotBeUsedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new ClientContext();
        $context->setMink($mink);

        static::expectException(InvalidArgumentException::class);
        $context->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCanBeUsed(): void
    {
        $mink = $this->createMinkContext(self::exactly(3));

        $context = new ClientContext();
        $context->setMink($mink);

        $context->iCreateANewClient('test');
        $context->iSwitchToANewClient('test');
        $context->iSwitchBackToDefaultClient();
    }

    public function testTheRootClientCannotBeRemovedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new ClientContext())->iRemoveTheClient('_root');
    }

    public function testTheRootClientCannotBeRemoved(): void
    {
        $mink = $this->createMinkContext(self::once());
        $context = new ClientContext();
        $context->setMink($mink);

        static::expectException(DriverException::class);
        $context->iRemoveTheClient('_root');
    }

    public function testClientsCannotBeCountedOnEmptySession(): void
    {
        static::expectException(\RuntimeException::class);
        (new ClientContext())->iShouldHave(2);
    }

    public function testClientsCannotBeCountedOnEmptyArray(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new ClientContext();
        $context->setMink($mink);

        static::expectException(LogicException::class);
        $context->iShouldHave(2);
    }

    public function testClientsCanBeCountedOnDefaultClient(): void
    {
        $mink = $this->createMinkContext(self::once());

        $context = new ClientContext();
        $context->setMink($mink);
        $context->iShouldHave(1);
    }
}
