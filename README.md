# Odoo JSON-2 PHP Client

A simple PHP client library for interacting with Odoo's JSON-2 API (Odoo 19+). This library provides a simple way to perform CRUD operations and call custom methods using Odoo's Json2 API.  
Uses Guzzle HTTP client for the actual communication with Odoo.

## Features

- Modern JSON-2 API support (Odoo 19+)
- API key-based authentication
- Full CRUD operations (Create, Read, Update, Delete)
- Built on Guzzle HTTP client

## Requirements

- PHP 8.2 or higher
- Composer
- Odoo 19 or higher with JSON-2 API enabled

## Installation

```bash
composer require shusaura85/odoo-json2-php
```

## Configuration

### Getting Your API Key

1. Log in to your Odoo instance
2. Go to **Settings** → **Users & Companies** → **API Keys**
3. Create a new API key
4. Copy the generated API key

## Usage

### Basic Setup

```php
use OdooPHP\Client;

$client = new Client(
    baseUri: 'https://your-odoo-instance.com',
    apiKey: 'your-api-key-here',
    database: 'your-database-name'
);
```

### Search Records

```php
// Search with domain
$partners = $client->search('res.partner', [
    ['name', '=', 'John Doe']
]);

// Search with options
$partners = $client->search('res.partner', [
    ['is_company', '=', true]
], [
    'limit' => 10,
    'offset' => 0,
    'order' => 'name asc'
]);
```

### Read Records

```php
// Read specific records
$partners = $client->read('res.partner', [1, 2, 3]);

// Read with specific fields
$partners = $client->read('res.partner', [1, 2, 3], [
    'name',
    'email',
    'phone'
]);
```

### Search and Read

```php
// Search and read in one call
$partners = $client->search_read('res.partner', [
    ['is_company', '=', true]
], [
    'fields' => ['name', 'email', 'phone'],
    'limit' => 10
]);
```

### Create Records

```php
// Create a single record
$partnerId = $client->create('res.partner', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'is_company' => false
]);

// Create multiple records
$partnerIds = $client->create('res.partner', [
    [
        'name' => 'Company A',
        'is_company' => true,
    ],
    [
        'name' => 'Company B',
        'is_company' => true,
    ],
]);
```

### Update Records

```php
// Update records
$success = $client->write('res.partner', [1, 2], [
    'email' => 'newcontact@example.com',
    'phone' => '+9876543210'
]);
```

### Delete Records

```php
// Delete records
$success = $client->unlink('res.partner', [1, 2, 3]);
```

### Count Records

```php
// Count records matching a domain
$count = $client->count('res.partner', [
    ['is_company', '=', true]
]);
```

### Get Model Fields

```php
// Get all fields of a model
$fields = $client->fields_get('res.partner');

// Get specific field attributes
$fields = $client->fields_get('res.partner', ['type', 'required', 'string']);
```

### Custom Method Calls
For any other method or action  

```php
// Call any custom method on a model
$result = $client->call('sale.order', 'action_confirm', [
    'ids' => [123],
    'context' => []
]);
```

### Error Handling

```php
use OdooPHP\OdooException;

try {
    $partners = $client->search('res.partner', []);
}  catch (OdooException $e) {
    // Handle other Odoo errors
    echo "Odoo error: " . $e->getMessage();
}
```

## Domain Syntax

The domain syntax (filters) follows Odoo's standard format:

```php
// Simple condition
[['field', '=', 'value']]

// Multiple conditions (AND)
[
    ['field1', '=', 'value1'],
    ['field2', '!=', 'value2']
]

// OR conditions
['|', ['field1', '=', 'value1'], ['field2', '=', 'value2']]

// Operators: =, !=, <, >, <=, >=, like, ilike, in, not in, child_of, parent_of
```

## Examples

### Working with Sales Orders

```php
// Create a sales order
$orderId = $client->create('sale.order', [
    'partner_id' => 1,
    'order_line' => [
        [
            'product_id' => 5,
            'product_uom_qty' => 10,
            'price_unit' => 100.00,
        ],
    ],
]);

// Confirm the order
$client->call('sale.order', 'action_confirm', [
    'ids' => [$orderId]
]);

// Search for confirmed orders
$orders = $client->search_read('sale.order', [
    ['state', '=', 'sale']
], 
    fields: ['name', 'partner_id', 'amount_total'],
    limit: 20
);
```

### Working with Products

```php
// Create a product
$productId = $client->create('product.product', [
    'name' => 'New Product',
    'type' => 'product',
    'categ_id' => 1,
    'list_price' => 99.99,
    'standard_price' => 50.00,
]);

// Update product price
$client->write('product.product', [$productId], [
    'list_price' => 89.99
]);

// Search products by category
$products = $client->search_read('product.product', [
    ['categ_id', '=', 1],
    ['sale_ok', '=', true]
], 
    fields: ['name', 'list_price', 'qty_available'],
    order: 'list_price desc'
);
```

## Laravel

If you need an implementation for Laravel, see [Oscar-Team/Odoo-Json2](https://github.com/oscar-team/odoo-json2) library.  

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For issues, questions, or contributions, please open an issue on GitHub.
