# Module ID Policy

## MokoDoliDymo Module ID

**Module ID: 185072** — registered in the [Moko Consulting module registry](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md).

This ID is **permanent and immutable**. It is stored in `$this->numero` in the module descriptor and must never be changed after deployment. Changing it would break all existing Dolibarr installations using this module.

## Dolibarr Module ID Ranges

| Range | Purpose |
|-------|---------|
| 0 – 94,999 | Core Dolibarr modules |
| 95,000 – 99,999 | Community modules (official repos) |
| 100,000 – 499,999 | Third-party public modules |
| 500,000 – 599,999 | Private/unlisted modules |
| 600,000+ | Development/temporary (never distribute) |

MokoDoliDymo's ID (185072) falls in the third-party public range.

## References

- [Dolibarr Module ID List](https://wiki.dolibarr.org/index.php/List_of_modules_id)
- [Moko Consulting Module Registry](https://github.com/mokoconsulting-tech/MokoStandards/blob/main/docs/development/crm/module-registry.md)
