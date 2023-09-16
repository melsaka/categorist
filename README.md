# Categorist

Categorist is a laravel package designed to handle hierarchical categorization for various models within your application. It allows you to organize your data into categories and provides an easy-to-use interface to perform operations like adding, editing, and querying categories.

## Installation

Add the package to your Laravel app via Composer:

```bash
composer require melsaka/categorist
```

Register the package's service provider in config/app.php.

```php
'providers' => [
    ...
    Melsaka\Categorist\CategoristServiceProvider::class,
    ...
];
```

Run the migrations to add the required table to your database:

```bash
php artisan migrate
```

Add `Categorized` trait to the model that you want categorize:

```php
use Melsaka\Categorist\Categorized;

class Post extends Model
{
    use Categorized;
    
    // ...   
}
```

## Configuration

To configure the package, publish its configuration file:

```bash
php artisan vendor:publish --tag=categorist
```

You can then modify the configuration file to change the categories table name if you want, default: `categories`.

## Usage

### Adding a New Category

You can add a new category for a specific model, such as **Post**, by using the `Category::add()` method. If a category with the same **slug** and **type** already exists, it will be **returned** instead. Here's an example:

```php
$data = [
    'name' => 'Foo',
    'slug' => 'foo',
    'children' => [
        [
            'name' => 'Bar',
            'slug' => 'bar',
            'children' => [
                [
                    'name' => 'Baz',
                    'slug' => 'baz',
                ],
            ],
        ],
    ],
];

$category = Category::add(Post::class, $data);
```

If you don't want the existed record to be returned:

```php
$category = Category::add(Post::class, $data, false); // will throw duplicate entry error if record exists 
```

### Retrieving Models Related to a Category

You can retrieve all models related to a specific category using the `categorized()` method:

```php
$posts = $category->categorized();
```

### Checking if a Category is Newly Added

You can check whether a category is newly added or if it already existed and was returned using the `isNew()` method:

```php
$isNew = Category::isNew($category);
```

### Editing a Category

To edit a category, use the `Category::edit()` method. Pass the category instance and an array of data to update:

```php
$data = [
    'name' => 'New Category Name',
];

Category::edit($category, $data);
```

### Removing a Category

You can remove a category using the `Category::remove()` method. Pass either a category instance or its ID:

```php
Category::remove($category);
```

### Synchronizing Categories with a Model

To synchronize categories with a model, you can use the `Category::syncWith()` method. This associates a category with a model:

```php
Category::syncWith($post, $category);
```

### Checking if a Model has a Category

You can check whether a model has a specific category using the `Category::has()` method:

```php
$hasCategory = Category::has($post, $category);
```

### Listing Categories

To list all categories of a specific type, you can use the `Category::list()` method:

```php
$categories = Category::list(Post::class);
```

### Retrieving a Category Tree

To retrieve a hierarchical list of all categories for a specific type, you can use the `Category::treeList()` method:

```php
$categoryTree = Category::treeList(Post::class);
```

### Working with Model Relationships

You can retrieve categories associated with a model using Eloquent relationships. For example, to retrieve categories for a `Post` model:

```php
$postWithcategories = Post::with('categories')->get();

// or retrieve only the categories

$postCategories = $post->categories;

$postCategories = Category::ofThis($post);

$firstCategory = $post->firstCategory();
```

To retrieve the parent categories of a model, you can use the `parentCategories()` method:

```php
$parentCategories = $post->parentCategories();
```

To retrieve a hierarchical list of categories associated with a model, you can use the `treeCategories()` method:

```php
$treeCategories = $post->treeCategories();
```

You can retrieve a list of category names associated with a model using the `categoriesList()` method:

```php
$categoryNames = $post->categoriesList();
```

You can `attach`, `detach`, or `sync` multiple categories to a model using the following methods:

```php
// Attach categories to a model
$post->attachCategories($category);

// Detach categories from a model
$post->detachCategories($category);

// Sync categories with a model
$post->syncCategories($category);

// Check if a model has specific categories
$post->hasCategories($cat, $category);

// Check if a model has all of the specified categories
$post->hasAllCategories($cat, $category);
```

### Additional Methods

You can also retrieve related data using additional methods:

```php
// Retrieve all models related to a category
$posts = $category->categorized()->get();

// Retrieve the parent of a category
$parent = Category::with('parent')->get();

// Retrieve the children of a category
$children = Category::with('children')->get();

// Retrieve the ancestors of a category
$ancestors = Category::with('ancestors')->get();

// Retrieve the descendants of a category
$descendants = Category::with('descendants')->get();
```

**Note**: Check (**Nested Sets**)[https://github.com/lazychaser/laravel-nestedset] package for **more methods** and **details**.

## License

This package is released under the MIT license (MIT).
