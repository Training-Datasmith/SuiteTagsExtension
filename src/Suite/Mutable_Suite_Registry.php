<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite;

use Behat\Testwork\Suite\Exception\Suite_Configuration_Exception;
use Behat\Testwork\Suite\Exception\Suite_Generation_Exception;
use Behat\Testwork\Suite\Generator\Suite_Generator;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\Suite_Registry;
/**
 * Mutable registry of Behat suites.
 *
 * Unlike the standard Behat SuiteRegistry, this implementation allows removing
 * suite configurations at runtime so that tag-based filtering can exclude
 * suites that do not match the requested tags.
 *
 * @see SuiteRegistry
 */
final class Mutable_Suite_Registry implements Mutable_Suite_Repository_Interface
{
    /** Whether suites have been generated from the current configuration. */
    private bool $suites_generated = false;

    /**
     * Registered suite generators, tried in order until one supports the suite type.
     *
     * @var Suite_Generator[]
     */
    private array $generators = [];

    /**
     * Suite configurations keyed by suite name. Each entry is a tuple of [type, settings].
     *
     * @var array<string, array{0: string|null, 1: array<string, string[]>}>
     */
    private array $suite_configurations = [];

    /**
     * Generated suite instances, cached until configurations change.
     *
     * @var Suite[]
     */
    private array $suites = [];

    /**
     * Registers a suite generator.
     *
     * A generator is responsible for producing Suite instances for suites of a
     * particular type. Generators are tried in registration order.
     *
     * @param Suite_Generator $generator The generator to add
     *
     * @return void
     */
    public function register_suite_generator(Suite_Generator $generator): void
    {
        $this->generators[] = $generator;
        $this->suites_generated = false;
    }

    /**
     * Registers a named suite configuration.
     *
     * @param string               $name     Unique name for the suite
     * @param string|null          $type     Suite type (e.g. 'gherkin'), or null for default
     * @param array<string, string[]> $settings Suite settings (paths, contexts, tags, etc.)
     *
     * @return void
     *
     * @throws Suite_Configuration_Exception If a suite with the same name is already registered
     */
    public function register_suite_configuration(string $name, ?string $type, array $settings): void
    {
        if (isset($this->suite_configurations[$name])) {
            throw new Suite_Configuration_Exception(sprintf('Suite configuration for a suite "%s" is already registered.', $name), $name);
        }
        $this->suite_configurations[$name] = [$type, $settings];
        $this->suites_generated = false;
    }

    /**
     * Returns all registered suite configurations.
     *
     * @return array<string, array{0: string|null, 1: array<string, string[]>}> Suite configurations keyed by name
     */
    public function get_suites_configurations(): array
    {
        return $this->suite_configurations;
    }

    /**
     * Removes a suite configuration by name.
     *
     * This is the key method used by the tag-filtering controller to exclude
     * suites that do not match the requested CLI tags.
     *
     * @param string $name Name of the suite configuration to remove
     *
     * @return void
     */
    public function remove_suite_configuration(string $name): void
    {
        unset($this->suite_configurations[$name]);
        $this->suites_generated = false;
    }

    /**
     * Returns all Suite instances generated from the current configurations.
     *
     * Results are cached; the cache is invalidated whenever configurations change.
     *
     * @return Suite[] Generated suite instances
     */
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
     * Finds a generator that supports the given type and settings, and produces a Suite.
     *
     * @param string                  $name     Suite name
     * @param string|null             $type     Suite type or null for default
     * @param array<string, string[]> $settings Suite settings
     *
     * @return Suite The generated suite instance
     *
     * @throws Suite_Generation_Exception If no registered generator supports the given type and settings
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