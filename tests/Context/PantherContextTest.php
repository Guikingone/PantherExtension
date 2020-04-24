<?php

declare(strict_types=1);

namespace Tests\Context;

use PantherExtension\Context\PantherContext;
use PHPUnit\Framework\TestCase;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherContextTest extends TestCase
{
    public function testWaitCannotBeCalledWithoutSession(): void
    {
        static::expectException(\RuntimeException::class);
        (new PantherContext())->iWaitForElement('@userList', 10);
    }
}
