<?php

namespace Behat\Sauce\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Processor\ProcessorInterface;
use Behat\Sauce\Context\SauceContext;

class SauceOnDemandProcessor implements ProcessorInterface {
    
    public function configure(Command $command) {
        $command
            ->addOption(
                '--browser', null, InputOption::VALUE_REQUIRED,
                'SauceLabs browser name.  Default is:  <comment>firefox</comment>'
            )
            ->addOption(
                '--browser-version', null, InputOption::VALUE_REQUIRED,
                'SauceLabs browser version.  Default is:  <comment>7</comment>'
            )
            ->addOption(
                '--os', null, InputOption::VALUE_REQUIRED,
                'SauceLabs operating system.  Default is:  <comment>Windows 2003</comment>'
            )
            ->addOption(
                '--local', null, InputOption::VALUE_NONE,
                'Run test locally instead.');
    }

    public function process(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output
    ) {
        $manager = $container->get('behat.format_manager');
        $manager->setFormatterClass(
            'pretty',
            'Behat\Sauce\Formatter\PrettyFormatter'
        );
        $manager->setFormatterClass(
            'html',
            'Behat\Sauce\Formatter\HtmlFormatter'
        );
        SauceContext::initialize(
            $input->getOption('browser'),
            $input->getOption('browser-version'),
            $input->getOption('os'),
            $input->getOption('local')
        );
    }
}