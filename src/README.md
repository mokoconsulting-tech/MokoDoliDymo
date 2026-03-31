# MokoDoliDymo for [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## What It Does

MokoDoliDymo lets you design and print labels for DYMO LabelWriter printers directly from Dolibarr. Build label templates that pull live data — product refs, barcodes, addresses, prices, custom fields — from any Dolibarr record and send them straight to your LabelWriter.

## Use Cases

- **Product labels** — SKU, barcode, price, and description on shelf or bin labels
- **Shipping labels** — recipient address from shipments or sales orders
- **Asset tags** — barcode labels for inventory, equipment, or warehouse locations
- **Address labels** — batch-print mailing labels from contacts or third parties
- **Custom labels** — freeform layouts with text, barcodes, QR codes, and images

## Supported Printers

DYMO LabelWriter 450, 450 Turbo, 450 Twin Turbo, 550, 550 Turbo, LabelWriter Wireless, and any LabelWriter-compatible device.

## Supported Label Sizes

Standard DYMO rolls: address (30252), shipping (30256), multi-purpose (30334), file folder (30327), name badge (30857), and custom continuous-feed sizes.

## Requirements

- Dolibarr ERP/CRM 19.0+
- PHP 8.1+
- DYMO LabelWriter connected to server or network

## Installation

```shell
cd /path/to/dolibarr/htdocs/custom/
git clone https://github.com/mokoconsulting-tech/MokoDoliDymo.git mokodolidymo
```

Then in Dolibarr:

1. Log in as administrator
2. Go to **Home > Setup > Modules/Applications**
3. Find **MokoDoliDymo** under **Moko Consulting** and activate it

## Translations

Translation files are in `langs/`. Add or edit `.lang` files for your locale.

## License

GPLv3 or (at your option) any later version. See file COPYING for more information.
