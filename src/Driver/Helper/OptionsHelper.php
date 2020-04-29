<?php

declare(strict_types=1);

namespace PantherExtension\Driver\Helper;

use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverWindow;

/**
 * @author Guillaume LOULIER <contact@guillaumeloulier.fr>
 */
final class OptionsHelper
{
    /**
     * @var WebDriver
     */
    private $webDriver;

    public function __construct(WebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
    }

    public function resizeWindow(?string $name, int $width, int $height): void
    {
        $driver = null !== $name ? $this->webDriver->switchTo()->window($name) : $this->webDriver;

        $options = $driver->manage();

        $options->window()->setSize(new WebDriverDimension($width, $height));
    }

    public function maximizeWindow(?string $name): void
    {
        $driver = null !== $name ? $this->webDriver->switchTo()->window($name) : $this->webDriver;

        $options = $driver->manage();

        $options->window()->maximize();
    }

    /**
     * @throws UnsupportedOperationException {@see WebDriverWindow::fullscreen()}
     */
    public function moveToFullScreen(): void
    {
        $options = $this->webDriver->manage();

        $options->window()->fullscreen();
    }
}
