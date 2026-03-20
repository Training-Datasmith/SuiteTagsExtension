<?php

declare (strict_types=1);
namespace Sylius_Labs\Suite_Tags_Extension\Suite\Cli;

use Behat\Gherkin\Filter\Tag_Filter;
use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Suite\Cli\Suite_Controller;
use Sylius_Labs\Suite_Tags_Extension\Suite\Exception\Suite_Filtration_Exception;
use Sylius_Labs\Suite_Tags_Extension\Suite\Mutable_Suite_Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
/** @see SuiteController */
final class Filtered_Tags_Suite_Controller implements Controller
{
    private Mutable_Suite_Registry $registry;
    public function __construct(Mutable_Suite_Registry $registry)
    {
        $this->registry = $registry;
    }
    public function configure(Command $command): void
    {
        $command->add_option('--suite-tags', null, Input_Option::VALUE_REQUIRED | Input_Option::VALUE_IS_ARRAY, 'Filtrate used suites based on their configured tags.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): ?int
    {
        /** @var string[] $tags */
        $tags = $input->get_option('suite-tags');
        if (empty($tags) || !isset($tags[0]) || empty(trim($tags[0]))) {
            return null;
        }
        $this->process_suites_isolation($tags[0]);
        return null;
    }
    private function process_suites_isolation(string $input_tags): void
    {
        /** @var array<string, string[]> $config */
        foreach ($this->registry->get_suites_configurations() as $name => [$type, $config]) {
            if (isset($config['filters']['tags'])) {
                $suite_tags = array_map(fn(string $tag): string => $this->normalize_tag($tag), explode('&&', $config['filters']['tags']));
                if (!$this->is_tags_match_condition($suite_tags, $input_tags)) {
                    $this->registry->remove_suite_configuration($name);
                }
            }
        }
        if ([] === $this->registry->get_suites_configurations()) {
            throw new Suite_Filtration_Exception(sprintf('No suites left using suite tags: %s.', $input_tags));
        }
    }
    /**
     * @see TagFilter::isTagsMatchCondition()
     *
     * @param string[] $suiteTags
     */
    private function is_tags_match_condition(array $suite_tags, string $input_tags_string): bool
    {
        $satisfies = true;
        foreach (explode('&&', $input_tags_string) as $and_tags) {
            $satisfies_comma = false;
            foreach (explode(',', $and_tags) as $tag) {
                $tag = $this->normalize_tag($tag);
                if ('~' === $tag[0]) {
                    $tag = mb_substr($tag, 1, mb_strlen($tag, 'utf8') - 1, 'utf8');
                    $satisfies_comma = !in_array($tag, $suite_tags, true) || $satisfies_comma;
                } else {
                    $satisfies_comma = in_array($tag, $suite_tags, true) || $satisfies_comma;
                }
            }
            $satisfies = $satisfies_comma && $satisfies;
        }
        return $satisfies;
    }
    private function normalize_tag(string $tag): string
    {
        return str_replace('@', '', trim($tag));
    }
}