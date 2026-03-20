<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\Cli\Suite_Controller as BaseSuiteController;
use Behat\Testwork\Suite\Exception\Suite_Not_Found_Exception;
use Sylius_Labs\Suite_Tags_Extension\Suite\Mutable_Suite_Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/**
 * Overridden to allow passing a different suite registry.
 *
 * @see BaseSuiteController
 */
final class Suite_Controller implements Controller
{
    private Mutable_Suite_Registry $registry;
    /** @var array<string, array{0: string|null, 1: string[]}> */
    private array $suite_configurations;
    /** @param array<string, array{0: string|null, 1: string[]}> $suiteConfigurations */
    public function __construct(Mutable_Suite_Registry $registry, array $suite_configurations = [])
    {
        $this->registry = $registry;
        $this->suite_configurations = $suite_configurations;
    }
    public function configure(Command $command): void
    {
        $command->add_option('--suite', '-s', Input_Option::VALUE_REQUIRED, 'Only execute a specific suite.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        /** @var string|null $exerciseSuiteName */
        $exercise_suite_name = $input->get_option('suite');
        if (!empty($exercise_suite_name) && !isset($this->suite_configurations[$exercise_suite_name])) {
            throw new Suite_Not_Found_Exception(sprintf('`%s` suite is not found or has not been properly registered.', $exercise_suite_name), $exercise_suite_name);
        }
        /**
         * @var string $name
         * @var array{type: string|null, settings: array<string, string[]>} $config
         */
        foreach ($this->suite_configurations as $name => $config) {
            if (null !== $exercise_suite_name && $exercise_suite_name !== $name) {
                continue;
            }
            $this->registry->register_suite_configuration($name, $config['type'], $config['settings']);
        }
        return null;
    }
}