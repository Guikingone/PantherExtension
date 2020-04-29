# Client

[Symfony Panther](https://github.com/symfony/panther) provide a default client which is capable of using Chrome, Firefox and Selenium.
This extension aim to provide the same features (even if some may be missing for now), here's a list
of what's provided: 

- Additional client

[Symfony Panther](https://github.com/symfony/panther) provides a shortcut which helps you to handle real-time features ([Mercure](https://mercure.rocks/), WebSockets, etc),
this extension provide the same feature with the Gherkin approach: 

```gherkin
  Scenario: I should be able to test the real-time comments
    Given I am on "/"
    And I follow "Blog"
    And I create a new client "test" using the "chrome" driver
    Then I should have 2 clients
    When I switch to client "test"
    And I fill in "comment" with "new random comment"
    And I press "Submit"
    And I wait for ".comments" during 5
    Then I should see 2 comments
    And I switch to default client
    Then I should see 2 comments
```

During this scenario, the extension will create a new client and let you switch to if needed.
Once the scenario done, the clients are destroyed then you can continue to use the "default" client.
