# Contributing to RationalNumber

Thank you for your interest in contributing to RationalNumber! This document provides guidelines for contributing to the project.

## Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/jeremie-pasquis/rational-number.git
   cd rational-number
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Run tests**
   ```bash
   ./vendor/bin/phpunit tests/
   ```

4. **Run example**
   ```bash
   php example.php
   ```

## Development Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Add type hints to all method parameters and return types
- Use meaningful variable and method names
- Keep methods focused and single-purpose (SRP)

### Testing

- Write tests for all new features
- Ensure all tests pass before submitting a pull request
- Aim for high test coverage
- Include edge cases and exception handling tests

### Commit Messages

- Use clear, descriptive commit messages
- Start with a verb in present tense (Add, Fix, Update, Remove)
- Reference issue numbers when applicable

Example:
```
Add equals() method for comparing rational numbers

Implements value object comparison as requested in #123
```

## Pull Request Process

1. **Fork the repository** and create your branch from `main`
2. **Make your changes** following the guidelines above
3. **Add tests** for your changes
4. **Run the test suite** to ensure everything passes
5. **Update documentation** (README, CHANGELOG, docblocks)
6. **Submit a pull request** with a clear description of your changes

## What to Contribute

### Good First Issues

- Additional test cases
- Documentation improvements
- Code examples
- Bug fixes

### Feature Ideas

See the project's issue tracker for planned features, including:
- Implementing SOLID principles refactoring
- Adding comparison methods (`equals()`, `compareTo()`)
- Adding mathematical operations (`abs()`, `negate()`, `pow()`)
- Extracting percentage operations to a separate class
- Creating interfaces for better extensibility

## Questions?

If you have questions or need clarification, please:
- Open an issue on GitHub
- Check existing issues and pull requests first

## Code of Conduct

- Be respectful and constructive
- Welcome newcomers and help them learn
- Focus on the code, not the person

Thank you for contributing! 🎉
