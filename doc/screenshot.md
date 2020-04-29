# Screenshot

Taking screenshots can be hard during E2E/functional tests, thanks to Panther and Mink, we can ease the process and
make it fun!

## Usage

```gherkin
# ...

  And I take a screenshot that should be stored at "/srv/app/features/screenshot.png"
```

By default, Panther will (thanks to WebDriver) take a screenshot and send it into the desired directory, by default,
keep in mind that the dimensions are the ones from the current browser window.
