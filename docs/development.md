# Development Guide

This guide covers developing and extending MokoDoliDymo.

## Module Structure

```
mokodolidymo/
├── core/
│   └── modules/
│       └── modMokoDoliDymo.class.php   # Module descriptor (ID 185072)
├── class/                               # Business logic classes
├── langs/
│   └── en_US/
│       └── mokodolidymo.lang            # Translations
├── sql/                                 # Database schema
├── lib/
│   └── mokodolidymo.lib.php             # Shared library functions
├── admin/
│   ├── setup.php                        # Configuration page
│   └── about.php                        # About page
├── img/                                 # Icons and images
└── mokodolidymoindex.php                # Module home page
```

## Module Descriptor

The module descriptor at `core/modules/modMokoDoliDymo.class.php` defines:

- **Module ID**: `185072` — permanently registered, never change
- **Rights class**: `mokodolidymo`
- **Permissions**: `label.read`, `label.write`, `label.delete`, `label.print`
- **Version**: Must match `README.md`

## Coding Standards

This project follows [MokoStandards](https://github.com/mokoconsulting-tech/MokoStandards):

| Context | Convention |
|---------|-----------|
| PHP class | `PascalCase` |
| PHP method | `camelCase` |
| PHP variable | `$snake_case` |
| PHP constant | `UPPER_SNAKE_CASE` |
| Indentation | Tabs (per `.editorconfig`) |

### Security

```php
// Always check permissions
if (!$user->hasRight('mokodolidymo', 'label', 'read')) {
	accessforbidden();
}

// Sanitize inputs
$id = GETPOSTINT('id');
$name = GETPOST('name', 'alpha');

// Escape output
print dol_escape_htmltag($user_input);
```

### Translations

Add keys to `langs/en_US/mokodolidymo.lang`:

```
MyNewKey = My translated string
```

Use in PHP:

```php
$langs->load("mokodolidymo@mokodolidymo");
print $langs->trans("MyNewKey");
```

### Database

SQL files go in `sql/` and are auto-loaded on module activation:

```
sql/
├── llx_mokodolidymo_label.sql
└── llx_mokodolidymo_label.key.sql
```

## Version Management

- **`README.md`** is the single source of truth for the version
- `$this->version` in the module descriptor must always match
- Bump patch version on every PR (`01.00.00` -> `01.00.01`)
- Format: zero-padded semver `XX.YY.ZZ`

## File Headers

Every new PHP file requires a copyright header:

```php
<?php
/* Copyright (C) 2026 Moko Consulting <hello@mokoconsulting.tech>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * FILE INFORMATION
 * DEFGROUP: MokoDoliDymo.Module
 * INGROUP: MokoDoliDymo
 * REPO: https://github.com/mokoconsulting-tech/MokoDoliDymo
 * PATH: /src/class/MyClass.php
 * VERSION: 01.00.00
 * BRIEF: One-line description
 */
```

## Testing

```bash
# Run PHP linting
php -l src/core/modules/modMokoDoliDymo.class.php

# Run PHPStan
composer phpstan

# Run code style checks
composer phpcs
```

## Resources

- [Dolibarr Developer Docs](https://wiki.dolibarr.org/index.php/Developer_documentation)
- [Dolibarr Module Development](https://wiki.dolibarr.org/index.php/Module_development)
- [MokoStandards](https://github.com/mokoconsulting-tech/MokoStandards)
