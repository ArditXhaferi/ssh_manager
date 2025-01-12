<p align="center">  
  <a href="https://github.com/arditxhaferi/ssh_manager/actions">  
    <img src="https://img.shields.io/github/actions/workflow/status/arditxhaferi/sssh/ci.yml?style=flat-square&logo=github&label=build" alt="Build Status">  
  </a>  
  <a href="https://packagist.org/packages/laravel/framework">  
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">  
  </a>  
  <a href="https://github.com/arditxhaferi/ssh_manager/actions">  
    <img src="https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/arditxhaferi/ssh_manager/main/coverage.json" alt="Coverage">  
  </a>
  <a href="https://github.com/arditxhaferi/ssh_manager/releases">
    <img src="https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/arditxhaferi/sssh/main/version.json&style=flat-square&label=version" alt="Version">
  </a>
</p>

# SSSH - Simple SSH Manager

**SSSH** is a native macOS application built with Laravel and NativePHP that provides a simple and intuitive interface for managing your SSH connections and keys.

---

## Features

### 🔑 SSH Key Management
- Generate new SSH keys
- View and copy existing public keys
- Delete key pairs
- Automatic key directory detection

### 🖥️ Connection Management
- Store and organize SSH connections
- Test connection health
- Quick connect functionality
- Password and key-based authentication support
- Connection locking for security

---

## Requirements

- macOS
- PHP 8.2+
- Composer
- Node.js & NPM

---

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/arditxhaferi/sssh.git
   cd sssh
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node dependencies:
   ```bash
   npm install
   ```

4. Start the development environment:
   ```bash
   composer native:dev
   ```

---

## Development

The application uses several key technologies:
- **Laravel** for the backend framework
- **Livewire** for reactive components
- **NativePHP** for native macOS integration
- **TailwindCSS** for styling

### Run the development environment:
```bash
composer dev
```

### Run tests:
```bash
composer test
```

### Generate coverage report:
```bash
composer test:coverage
```

---

## Testing

The application includes comprehensive testing:
- Feature tests for Livewire components
- Unit tests for helpers and actions
- SSH connection testing
- Key management testing

---

## Contributing

Thank you for considering contributing to SSSH! Please feel free to submit pull requests or create issues for bugs and feature requests.

---

## Security

If you discover any security vulnerabilities, please create an issue or submit a pull request with the fix.

---

## License

SSSH is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
