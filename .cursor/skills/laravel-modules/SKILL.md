---
name: laravel-modules
description: 'ACTIVATE when the user works on modularizing a Laravel application using nWidart/laravel-modules. This includes creating modules, module-specific controllers, models, migrations, views, or routes; managing module manifests (module.json); or handling inter-module dependencies and shared logic. Activate when the user mentions modules, modular architecture, or references Modules/ directory or php artisan module: commands.'
license: MIT
metadata:
  author: nWidart
---

# Laravel Modules Development

nWidart/laravel-modules is the industry standard for modularizing large Laravel applications, allowing you to treat each module as a mini-application.

## Documentation

Use `search-docs` for detailed Laravel Modules patterns and documentation.

## Core Concepts

- **Modules Path**: Usually `Modules/` (customizable in `config/modules.php`).
- **Module Structure**: Each module contains its own `Http`, `Models`, `Database`, `Resources`, `Routes`, etc.
- **module.json**: The manifest file for each module defining its name, alias, and dependencies.

## Usage

### Artisan Commands

Always use the `module:` prefix for module-related tasks:

- `php artisan module:make <Name>` - Create a new module
- `php artisan module:make-controller <Controller> <Module>` - Create a controller in a module
- `php artisan module:make-model <Model> <Module>` - Create a model in a module
- `php artisan module:make-migration <migration_name> <Module>` - Create a migration in a module
- `php artisan module:migrate <Module>` - Run migrations for a specific module
- `php artisan module:seed <Module>` - Seed a specific module
- `php artisan module:publish-config <Module>` - Publish module configuration
- `php artisan module:list` - List all modules and their status

### Autoloading

You MUST add the `Modules\` namespace to your root `composer.json` if not already present:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "Modules/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
}
```

Then run `composer dump-autoload`.

### Referencing Resources

- **Views**: `view('blog::index')` (References `Modules/Blog/Resources/views/index.blade.php`)
- **Translations**: `__('blog::messages.welcome')`
- **Config**: `config('blog.name')`
- **Routes**: Automatically loaded from `Modules/Blog/Routes/web.php` and `api.php`.

## Best Practices

### 1. Module Independence
Treat modules as independent units. Avoid circular dependencies. If two modules are tightly coupled, they might belong in the same module or require a `Core` module to hold shared logic.

### 2. Shared Logic
Create a `Core` or `Base` module for shared components like Base Models, Traits, Middleware, or common UI components that multiple modules need.

### 3. Service Providers
Each module has its own `ModuleServiceProvider`. Use it to register module-specific bindings, events, and component aliases. Ensure it correctly loads migrations and views.

### 4. File Paths
Use the `module_path('Blog', 'Http/Controllers')` helper instead of `base_path()` to ensure portability.

## Common Pitfalls

- **Autoloading Issues**: Forgetting to add `Modules\` to `composer.json` or neglecting `composer dump-autoload`.
- **Missing `module.json`**: Ensure this manifest exists and is correctly configured.
- **Hardcoding Namespaces**: Always use the generated namespaces which follow the `Modules\ModuleName\...` pattern.
- **Migration Scope**: Forgetting to use `module:migrate` to target a specific module, which is safer in large modular apps.
