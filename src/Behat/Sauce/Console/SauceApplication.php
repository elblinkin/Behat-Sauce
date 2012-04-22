<?php

namespace Behat\Sauce\Console;

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption;
    
use Behat\Sauce\Console\Command\SauceCommand;

/**
 * Behat-Sauce console application.
 *
 * @author LB Denker <lb@elblinkin.info>
 */
class SauceApplication extends Application {
    
    /**
     * {@inheritDoc}
     */
    public function __construct($version) {
        parent::__construct('Behat-Sauce', $version);
        $this->add(new SauceCommand());
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition() {
        return new InputDefinition(
            array(
                new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
                new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of exceptions.'),
                new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getCommandName(InputInterface $input) {
        return 'behat-sauce';
    }
}