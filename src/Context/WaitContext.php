<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use PantherExtension\Driver\Exception\LogicException;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class WaitContext extends RawMinkContext
{
    use DriverTrait;

    /**
     * @Given I wait for :element to be equal to :text
     * @And   I wait for :element to be equal to :text
     */
    public function iWaitForElementToBeEqualTo(string $element, string $text): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text, true);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to be equal to :text during :seconds seconds
     * @And   I wait for :element to be equal to :text during :seconds seconds
     */
    public function iWaitForElementToBeEqualToDuring(string $element, string $text, int $timeoutInSeconds): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text, true, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to be equal to :text during :seconds seconds every :milliseconds milliseconds
     * @And   I wait for :element to be equal to :text during :seconds seconds every :milliseconds milliseconds
     */
    public function iWaitForElementToBeEqualToDuringEvery(string $element, string $text, int $timeoutInSeconds = 30, int $intervalInMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text, true, $timeoutInSeconds, $intervalInMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to contains :text
     * @And   I wait for :element to contains :text
     */
    public function iWaitForElementToContains(string $element, string $text): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to contains :text during :seconds seconds
     * @And   I wait for :element to contains :text during :seconds seconds
     */
    public function iWaitForElementToContainsDuring(string $element, string $text, int $timeoutInSeconds = 30): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text, false, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to contains :text during :seconds seconds every :milliseconds milliseconds
     * @And   I wait for :element to contains :text during :seconds seconds every :milliseconds milliseconds
     */
    public function iWaitForElementToContainsDuringEvery(string $element, string $text, int $timeoutInSeconds = 30, int $intervalInMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForText($element, $text, false, $timeoutInSeconds, $intervalInMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to be invisible
     * @And   I wait for :element to be invisible
     */
    public function iWaitForElementToBeInvisible(string $element): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForInvisibility($element);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to be invisible during :seconds seconds
     * @And   I wait for :element to be invisible during :seconds seconds
     */
    public function iWaitForElementToBeInvisibleDuring(string $element, int $timeoutInSeconds = 30): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForInvisibility($element, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }

    /**
     * @Given I wait for :element to be invisible during :seconds seconds every :milliseconds milliseconds
     * @And   I wait for :element to be invisible during :seconds seconds every :milliseconds milliseconds
     */
    public function iWaitForElementToBeInvisibleDuringEvery(string $element, int $timeoutInSeconds = 30, int $intervalInMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForInvisibility($element, $timeoutInSeconds, $intervalInMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired text cannot be found.'));
        }
    }
}
