<?php

namespace As3\SymfonyData\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class provides core command logic such as loop iteration and start/end hooks.
 * Utilizes the IO trait for standard input/output and loop logic.
 *
 * Extending classes should override executeStart/executeEnd if needed. By default,
 * any methods matching static::COMMAND_PREFIX{*} will be run unless the `type` argument
 * is specified. If an extending class needs additional services, it MUST ensure that
 * all parent dependancies are injected and passed up the tree.
 *
 * Extending classes MUST also call parent::__construct if overwriting the constructor.
 *
 * @author  Josh Worden <jworden@southcomm.com>
 * @since   2016-02-23
 */
abstract class Command extends BaseCommand
{
    use Traits\IO;

    /**
     * Command prefix. Used to determine command to run.
     * @see self::execute()
     */
    const COMMAND_PREFIX = 'doCommand';

    /**
     * Handles configuration of this console command.
     * This method must be overriden in extending classes to change name or add arguments
     */
    protected function configure()
    {
        $this
            ->addArgument('action', InputArgument::OPTIONAL, sprintf('Action you want to execute (%s*)', static::COMMAND_PREFIX), 'all')
        ;
    }

    /**
     * Runs before import methods to allow global configuration by extending commands
     * @see self::$execute()
     */
    protected function executeStart()
    {
    }

    /**
     * Generic processor for import commands. Calls doCommand* methods unless passed a `action` in config.
     * Command methods matching static::COMMAND_PREFIX will be called in the order the are DEFINED in extending
     * classes or traits -- NOT alphabetically.
     *
     * @param   InputInterface  $input  Interface to retreive CLI parameters
     * @param   OutputInterface $output Interface to return output in a standard way
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (extension_loaded('newrelic')) {
            newrelic_background_job(true);
        }

        set_time_limit(0);

        $this->input = $input;
        $this->output = $output;

        $this->executeStart();

        if ($this->input->getArgument('action') == 'all') {
            $refl = new \ReflectionObject($this);
            foreach ($refl->getMethods() as $method) {
                if (strpos($method->getName(), static::COMMAND_PREFIX) !== false) {
                    $this->indent();
                    $this->{$method->getName()}();
                    $this->outdent();
                }
            }
        } else {
            $method = sprintf('%s%s', static::COMMAND_PREFIX, ucfirst($this->input->getArgument('action')));
            if (!method_exists($this, $method)) {
                if (method_exists($this, 'executeFailure')) {
                    $this->executeFailure();
                } else {
                    $this->writeln(sprintf('<error>Unable to start command %s (%s).</error>', $this->input->getArgument('action'), $method));
                    return false;
                }
            }
            $this->$method();
        }

        $this->executeEnd();

        $this->writeln('<info>Command complete.</info>', true, true);
    }

    /**
     * Runs after import methods to allow global cleanup by extending commands
     * @see self::$execute()
     */
    protected function executeEnd()
    {
    }
}
