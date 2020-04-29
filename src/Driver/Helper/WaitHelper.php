<?php

declare(strict_types=1);

namespace PantherExtension\Driver\Helper;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class WaitHelper
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var mixed[]
     */
    private $requests = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function waitFor(string $element, int $timeoutInSeconds = 30, int $intervalInMillisecond = 250): void
    {
        if (0 === strpos('@', $element)) {
            $this->waitForAjax($element, $timeoutInSeconds);

            return;
        }

        $this->client->waitFor($element, $timeoutInSeconds, $intervalInMillisecond);
    }

    public function waitForText(string $elementLocator, string $text, bool $strict = false, int $timeoutInSeconds = 30, int $intervalInMilliseconds = 250): void
    {
        $this->checkResearchContent($text);

        $locator = trim($text);

        $condition = $strict
            ? WebDriverExpectedCondition::elementTextIs(WebDriverBy::xpath($elementLocator), $locator)
            : WebDriverExpectedCondition::elementTextContains(WebDriverBy::xpath($elementLocator), $locator)
        ;

        $this->client->wait($timeoutInSeconds, $intervalInMilliseconds)->until($condition);

        $this->client->refreshCrawler();
    }

    public function waitForInvisibility(string $elementLocator, int $timeoutInSeconds = 30, int $intervalInMilliseconds = 250): void
    {
        $locator = trim($elementLocator);

        $by = '' === $locator || '/' !== $locator[0]
            ? WebDriverBy::cssSelector($locator)
            : WebDriverBy::xpath($locator)
        ;

        $this->client->wait($timeoutInSeconds, $intervalInMilliseconds)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($by)
        );

        $this->client->refreshCrawler();
    }

    public function trackAjaxRequest(string $uri, string $alias, string $method = 'GET'): void
    {
        if (\array_key_exists($alias, $this->requests)) {
            throw new InvalidArgumentException(
                sprintf('The "%s" alias already exist, please change the desired one.', $alias)
            );
        }

        // TODO
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

    private function checkResearchContent(string $research): void
    {
        if (0 === strpos($research, '#')) {
            throw new InvalidArgumentException(
                sprintf('The desired text seems to be an tag identifier, please consider using "%s::waitForElementByIdentifier"', self::class)
            );
        }

        if (0 === strpos($research, '.')) {
            throw new InvalidArgumentException(
                sprintf('The desired text seems to be an tag className, please consider using "%s::waitForElementByClassName"', self::class)
            );
        }
    }
}
