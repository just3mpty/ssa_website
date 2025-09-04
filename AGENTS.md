# AGENTS.md - SSA Website Project

## Build/Test Commands
- **Install dependencies**: `composer install` or `make setup`
- **Run all tests**: `vendor/bin/phpunit --testdox` or `make test`
- **Run single test**: `vendor/bin/phpunit --filter "testMethodName" tests/TestFile.php`
- **Static analysis**: `vendor/bin/phpstan analyse lib src --level=6` or `make phpstan`
- **Code formatting**: `vendor/bin/php-cs-fixer fix`
- **Full QA**: `composer run-script qa` (dump, cs, stan, test)

## Code Style Guidelines
- **PHP Version**: >=8.2, use strict types (`declare(strict_types=1);`)
- **Namespaces**: PSR-4 autoloading - `CapsuleLib\` (lib/), `App\` (src/), `Tests\` (tests/)
- **Classes**: Use `final` for concrete classes, PascalCase naming
- **Properties**: Private with constructor promotion where applicable
- **Methods**: camelCase, explicit return types, PHPDoc for complex params
- **Imports**: Group by type (PHP core, vendor, project), alphabetical order
- **Error Handling**: Throw typed exceptions with descriptive messages
- **Dependencies**: Inject via constructor, use DIContainer for service registration
- **Testing**: PHPUnit with attributes (`#[CoversNothing]`), descriptive test names
- **Comments**: French allowed for business logic, English for technical aspects