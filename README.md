# MokoDoliDymo

A Dolibarr module for designing and printing labels using DYMO LabelWriter thermal label printers.

## Overview

MokoDoliDymo integrates DYMO LabelWriter printers with Dolibarr ERP/CRM. Design label templates with data pulled directly from your Dolibarr records — products, contacts, shipments, warehouses, and more — then print them to any connected LabelWriter device.

### Key Features

- Label template designer with drag-and-drop field placement
- Barcode and QR code generation on labels
- Data binding to Dolibarr objects (products, third parties, contacts, etc.)
- Support for standard DYMO LabelWriter label sizes
- Batch printing from list views
- Permission-based access control (read, write, delete, print)

## Requirements

- Dolibarr ERP/CRM 19.0+
- PHP 8.1+
- DYMO LabelWriter printer (LabelWriter 450, 550, or compatible)

## Installation

Clone into your Dolibarr `custom/` directory:

```bash
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/mokoconsulting-tech/MokoDoliDymo.git mokodolidymo
```

Then enable the module:

1. Log in as administrator
2. Go to **Home > Setup > Modules/Applications**
3. Find **MokoDoliDymo** under **Moko Consulting**
4. Click **Activate**

See [docs/installation.md](docs/installation.md) for detailed instructions.

## Module Identity

| Property | Value |
|----------|-------|
| Module ID | **185072** |
| Rights class | `mokodolidymo` |
| Module class | `modMokoDoliDymo` |
| License | GPL-3.0-or-later |

## Documentation

- [Installation Guide](docs/installation.md)
- [Development Guide](docs/development.md)
- [Changelog](docs/changelog.md)

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

GPL-3.0-or-later — see [LICENSE](LICENSE).

## Version

01.00.00
