<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Service_Container;

use Behat\Testwork\Cli\Service_Container\Cli_Extension;
use Behat\Testwork\Service_Container\Extension;
use Behat\Testwork\Suite\Service_Container\Suite_Extension;
use Sylius_Labs\Suite_Tags_Extension\Suite\Cli\Filtered_Tags_Suite_Controller;
use Sylius_Labs\Suite_Tags_Extension\Suite\Cli\Suite_Controller;
use Sylius_Labs\Suite_Tags_Extension\Suite\Mutable_Suite_Registry;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Reference;
final class Suite_Tags_Extension implements Extension
{
    public function get_config_key(): string
    {
        return 'sylius_labs_suite_tags';
    }
    public function load(Container_Builder $container, array $config): void
    {
        $this->overwrite_suite_registry($container);
        $this->overwrite_suite_controller($container);
        $controller_definition = new Definition(Filtered_Tags_Suite_Controller::class, [new Reference(Suite_Extension::REGISTRY_ID)]);
        $controller_definition->add_tag(Cli_Extension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->set_definition(Cli_Extension::CONTROLLER_TAG . '.filtered_tags_suite', $controller_definition);
    }
    private function overwrite_suite_registry(Container_Builder $container): void
    {
        $definition = new Definition(Mutable_Suite_Registry::class);
        $container->set_definition(Suite_Extension::REGISTRY_ID, $definition);
    }
    private function overwrite_suite_controller(Container_Builder $container): void
    {
        $container->get_definition(Cli_Extension::CONTROLLER_TAG . '.suite')->set_class(Suite_Controller::class);
    }
}