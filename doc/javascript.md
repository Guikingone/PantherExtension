# Javascript

Javascript can be hard to handle (especially hidden elements) but thanks to [Symfony Panther](https://github.com/symfony/panther),
we can now ease the process of testing highly reactive apps!

## Waiting for element

[Symfony Panther](https://github.com/symfony/panther) define a `waitFor` method which use the `WebDriverWait` shortcuts
in order to wait for the element to be present, in `PantherContext` a set of steps have been defined to add this behaviour:

```gherkin
# ...

  Scenario: I should be able to document myself about GraphQL support thanks to the search field
    Given I am on "/"                                                                           
    When I fill in "SEARCH..." with "GraphQL"                                                   
    And I wait for "#algolia-autocomplete-listbox-0"                                            
    Then I should see "Documentation"                                                           
    And I should see "Search by"                                                                
    And I should see "Enabling GraphQL"                                                         
```

Given the above scenario (used on API-Platform website), we want to test that when we search for a specific feature, 
the search input display a list (powered by Algolia) that gave us the opportunity to click on multiples links 
(which are not `<a>` tags sadly).

Using the `I wait for "#algolia-autocomplete-listbox-0"` allows us to use the `waitFor` method defined in Panther but 
what if we need to wait for a specific time? 

We could also have used the following sentences:

```gherkin
# ...

  And I wait for "#algolia-autocomplete-listbox-0" during 10 # In secondes
  And I wait for "#algolia-autocomplete-listbox-0" during 10 every 150 milliseconds
```

By default, the timeout used in `And I wait for "#algolia-autocomplete-listbox-0"` is 30 seconds (as defined in Panther).

The important point here is that we wait for the element to be **present**, not necessarily **visible**, that why a set 
of steps is define to do so: 

```gherkin
# ...

  And I wait for "#algolia-autocomplete-listbox-0" to be visible
  And I wait for "#algolia-autocomplete-listbox-0" to be visible during 300 milliseconds # Milliseconds here as we don't use Panther directly
```

## Waiting for text (**Added in 0.4**)

Sometimes, you may need to wait for specific text to appear (think Ajax request which return a list), as this extension is 
build around `WebDriver` and give you shortcuts to wait for text, let's see how to use it:

```gherkin
# ...

  And I wait for ".text" to be equal to "New Random text"
  And I wait that ".text" is equal to "New Random text" during 10 seconds # Waiting with a timeout
  And I wait that ".text" is equal to "New Random text" during 10 seconds every 200 milliseconds # Waiting with a timeout and a retry strategy
```

What if you only need to check that some element "contains" a specific text?

```gherkin
# ...

  And I wait for ".text" to contains "New Random text"
  And I wait that ".text" contains "New Random text" during 10 seconds # Waiting with a timeout
  And I wait that ".text" contains "New Random text" during 10 seconds every 200 milliseconds # Waiting with a timeout and a retry strategy
```

**Note: By default the timeout is 30 seconds, retry strategy is 250 milliseconds**

## Waiting for element invisibility (**Added in 0.4**)

Sometimes, you may need to wait for an element to disappear (think a div which is hidden/removed when a button has been clicked),
this extension provide a set of steps to check this new state:

```gherkin
# ...

  And I wait for ".text" to be invisible
  And I wait for ".text" to be invisible during 10 seconds # Waiting with a timeout
  And I wait for ".text" to be invisible during 10 seconds every 200 milliseconds # Waiting with a timeout and a retry strategy
```

**Note: By default the timeout is 30 seconds, retry strategy is 250 milliseconds**
