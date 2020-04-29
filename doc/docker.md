# Docker

If you use Docker | Docker-Compose, please refer to [Symfony Panther](https://github.com/symfony/panther#docker-integration) recommandations.

Given you use a valid setup, here's the change that you could apply: 

```yaml
# ...

  extensions:
    PantherExtension\Extension\PantherExtension: ~
    Behat\MinkExtension:
      browser_name: chrome
      base_url: http://nameOfTheServerName # Ex: http://nginx or http://project.docker if a proxy is used
      # ...
```
