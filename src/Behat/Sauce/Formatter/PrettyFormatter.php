<?php

namespace Behat\Sauce\Formatter;

use Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Formatter as Behat;
use Behat\Sauce\Context\SauceContext;

class PrettyFormatter extends Behat\PrettyFormatter {

    /**
     * {@inheritDoc}
     */
	public function afterScenario(ScenarioEvent $event) {
		parent::afterScenario($event);
        $context = $event->getContext();
        if ($context instanceof SauceContext) {
        	if ($context->isLocal()) {
        		return;
        	}
            $this->writeln(
            	sprintf(
            		'  %s: %s',
            		$this->translate('Sauce On-Demand Job'),
            		$context->getNoLoginJobLink()
            	)
            );
            $this->writeln('');
        }
	}

    /**
     * {@inheritDoc}
     */
	public function afterOutlineExample(OutlineExampleEvent $event) {
        parent::afterOutlineExample($event);
        $context = $event->getContext();
        if ($context instanceof SauceContext) {
        	if ($context->isLocal()) {
        		return;
        	}
            $this->writeln(
            	sprintf(
            		'        %s: %s',
            		$this->translate('Sauce On-Demand Job'),
            		$context->getNoLoginJobLink()
            	)
            );
            $this->writeln();
        }
	}
}