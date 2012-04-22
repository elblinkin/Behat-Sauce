<?php

namespace Behat\Sauce\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\PathLocator;
use Behat\Behat\Console\Processor as Behat;

/**
 * Init operation processor.
 *
 * @author LB Denker <lb@elblinkin.info>
 */
class InitProcessor
extends Behat\InitProcessor {

    /**
     * {@inheritDoc}
     */
    protected function initFeaturesDirectoryStructure(
        PathLocator $locator,
        OutputInterface $output
    ) {
        parent::initFeaturesDirectoryStructure($locator, $output);
        $base_path = realpath($locator->getWorkPath()) . DIRECTORY_SEPARATOR;
        $config_path = $base_path . 'config';
        if (!is_dir($config_path)) {
            mkdir($config_path, 0777, true);
            $output->writeln(
                sprintf(
                    '<info>+d</info> %s <comment>- edit you config settings here</comment>',
                    str_replace($base_path, '', $config_path)
                )
            );

            file_put_contents(
                $config_path . DIRECTORY_SEPARATOR . 'behat.yml',
                $this->getConfigSkelet()
            );
            $output->writeln(
                sprintf(
                    '<info>+f</info> %sbehat.yml <comment>- place your feature related code here</comment>',
                    str_replace($base_path . DIRECTORY_SEPARATOR, '', $config_path)
                )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getFeatureContextSkelet() {
        return <<<'PHP'
<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Sauce\Context\SauceContext;

/**
 * Features context.
 */
class FeatureContext extends SauceContext {
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters) {
        parent::__construct($parameters);
    }

//
// Place your definition and hook methods here:
//
//    /**
//     * @Given /^I have done something with "([^"]*)"$/
//     */
//    public function iHaveDoneSomethingWith($argument) {
//        doSomethingWith($argument);
//    }
//
}

PHP;
    }

    protected function getConfigSkelet() {
        return <<<'YAML'
default:
    paths:
        features: 'features'
        bootstrap: '%behat.paths.features%/bootstrap'
    context:
        class: 'FeatureContext'
        parameters:
            username: 'sauce-user'
            access_key: 'access-key'
            base_url: 'http://localhost/'
            name: 'optional-test-name'
YAML;
    }
}