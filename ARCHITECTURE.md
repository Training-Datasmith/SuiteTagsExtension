# Architecture: SuiteTagsExtension

## Purpose

Behat extension that adds tag-based filtering to test suites. It allows CLI `--tags` filtering to apply at the suite level, so only suites that match specified tags are executed.

## Directory Structure

```
src/
  ServiceContainer/
    Suite_Tags_Extension.php          Behat extension entry point; registers services
  Suite/
    Cli/
      Filtered_Tags_Suite_Controller.php  CLI controller that filters suites by tag
      Suite_Controller.php               Base suite controller integration
    Exception/
      Suite_Filtration_Exception.php    Thrown when tag filtering fails
    Mutable_Suite_Registry.php          Registry that allows modifying registered suites
    Mutable_Suite_Repository_Interface.php  Contract for mutable suite registries
features/                             Behat self-tests
tests/                                Unit/integration tests
```

## Key Design Decisions

- **Decorator pattern**: `Filtered_Tags_Suite_Controller` decorates the default Behat suite controller, adding tag evaluation before suite execution without modifying core Behat.
- **Mutable registry**: The standard Behat suite registry is immutable after boot. This extension introduces `Mutable_Suite_Registry` to allow removing non-matching suites at runtime.
- **Minimal surface**: The extension has one job (filter suites by CLI tags) and exposes only the services needed for that job.

## Extension Points

- Implement `Mutable_Suite_Repository_Interface` to use a different underlying suite storage mechanism.

## Dependency Flow

```
behat --tags=@smoke
  -> Suite_Tags_Extension registers Filtered_Tags_Suite_Controller
  -> Filtered_Tags_Suite_Controller::initialize()
    -> reads --tags CLI option
    -> iterates registered suites
    -> removes suites whose tags don't match
  -> remaining suites execute normally
```
