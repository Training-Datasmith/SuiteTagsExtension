<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite;

use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Exception\Suite_Generation_Exception;
use Behat\Testwork\Suite\Generator\Suite_Generator;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\Suite_Registry;
/** @see SuiteRegistry */
final class Mutable_Suite_Registry implements Mutable_Suite_Repository_Interface
{
    private bool $suites_generated = false;
    /** @var SuiteGenerator[] */
    private array $generators = [];
    /** @var array<string, array{0: string|null, 1: array<string, string[]>}> */
    private array $suite_configurations = [];
    /** @var Suite[] */
    private array $suites = [];
    public function register_suite_generator(Suite_Generator $generator): void
    {
        $this->generators[] = $generator;
        $this->suites_generated = false;
    }
    /** @param array<string, string[]> $settings */
    public function register_suite_configuration(string $name, ?string $type, array $settings): void
    {
        if (isset($this->suite_configurations[$name])) {
            throw new Suite_Configuration_Exception(sprintf('Suite configuration for a suite "%s" is already registered.', $name), $name);
        }
        $this->suite_configurations[$name] = [$type, $settings];
        $this->suites_generated = false;
    }
    /** @return array<string, array{0: string|null, 1: array<string, string[]>}> */
    public function get_suites_configurations(): array
    {
        return $this->suite_configurations;
    }
    public function remove_suite_configuration(string $name): void
    {
        unset($this->suite_configurations[$name]);
        $this->suites_generated = false;
    }
    /** @return Suite[] */
    public function get_suites(): array
    {
        if ($this->suites_generated) {
            return $this->suites;
        }
        $this->suites = [];
        foreach ($this->suite_configurations as $name => $configuration) {
            [$type, $settings] = $configuration;
            $this->suites[] = $this->generate_suite($name, $type, $settings);
        }
        $this->suites_generated = true;
        return $this->suites;
    }
    /**
     * @param array<string, string[]> $settings
     *
     * @throws SuiteGenerationException
     */
    private function generate_suite(string $name, ?string $type, array $settings): Suite
    {
        foreach ($this->generators as $generator) {
            if (!$generator->supports_type_and_settings($type, $settings)) {
                continue;
            }
            return $generator->generate_suite($name, $settings);
        }
        throw new Suite_Generation_Exception(sprintf('Can not find suite generator for a suite `%s` of type `%s`.', $name, $type), $name);
    }
}