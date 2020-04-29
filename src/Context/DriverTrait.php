<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
trait DriverTrait
{
    protected function getDriver(): PantherDriver
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof PantherDriver) {
            throw new LogicException(
                sprintf('The driver must be %s in order to wait for element | Ajax requests.', get_class($driver))
            );
        }

        return $driver;
    }
}
