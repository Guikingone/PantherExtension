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
     * @And   I wait for :element
     */
    public function iWaitForElement(string $element): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element during :timeout
     * @And   I wait for :element during :timeout
     */
    public function iWaitForElementDuring(string $element, int $timeoutInSeconds = 30): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element during :timeout every :interval
     * @And   I wait for :element during :timeout every :interval
     */
    public function iWaitForElementDuringEvery(string $element, int $timeoutInSeconds = 30, int $intervalMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->waitFor($element, $timeoutInSeconds, $intervalMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I create a new client :name using the :driver driver
     * @And   I create a new client :name using the :driver driver
     */
    public function iCreateANewClient(string $name, string $driver): void
    {
        $this->getDriver()->createAdditionalClient($name, $driver);
    }

    /**
     * @Given I switch to client :name
     * @And   I switch to client :name
     */
    public function iSwitchToANewClient(string $name): void
    {
        $this->getDriver()->switchToClient($name);
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
