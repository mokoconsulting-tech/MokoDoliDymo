# Installation Guide

## Prerequisites

- **Dolibarr ERP/CRM**: Version 19.0 or higher
- **PHP**: 8.1 or higher
- **DYMO LabelWriter**: LabelWriter 450, 550, or compatible model
- **PHP Extensions**: mysqli or pgsql, gd, curl, json

## Installation

### 1. Clone the Repository

Navigate to your Dolibarr custom modules directory and clone:

```bash
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/mokoconsulting-tech/MokoDoliDymo.git mokodolidymo
```

### 2. Set File Permissions

```bash
chown -R www-data:www-data /path/to/dolibarr/htdocs/custom/mokodolidymo
find /path/to/dolibarr/htdocs/custom/mokodolidymo -type d -exec chmod 755 {} \;
find /path/to/dolibarr/htdocs/custom/mokodolidymo -type f -exec chmod 644 {} \;
```

### 3. Enable the Module

1. Log in to Dolibarr as an administrator
2. Navigate to **Home > Setup > Modules/Applications**
3. Find **MokoDoliDymo** under the **Moko Consulting** family
4. Click **Activate**

### 4. Configure Permissions

After activation, assign permissions to users:

1. Go to **Home > Users & Groups**
2. Select the user or group
3. Under **Permissions**, find **MokoDoliDymo** and enable:
   - **Read label templates** — view existing labels
   - **Create/Update label templates** — design and edit labels
   - **Delete label templates** — remove labels
   - **Print labels** — send labels to the printer

### 5. Configure Module Settings

1. Go to **Home > Setup > Modules/Applications**
2. Click on **MokoDoliDymo** to access settings
3. Configure printer connection and default label sizes

## Troubleshooting

### Module Not Appearing

- Verify the module directory is named `mokodolidymo` (lowercase)
- Clear Dolibarr cache and refresh
- Check PHP syntax: `php -l src/core/modules/modMokoDoliDymo.class.php`
- Ensure `$dolibarr_main_document_root_alt` is set in `conf/conf.php`

### Permission Errors

- Ensure the web server user has read access to all module files
- Verify the module is activated before assigning permissions

## Uninstallation

1. Go to **Home > Setup > Modules/Applications**
2. Find **MokoDoliDymo** and click **Deactivate**
3. Optionally remove the module directory:
   ```bash
   rm -rf /path/to/dolibarr/htdocs/custom/mokodolidymo
   ```

## Next Steps

- Read the [Development Guide](development.md) if you want to extend the module
- Check the [Changelog](changelog.md) for version history
