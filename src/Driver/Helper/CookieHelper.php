<?php

declare(strict_types=1);

namespace PantherExtension\Driver\Helper;

use Facebook\WebDriver\Exception\NoSuchCookieException;
use PantherExtension\Driver\Exception\InvalidArgumentException;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class CookieHelper
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function setCookie(string $name, ?string $value = null): void
    {
        if (null === $value) {
            return;
        }

        $cookies = $this->client->getCookieJar();

        if ($cookies->get($name) instanceof Cookie) {
            throw new InvalidArgumentException('This cookie already exist.');
        }

        $cookies->set(new Cookie($name, $value));
    }

    public function getCookie(string $name): string
    {
        $cookie = $this->client->getCookieJar()->get($name);
        if (!$cookie instanceof Cookie) {
            throw new NoSuchCookieException('The desired cookie cannot be found.');
        }

        return $cookie->getValue();
    }

    public function getSpecificCookie(string $name, string $path, string $domain): string
    {
        $cookie = $this->client->getCookieJar()->get($name, $path, $domain);
        if (!$cookie instanceof Cookie) {
            throw new NoSuchCookieException('The desired cookie cannot be found.');
        }

        return $cookie->getValue();
    }
}
