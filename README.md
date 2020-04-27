Panther Mink Extension
======================

Mink extension for controlling `Chrome | Firefox | Selenium` thanks to [Symfony Panther](https://github.com/symfony/panther).

## Foreword:

This extension is experimental (even if stable at 95%), some features may be missing.

## Installation:

First, you need to install [Symfony Panther](https://github.com/symfony/panther) and it's required dependencies, then:

```bash
composer require guikingone/panther-extension
```

## Usage:

```yaml
default:
  suites:
    default:
      contexts:
        - PantherExtension\Context\PantherContext:
        # Your contexts

  extensions:
    PantherExtension\Extension\PantherExtension: ~
    Behat\MinkExtension:
      browser_name: chrome
      base_url: http://localhost
      sessions:
        default:
          panther:
            driver: 'chrome' # Or 'firefox', 'selenium', 'chrome' is the default value
```

Here's a simple example using a POC project which call [API-Platform website](https://api-platform.com/)

```bash
Feature:
  As a newbie in API-Platform, I want to document myself in many features

  Scenario: I should be able to see the main documentation                            # features/demo.feature:4
    Given I am on "/"                                                                 # FeatureContext::visit()
    And I should see "REST and GraphQL framework to build modern API-driven projects" # FeatureContext::assertPageContainsText()

  Scenario: I should be able to see the main documentation                                            # features/demo.feature:8
    Given I am on "/"                                                                                 # FeatureContext::visit()
    And I go to "/docs/distribution/"                                                                 # FeatureContext::visit()
    Then I should see "API Platform is the most advanced API platform, in any framework or language." # FeatureContext::assertPageContainsText()

  Scenario: I should be able to document myself about GraphQL support # features/demo.feature:13
    Given I am on "/"                                                 # FeatureContext::visit()
    And I follow "Get started"                                        # FeatureContext::clickLink()
    When I follow "Adding GraphQL Support"                            # FeatureContext::clickLink()
    Then I should be on "/docs/distribution/#adding-graphql-support"  # FeatureContext::assertPageAddress()
    Then I should see "You now have a GraphQL API!"                   # FeatureContext::assertPageContainsText()

  Scenario: I should be able to document myself about GraphQL support # features/demo.feature:20
    Given I am on "/"                                                 # FeatureContext::visit()
    When I fill in "SEARCH..." with "GraphQL"                         # FeatureContext::fillField()
    And I follow "Get started"                                        # FeatureContext::clickLink()
    When I follow "Adding GraphQL Support"                            # FeatureContext::clickLink()
    Then I should be on "/docs/distribution/#adding-graphql-support"  # FeatureContext::assertPageAddress()
    Then I should see "You now have a GraphQL API!"                   # FeatureContext::assertPageContainsText()
    And I wait for ".support"                                         # PantherExtension\Context\PantherContext::iWaitForElement()
      The desired element cannot be found in the given timeout or seems to appear later than expected. (PantherExtension\Driver\Exception\LogicException)

  Scenario: I should be able to test the demo                   # features/demo.feature:29
    Given I am on "/"                                           # FeatureContext::visit()
    And I follow "Demo"                                         # FeatureContext::clickLink()
    Then I should be on "https://demo-client.api-platform.com/" # FeatureContext::assertPageAddress()
    When I follow "API"                                         # FeatureContext::clickLink()
    Then I should be on "https://demo.api-platform.com/"        # FeatureContext::assertPageAddress()

--- Failed scenarios:

    features/demo.feature:20

5 scenarios (4 passed, 1 failed)
22 steps (21 passed, 1 failed)
0m56.28s (20.56Mb)
```

## Development

The project can be launched using: 

```bash
make boot
```

Every test can be launched using:

```bash
make tests
```

For more commands or help, please use: 

```bash
make
```

## Contributing

Just fork this repo and submit a new PR!
