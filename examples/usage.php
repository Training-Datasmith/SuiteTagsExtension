<?php

declare(strict_types=1);

/**
 * SuiteTagsExtension — Behat suite filtering by tags example.
 *
 * Shows how to register and configure the extension in a Behat project.
 *
 * --- behat.yml ---
 *
 * default:
 *   extensions:
 *     Zalas\Behat\SuiteTagsExtension\ServiceContainer\SuiteTagsExtension: ~
 *
 *   suites:
 *     smoke:
 *       tags: '@smoke'
 *       paths: ['%paths.base%/features/smoke']
 *       contexts: [App\Tests\Behat\Context\SmokeSuiteContext]
 *
 *     full:
 *       tags: '@full'
 *       paths: ['%paths.base%/features']
 *       contexts: [App\Tests\Behat\Context\FullSuiteContext]
 *
 * --- Run only the smoke suite ---
 *
 * vendor/bin/behat --tags=@smoke
 *
 * This will execute only the suites whose configured tag matches '@smoke'.
 * Suites without a matching tag are excluded from the run.
 *
 * --- Run all suites (default, no tag filtering) ---
 *
 * vendor/bin/behat
 *
 * --- Feature file example ---
 *
 * @smoke
 * Feature: User login
 *   Scenario: Successful login
 *     Given I am on the login page
 *     When I fill in the credentials
 *     Then I should be logged in
 */

echo 'SuiteTagsExtension requires a Behat project.' . PHP_EOL;
echo 'See the docblock above for configuration patterns.' . PHP_EOL;
