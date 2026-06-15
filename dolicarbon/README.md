# DoliCarbon for [Dolibarr ERP & CRM](https://www.dolibarr.org)

**DoliCarbon** is a Dolibarr module to build and manage **greenhouse gas (GHG) inventories** inside your instance: year-based inventories (“bilans”), activity lines with emission factors, reduction actions, methodological framing, dashboards, reporting, and optional links to Dolibarr purchase flows.

Other external modules are available on [Dolistore](https://www.dolistore.com).

## What you can do

### Inventories (bilans)

- Create one inventory per reporting period; each record gets an automatic reference in the form **`CARBON-{year}-{sequence}`** (see class `DoliCarbonBilan`).
- Store period boundaries (start/end dates), optional link to a third party, **total** and **target** emissions in tCO2e, notes, and workflow status (draft / validated / archived).

### Activity lines (entries)

- Record emissions by **scope** (1, 2, or 3) and **category** (module taxonomy), with quantities, units, and a link to an **emission factor** where applicable.
- Lines support validation workflow, comments, audit trail, and traceability to source objects (for example supplier invoices when imported).

### Emission factors

- Maintain a library of **emission factors** (including versioning fields in the data model) and activate or deactivate factors per entity where the schema allows it.

### Reduction actions

- Attach **actions** to an inventory: estimated gains, costs, scoring fields, and status to track the reduction plan.

### Methodological framing (“cadrage”)

- Document scope, exclusions, materiality, reference and reporting years, and related notes in a dedicated framing object used by reporting.

### Dashboard

- View aggregated KPIs: totals by scope, time series, and main categories, driven by inventory data.

### Reporting

- Open the **Report** area in the embedded app: executive summary, analyst breakdown, methodology annex, optional **communication guardrail** text, **uncertainty** range, export to **CSV** or **JSON**, and **freeze snapshot** for a reproducible point in time.

### Data quality and collaboration

- Use the **Quality** screen for workflow steps, **comments**, and audit-oriented traceability on lines.

### Transition plan

- Use the **Transition** view to compare reduction actions (cost / benefit style summary as implemented in the UI).

### Import from Dolibarr

- Run the **Import** wizard to pull relevant data from Dolibarr into inventory lines (see `carbon_import.php` and related AJAX services).

### Optional triggers (administration)

Under **Home → Setup → Modules → DoliCarbon → Setup**, two options are available (stored as `DOLICARBON_TRIGGER_NOTIFY` and `DOLICARBON_AUTO_IMPORT_SUPPLIER_INVOICE`):

1. **Hints on validation**  
   When enabled, validating a **supplier invoice**, an **expense report**, or a **shipment** can show a Dolibarr event message pointing to DoliCarbon (with a link to the import screen for supplier invoices).

2. **Automatic line from supplier invoice**  
   When both this option and the hints are enabled, validating a **supplier invoice** can **automatically** create a **Scope 3** line in category **`purchases_services`**, using the invoice **total excluding tax** in **EUR**, **only if** there is a **draft** inventory and an **active** matching emission factor for that category and scope. Duplicate imports for the same invoice are avoided via an import hash.

If these options are disabled, no messages or automatic lines are created.

## User interface

- **Embedded web app** (menu **DoliCarbon** → `custom/dolicarbon/index.php`): routes include dashboard, bilans, cadrage, entries, factors, actions, quality, transition, import, and report. **Read** permission on the module is required.
- **Classic PHP screens** remain available (for example inventory list and factors from the left menu: `carbon_bilan_list.php`, `carbon_factors.php`, and other `carbon_*.php` pages as shipped).

The Angular bundle ships **French** UI strings (`assets/i18n/fr.json`). Dolibarr module translations are provided for **French** (`fr_FR`) and **English** (`en_US`) under `langs/`.

## Permissions

The module defines four rights: **read**, **write**, **delete**, and **validate** (validate / lock inventory workflow). Assign them per group under **Users & groups**.

## Prerequisites

- **Dolibarr 17** or compatible newer branch as required by the module descriptor (`need_dolibarr_version`).
- **PHP 8.1** or newer (`phpmin`).

No other Dolibarr module is declared as a hard dependency in the module descriptor; optional features assume the related Dolibarr objects (e.g. supplier invoices) exist when you use them.

## Installation

Prerequisites: a working Dolibarr ERP & CRM installation. You can download it from [dolibarr.org](https://www.dolibarr.org). Hosted instances are also available (see below).

### From a ZIP package

If the module is distributed as `module_dolicarbon-x.y.z.zip` (for example from [Dolistore](https://www.dolistore.com)), use **Home → Setup → Modules → Deploy/install external module** and upload the archive.

### Final steps

1. Log in as an administrator.
2. Open **Setup → Modules**, find **DoliCarbon**, and enable it.
3. Open **Setup → Modules → DoliCarbon → Setup** to configure the two trigger-related options if needed.

## Configuration

1. **Module setup**: **Home → Setup → Modules → DoliCarbon → Setup** — enable or disable validation hints and automatic supplier-invoice import as described above.
2. **Permissions**: grant **read** (minimum to open the app), **write**, **delete**, and **validate** to the appropriate groups.

## Getting Dolibarr in the cloud (Ma Gestion Cloud)

You can run Dolibarr with Aplose modules on **[Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)** instead of maintaining your own server: hosting, backups, and support. Registration / trial (tracking for Dolistore visitors):

**[Create an account — Ma Gestion Cloud](https://aplose.ma-gestion-cloud.fr/custom/sellyoursaas/myaccount/register.php?origin=dolistore)**

Contact: [contact@aplose.fr](mailto:contact@aplose.fr)

Commercial plans and bundled modules depend on your subscription; ask Ma Gestion Cloud for an offer that matches your needs.

## Support

- Email: [contact@aplose.fr](mailto:contact@aplose.fr)
- Publisher site: [Aplose](https://www.aplose.fr)

## Licenses

### Main code

GPLv3 or (at your option) any later version. See file `COPYING`.

### Documentation

This documentation is licensed under [GFDL](https://www.gnu.org/licenses/fdl-1.3.en.html).

## Version

Current module version: **1.0.0** (see `core/modules/modDoliCarbon.class.php`).
