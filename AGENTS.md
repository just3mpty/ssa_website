# Agent Guidelines for SSA Website

## Build/Lint/Test Commands
- `make test` - Run all tests with PHPUnit
- `vendor/bin/phpunit --testdox` - Run tests with detailed output
- `make phpstan` - Run static analysis (PHPStan)
- `vendor/bin/phpstan analyse app src --level=6` - Run PHPStan with level 6
- `composer dump` - Regenerate autoloader
- `make setup-dev` - Install dependencies in container

## Code Style Guidelines
- **PHP 8.2+** with strict types (`declare(strict_types=1)`)
- **PSR-12** coding standard with PHP-CS-Fixer
- **Namespaces**: `Capsule\` for framework, `App\` for application code
- **Imports**: Group by framework, then application imports
- **Types**: Use type hints and return types everywhere
- **Naming**: `camelCase` for methods/vars, `PascalCase` for classes
- **Error handling**: Use HTTP status codes and proper error responses
- **Security**: Input validation, CSRF protection, secure headers
- **Documentation**: Use PHPDoc for complex methods

## Testing
- Tests in `tests/` directory
- Use `TestCase` base class with PHPUnit attributes
- Follow Arrange-Act-Assert pattern
- Test both success and error cases

## Project Structure
- `src/` - Framework code (Capsule namespace)
- `app/` - Application code (App namespace) 
- `templates/` - PHP view templates
- `public/` - Web root with assets
- `config/` - Configuration files