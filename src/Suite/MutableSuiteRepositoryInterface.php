<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite;

use Behat\Testwork\Suite\Suite_Repository;
interface Mutable_Suite_Repository_Interface extends Suite_Repository
{
    /** @return array<string, string[]> */
    public function get_suites_configurations(): array;
    public function remove_suite_configuration(string $name): void;
}