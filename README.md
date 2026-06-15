# Free Aplose Dolibarr Modules

> **Digital sovereignty isn't reserved for large enterprises.**
> A collection of free, open-source modules for [Dolibarr ERP & CRM](https://www.dolibarr.org), built and maintained by [Aplose](https://www.aplose.fr).

[](https://www.gnu.org/licenses/gpl-3.0)
[](https://www.dolibarr.org)
[](#)
[](#contributing)
[](https://www.aplose.fr)

---

## Why this repository?

These modules have **always** been free software (GPLv3) — they were simply living in private repositories until now. We're opening them up so the wider Dolibarr community can use them, improve them, and translate them.

This is a **growing collection**: the modules listed below are only the first wave, and more will be published here over time. Some are hidden gems that never made it to the spotlight on the marketplace. If one of them saves you an afternoon of work, that's exactly why it's here. **A ⭐ helps others find them too — and ⭐ Watch the repo to be notified when new modules land.**

---

## Modules at a glance

| Module | What it does | Best for |
| --- | --- | --- |
| **[bankimportapi](./bankimportapi)** | Import bank transactions from a CSV file **or directly from the Qonto API**, then create and reconcile/pay the matching documents. | Anyone tired of typing bank lines by hand. |
| **[dolilinks](./dolilinks)** | Build **parent/child relationships between third parties**, visualize them in an interactive diagram, and stop sending invoices to your clients' clients. | Groups, holdings, franchises, multi-entity setups. |
| **[productrecall](./productrecall)** | Track official **consumer product recalls** (RappelConso), get email alerts filtered by category, and print PDF posters for the point of sale. | Retailers, food businesses, anyone with safety obligations. |

> 🚧 **More modules on the way.** This list will keep growing — Watch the repository to get notified.

---

## The modules in detail

### bankimportapi — Bank reconciliation, automated

Load your bank movements without the copy-paste marathon. Feed the module a **CSV export** or connect it **straight to the Qonto API**, and it will help you create and settle the corresponding entries in Dolibarr.

> 🙏 **Credit where it's due:** this module was developed by Florian Dufourg and is published here with his kind permission. Thank you!

- Import from CSV or live from Qonto
- Create and pay the matching documents
- A simple bridge between your bank and your accounting

➡️ [Read the module documentation](./bankimportapi)

---

### dolilinks — See how your companies connect

DoliLinks turns a flat list of third parties into a real **organizational map**. Define who is the parent and who is the child, give each relationship a meaning, and navigate the whole structure visually.

- **Hierarchical links** between companies, with circular-link protection
- **Custom link types** (subsidiary, branch, partner…) stored in the Dolibarr dictionary
- **Interactive diagram**: parents, children and grandchildren, color-coded and clickable
- **Smart contact filtering**: optionally offer only billing contacts when sending emails — *don't send invoices to your clients' clients!*
- **Multi-entity** support, access-rights aware
- **Multilingual**: English, French, German, Spanish
- **Migration tool** to import data from the old SocParent module

➡️ [Read the module documentation](./dolilinks)

---

### productrecall — Keep your customers safe

ProductRecall watches France's official **RappelConso** consumer-recall feed and warns you the moment something on your shelves is affected.

- **Email alerts** filtered by product **category and subcategory**
- **Homepage widget** showing the latest recalls
- **Scheduled job** that imports the full recall database on first run, then checks automatically
- **Downloadable PDF posters** to display at the cash register
- Searchable history of every current and past recall

➡️ [Read the module documentation](./productrecall)

---

## Installation

All modules follow the standard Dolibarr "external module" layout. Two ways to install:

### Option A — From the GUI (ZIP)

1. Download / build a ZIP of the module folder you want.
2. Log into Dolibarr as a **super-administrator**.
3. Go to **Home → Setup → Modules → Deploy external module** and upload the ZIP.
4. Find the module in the list and **enable** it.

> If Dolibarr says there's no custom directory, check that `$dolibarr_main_url_root_alt` and `$dolibarr_main_document_root_alt` are set in `htdocs/conf/conf.php`.

### Option B — From Git

Clone the module straight into your `custom` directory:

```sh
cd /var/www/dolibarr/htdocs/custom
git clone https://github.com/Aplose/FreeAploseDolibarrModules.git
# then move (or symlink) the module folder you need, e.g. dolilinks/, into custom/
```

Then enable it from **Setup → Modules**.

---

## Contributing

Pull requests, issues and translations are very welcome.

- 🐛 **Found a bug?** Open an [issue](https://github.com/Aplose/FreeAploseDolibarrModules/issues).
- ✨ **Have an idea or a fix?** Send a pull request.
- 🌍 **Speak another language?** Add or improve the files in each module's `langs/` directory.

If you're not sure where to start, just open a discussion or drop us a line at [oandrade@aplose.fr](mailto:oandrade@aplose.fr?subject=[FreeAploseDolibarrModules]).

---

## Credits

Most modules are developed and maintained by the Aplose team. Some are generous contributions from the community, published here with their authors' permission:

- **bankimportapi** — developed by `<author name / link to fill in>`, published with their kind authorization.

Want your module listed here? See [Contributing](#contributing).

---

## About Aplose

[Aplose](https://www.aplose.fr) is a French open-source software company specialized in Dolibarr integration and hosting. We build tools that give small and medium businesses real control over their own systems.

- 🌐 **Website**: [www.aplose.fr](https://www.aplose.fr)
- ☁️ **Managed Dolibarr hosting**: [Ma Gestion Cloud](https://www.ma-gestion-cloud.fr)
- 🛒 **More modules**: [our publisher page on DoliStore](https://www.dolistore.com)
- 📨 **Contact**: [oandrade@aplose.fr](mailto:oandrade@aplose.fr)

> *Our WHY: so that every entrepreneur can exercise their full power, without depending on those who would like to control it.*

---

## License

- **Code**: [GNU General Public License v3](./LICENSE) or (at your option) any later version.
- **Documentation**: GFDL.

You are free to use, study, share and improve this software. If you redistribute a modified version, keep it free for the next person too.
