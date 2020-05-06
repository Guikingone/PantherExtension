<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherContext extends RawMinkContext
{
    use DriverTrait;

    /**
     * @AfterScenarioScope
     */
    public static function reset(AfterScenarioScope $scope): void
    {
        $environment = $scope->getEnvironment();

        if (!$environment instanceof InitializedContextEnvironment) {
            return;
        }

        try {
            $context = $environment->getContext(PantherContext::class);
        } catch (ContextNotFoundException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        if (!$context instanceof PantherContext) {
            throw new LogicException(sprintf('The context must be an instance of "%s", found "%s"', PantherContext::class, get_class($context)));
        }

        $context->getDriver()->resetClients();
    }

    /**
     * @Given I try to get the cookie :name on path :path and domain :domain
     * @And   I try to get the cookie :name on path :path and domain :domain
     */
    public function iTryToAccessASpecificCookie(string $name, string $path, string $domain): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getSpecificCookie($name, $path, $domain);
        } catch (NoSuchCookieException $exception) {
            throw new LogicException($exception->getMessage());
        }
    }

    /**
     * @internal This method will be moved to {@see WaitContext::iWaitForElement} in 1.0
     *
     * @Given I wait for :element
     * @And   I wait for :element
     */
    public function iWaitForElement(string $element): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitFor($element);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @internal This method will be moved to {@see WaitContext::iWaitForElementToBeVisible} in 1.0
     *
     * @Given I wait for :element to be visible
     * @And   I wait for :element to be visible
     */
    public function iWaitForElementToBeVisible(string $element): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForVisibility($element);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @internal This method will be moved to {@see WaitContext::iWaitForElementToBeVisibleDuring} in 1.0
     *
     * @Given I wait for :element to be visible during :timeout milliseconds
     * @And   I wait for :element to be visible during :timeout milliseconds
     */
    public function iWaitForElementToBeVisibleDuring(string $element, int $timeoutInMilliseconds = 3000): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitForVisibility($element, $timeoutInMilliseconds / 1000);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @internal This method will be moved to {@see WaitContext::iWaitForElementDuring} in 1.0
     *
     * @Given I wait for :element during :timeout seconds
     * @And   I wait for :element during :timeout seconds
     */
    public function iWaitForElementDuring(string $element, int $timeoutInSeconds = 30): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitFor($element, $timeoutInSeconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @internal This method will be moved to {@see WaitContext::iWaitForElementDuringEveryMilliseconds} in 1.0
     *
     * @Given I wait for :element during :timeout every :interval milliseconds
     * @And   I wait for :element during :timeout every :interval milliseconds
     */
    public function iWaitForElementDuringEveryMilliseconds(string $element, int $timeoutInSeconds = 30, int $intervalMilliseconds = 250): void
    {
        $driver = $this->getDriver();

        try {
            $driver->getWaitHelper()->waitFor($element, $timeoutInSeconds, $intervalMilliseconds);
        } catch (TimeoutException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found in the given timeout or seems to appear later than expected.'));
        } catch (NoSuchElementException $exception) {
            throw new LogicException(sprintf('The desired element cannot be found.'));
        }
    }

    /**
     * @Given I create a new client :name
     * @And   I create a new client :name
     */
    public function iCreateANewClient(string $name): void
    {
        $this->getDriver()->createAdditionalClient($name);
    }

    /**
     * @Given I create a new set of clients :options
     * @And   I create a new set of clients :options
     */
    public function iCreateANewSetOfClientUsingTheDriveAndTheFollowingOptions(TableNode $options): void
    {
        foreach ($options as $option) {
            $this->getDriver()->createAdditionalClient($option['name']);
        }
    }

    /**
     * @Given I switch to client :name
     * @And   I switch to client :name
     */
    public function iSwitchToANewClient(string $name): void
    {
        $this->getDriver()->switchToClient($name);
    }

    /**
     * @Given I switch back to the default client
     * @And   I switch back to the default client
     */
    public function iSwitchBackToDefaultClient(): void
    {
        $this->getDriver()->switchToClient(PantherDriver::DEFAULT_CLIENT_KEY);
    }

    /**
     * @Given I remove the client :name
     * @And   I remove the client :name
     */
    public function iRemoveTheClient(string $name): void
    {
        $this->getDriver()->removeClient($name);
    }

    /**
     * @Given I should have :count clients
     * @And   I should have :count clients
     */
    public function iShouldHave(int $count): void
    {
        $driver = $this->getDriver();

        if ($count !== $clientCount = \count($driver->getClients())) {
            throw new LogicException(
                sprintf('The desired client count cannot be validated, found "%d".', $clientCount)
            );
        }
    }

    /**
     * @Given I switch to fullscreen
     * @And   I switch to fullscreen
     */
    public function iSwitchToFullScreen(): void
    {
        $this->getDriver()->moveToFullScreen();
    }

    /**
     * @Given I change the screen orientation to :orientation
     * @And   I change the screen orientation to :orientation
     */
    public function iChangeTheScreenOrientation(string $orientation = 'PORTRAIT'): void
    {
        $this->getDriver()->setOrientation($orientation);
    }

    /**
     * @Given I scroll to :xOffset :yOffset
     * @And   I scroll to :xOffset :yOffset
     */
    public function iScrollTo(int $xOffset, int $yOffset): void
    {
        $this->getDriver()->scrollTo($xOffset, $yOffset);
    }

    /**
     * @Given I scroll from :element to :xOffset :yOffset
     * @And   I scroll from :element to :xOffset :yOffset
     */
    public function iScrollFromTo(string $element, int $xOffset, int $yOffset): void
    {
        $this->getDriver()->scrollFromTo($element, $xOffset, $yOffset);
    }

    /**
     * @Given If the :capability browser capability is enabled
     * @And   If the :capability browser capability is enabled
     */
    public function ifTheBrowserCapabilityIsEnabled(string $capability): void
    {
        $this->getDriver()->isCapabilityAvailable($capability);
    }

    /**
     * @Given I try to set a new browser capability called :capability with the value :value
     * @And   I try to set a new browser capability called :capability with the value :value
     */
    public function iTryToSetANewBrowserCapabilityWithTheValue(string $capability, string $value): void
    {
        $this->getDriver()->setCapability($capability, $value);
    }

    /**
     * @Given I take a new screenshot that should be stored in :directory
     * @And   I take a new screenshot that should be stored in :directory
     */
    public function iTakeANewScreenshot(string $directory): void
    {
        $this->getDriver()->takeScreenshot($directory);
    }

    /**
     * @Given I execute the following script :script asynchronously
     * @And   I execute the following script :script asynchronously
     */
    public function iExecuteAnAsyncScript(string $script): void
    {
        $this->getDriver()->executeAsyncScript($script);
    }
}
