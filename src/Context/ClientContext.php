<?php

declare(strict_types=1);

namespace PantherExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use PantherExtension\Driver\Exception\LogicException;
use PantherExtension\Driver\PantherDriver;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class ClientContext extends RawMinkContext
{
    use DriverTrait;

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
}
