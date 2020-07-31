<?php

namespace Benzine\Tests;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;

abstract class SeleniumTestCase extends BaseTestCase
{
    /** @var RemoteWebDriver */
    protected static $webDriver;

    protected static $screenshotsDir;

    protected static $screenshotIndex = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $capabilities = [WebDriverCapabilityType::BROWSER_NAME => 'chrome'];
        self::$webDriver = RemoteWebDriver::create(
            'http://'.$_SERVER['SELENIUM_HOST'].':'.$_SERVER['SELENIUM_PORT'].'/wd/hub',
            $capabilities,
            60000,
            60000
        );

        self::$webDriver->manage()->timeouts()->implicitlyWait(3);

        self::$screenshotsDir = APP_ROOT.'/build/Screenshots/'.date('Y-m-d H-i-s').'/';
        if (!file_exists(self::$screenshotsDir)) {
            mkdir(self::$screenshotsDir, 0777, true);
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$webDriver->close();
        parent::tearDownAfterClass();
    }

    protected function takeScreenshot($name): void
    {
        self::$webDriver->takeScreenshot(self::$screenshotsDir.self::$screenshotIndex."_{$name}.jpg");
        ++self::$screenshotIndex;
    }
}
