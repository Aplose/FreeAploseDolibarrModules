# PRODUCT RECALL FOR DOLIBARR

Ensure your customers' safety by monitoring consumption alerts!

![Screenshot ProductRecall](img/productrecall-screenshot-001.png?raw=true "ProductRecall")

## Features
This module allows you to track official consumer product recalls and receive email alerts. You can set up a filter for product categories and/or subcategories. A widget is provided on the homepage with the latest recalls. A scheduled job is included and must be activated. During the initial run, all recalls available on the official RappelConso site are imported.

## Coming Soon
There are no plans at the moment, but feel free to provide your feedback on this matter to [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[ProductRecallRequest]-).

## Configuration

### Email
To receive consumption alert emails, it is necessary to have email configuration set up in Dolibarr beforehand and then add an email address (or multiple separated by commas) in the designated field.
![Screenshot email](img/productrecall-screenshot-002.png?raw=true "Email")

### Category and Subcategory Filters
You can select as many category and/or subcategory filters as desired.
![Screenshot filters](img/productrecall-screenshot-003.png?raw=true "Filters")
Filters can be added, so it's unnecessary to select the "Food" category and the "Meat" subcategory, as the latter is already included in "Food." However, you can select the "Meat" subcategory only if you want alerts for those products. If you want all product recalls, do not apply any filters.
![Screenshot filters 2](img/productrecall-screenshot-004.png?raw=true "Filters 2")

## Usage

### Product Recall Verification Job (Automated Jobs)
A job (program executed automatically or manually) is provided to manually load your product recall database for the first time (remember to configure your filters according to your preferences, or you will load all product recalls). In "Administration Tools -> Scheduled Jobs," activate the "Product Recall Verification" job and then run it!
![Screenshot job](img/productrecall-screenshot-005.png?raw=true "Job")
The first time, you will need to wait a few minutes...
![Screenshot job 2](img/productrecall-screenshot-006.png?raw=true "Job 2")

Afterward, every ten minutes, if you have configured Dolibarr correctly to regularly execute scheduled tasks, the job will run automatically, and you will receive an email if there are recalls relevant to you (and if email settings are properly configured).

When product recalls match your filters, and you have configured at least one email address and email sending, you will receive an email like this:
![Screenshot mail sent](img/productrecall-screenshot-007.png?raw=true "Mail sent")
You will notice that many pieces of information are available, including the download of the PDF poster to display near the cash register.

### Product Recall Screen
You can find all current and past product recalls by clicking on the top menu link. You can filter this list using your usual techniques in the filter fields (here using %carrot% to find products containing the word "carrot").
![Screenshot screen recall](img/productrecall-screenshot-008.png?raw=true "Screen recall")

## Stay in Touch!
Feel free to share your ideas and comments with us at [oandrade@aplose.fr](mailto:oandrade@aplose.fr).

## Licenses

### Main Code

GPLv3.

### Documentation

All texts and readmes are under the GFDL license.