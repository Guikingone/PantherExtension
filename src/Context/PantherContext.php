<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use Behat\Mink\Driver\DriverInterface;
use Behat\MinkExtension\Context\RawMinkContext;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherContext extends RawMinkContext
{
    /**
     * @Given I wait for :element
     *
     * @And   I wait for :element
     * @And   I wait for :element during :timeout
     * @And   I wait for :element during :timeout every :interval milliseconds
     * @And   I wait for :element to be visible
     * @And   I wait for :element to appear
     */
    public function iWaitForElement(string $element, int $timeoutInSeconds = 30, int $intervalInMillisecond = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element, $timeoutInSeconds, $intervalInMillisecond);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    private function getDriver(): DriverInterface
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
