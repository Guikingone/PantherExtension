<?php

declare(strict_types=1);

namespace Tests\Driver;

use Behat\Mink\Exception\UnsupportedDriverActionException;
use Facebook\WebDriver\WebDriver;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\Helper\WaitHelper;
use PantherExtension\Driver\PantherDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class PantherDriverTest extends TestCase
{
    /**
     * @var PantherDriver
     */
    private $driver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->driver = new PantherDriver('chrome', ['webServerDir' => __DIR__.'/../fixtures']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->driver->stop();
    }

    public function testDriverCannotBeCreated(): void
    {
        static::expectException(LogicException::class);
        new PantherDriver('test', ['webServerDir' => __DIR__]);
    }

    public function testDriverCanBeCreated(): void
    {
        static::assertInstanceOf(
            Client::class,
            (new PantherDriver('chrome', ['webServerDir' => __DIR__]))->getClient()
        );
    }

    public function testWebDriverCannotBeAccessedOnInvalidClient(): void
    {
        static::expectException(LogicException::class);
        $this->driver->getWebDriver();
    }

    public function testWebDriverCanBeAccessedOnValidClient(): void
    {
        $this->driver->start();
        static::assertInstanceOf(WebDriver::class, $this->driver->getWebDriver());
    }

    public function testWaitHelperCannotBeAccessedOnInvalidClient(): void
    {
        static::expectException(LogicException::class);
        $this->driver->getWaitHelper();
    }

    public function testWaitHelperCanBeAccessedOnValidClient(): void
    {
        $this->driver->start();
        static::assertInstanceOf(WaitHelper::class, $this->driver->getWaitHelper());
    }

    public function testDriverCanBeStarted(): void
    {
        $this->driver->start();

        static::assertTrue($this->driver->isStarted());
    }

    public function testDriverCanBeStopped(): void
    {
        $this->driver->start();

        static::assertTrue($this->driver->isStarted());

        $this->driver->stop();
        static::assertFalse($this->driver->isStarted());
    }

    public function testDriverCanBeReset(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');
        $this->driver->setCookie('test', 'test');

        $this->driver->reset();
        static::assertCount(1, $this->driver->getClients());
        static::assertCount(0, $this->driver->getWebDriver()->manage()->getCookies());
    }

    public function testDriverCanBeResetMultipleTimes(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');
        $this->driver->setCookie('test', 'test');

        $this->driver->reset();
        static::assertCount(1, $this->driver->getClients());
        static::assertCount(0, $this->driver->getWebDriver()->manage()->getCookies());

        $this->driver->start();
        $this->driver->visit('/basic.html');
        $this->driver->setCookie('test', 'test');

        $this->driver->reset();
        static::assertCount(1, $this->driver->getClients());
        static::assertCount(0, $this->driver->getWebDriver()->manage()->getCookies());
    }

    /**
     * @dataProvider waitForDataProvider
     */
    public function testDriverCanBeReload(string $locator): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor.html');

        $this->driver->getWaitHelper()->waitFor($locator);
        static::assertTrue($this->driver->isVisible('//*[@id="hello"]'));

        $this->driver->reload();
        $this->driver->getWaitHelper()->waitFor($locator);
        static::assertTrue($this->driver->isVisible('//*[@id="hello"]'));
    }

    public function testDriverCannotSetRequestHeaders(): void
    {
        $this->driver->start();

        static::expectException(UnsupportedDriverActionException::class);
        $this->driver->setRequestHeader('HTTP_AUTH_BASIC', 'user:password');
    }

    public function testDriverCannotGetRequestHeaders(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');

        static::expectException(UnsupportedDriverActionException::class);
        $this->driver->getResponseHeaders();
    }

    public function testCurrentUrlCannotBeAccessed(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');

        static::assertNotNull($this->driver->getCurrentUrl());
        static::assertSame('http://127.0.0.1:9080/basic.html', $this->driver->getCurrentUrl());
    }

    public function testCookieCanBeSet(): void
    {
        $this->driver->start();
        $this->driver->getClient()->getCookieJar()->clear();
        $this->driver->visit('/cookie.php');
        $this->driver->setCookie('cookie', 'randomNewValue');

        static::assertSame('randomNewValue', $this->driver->getCookie('cookie'));
    }

    public function testCookieCanBeAccessed(): void
    {
        $this->driver->start();
        $this->driver->getClient()->getCookieJar()->clear();
        $this->driver->visit('/cookie.php');
        $cookie = $this->driver->getSpecificCookie('barcelona', '/cookie.php', '127.0.0.1');

        static::assertNotNull($cookie);
    }

    public function testContentCanBeAccessed(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');

        static::assertContains('Hello', $this->driver->getContent());
    }

    public function testScreenshotCanBeTaken(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');
        $screenshot = $this->driver->getScreenshot();

        static::assertNotEmpty($screenshot);
    }

    public function testScreenshotCanBeSaved(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');
        $this->driver->takeScreenshot(__DIR__.'/../assets/test.png');

        static::assertTrue(file_exists(__DIR__.'/../assets/test.png'));
    }

    public function testTitleCanBeRetrieved(): void
    {
        $this->driver->start();
        $this->driver->visit('/basic.html');

        static::assertSame('A basic welcome page', $this->driver->getWindowName());
    }

    public function testCapabilitiesCanBeRetrieved(): void
    {
        $this->driver->start();

        static::assertNotEmpty($this->driver->getCapabilities());
    }

    public function testClientCanInteractWithCheckbox(): void
    {
        $this->driver->start();
        $this->driver->visit('/checkbox.html');

        $this->driver->check('//*[@id="test"]');
        static::assertTrue($this->driver->isSelected('//*[@id="test"]'));
        static::assertTrue($this->driver->isChecked('//*[@id="test"]'));
        static::assertTrue($this->driver->getClient()->getCrawler()->filter('#test')->isSelected());

        $this->driver->uncheck('//*[@id="test"]');
        static::assertFalse($this->driver->isSelected('//*[@id="test"]'));
        static::assertFalse($this->driver->isChecked('//*[@id="test"]'));
        static::assertFalse($this->driver->getClient()->getCrawler()->filter('#test')->isSelected());
    }

    public function testClientCanClickOnCheckbox(): void
    {
        $this->driver->start();
        $this->driver->visit('/checkbox.html');

        $this->driver->click('//*[@id="test"]');
        static::assertTrue($this->driver->isSelected('//*[@id="test"]'));
        static::assertTrue($this->driver->isChecked('//*[@id="test"]'));
        static::assertTrue($this->driver->getClient()->getCrawler()->filter('#test')->isSelected());

        $this->driver->click('//*[@id="test"]');
        static::assertFalse($this->driver->isSelected('//*[@id="test"]'));
        static::assertFalse($this->driver->isChecked('//*[@id="test"]'));
        static::assertFalse($this->driver->getClient()->getCrawler()->filter('#test')->isSelected());
    }

    public function testClientCanDoubleClickOnCheckbox(): void
    {
        $this->driver->start();
        $this->driver->visit('/checkbox.html');

        $this->driver->doubleClick('//*[@id="test"]');
        static::assertFalse($this->driver->isSelected('//*[@id="test"]'));
        static::assertFalse($this->driver->isChecked('//*[@id="test"]'));
        static::assertFalse($this->driver->getClient()->getCrawler()->filter('#test')->isSelected());
    }

    public function testClientCanSelectOption(): void
    {
        $this->driver->start();
        $this->driver->visit('/select.html');

        $this->driver->selectOption('//*[@id="test-select"]', 'First');
        static::assertTrue($this->driver->isSelected('//*[@id="first-select"]'));
        static::assertTrue($this->driver->getClient()->getCrawler()->filter('[name="first"]')->isSelected());
        static::assertTrue($this->driver->getClient()->getCrawler()->filter('#first-select')->isSelected());
    }

    /**
     * @dataProvider waitForDataProvider
     */
    public function testClientCanWaitForElement(string $locator): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor.html');
        $this->driver->getWaitHelper()->waitFor($locator);

        static::assertSame('Hello', $this->driver->getClient()->getCrawler()->filter('#hello')->text());
    }

    public function testClientCanWaitForTextToBeVisible(): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor-equal.html');
        $this->driver->getWaitHelper()->waitForText('//*[@id="root"]', 'Hello');

        static::assertSame('Hello', $this->driver->getClient()->getCrawler()->filter('#root')->text());
    }

    public function testClientCanWaitForTextToBeStrictlyVisible(): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor-equal.html');
        $this->driver->getWaitHelper()->waitForText('//*[@id="root"]', 'Hello', true);

        static::assertSame('Hello', $this->driver->getClient()->getCrawler()->filter('#root')->text());
    }

    public function testClientCanWaitForTextToBeInvisible(): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor-invisibility.html');
        $this->driver->click('//*[@id="textButton"]');
        $this->driver->getWaitHelper()->waitForInvisibility('//*[@id="root"]');

        static::assertFalse($this->driver->isVisible('//*[@class="textContent"]'));
    }

    /**
     * @dataProvider waitForDataProvider
     */
    public function testClientCanWaitForElementAndCheckTheVisibility(string $locator): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor.html');
        $this->driver->getWaitHelper()->waitFor($locator);

        static::assertTrue($this->driver->isVisible('//*[@id="hello"]'));
    }

    public function testClientCanWaitForScriptToExecute(): void
    {
        $this->driver->start();
        $this->driver->visit('/waitfor.html');

        static::assertTrue($this->driver->wait(250, 'return true == true;'));
    }

    public function testAjaxCannotBeWaitedWithoutBeingTracked(): void
    {
        $this->driver->start();

        static::expectException(InvalidArgumentException::class);
        $this->driver->getWaitHelper()->waitForAjax('@test');
    }

    public function testAdditionalClientCanBeCreated(): void
    {
        $this->driver->createAdditionalClient('test');
        static::assertCount(2, $this->driver->getClients());
    }

    public function testAdditionalClientCanBeRemoved(): void
    {
        $this->driver->createAdditionalClient('test');
        static::assertCount(2, $this->driver->getClients());

        $this->driver->removeClient('test');
        static::assertCount(1, $this->driver->getClients());
    }

    public function waitForDataProvider(): iterable
    {
        yield 'css selector' => ['locator' => '#hello'];
        yield 'xpath expression' => ['locator' => '//*[@id="hello"]'];
    }
}
