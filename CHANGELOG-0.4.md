# Changelog

Core:

- `WaitContext` introduced
- Add a `started` attribute that determine if the driver has been started/stopped.
- `PantherDriver::reset` fixed on cookies reset and additional clients reset
- `PantherDriver::createAdditionalClient` core logic moved to `PantherTestCaseTrait` usage.
- Methods added for text "wait strategy"
- Fix on cookies `set` and `get` methods
- New method `PantherDriver::getSpecificCookie` that allow to search a specific cookie (name, path and domain)
- Methods added to set/check if capabilities are available
- `PantherDriver::getStatusCode` removed as `Symfony\Component\Panther\Client` does not support retrieving it

Doc:

- Doc for cookies, screenshot, docker, client, behat and javascript added/improved.

Tests:

- Tests improved for main `PantherDriver` logic.
- Tests improved for main `PantherContext` logic.
- Tests started for main `WaitContext` logic.

1.0 roadmap:

- `ClientContext` introduced
