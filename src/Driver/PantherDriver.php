<?php

declare(strict_types=1);

namespace PantherExtension\Driver;

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverSelect;
use PantherExtension\Driver\Element\PantherElement;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\Helper\CapabilitiesHelper;
use PantherExtension\Driver\Helper\CookieHelper;
use PantherExtension\Driver\Helper\OptionsHelper;
use PantherExtension\Driver\Helper\WaitHelper;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCaseTrait;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 * @author Vincent CHALAMON <vincentchalamon@gmail.com>
 */
final class PantherDriver extends CoreDriver
{
    use PantherTestCaseTrait;

    public const CHROME = 'chrome';
    public const FIREFOX = 'firefox';
    public const SELENIUM = 'selenium';
    private const ALLOWED_DRIVERS = [self::CHROME, self::FIREFOX, self::SELENIUM];
    public const DEFAULT_CLIENT_KEY = '_root';

    /**
     * @var Client[]
     */
    private $additionalClients = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * @var string[]
     */
    private $requests = [];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var bool
     */
    private $started;

    /**
     * @var OptionsHelper
     */
    private $optionsHelper;

    /**
     * @var WaitHelper
     */
    private $waitHelper;

    /**
     * @var CapabilitiesHelper
     */
    private $capabilitiesHelper;

    /**
     * @param mixed[] $options
     */
    public function __construct(string $driver = self::CHROME, array $options = [])
    {
        $this->client = $this->defineDriver($driver, $options);
    }

    public function getClient(): Client
    {
        if (null === $this->client) {
            throw new DriverException('The driver must be defined in order to access it!');
        }

        return $this->client;
    }
    
    public function getWebDriver(): RemoteWebDriver
    {
        if (null === $this->client || !$this->started) {
            throw new LogicException('The client MUST be defined to access the WebDriver!');
        }

        return $this->client->getWebDriver();
    }

