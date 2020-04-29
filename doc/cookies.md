# Cookies

Taste good but not only, cookies are a fundamental part of our daily tests/developments, 
time to see how to set and access it using panther-extension!

## Set a new cookie

First, let's imagine the use case where you need to bypass a Symfony authentication: 

```php
<?php

namespace Tests\Behat\Contexts;

use Behat\MinkExtension\Context\MinkContext;
use PantherExtension\Driver\PantherDriver;

final class FooContext extends MinkContext
{
    // ...

    public function __construct(
        UserRepository $repository, 
        SessionInterface $session
    ) {
        $this->repository = $repository;
        $this->session = $session;
    }
    
    public function iSignInAs(string $username): void 
    {
        // Maybe a repository call?  
        $user = $this->userRepository->findOneBy(['username' => $username]);
        
        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';
        
        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $this->session->set('_security_'.$firewallContext, serialize($token));
        $this->session->save();    

        $driver = $this->getMink()->getSession()->getDriver();
        
        if (!$driver instanceof PantherDriver) {
            return;
        }   
        
        $driver->setCookie($this->session->getName(), $this->session->getId());
    }
}
```

**_PS: This code block comes from the [Symfony documentation](https://symfony.com/doc/current/testing/http_authentication.html#creating-the-authentication-token)_**

So, what's different here? 

Not a lot of things except that we use the `PantherDriver` shortcut which internally 
call the `RemoteWebDriver` capability to set a new cookie, the cookie is sent to the browser, 
and that's all, pretty simple isn't it?

## Access a cookie

Why not? Right from the oven!

```php
<?php

namespace Tests\Behat\Contexts;

use Behat\MinkExtension\Context\MinkContext;
use PantherExtension\Driver\PantherDriver;

final class FooContext extends MinkContext
{
    // ...
    
    public function aCookieMustBeSet(string $cookie): void // Or bool maybe?
    {
        $driver = $this->getMink()->getSession()->getDriver();
        
        if (!$driver instanceof PantherDriver) {
            return;
        }   
        
        $cookie = $driver->getCookie($cookie);
        
        // ...
    }
}
```

Small precision here, the `$cookie` is not the one from Symfony but the one from `php-webdriver`, for more information, 
please refer to the related [class](https://github.com/php-webdriver/php-webdriver/blob/master/lib/Cookie.php).
