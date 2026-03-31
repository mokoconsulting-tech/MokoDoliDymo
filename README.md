# MokoDoliDymo

Print labels from Dolibarr using DYMO LabelWriter printers.

## What It Does

MokoDoliDymo turns your Dolibarr ERP/CRM into a label printing workstation. Connect a DYMO LabelWriter, design your label layouts inside Dolibarr, and print — pulling live data from your products, contacts, warehouses, shipments, or any other Dolibarr record.

### Use Cases

- **Product labels** — print SKU, barcode, price, and description onto shelf or bin labels
- **Shipping labels** — pull recipient address from a shipment or order and print directly
- **Asset tags** — generate barcode labels for inventory, equipment, or warehouse locations
- **Address labels** — batch-print mailing labels from your contact or third-party list
- **Custom labels** — design freeform layouts with text, barcodes, QR codes, and images

### How It Works

1. **Design** — create label templates in the built-in designer, choosing a DYMO label size and placing fields where you want them
2. **Bind** — map template fields to Dolibarr data (product ref, third-party name, address, custom extrafields, etc.)
3. **Print** — select records in Dolibarr and print labels individually or in batch to your LabelWriter

### Supported Hardware

- DYMO LabelWriter 450 / 450 Turbo / 450 Twin Turbo
- DYMO LabelWriter 550 / 550 Turbo
- DYMO LabelWriter Wireless
- Any LabelWriter-compatible printer that accepts standard DYMO label rolls

### Supported Label Sizes

Standard DYMO label rolls including address (30252), shipping (30256), multi-purpose (30334), file folder (30327), name badge (30857), and custom continuous-feed sizes.

## Requirements

- Dolibarr ERP/CRM 19.0+
- PHP 8.1+
- A DYMO LabelWriter printer connected to the server or accessible over the network

## Installation

```bash
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/mokoconsulting-tech/MokoDoliDymo.git mokodolidymo
```

Then in Dolibarr: **Home > Setup > Modules/Applications** — find **MokoDoliDymo** under **Moko Consulting** and activate it.

See [docs/installation.md](docs/installation.md) for full setup instructions including permissions.

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
