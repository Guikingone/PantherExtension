# Behat

As you may answer, how can I use this extension in an existing project?

Here's the process for the main libraries:

## FriendsOfBehat/SymfonyExtension (Symfony 3/4/5)

First, follow the installations steps in the [repository](https://github.com/FriendsOfBehat/SymfonyExtension) 
then keep this points in mind:

- This extension requires that `FriendsOfBehat/MinkExtension` is installed.

- FriendsOfBehat use a **isolated** kernel which run on `test` env with debug set to `false`, 
this way, the configuration keys that you set on `behat.yml?(.dist)` only occurs when the `test` env is set 
on your `.env` | `.env.local.php` files.

**_This point is important as the extension DOES NOT change the current application environment_**

- To run your app in `test` env, you must change the `APP_ENV` value in your `.env` file or 
generate the `env-related` file thanks to `composer dump-env test`.

**_The project which run under Docker can easily override the variable thanks to the `-e` option 
or the `environment` configuration key in `docker-compose.?(override).yml?.(dist)`_**

## Behat/SymfonyExtension (Symfony 3/4)

The main points still relevant here, but you need to install `Behat/MinkExtension` (this extension allows it) 
in order to allow Panther to be registered.

In both situations, the installation process described in `README.md` stay relevant and must be used to enable
the current extension.

## Critical notes

This extension DOES NOT extend `MinkContext`, you MUST define a custom context that extend it.
