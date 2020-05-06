Panther Mink Extension
======================

![PantherExtension CI](https://github.com/Guikingone/panther-extension/workflows/PantherExtension%20CI/badge.svg?branch=master)
![PantherExtension CI - Config](https://github.com/Guikingone/panther-extension/workflows/PantherExtension%20CI%20-%20Config/badge.svg?branch=master)

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
        - PantherExtension\Context\WaitContext:
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

**`WaitContext` has been introduced in 0.4**

If you need to use `Selenium`, just adapt the session configuration:

```yaml
# ...

  extensions:
    PantherExtension\Extension\PantherExtension: ~
    Behat\MinkExtension:
      browser_name: chrome
      base_url: http://localhost
      sessions:
        default:
          panther:
            driver: 'selenium'
            selenium:
              hub_url: 'http://127.0.0.1:4444/wd/hub'
```

Here's a simple example using a POC project which call [API-Platform website](https://api-platform.com/)

```gherkin
Feature:
  As a newbie in API-Platform, I want to document myself in many features

  Scenario: I should be able to see the main documentation                           
    Given I am on "/"                                                                
    And I should see "REST and GraphQL framework to build modern API-driven projects"

  Scenario: I should be able to see the main documentation                                           
    Given I am on "/"                                                                                
    And I go to "/docs/distribution/"                                                                
    Then I should see "API Platform is the most advanced API platform, in any framework or language."

  Scenario: I should be able to document myself about GraphQL support
    Given I am on "/"                                                
    And I follow "Get started"                                       
    When I follow "Adding GraphQL Support"                           
    Then I should be on "/docs/distribution/#adding-graphql-support" 
    Then I should see "You now have a GraphQL API!"                  

  Scenario: I should be able to document myself about GraphQL support thanks to the search field
    Given I am on "/"                                                                           
    When I fill in "SEARCH..." with "GraphQL"                                                   
    And I wait for "#algolia-autocomplete-listbox-0"                                            
    Then I should see "Documentation"                                                           
    And I should see "Search by"                                                                
    And I should see "Enabling GraphQL"                                                         

  Scenario: I should be able to test the demo                  
    Given I am on "/"                                          
    And I follow "Demo"                                        
    Then I should be on "https://demo-client.api-platform.com/"
    When I follow "API"                                        
    Then I should be on "https://demo.api-platform.com/"       

  Scenario: I should be able to test the demo                                         
    Given I am on "/"                                                                 
    And I follow "Community"                                                          
    And I create a new client "test" using the "chrome" driver                        
    Then I switch to client "test"                                                    
    And I go to "/"                                                                   
    Then I should see "REST and GraphQL framework to build modern API-driven projects"
    Then I remove the client "test"                                                   
    Then I should see "API Platform's community"                                      

6 scenarios (6 passed)
29 steps (29 passed)
0m28.61s (20.63Mb)
```

## Documentation

The full documentation can be found [here](/doc)

## CI usage

Please refer to [Symfony Panther](https://github.com/symfony/panther) documentation about using it in CI environments. 

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