    public function getClients(): array
    {
        return [$this->client] + $this->additionalClients;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        try {
            $this->client->start();
            $this->started = true;
            $this->capabilitiesHelper = new CapabilitiesHelper($this->client);
            $this->cookieHelper = new CookieHelper($this->client);
            $this->optionsHelper = new OptionsHelper($this->getWebDriver());
            $this->waitHelper = new WaitHelper($this->client);
        } catch (\RuntimeException $exception) {
            throw new DriverException(
                sprintf('The driver cannot be started, error message: %s', $exception->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        try {
            $this->additionalClients = [];
            $this->client->quit();
            self::stopWebServer();
            $this->started = false;
        } catch (\LogicException $exception) {
            throw new DriverException(
                sprintf('The driver cannot be stopped, error message "%s"', $exception->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return null !== $this->client && $this->started;
    }

    /**
     * {@inheritdoc}
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        if (!$this->started) {
            return;
        }

        try {
            $this->additionalClients = [];
            $this->getWebDriver()->manage()->deleteAllCookies();
        } catch (\LogicException $exception) {
            throw new DriverException(
                sprintf('The driver cannot reset the current session, error message "%s"', $exception->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function visit($url)
    {
        try {
            $this->client->get($url);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUrl()
    {
        try {
            return $this->client->getCurrentURL();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reload()
    {
        try {
            $this->client->reload();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forward()
    {
        try {
            $this->client->forward();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function back()
    {
        try {
            $this->client->back();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function isCapabilityAvailable(string $capability): bool
    {
        try {
            return $this->capabilitiesHelper->isCapabilityAvailable($capability);
        } catch (LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function setCapability(string $capability, string $value): void
    {
        try {
            $this->capabilitiesHelper->setCapability($capability, $value);
        } catch (LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function getCapabilities(): array
    {
        try {
            return $this->capabilitiesHelper->getCapabilities();
        } catch (LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function switchToWindow($name = null)
    {
        try {
            $targetLocator = $this->getWebDriver()->switchTo();

            null === $name ? $targetLocator->defaultContent() : $targetLocator->window($name);
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function switchToIFrame($name = null)
    {
        try {
            $targetLocator = $this->getWebDriver()->switchTo();

            null !== $name ? $targetLocator->frame($name) : $targetLocator->parent();
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeader($name, $value)
    {
        try {
            $this->client->setServerParameter($name, $value);
        } catch (\LogicException $exception) {
            throw new UnsupportedDriverActionException(
                sprintf('The %s::%s() method cannot be called when using %s', self::class, __METHOD__, self::class),
                $this
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaders()
    {
        try {
            return $this->client->getResponse()->getHeaders();
        } catch (\LogicException $exception) {
            throw new UnsupportedDriverActionException(
                sprintf('The %s::%s() method cannot be called when using %s', self::class, __METHOD__, self::class),
                $this
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCookie($name, $value = null)
    {
        try {
            $this->cookieHelper->setCookie($name, $value);
        } catch (InvalidArgumentException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($name)
    {
        try {
            return $this->cookieHelper->getCookie($name);
        } catch (NoSuchCookieException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function getSpecificCookie(string $name, string $path, string $domain): string
    {
        try {
            return $this->cookieHelper->getSpecificCookie($name, $path, $domain);
        } catch (NoSuchCookieException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        try {
            return $this->client->getPageSource();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScreenshot()
    {
        return $this->client->takeScreenshot();
    }

    /**
     * @throws DriverException If an exception is thrown
     */
    public function takeScreenshot(string $path): void
    {
        try {
            $this->client->takeScreenshot($path);
        } catch (\InvalidArgumentException|\LogicException $exception) {
            throw new DriverException(
                sprintf('The %s:%s() method encounter an error. Error message: %s', self::class, __METHOD__, $exception->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWindowName()
    {
        return $this->client->getTitle();
    }

    /**
     * {@inheritdoc}
     */
    public function find($xpath)
    {
        $nodes = $this->client->findElements(WebDriverBy::xpath($xpath));
        $elements = [];

        foreach ($nodes as $key => $node) {
            $elements[] = new PantherElement(sprintf('(%s)[%d]', $xpath, $key + 1), $this->session);
        }

        return $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagName($xpath)
    {
        return $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getTagName();
    }

    /**
     * {@inheritdoc}
     */
    public function getText($xpath)
    {
        return $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getText();
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml($xpath)
    {
        return $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getAttribute('innerHTML');
    }

    /**
     * {@inheritdoc}
     */
    public function getOuterHtml($xpath)
    {
        return $this->client->getCrawler()->filterXPath($xpath)->outerHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($xpath, $name)
    {
        return $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getAttribute($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($xpath)
    {
        return $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getAttribute('value');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        $element->sendKeys($value);

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function check($xpath)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        if (!$element->isSelected()) {
            $element->click();
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function uncheck($xpath)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));

        if ($element->isSelected()) {
            $element->click();
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function isChecked($xpath)
    {
        return (bool) $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->getAttribute('checked');
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        $selectElement = new WebDriverSelect($element);

        if ($selectElement->isMultiple()) {
            $selectElement->deselectAll();
        }

        $selectElement->selectByValue($value);

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function isSelected($xpath)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        try {
            return $element->isSelected();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function click($xpath)
    {
        $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->click();
    }

    /**
     * {@inheritdoc}
     */
    public function doubleClick($xpath)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));
        $mouse = $this->getWebDriver()->getMouse();

        $mouse->doubleClick($element->getCoordinates());

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function rightClick($xpath)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));
        $mouse = $this->getWebDriver()->getMouse();

        $mouse->contextClick($element->getCoordinates());

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function attachFile($xpath, $path)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        if ('input' !== $element->getTagName()) {
            throw new InvalidArgumentException(
                sprintf('The current element cannot receive file, please check the selector')
            );
        }

        $element->setFileDetector(new LocalFileDetector());
        $element->sendKeys($path);

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($xpath)
    {
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        return $element->isDisplayed();
    }

    /**
     * {@inheritdoc}
     */
    public function mouseOver($xpath)
    {
        $webDriver = $this->getWebDriver();

        $element = $webDriver->findElement(WebDriverBy::xpath($xpath));
        $mouse = $webDriver->getMouse();

        try {
            $mouse->mouseMove($element->getCoordinates());
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $webDriver = $this->getWebDriver();

        $element = $webDriver->findElement(WebDriverBy::xpath($xpath));
        $actions = $webDriver->action();

        $actions->keyDown($element, $char);

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $webDriver = $this->getWebDriver();

        $element = $webDriver->findElement(WebDriverBy::xpath($xpath));
        $actions = $webDriver->action();

        $actions->keyUp($element, $char);

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $webDriver = $this->getWebDriver();

        $actions = $webDriver->action();

        $sourceElement = $webDriver->findElement(WebDriverBy::xpath($sourceXpath));
        $destinationElement = $webDriver->findElement(WebDriverBy::xpath($destinationXpath));

        try {
            $actions->dragAndDrop($sourceElement, $destinationElement)->perform();
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    public function scrollTo(int $xOffset, int $yOffset): void
    {
        $touchScreen = $this->getWebDriver()->getTouch();

        try {
            $touchScreen->scroll($xOffset, $yOffset);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    public function scrollFromTo(string $xpath, int $xOffset, int $yOffset): void
    {
        $touchScreen = $this->getWebDriver()->getTouch();
        $element = $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath));

        $touchScreen->scrollFromElement($element, $xOffset, $yOffset);
        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function executeScript($script)
    {
        try {
            $this->client->executeScript($script);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateScript($script)
    {
        try {
            return $this->client->executeScript($script);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function executeAsyncScript(string $script, array $arguments = []): void
    {
        try {
            $this->client->executeAsyncScript($script, $arguments);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function wait($timeoutInMilliseconds, $condition)
    {
        if (!\is_string($condition)) {
            throw new DriverException(
                sprintf('The %s::%s() method cannot be called with the given arguments', self::class, __METHOD__)
            );
        }

        return $this->client->wait($timeoutInMilliseconds / 1000)->until(function () use ($condition): bool {
            return $this->evaluateScript($condition);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function resizeWindow($width, $height, $name = null)
    {
        try {
            $this->optionsHelper->resizeWindow($name, $width, $height);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function maximizeWindow($name = null)
    {
        try {
            $this->optionsHelper->maximizeWindow($name);
        } catch (\LogicException $exception) {
            throw new DriverException($exception->getMessage());
        }

        $this->client->refreshCrawler();
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm($xpath)
    {
        $this->getWebDriver()->findElement(WebDriverBy::xpath($xpath))->submit();
    }

    public function moveToFullScreen(): void
    {
        try {
            $this->optionsHelper->moveToFullScreen();
        } catch (UnsupportedOperationException $exception) {
            throw new DriverException('The window cannot be be moved to fullscreen.');
        }

        $this->client->refreshCrawler();
    }

    public function setOrientation(string $orientationMode = 'PORTRAIT'): void
    {
        $options = $this->getWebDriver()->manage();

        try {
            $options->window()->setScreenOrientation($orientationMode);
        } catch (WebDriverException $exception) {
            throw new DriverException(
                sprintf('The window cannot be be moved to "%s" mode.', $orientationMode)
            );
        }

        $this->client->refreshCrawler();
    }

    /**
     * @param string $type {@see WebDriverOptions::getLog()}
     */
    public function getLogs(string $type): array
    {
        $options = $this->getWebDriver()->manage();

        try {
            return $options->getLog($type);
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException($exception->getMessage());
        }
    }

    public function createAdditionalClient(string $name): void
    {
        if (self::DEFAULT_CLIENT_KEY === $name) {
            throw new InvalidArgumentException(
                sprintf('The "%s" client name is reserved, please use a different name.', self::DEFAULT_CLIENT_KEY)
            );
        }

        try {
            $this->additionalClients[$name] = self::createAdditionalPantherClient();
        } catch (LogicException $exception) {
            throw new DriverException('The desired client cannot be created, please check the requested driver and options.');
        }
    }

    public function switchToClient(string $name): void
    {
        if (!\array_key_exists($name, $this->additionalClients)) {
            throw new InvalidArgumentException(sprintf('The desired "%s" cannot be found.', $name));
        }

        if (!\array_key_exists(self::DEFAULT_CLIENT_KEY, $this->additionalClients)) {
            $this->additionalClients[self::DEFAULT_CLIENT_KEY] = $this->client;
        }

        $this->client = $this->additionalClients[$name];
    }

    public function removeClient(string $name): void
    {
        if (self::DEFAULT_CLIENT_KEY === $name) {
            throw new DriverException(sprintf('The "%s" client cannot removed!', self::DEFAULT_CLIENT_KEY));
        }

        if (\array_key_exists(self::DEFAULT_CLIENT_KEY, $this->additionalClients)) {
            $this->client = $this->additionalClients[self::DEFAULT_CLIENT_KEY];
        }

        unset($this->additionalClients[$name]);
    }

    public function resetClients(): void
    {
        if (!\array_key_exists(self::DEFAULT_CLIENT_KEY, $this->additionalClients)) {
            $this->additionalClients = [];
        }

        $this->client = $this->additionalClients[self::DEFAULT_CLIENT_KEY];
        $this->additionalClients = [];
    }

    public function getWaitHelper(): WaitHelper
    {
        if (null === $this->client || !$this->started) {
            throw new LogicException('The client MUST be defined to access the WaitHelper!');
        }

        return $this->waitHelper;
    }

    private function defineDriver(string $driver, array $options = [], array $kernelOptions = []): WebDriver
    {
        if (!\in_array($driver, self::ALLOWED_DRIVERS)) {
            throw new LogicException('The desired driver cannot be instantiated');
        }

        switch ($driver) {
            case self::CHROME:
            case self::FIREFOX:
                return self::createPantherClient(array_merge($options, ['browser' => $driver]), $kernelOptions);
            case self::SELENIUM:
                $config = $options['config']['selenium'];
                return Client::createSeleniumClient($config['hub_url'], null, null, $config);
            default:
                throw new LogicException('The desired driver cannot be instantiated');
        }
    }
}
