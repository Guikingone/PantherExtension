<?php

declare(strict_types=1);

namespace PantherExtension\Driver\Helper;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use PantherExtension\Driver\Exception\LogicException;
use Symfony\Component\Panther\Client;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class CapabilitiesHelper
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isCapabilityAvailable(string $capability): bool
    {
        $capabilities = $this->client->getWebDriver()->getCapabilities();

        if (!$capabilities instanceof DesiredCapabilities) {
            throw new LogicException('The capabilities must be enabled!');
        }

        return $capabilities->is($capability);
    }

    public function setCapability(string $capability, string $value): void
    {
        $capabilities = $this->client->getWebDriver()->getCapabilities();

        if (!$capabilities instanceof DesiredCapabilities) {
            throw new LogicException('The capabilities must be enabled!');
        }

        $capabilities->setCapability($capability, $value);
    }

    public function getCapabilities(): array
    {
        $capabilities = $this->client->getWebDriver()->getCapabilities();

        if (!$capabilities instanceof DesiredCapabilities) {
            throw new LogicException('The capabilities must be enabled!');
        }

        return $capabilities->toArray();
    }
}
