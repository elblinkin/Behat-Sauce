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

    const PASSED = 0;
    const PENDING = 2;
    const UNDEFINED = 3;
    const FAILED = 4;

    private static $mink;
    private static $browser = 'firefox';
    private static $version = '7';
    private static $os = 'Windows 2003';
    private static $local = false;

    private $parameters;
    private $session_id;

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
    public static function initialize(
        $browser,
        $version,
        $os,
        $local
    ) {
        if ($browser !==  null) {
            self::$browser = $browser;
        }
        if ($version !== null) {
            self::$version = $version;
        }
        if ($os !== null) {
            self::$os = $os;
        }
        if ($local) {
            self::$local = $local;
        }
    }

    /**
     * {@inheritDoc}
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

        $mink = $this->getMink();
        $host = $this->getHost();
        $port = $this->getPort();
        $username = $this->getUsername();
        $access_key = $this->getAccessKey();
        $browser = $this->getBrowser($scenario);

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

        $this->getMink()->setDefaultSessionName('selenium');
    }

    /**
     * Send the job result to SauceLabs.
     *
     * @AfterScenario
     */
    public function integrateJobResults($event) {
        if (self::$local) {
            return;
        }
        $result = $event->getResult();
        switch ($result) {
            case self::PASSED:
                $result = 'true';
                break;
            case self::PENDING:
            case self::UNDEFINED:
            case self::FAILED:
                $result = 'false';
                break;
            default:
                throw new UnexpectedValueException($result);
        };
        $this->modifySauceJob(
            sprintf(
                '{"passed": %s}',
                $result
            ),
            $this->getSessionId()
        );
        $this->session_id = null;
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

    private function getHost() {
        if (self::$local) {
            $local = $this->getParameter('local');
            if ($local !== null) {
                if (array_key_exists('host', $local)) {
                    return $local['host'];
                }
            }
            return '127.0.0.1';
        } else {
            $host = $this->getParameter('host');
            return ($host !== null) ? $host : 'ondemand.saucelabs.com';
        }
    }

    private function getPort() {
        if (self::$local) {
            $local = $this->getParameter('local');
            if ($local !== null) {
                if (array_key_exists('port', $local)) {
                    return $local['port'];
                }
            }
            return 4444;
         } else {
            $port = $this->getParameter('port');
            return ($port !== null) ? $port : '80';
         }
    }

    private function getUsername() {
        $username = $this->getParameter('username');
        if ($username === null) {
            throw new InvalidArgumentException('Must set "username" in behat.yml');
        }
        return $username;
    }

    private function getAccessKey() {
        $access_key = $this->getParameter('access_key');
        if ($access_key === null) {
            throw new InvalidArgumentException('Must set "access_key" in behat.yml');
        }
        return $access_key;
    }

    private function getBrowser($scenario) {
        if (self::$local) {
            return self::$browser;
        } else {
            return sprintf(
                '{
                    "username": "%s",
                    "access-key": "%s",
                    "browser": "%s",
                    "browser-version": "%s",
                    "os": "%s",
                    "name": "%s"
                }',
                $this->getUsername(),
                $this->getAccessKey(),
                self::$browser,
                self::$version,
                self::$os,
                $scenario->getTitle()
            );
        }
    }

    private function getSessionId() {
        if ($this->session_id === null) {
            $this->session_id = $this->getMink()
                ->getSession()
                ->getDriver()
                ->getBrowser()
                ->getEval('selenium.sessionId');
        }
        return $this->session_id;
    }

    private function getNoLoginJobLink($session_id) {
        $username = $this->getUsername();
        $access_key = $this->getAccessKey();
        $token = hash_hmac('md5', $session_id, $username.':'.$access_key);
        return sprintf(
            'https://saucelabs.com/jobs/%s?auth=%s',
            $session_id,
            $token
        );
    }

    private function modifySauceJob($payload, $session_id) {
        $username = $this->getUsername();
        $access_key = $this->getAccessKey();
        $ch = curl_init(
            sprintf(
                'https://%s:%s@saucelabs.com/rest/v1/%s/jobs/%s',
                $username,
                $access_key,
                $username,
                $session_id
            )
        );
        $length = strlen($payload);
        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $payload);
        rewind($fh);

        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_INFILE, $fh);
        curl_setopt($ch, CURLOPT_INFILESIZE, $length);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
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