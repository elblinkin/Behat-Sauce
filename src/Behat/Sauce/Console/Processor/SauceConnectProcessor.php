<?php

namespace Behat\Sauce\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Processor\ProcessorInterface;

class SauceConnectProcessor implements ProcessorInterface {
    
    public function configure(Command $command) {
        $command
            ->addOption(
                '--tunnel', null, InputOption::VALUE_NONE,
                "Start <commen>Sauce Connect</comment>.\n"
            );
    }

    public function process(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output
    ) {
        if ($input->getOption('tunnel')) {
            $parameters = $container
                ->get('behat.context_dispatcher')
                ->getContextParameters();
            if (!array_key_exists('username', $parameters)) {
                throw new InvalidArgumentException(
                    'Must set "username" in behat.yml'
                );
            }
            if (!array_key_exists('access_key', $parameters)) {
                throw new InvalidArgumentException(
                    'Must set "access_key" in behat.yml'
                );
            }
            passthru(
                sprintf(
                    'java -jar %s %s %s',
                     __DIR__ . '/../../../../../vendor/Sauce-Connect/Sauce-Connect.jar',
                    $parameters['username'],
                    $parameters['access_key']
                )
            );
            exit(0);
        }
    }
}