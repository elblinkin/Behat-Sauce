<?php

namespace Behat\Sauce\Context;

use Behat\Mink\Behat\Context\BaseMinkContext;

class SauceContext extends BaseMinkContext {

	private static $mink;
	private $parameters;

	public function __construct(
		array $parameters
	) {
		$this->parameters = $parameters;
		if (!array_contains_key('show_cmd', $this->parameters)) {
            $this->parameters['show_cmd'] = $this->getDefaultShowCmd();
		}
		if (!array_contains_key('show_tmp_dir', $this->parameters)) {
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
    	return $this->parameters[$name];
    }

    /**
     * Initializes Mink instance and sessions.
     * 
     * @param host SauceLabs host name
     * @param port SauceLabs port name
     * @param browser SauceLabs browser name
     * @param browser_version SauceLabs browser version
     * @param os SauceLabs operating system
     */
    public function initialize(
    	$browser,
    	$version,
    	$os
    ) {
    	$mink = $this->getMink();
        if (!$mink->hasSession('selenium')) {
        	$host = $this->getParameter('host');
        	$port = $this->getParameter('port');
        	$mink->registerSession(
        	    'selenium',
        	    array(
        	    	'username' => $this->getParameter('username'),
			        'access-key' => $this->getParameter('access_key'),
			        'browser' => isset($browser) ? $browser : 'firefox',
			        'browser-version' => isset($browser_version) ? $browser_version : '7',
			        'os' => isset($os) ? $os : 'Windows 2003',
        	    ),
        	    $this->getParameter('base_url'),
        	    new SeleniumClient(
        	    	isset($host) ? $host : 'ondemand.saucelabs.com',
        	    	isset($port) ? $port : '80'
        	    )
            );
        }
    }

    /**
     * {@inheritDoc}
     * @BeforeSuite
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
        $this->getMink()->stopSessions();
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