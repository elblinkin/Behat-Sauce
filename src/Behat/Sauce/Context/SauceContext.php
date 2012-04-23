<?php

namespace Behat\Sauce\Context;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\SuiteEvent;
use Behat\Mink\Behat\Context\BaseMinkContext;
use Behat\Mink\Mink,
    Behat\Mink\Session,
    Behat\Mink\Driver\SeleniumDriver;
use Selenium\Client as SeleniumClient;
use InvalidArgumentException;

class SauceContext extends BaseMinkContext {

    private static $mink;

    private $parameters;

    public function __construct(
        array $parameters
    ) {
        $this->parameters = $parameters;
        if (!array_key_exists('show_cmd', $this->parameters)) {
            $this->parameters['show_cmd'] = $this->getDefaultShowCmd();
        }
        if (!array_key_exists('show_tmp_dir', $this->parameters)) {
            $this->parameters['show_tmp_dir'] = sys_get_temp_dir();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMink() {
        if (self::$mink === null) {
            self::$mink = new Mink();
        }
        return self::$mink;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($name) {
        return isset($this->parameters[$name])
            ? $this->parameters[$name]
            : null;
    }

    /**
     * Initializes Mink instance and sessions.
     * 
     * @param string|null $browser SauceLabs browser name
     * @param string|null $browser_version SauceLabs browser version
     * @param string|null $os SauceLabs operating system
     * @param bool $local Flag to indicate whether or not to use Sauce
     */
    public function initialize(
        $browser,
        $version,
        $os,
        $local
    ) {
        $mink = $this->getMink();
        if (!$mink->hasSession('selenium')) {
            if ($local) {
                $this->registerLocalSession($browser);
            } else {
                $this->registerSauceSession($browser, $version, $os);
            }
        }
    }

    private function registerLocalSession($browser) {
        $mink = $this->getMink();
        $host = '127.0.0.1';
        $port = 4444;
        $local = $this->getParameter('local');
        if ($local !== null) {
            if (array_key_exists('host', $local)) {
                $host = $local['host'];
            }
            if (array_key_exists('port', $local)) {
                $port = $local['port'];
            }
        }
        $browser = ($browser !== null) ? $browser : 'firefox';
        $mink->registerSession(
            'selenium',
            new Session(
                new SeleniumDriver(
                    $browser,
                    $this->getParameter('base_url'),
                    new SeleniumClient($host, $port)
                )
            )
        );
    }

    private function registerSauceSession(
        $browser, 
        $version,
        $os
    ) {
        $mink = $this->getMink();
        $host = $this->getParameter('host');
        $port = $this->getParameter('port');
        $username = $this->getParameter('username');
        if ($username === null) {
            throw new InvalidArgumentException('Must set "username" in behat.yml');
        }
        $access_key = $this->getParameter('access_key');
        if ($access_key === null) {
            throw new InvalidArgumentException('Must set "access_key" in behat.yml');
        }
        $name = $this->getParameter('name');
        $browser = sprintf(
            '{
                "username": "%s",
                "access-key": "%s",
                "browser": "%s",
                "browser-version": "%s",
                "os": "%s",
                "name": "%s"
            }',
            $username,
            $access_key,
            ($browser !== null) ? $browser : 'firefox',
            ($version !== null) ? $version : '7',
            ($os !== null) ? $os : 'Windows 2003',
            ($name !== null) ? $name : 'BeHat-Sauce Test'
        );

        $mink->registerSession(
            'selenium',
            new Session(
                new SeleniumDriver(
                    $browser,
                    $this->getParameter('base_url'),
                    new SeleniumClient(
                        isset($host) ? $host : 'ondemand.saucelabs.com',
                        isset($port) ? $port : '80'
                    )
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     * @BeforeScenario
     */
    public function prepareMinkSessions($event) {
        $scenario = $event instanceof ScenarioEvent 
            ? $event->getScenario()
            : $event->getOutline();
        
        if ($scenario->hasTag('insulated')) {
            $this->getMink()->stopSessions();
        } else {
            $this->getMink()->resetSessions();
        }

        $this->getMink()->setDefaultSessionName('selenium');
    }

    /**
     * Stops started Mink sessions.
     *
     * @AfterSuite
     */
    public static function stopMinkSessions() {
        self::$mink->stopSessions();
        self::$mink = null;
    }

    /**
     * Returns default show command.
     *
     * @return  string
     */
    private function getDefaultShowCmd() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'explorer.exe $s';
        }

        switch(PHP_OS) {
            case 'Darwin':
                return 'open %s';
            case 'Linux':
            case 'FreeBSD':
                return 'xdg-open %s';
        }

        return null;
    }
}