<?php

class SauceProcessor implements ProcessorInterface {
	
	public function configuration(Command $command) {
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
	        );
	}

	public function process(
		ContainerInterface $container,
		InputInterface $input,
		OutputInterface $output
	) {
        $reader = $container->get('behat.context_reader');
        $reader->addLoader(
        	new SauceContextLoader(
        		$input->getOption('browser'),
        		$input->getOption('browser-version'),
        		$input->getOption('os')
        	)
        );
	}
}