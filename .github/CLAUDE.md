> [!IMPORTANT]
> **🔧 AI Self-Update Required on First Use**
>
> This file may contain unfilled `{{TOKEN}}` placeholders. The MokoStandards bulk-sync system
> replaces them automatically at sync time; if you can still see them, sync has not yet run for
> this repository — or this is a brand-new repo.
>
> **On your very first interaction in this repository, before answering any other question:**
>
> 1. Check whether any `{{TOKEN}}` placeholders remain in this file.
> 2. If they do, locate the values as described in the table below and replace every occurrence
>    in **both** `.github/copilot-instructions.md` and `.github/CLAUDE.md`.
> 3. After replacing all tokens, **delete this entire notice block** (from `> [!IMPORTANT]`
>    through the closing `> ---` line) in both files.
> 4. Tell the developer: *"I've self-updated the AI context files with this repository's
>    values — all set."*
>
> | Placeholder | Where to find the value |
> |---|---|
> | `MokoStandards-Template-Dolibarr` | The GitHub repository name (visible in the URL, `README.md` heading, or `git remote -v`) |
> | `https://github.com/mokoconsulting-tech/MokoStandards-Template-Dolibarr` | Full GitHub URL, e.g. `https://github.com/mokoconsulting-tech/<repo-name>` |
> | `A repo template for a Dolibarr module coding project according to MokoStandards` | First paragraph of `README.md` body, or the GitHub repo description |
> | `mokostandardstemplatedolibarr` | The module name as used in Dolibarr (lowercase, e.g. `mymodule`) — from the `langs/en_US/*.lang` filename or `$this->rights_class` in the module descriptor |
> | `MokoStandards-Template-Dolibarr` | PascalCase module class name (e.g. `MyModule`) — from the `src/core/modules/mod*.class.php` filename |
> | `500000` | The `$this->numero` value in `src/core/modules/mod*.class.php`; check [module-registry.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md) if creating a new module |
>
> ---

# What This Repo Is

**MokoStandards-Template-Dolibarr** is a Moko Consulting **MokoCRM** (Dolibarr) module repository.

A repo template for a Dolibarr module coding project according to MokoStandards

Module name: **mokostandardstemplatedolibarr**
Module class: **MokoStandards-Template-Dolibarr**
Module ID: **500000** *(unique, immutable — registered in [module-registry.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md))*
Repository URL: https://github.com/mokoconsulting-tech/MokoStandards-Template-Dolibarr

