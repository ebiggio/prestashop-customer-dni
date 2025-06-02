# <img src="logo.png" width="32" height="32" alt="Module logo"> Customer DNI module for PrestaShop

## Version 1.1.2

By default, PrestaShop does not allow setting the customer's DNI (National Identity Document) when creating an account.
Instead, the field is saved at the address level, which for some cases is not the most appropriate. There's also no additional validation for the DNI field;
a customer can have multiple addresses with different DNIs, and even the same DNI can be used for multiple addresses for different customers.
This module adds a new field to the registration and edit form of the customer, changing the handling of the DNI field at the customer's personal information level.
The module also provides additional validation options for this field, such as making it required, unique, and validating it against a regular expression.

## Features

- Adds a new field to the registration and edit form of the customer, allowing them to save their DNI.
- Displays the DNI field in the back office as well, so it can be easily viewed and edited by back office users. It also displays the DNI in the customer list.
- Allows setting the DNI as required, unique, and validate it against a regular expression using the module's configuration page.
- Option to overwrite the DNI field of the addresses when the customer DNI changes, so that value is copied to all the addresses of that customer. Useful for modules that use the address DNI field.
- Additional validations can be added by uploading a custom validation class inside the module's `custom_validators` folder. The module includes a custom validator that checks if the DNI is a valid
  chilean RUT.
- The DNI is stored in a new table in the database, linked to the customer's ID, so it can be easily retrieved and used in other modules or customizations.
- Provides custom hooks to add additional functionality programmatically.
- The module is fully translatable.

## Requirements

- Developed on PrestaShop 8.1, but _should work_ (not tested) on any version of PrestaShop 1.7.7 or higher.
- PHP 8.0 or higher.
- Composer, to generate the necessary autoload files if installing from this repository.

## Installation

To install the module in your PrestaShop instance, you will need a ZIP file containing the module's code and its dependencies.
You can get this ZIP file in two ways: downloading it from the repository's releases or generating it from the source code.

### Getting the module ZIP file

#### From the repository's releases

1. Download the ZIP file of the latest release from the [Releases](https://github.com/ebiggio/prestashop-customer-dni/releases) section.

#### From the source code

1. Download this repository to a folder named `customer_dni`.
2. Enter that folder, and run the following command to generate the necessary Composer autoload files:

```bash
composer dump-autoload -o --no-dev
```

3. Compress the folder into a ZIP file.

### Installing the module in PrestaShop

1. Upload the ZIP file to your PrestaShop instance. You can do this by going to the back office of your PrestaShop store and navigating to the `Modules` section.
   Click on the `Upload a module` button and select the ZIP file you just created. You can also upload the ZIP file directly to the `modules` folder of your PrestaShop installation.
   If you choose this method, make sure to extract the ZIP file after uploading it, so the `customer_dni` folder is created inside the `modules` folder.
2. After uploading the ZIP file, the module should appear in the list of modules in the back office, where you can install it.
3. Once the module is installed, click on the `Configure` button to access the module's configuration page.
4. Configure the module according to your needs and save the changes.
5. The module is now ready to use. The DNI field should be displayed in the registration and edit form of the customer.

## Configuration

The module has a configuration page where you can set the following options:

- **Display customer DNI in back office**: Show the DNI field in the customer list of the back office, which also allows filtering and ordering by DNI value.
- **Mark as required**: Make the DNI field required in the registration and edit form of the customer.
- **Mark as unique**: Make the DNI field unique, so the same DNI cannot be used by multiple customers.
- **Overwrite address DNI field**: Overwrite the default DNI field in the address form, so when the customer saves the DNI in the customer form,
  it is also saved in the address DNI field for **all** the addresses related to that customer.
  Keep in mind that the default DNI field for the address has a maximum length of 16 characters, so if the customer DNI is longer than that, it will be truncated.
- **Validate against a regular expression**: Set a regular expression to use for validating the DNI.
- **Use custom validators**: Enable the use of custom validators for the DNI field.
  You can upload a custom validation class inside the module's `custom_validators` folder to add additional validations to the DNI field.

## Usage

Once the module is installed and configured, the DNI field should be displayed in the registration and edit form of the customer. The DNI field is also displayed in the back office, so users with
customer edit permissions can easily edit it.

The DNI field is stored in a new table in the database, linked to the customer's ID.
Upon resetting or uninstalling the module, the DNI field will be removed from the customer form, but previously saved DNI data will remain in the module's `customer_dni` table.

### Working with the customer DNI programmatically

The module provides a method to retrieve the customer's DNI programmatically, which can be useful for other modules or customizations that need to access this information.
Using the main module class, `CustomerDNI`, with the `getDNIByCustomerID` method, you can retrieve the DNI value by passing the customer ID as a parameter:

```php
$customer_id = 1; // The ID of the customer you want to retrieve the DNI from.
$dni = '';

$customerDNIModule = Module::getInstanceByName('customer_dni');
if ($customerDNIModule && $customerDNIModule->active) {
    $dni = $customerDNIModule->getDNIByCustomerID($customerID);
}
```

This method will return the DNI value as a string, or an empty string if the customer does not have a DNI associated with it.

#### Custom hooks

The module also offers two custom hooks that can be used to add additional functionality when the customer DNI is saved or deleted:

- `actionCustomerDNIAddAfter`: Triggered when the customer DNI is saved to the database. It will be fired when a new DNI is added or when an existing DNI is updated.
- `actionCustomerDNIDeleteAfter`: Triggered when the customer DNI is removed from the database, which usually happens when the customer is deleted.
  This hook will trigger even if no DNI was associated with the customer at the time of deletion.

Both hooks return the customer ID and the DNI value as parameters.

## Customization

You can customize the module by adding additional validators for the DNI field.
To do this, add a custom validator class that implements the `CustomValidator` interface to the `custom_validators` folder.
The module includes a built-in custom validator that checks the DNI against the chilean RUT format.
You can use this class as a reference to create your own custom validator.

## License

This module is released under GNU General Public License version 3. You can find a copy of the license in the [LICENSE](LICENSE) file.

## TODOs

Ideas for future improvements not covered in the current version of the module:

- ~~Allow getting the customer DNI directly from PrestaShop's `Customer` ObjectModel, instead of querying the database.~~  
This would require overriding the `CustomerCore` class, which would make this module incompatible with other modules that also do this override.
Making overrides is also not recommended per PrestaShop's guidelines: [PrestaShop developer documentation - Overrides](https://devdocs.prestashop-project.org/8/modules/concepts/overrides/)
- Allow getting the customer DNI through the API.
- Add support for PrestaShop 1.7.6 and earlier versions.
- Allow selecting the location of the DNI field in the customer form of the front office (e.g., before or after the email field).
- Validate the DNI during customer creation through the API (not sure if this check is actually needed).
- Define hooks to add additional custom validations to the customer's DNI programmatically.