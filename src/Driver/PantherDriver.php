<?php

declare(strict_types=1);

namespace PantherExtension\Driver;

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
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

    private $additionalClients = [];
    private $client;
    private $requests = [];
    private $session;

    public function __construct(string $driver = 'chrome')
    {
        $this->client = $this->defineDriver($driver);
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
        $selectElement->deselectAll();
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

    public function createAdditionalClient(string $name, string $driver): void
    {
        try {
            $this->additionalClients[$name] = $this->defineDriver($driver);
        } catch (LogicException $exception) {
            throw new DriverException('The desired client cannot be created, please check the requested driver and options.');
        }
    }

    public function switchToClient(string $name): void
    {
        if (!\array_key_exists($name, $this->additionalClients)) {
            throw new InvalidArgumentException(sprintf('The desired "%s" cannot be found.', $name));
        }

        $this->additionalClients['_root'] = $this->client;
        $this->client = $this->additionalClients[$name];
    }

    public function getClient(): WebDriver
    {
        return $this->client;
    }

    private function defineDriver(string $driver): WebDriver
    {
        if (!\in_array($driver, self::ALLOWED_DRIVERS)) {
            throw new LogicException('The desired driver cannot be instantiated');
        }

        switch ($driver) {
            case 'chrome':
                return Client::createChromeClient();
                break;
            case 'firefox':
                return Client::createFirefoxClient();
                break;
            case 'selenium':
                return Client::createSeleniumClient();
                break;
            default:
                throw new LogicException('The desired driver cannot be instantiated');
        }
    }
}