This repository is governed by [MokoStandards](https://github.com/mokoconsulting-tech/MokoStandards) — the single source of truth for coding standards, file-header policies, GitHub Actions workflows, and Terraform configuration templates across all Moko Consulting repositories.

---

# Repo Structure

```
MokoStandards-Template-Dolibarr/
├── src/                              # Module source (deployed to Dolibarr)
│   ├── README.md                     # End-user documentation
│   ├── core/
│   │   └── modules/
│   │       └── modMokoStandards-Template-Dolibarr.class.php  # Main module descriptor
│   ├── langs/
│   │   └── en_US/mokostandardstemplatedolibarr.lang
│   ├── sql/                          # Database schema
│   ├── class/                        # PHP class files
│   └── lib/                          # Library files
├── docs/                             # Technical documentation
├── scripts/                          # Build and maintenance scripts
├── tests/                            # Test suite
│   ├── unit/
│   └── integration/
├── .github/
│   ├── workflows/                    # CI/CD workflows (synced from MokoStandards)
│   ├── copilot-instructions.md
│   └── CLAUDE.md                     # This file
├── README.md                         # Version source of truth
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE                           # GPL-3.0-or-later
└── Makefile                          # Build automation
```

---

# Primary Language

**PHP** (≥ 8.1) is the primary language for this Dolibarr module. YAML uses 2-space indentation. All other text files use tabs per `.editorconfig`.

---

# Version Management

**`README.md` is the single source of truth for the repository version.**

- **Bump the patch version on every PR** — increment `XX.YY.ZZ` (e.g. `01.02.03` → `01.02.04`) in `README.md` before opening the PR; the `sync-version-on-merge` workflow propagates it to all `FILE INFORMATION` headers automatically on merge.
- Version format is zero-padded semver: `XX.YY.ZZ` (e.g. `01.02.03`).
- Never hardcode a version number in body text — use the badge or FILE INFORMATION header only.

### Dolibarr Version Alignment

Two artefacts must always carry the same version:

| Artefact | Location |
|----------|----------|
| `README.md` | `FILE INFORMATION VERSION` field + badge |
| Module descriptor | `$this->version` in `src/core/modules/modMokoStandards-Template-Dolibarr.class.php` |

---

# Module Descriptor Class

The file `src/core/modules/modMokoStandards-Template-Dolibarr.class.php` is the Dolibarr module descriptor. The key properties:

```php
public $numero  = 500000;       // IMMUTABLE — never change; registered globally
public $version = 'XX.YY.ZZ';         // Must match README.md version exactly
```

**`$numero` is permanent.** It was registered in [module-registry.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md) when this module was created. Changing it would break all Dolibarr installations that have this module activated.

Before creating a new module, always check the registry for the next available ID.

---

# File Header Requirements

Every new file **must** have a copyright header as its first content. JSON files, binary files, generated files, and third-party files are exempt.

**PHP:**
```php
<?php
/* Copyright (C) 2026 Moko Consulting <hello@mokoconsulting.tech>
 *
 * This file is part of a Moko Consulting project.
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * FILE INFORMATION
 * DEFGROUP: MokoStandards-Template-Dolibarr.Module
 * INGROUP: MokoStandards-Template-Dolibarr
 * REPO: https://github.com/mokoconsulting-tech/MokoStandards-Template-Dolibarr
 * PATH: /src/class/MyClass.php
 * VERSION: XX.YY.ZZ
 * BRIEF: One-line description of file purpose
 */
```

**Markdown / YAML / Shell:** Use the appropriate comment syntax with the same fields.

---

# Coding Standards

## Naming Conventions

| Context | Convention | Example |
|---------|-----------|---------|
| PHP class | `PascalCase` | `MyService` |
| PHP method / function | `camelCase` | `getUserData()` |
| PHP variable | `$snake_case` | `$module_name` |
| PHP constant | `UPPER_SNAKE_CASE` | `MAX_RETRIES` |
| PHP class file | `PascalCase.php` | `ApiClient.php` |
| PHP script file | `snake_case.php` | `check_health.php` |
| YAML workflow | `kebab-case.yml` | `ci-dolibarr.yml` |
| Markdown doc | `kebab-case.md` | `installation-guide.md` |

## Commit Messages

Format: `<type>(<scope>): <subject>` — imperative, lower-case subject, no trailing period.

Valid types: `feat` · `fix` · `docs` · `chore` · `ci` · `refactor` · `style` · `test` · `perf` · `revert` · `build`

## Branch Naming

Format: `<prefix>/<MAJOR.MINOR.PATCH>[/description]`

Approved prefixes: `dev/` · `rc/` · `version/` · `patch/` · `copilot/` · `dependabot/`

---

# GitHub Actions — Token Usage

Every workflow must use **`secrets.GH_TOKEN`** (the org-level Personal Access Token).

```yaml
# ✅ Correct
- uses: actions/checkout@v4
  with:
    token: ${{ secrets.GH_TOKEN }}

env:
  GH_TOKEN: ${{ secrets.GH_TOKEN }}
```

```yaml
# ❌ Wrong — never use these
token: ${{ github.token }}
token: ${{ secrets.GITHUB_TOKEN }}
```

PHP scripts read the token with: `getenv('GH_TOKEN') ?: getenv('GITHUB_TOKEN')` — `GH_TOKEN` is always preferred; `GITHUB_TOKEN` is a local-dev fallback only.

---

# Keeping Documentation Current

| Change type | Documentation to update |
|-------------|------------------------|
| New or renamed PHP class/method | PHPDoc block; `docs/api/` entry |
| New or changed module version | Update `$this->version` in module descriptor; bump `README.md` |
| New library class or major feature | `CHANGELOG.md` entry under `Added` |
| Bug fix | `CHANGELOG.md` entry under `Fixed` |
| Breaking change | `CHANGELOG.md` entry under `Changed` |
| Any modified file | Update the `VERSION` field in that file's `FILE INFORMATION` block |
| **Every PR** | **Bump the patch version** — increment `XX.YY.ZZ` in `README.md`; `sync-version-on-merge` propagates it |

---

# What NOT to Do

- **Never commit directly to `main`** — all changes go through a PR.
- **Never hardcode version numbers** in body text — update `README.md` and let automation propagate.
- **Never change `$this->numero`** — the module ID is permanent and globally registered.
- **Never skip the FILE INFORMATION block** on a new source file.
- **Never use bare `catch (\Throwable $e) {}`** — always log or re-throw.
- **Never mix tabs and spaces** within a file — follow `.editorconfig`.
- **Never use `github.token` or `secrets.GITHUB_TOKEN` in workflows** — always use `secrets.GH_TOKEN`.
- **Never register a new module ID** without first consulting module-registry.md.
- **Never let `$this->version` and `README.md` version diverge.**

---

# PR Checklist

Before opening a PR, verify:

- [ ] Patch version bumped in `README.md` (e.g. `01.02.03` → `01.02.04`)
- [ ] `$this->version` in module descriptor updated to match
- [ ] FILE INFORMATION headers updated in modified files
- [ ] CHANGELOG.md updated

---

# Key Policy Documents (MokoStandards)

| Document | Purpose |
|----------|---------|
| [file-header-standards.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/file-header-standards.md) | Copyright-header rules for every file type |
| [coding-style-guide.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/coding-style-guide.md) | Naming and formatting conventions |
| [branching-strategy.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/branching-strategy.md) | Branch naming, hierarchy, and release workflow |
| [merge-strategy.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/merge-strategy.md) | Squash-merge policy and PR conventions |
| [changelog-standards.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/changelog-standards.md) | How and when to update CHANGELOG.md |
| [module-registry.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md) | Dolibarr module ID registry — check before reserving a new ID |
| [crm/development-standards.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/policy/crm/development-standards.md) | MokoCRM Dolibarr module development standards |
| [dolibarr-development-guide.md](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/guide/crm/dolibarr-development-guide.md) | MokoCRM full development guide |