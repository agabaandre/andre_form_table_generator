# Form Generator

This project demonstrates the usage of the `FormGenerator` library in PHP, CodeIgniter 3, and Laravel for dynamically generating forms with dropdowns and multi-select fields from a database.

## Requirements

- PHP 7.4+
- Composer
- MySQL
- CodeIgniter 3 (for the CI example)
- Laravel (for the Laravel example)

## Installation

1. Install dependencies using Composer:

   ```sh
   composer require formgenerator/formgenerator
   ```

2. Ensure that your database is properly configured in your application.

---

## Usage

### PHP Standalone Implementation

```php
require 'vendor/autoload.php';

use FormGenerator\FormGenerator;
use FormGenerator\TableGenerator;

// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=mydb", "username", "password");

// Define select fields dynamically
$selectFields = [
    'user_id' => ['users', 'id', 'name'], // Dropdown of users
    'category' => ['categories', 'id', 'category_name'] // Dropdown of categories
];

$formGen = new FormGenerator($pdo, $selectFields);

// Generate form
echo $formGen->generateForm('posts', 2);
```

---

### CodeIgniter 3 Implementation

#### Controller (`FormController.php`)

```php
require APPPATH . '../vendor/autoload.php';

use FormGenerator\FormGenerator;

class FormController extends CI_Controller
{
    public function index()
    {
        $this->load->database();

        $pdo = new PDO("mysql:host=" . $this->db->hostname . ";dbname=" . $this->db->database,
                       $this->db->username,
                       $this->db->password);

        $selectFields = [
            'user_id' => ['users', 'id', 'name'],
            'category_id' => ['categories', 'id', 'category_name']
        ];

        $multiSelectFields = [
            'tags' => ['tags', 'id', 'tag_name']
        ];

        $select2Fields = ['category_id', 'tags']; // Enable Select2 for these fields

        $formGen = new FormGenerator($pdo, $selectFields, $multiSelectFields, $select2Fields);

        $data['form'] = $formGen->generateForm('posts', 2);

        $this->load->view('form_view', $data);
    }
}
```

#### View (`form_view.php`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>Form Generator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
    <h2>Generated Form</h2>
    <?= $form ?> <!-- Render the generated form -->
</body>
</html>
```

---

### Laravel Implementation

#### Controller (`FormController.php`)

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;
use FormGenerator\FormGenerator;

class FormController extends Controller
{
    public function index()
    {
        // Laravel DB Connection
        $pdo = new PDO("mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_DATABASE'),
                        env('DB_USERNAME'),
                        env('DB_PASSWORD'));

        // Define dynamic select fields
        $selectFields = [
            'user_id' => ['users', 'id', 'name'], // Dropdown for users
            'category_id' => ['categories', 'id', 'category_name']
        ];

        // Initialize FormGenerator
        $formGen = new FormGenerator($pdo, $selectFields);

        // Generate form for `posts` table
        $form = $formGen->generateForm('posts', 2);

        // Pass data to the view
        return view('form_view', compact('form'));
    }
}
```

#### View (`form_view.blade.php`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>Form Generator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
    <h2>Generated Form</h2>
    {!! $form !!} <!-- Render the generated form -->
</body>
</html>
```

---

## License

This project is licensed under the MIT License.
