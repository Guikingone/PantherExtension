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
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverSelect;
use PantherExtension\Driver\Element\PantherElement;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use Facebook\WebDriver\WebDriver;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherDriver extends CoreDriver
{
    private const ALLOWED_DRIVERS = ['chrome', 'firefox', 'selenium'];
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
     * @var string[]
     */
    private $requests = [];

    /**
     * @var Session
     */
    private $session;

    /**
     * @param string[] $options
     */
    public function __construct(string $driver = 'chrome', array $options = [])
    {
        $this->client = $this->defineDriver($driver, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        $this->client->start();
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        $this->client->quit();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return null !== $this->client;
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
        $this->client->restart();
    }

    /**
     * {@inheritdoc}
     */
    public function visit($url)
    {
        $this->client->get($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUrl()
    {
        return $this->client->getCurrentURL();
    }

    /**
     * {@inheritdoc}
     */
    public function reload()
    {
        $this->client->reload();
    }

    /**
     * {@inheritdoc}
     */
    public function forward()
    {
        $this->client->forward();
    }

    /**
     * {@inheritdoc}
     */
    public function back()
    {
        $this->client->back();
    }

    /**
     * {@inheritdoc}
     */
    public function switchToWindow($name = null)
    {
        try {
            $this->client->switchTo()->window($name);
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException(sprintf('An error occurred when using %s::%s()', self::class, __METHOD__));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function switchToIFrame($name = null)
    {
        try {
            $this->client->switchTo()->frame($name);
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException(sprintf('An error occurred when using %s::%s()', self::class, __METHOD__));
        }
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
     * {@inheritdoc}s
     */
    public function getResponseHeaders()
    {
        try {
            $this->client->getResponse()->getHeaders();
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
        $options = $this->client->manage();

        try {
            $options->addCookie(['name' => $name, 'value' => $value]);
        } catch (\InvalidArgumentException $exception) {
            throw new DriverException(sprintf('An error occurred when using %s::%s()', self::class, __METHOD__));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($name)
    {
        $options = $this->client->manage();

        try {
            $options->getCookieNamed($name);
        } catch (NoSuchCookieException $exception) {
            throw new DriverException(sprintf('An error occurred when using %s::%s()', self::class, __METHOD__));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        try {
            $this->client->getResponse()->getStatusCode();
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
    public function getContent()
    {
        return $this->client->getPageSource();
    }

    /**
     * {@inheritdoc}
     */
    public function getScreenshot()
    {
        return $this->client->takeScreenshot();
    }

    /**
     * {@inheritdoc}
     */
    public function getWindowNames()
    {
        throw new UnsupportedDriverActionException(sprintf('The %s::%s() method cannot be used.', self::class, __METHOD__), $this);
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
            $elements[] = new PantherElement(sprintf('(%s)[%d]', $xpath, $key+1), $this->session);
        }

        return $elements;
    }

    /**
     * {@inheritdoc}
     */
    public function getTagName($xpath)
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath))->getTagName();
    }

    /**
     * {@inheritdoc}
     */
    public function getText($xpath)
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath))->getText();
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml($xpath)
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath))->html();
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
        return $this->client->findElement(WebDriverBy::xpath($xpath))->getAttribute($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($xpath)
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath))->getAttribute('value');
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($xpath, $value)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));

        $element->sendKeys($value);
    }

    /**
     * {@inheritdoc}
     */
    public function check($xpath)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));

        if (!$element->isSelected()) {
            $element->click();
        }
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
    }

    /**
     * {@inheritdoc}
     */
    public function isChecked($xpath)
    {
        return (bool) $this->client->findElement(WebDriverBy::xpath($xpath))->getAttribute('checked');
    }

    /**
     * {@inheritdoc}
     */
    public function selectOption($xpath, $value, $multiple = false)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));

        $selectElement = new WebDriverSelect($element);

        if ($selectElement->isMultiple()) {
            $selectElement->deselectAll();
        }

        $selectElement->selectByValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function isSelected($xpath)
    {
        return $this->client->findElement(WebDriverBy::xpath($xpath))->isSelected();
    }

    /**
     * {@inheritdoc}
     */
    public function click($xpath)
    {
        $this->client->findElement(WebDriverBy::xpath($xpath))->click();
    }

    /**
     * {@inheritdoc}
     */
    public function doubleClick($xpath)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));
        $mouse = $this->client->getMouse();

        $mouse->doubleClick($element->getCoordinates());
    }

    /**
     * {@inheritdoc}
     */
    public function attachFile($xpath, $path)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));

        if ('input' !== $element->getTagName()) {
            throw new InvalidArgumentException(sprintf('The current element cannot receive file, please check the selector'));
        }

        $element->setFileDetector(new LocalFileDetector());
        $element->sendKeys($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible($xpath)
    {
        $element = $this->client->getCrawler()->findElement(WebDriverBy::xpath($xpath));

        return $element->isDisplayed();
    }

    /**
     * {@inheritdoc}
     */
    public function mouseOver($xpath)
    {
        $element = $this->client->findElement(WebDriverBy::xpath($xpath));
        $mouse = $this->client->getMouse();

        $mouse->mouseMove($element->getCoordinates());
    }

    /**
     * {@inheritdoc}
     */
    public function keyPress($xpath, $char, $modifier = null)
    {
        $keyboard = $this->client->getKeyboard();

        $keyboard->pressKey($char);
    }

    /**
     * {@inheritdoc}
     */
    public function keyUp($xpath, $char, $modifier = null)
    {
        $keyboard = $this->client->getKeyboard();

        $keyboard->releaseKey($char);
    }

    /**
     * {@inheritdoc}
     */
    public function dragTo($sourceXpath, $destinationXpath)
    {
        $actions = $this->client->action();

        $sourceElement = $this->client->findElement(WebDriverBy::xpath($sourceXpath));
        $destinationElement = $this->client->findElement(WebDriverBy::xpath($destinationXpath));

        $actions->dragAndDrop($sourceElement, $destinationElement);
    }

    /**
     * {@inheritdoc}
     */
    public function executeScript($script)
    {
        $this->client->executeScript($script);
    }

    public function executeAsyncScript($script)
    {
        $this->client->executeAsyncScript($script);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateScript($script)
    {
        throw new UnsupportedDriverActionException(sprintf('The %s::%s() method cannot be used.', self::class, __METHOD__), $this);
    }

    /**
     * {@inheritdoc}
     */
    public function wait($timeout, $condition)
    {
        return (bool) $this->client->wait($timeout)->until($condition);
    }

    /**
     * {@inheritdoc}
     */
    public function resizeWindow($width, $height, $name = null)
    {
        $options = $this->client->manage();

        $options->window()->setSize(new WebDriverDimension($width, $height));
    }

    /**
     * {@inheritdoc}
     */
    public function maximizeWindow($name = null)
    {
        $options = $this->client->manage();

        $options->window()->maximize();
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm($xpath)
    {
        $this->client->findElement(WebDriverBy::xpath($xpath))->submit();
    }

    public function moveTofullScreen(): void
    {
        $options = $this->client->manage();

        try {
            $options->window()->fullscreen();
        } catch (UnsupportedOperationException $exception) {
            throw new DriverException('The window cannot be be moved to fullscreen.');
        }
    }

    public function setOrientation(string $orientationMode = 'PORTRAIT'): void
    {
        $options = $this->client->manage();

        try {
            $options->window()->setScreenOrientation($orientationMode);
        } catch (WebDriverException $exception) {
            throw new DriverException(sprintf('The window cannot be be moved to "%s" mode.', $orientationMode));
        }
    }

    /**
     * @param string $type {@see WebDriverOptions::getLog()}
     */
    public function getLogs(string $type): array
    {
        $options = $this->client->manage();

        return $options->getLog($type);
    }

    public function waitFor(string $element, int $timeoutInSeconds = 30, int $intervalInMillisecond = 250): void
    {
        if (0 === strpos('@', $element)) {
            $this->waitForAjax($element, $timeoutInSeconds);
        }

        $elementLocator = trim($element);

        if ('' === $elementLocator || '/' !== $elementLocator[0]) {
            $this->client->waitFor($element, $timeoutInSeconds, $intervalInMillisecond);
        }
    }

    public function waitForAjax(string $requestIdentifier, int $timeoutInSeconds = 30): void
    {
        if (!\array_key_exists($requestIdentifier, $this->requests)) {
            throw new InvalidArgumentException(
                sprintf('The "%s" alias does not refer to a current request, please be sure to watch a request before referring to it', $requestIdentifier)
            );
        }

        // TODO
    }

    public function createAdditionalClient(string $name, string $driver, array $options = []): void
    {
        if (self::DEFAULT_CLIENT_KEY === $name) {
            throw new InvalidArgumentException(
                sprintf('The "%s" client name is reserved, please use a different name.', self::DEFAULT_CLIENT_KEY)
            );
        }

        foreach ($options as $option) {
            if (!\is_string($option)) {
                continue;
            }

            $definedOptions = explode(' => ', $option);

            if (0 === \count($definedOptions)) {
                continue;
            }

            if ('port' === $definedOptions[0]) {
                $options['port'] = $definedOptions[1];
            }
        }

        try {
            $this->additionalClients[$name] = $this->defineDriver($driver, array_merge($options, [
                'port' => $options['port'] ?? 9080,
            ]));
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

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getClients(): array
    {
        return [$this->client] + $this->additionalClients;
    }

    private function defineDriver(string $driver, array $options = []): WebDriver
    {
        if (!\in_array($driver, self::ALLOWED_DRIVERS)) {
            throw new LogicException('The desired driver cannot be instantiated');
        }

        switch ($driver) {
            case 'chrome':
                return Client::createChromeClient(null, null, $options);
                break;
            case 'firefox':
                return Client::createFirefoxClient(null, null, $options);
                break;
            case 'selenium':
                $config = $options['config']['selenium'];
                return Client::createSeleniumClient($config['hub_url'], null, null, $config);
                break;
            default:
                throw new LogicException('The desired driver cannot be instantiated');
        }
    }
}
