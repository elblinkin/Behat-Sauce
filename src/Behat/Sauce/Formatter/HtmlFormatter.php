<?php

namespace Behat\Sauce\Formatter;

use Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Formatter as Behat;
use Behat\Gherkin\Node\AbstractScenarioNode;
use Behat\Sauce\Context\SauceContext;

class HtmlFormatter extends Behat\HtmlFormatter {

    private $sauce_link;

    /**
     * {@inheritDoc}
     */
	public function beforeScenario(ScenarioEvent $event) {
        $context = $event->getContext();
        if ($context instanceof SauceContext) {
        	if (!$context->isLocal()) {
                $this->sauce_link = $context->getNoLoginJobLink();
        	}
        }
        parent::beforeScenario($event);
        $this->sauce_link = null;
	}

    /**
     * {@inheritdoc}
     */
    protected function printScenarioName(AbstractScenarioNode $scenario) {
        $this->writeln('<h3>');
        $this->writeln('<span class="keyword">' . $scenario->getKeyword() . ': </span>');
        if ($scenario->getTitle()) {
            if ($this->sauce_link !== null) {
                $this->writeln(
                    sprintf(
                        '<span class="title"><a href="%s">%s</a></span>',
                        $this->sauce_link,
                        $scenario->getTitle()
                    )
                );
            } else {
                $this->writeln('<span class="title">' . $scenario->getTitle() . '</span>');
            }
        }
        $this->printScenarioPath($scenario);
        $this->writeln('</h3>');

        $this->writeln('<ol>');
    }

    /**
     * {@inheritDoc}
     */
	public function afterOutlineExample(OutlineExampleEvent $event) {
        $context = $event->getContext();
        if ($context instanceof SauceContext) {
        	if (!$context->isLocal()) {
                $this->sauce_link = $context->getNoLoginJobLink();
            }
        }
        parent::afterOutlineExample($event);
        $this->sauce_link = null;
	}

    /**
     * {@inheritDoc}
     */
    protected function printColorizedTableRow($row, $color) {
        $this->writeln('<tr class="' . $color . '">');

        foreach ($row as $column) {
            if ($this->sauce_link !== null
                && $this->isOutlineHeaderPrinted
            ) {
                $this->writeln(
                    sprintf(
                        '<td><a href="%s">%s</a></td>',
                        $this->sauce_link,
                        $column
                    )
                );
            } else {
                $this->writeln('<td>' . $column . '</td>');
            }
        }

        $this->writeln('</tr>');
    }
}