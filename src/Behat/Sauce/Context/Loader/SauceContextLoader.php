<?php

namespace Behat\Sauce\Context\Loader;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Loader\ContextLoaderInterface;

use Behat\Sauce\Context\SauceContext;

class SauceContextLoader implements ContextLoaderInterface {

    private $browser;
    private $browser_version;
    private $os;
    private $local;

    public function __construct(
        $browser,
        $browser_version,
        $os,
        $local
    ) {
        $this->browser = $browser;
        $this->browser_version = $browser_version;
        $this->os = $os;
        $this->local = $local;
    }

    public function supports(ContextInterface $context) {
        return $context instanceof SauceContext;
    }

    public function load(ContextInterface $context) {
        $context->initialize(
            $this->browser,
            $this->browser_version,
            $this->os,
            $this->local
        );
    }
}