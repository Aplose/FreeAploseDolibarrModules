# DOLILINKS FOR [DOLIBARR ERP & CRM](https://www.dolibarr.org)

## Module Description

DoliLinks is a module for Dolibarr that allows you to create and manage hierarchical links between companies (third parties). It provides a clear visualization of parent-child relationships between businesses and facilitates the management of complex organizational structures.

## Main Features

### 1. Company Link Management

The module allows you to create hierarchical relationships between companies:
- **Parent-child links**: Define which companies are parents or children of other companies
- **Customizable link types**: Create specific link types (subsidiary, branch, partner, etc.)
- **Circular link prevention**: The system prevents linking a company to itself

![Company link management interface: adding a parent](img/screenshot_dolilinks_links02.png)
![Company link management interface](img/screenshot_dolilinks_links03.png)


### 2. Hierarchical Visualization

#### 2.1 Display in Company Card
Links are automatically displayed in each company's card:
- **Parents Section**: List of parent companies with direct links
- **Children Section**: List of child companies with direct links
- **Action buttons**: Quick addition of new links and access to the diagram

![Link display in company card](img/screenshot_dolilinks_links01.png)

#### 2.2 Interactive Diagram
Complete graphical visualization of relationships:
- **Hierarchical network**: Display of all parents, children and grandchildren
- **Interactive navigation**: Click on nodes to access company cards
- **Color legend**: Visual distinction between parents (gray), current company (green) and children (blue)
- **Link types**: Display of link type labels on connections

![Interactive relationship diagram](img/screenshot_diagram_interactive.png)

### 3. Link Type Management

#### 3.1 Type Configuration
- **Custom type creation**: Define relationship types specific to your organization
- **Centralized management**: Administration interface to create and modify types
- **Integrated dictionary**: Types are stored in the Dolibarr dictionary

![Link type management interface: dictionary access](img/screenshot_link_types_management01.png)
![Link type management interface: adding link types](img/screenshot_link_types_management02.png)

### 4. Integration with Dolibarr Ecosystem

#### 4.1 Hooks and Extensions
- **Native integration**: The module integrates perfectly into the Dolibarr interface
- **Custom hooks**: Extend functionality through the hook system

#### 4.2 Billing Contact Filtering
- **Smart filtering**: Option to offer only billing contacts when sending emails (don't send invoices to your clients' clients!!!)
- **Child third party contacts**: Display of contacts from linked companies in contact cards
- **Flexible configuration**: Enable/disable via module parameters

![Child contact filtering on an order](img/screenshot_contact_filtering_order.png)
![Billing contact filtering on email sending](img/screenshot_contact_filtering_invoice_email.png)

#### 4.3 Compatibility
- **Multi-entity**: Full support for Dolibarr multi-entity mode
- **Security**: Respect for access rights and Dolibarr security
- **Translations**: Multilingual support (French, English, German, Spanish)

### 5. Advanced Features

#### 5.1 Data Import
- **SocParent migration**: Import tool to migrate data from the SocParent module

![Data import interface](img/screenshot_admin01.png)

#### 5.2 Reports and Statistics
- **Automatic counters**: Display of the number of parents/children for each company
- **Facilitated navigation**: Direct links to linked company cards
- **Overview**: Quick access to the complete relationship diagram

## Installation

### Prerequisites
- Dolibarr ERP & CRM installed
- Administrator rights for module installation

### Installation via Dolibarr Interface
1. Download the module from [Dolistore.com](https://www.dolistore.com)
2. Log into Dolibarr as administrator
3. Go to `Home > Setup > Modules > Deploy external module`
4. Upload the module ZIP file
5. Enable the module in the available modules list

### Initial Configuration
1. Access `Setup > Modules > DoliLinks`
2. Configure parameters according to your needs
3. Create your custom link types if necessary

## Usage

### Create a Link Between Companies
1. Open the concerned company's card
2. In the "Parents" or "Children" section, click the "+" button
3. Select the company to link from the dropdown list
4. Choose the link type (optional)
5. Click "Add"

### View Relationships
1. From the company card, click "View diagram"
2. The interactive diagram displays with all relationships
3. Click on any node to access the company's card

### Manage Link Types
1. Go to `Setup > Dictionaries > Company link type`
2. Create, modify or delete types according to your needs

![Link type dictionary](img/screenshot_link_types_management01.png)
![Link type dictionary: management](img/screenshot_link_types_management02.png)


## Configuration

### Available Parameters
- **Contact filtering**: Option to offer only billing contacts when sending emails

### Customization
The module can be extended via:
- Custom hooks
- Modifiable templates
- Extensible PHP classes

## Support and Development

### License
- **Main code**: GPLv3 or later version
- **Documentation**: GFDL

### Support
- Complete documentation in the module
- Compatible with recent Dolibarr versions

### Development
The module is developed following Dolibarr standards:
- MVC architecture
- Hook system
- Translation management
- Integrated security
