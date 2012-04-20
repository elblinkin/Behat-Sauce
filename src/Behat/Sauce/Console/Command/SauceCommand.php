<?php

namespace Behat\Sauce\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Command\BaseCommand;
use Behat\Behat\Console\Input\InputDefinition;
use Behat\Behat\Console\Processor as Behat;
use Behat\Sauce\Console\Processor as Sauce;

/**
 * Behat-Sauce concole command.
 *
 * @author LB Denker <lb@elblinkin.info>
 */
class SauceCommand extends BaseCommand {

    /** @var Symfony\Component\DependencyInjection\ContainerBuilder */
	private $container;

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->container = new ContainerBuilder();
		$this
		    ->setName('behat-sauce')
		    ->setDefinition(new InputDefinition)
		    ->setProcessors(
		    	array(
		    	    new Behat\ContainerProcessor(),
		    	    new Behat\LocatorProcessor(),
			    	new Sauce\InitProcessor(),
			    	new Sauce\SauceProcessor(),
			    	new Behat\ContextProcessor(),
			    	new Behat\FormatProcessor(),
			    	new Behat\HelpProcessor(),
			    	new Behat\GherkinProcessor(),
			    	new Behat\RunProcessor(),
		        )
		    )
		    ->addArgument(
		    	'features',
		    	InputArgument::OPTIONAL,
		    	"Feature(s) to run. Could be:\n"
                    . "- a dir <comment>(features/)</comment>\n"
                    . "- a feature <comment>(*.feature)</comment>\n"
                    . "- a scenario at specific line <comment>(*.feature:10)</comment>.\n"
                    . "- all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>.\n"
                    . "- all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>."
            )
            ->configureProcessors();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getContainer() {
		return $this->container;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(
		InputInterface $input,
		OutputInterface $output
	) {
        $this->getContainer()->get('behat.runner')->runSuite();
	}
}