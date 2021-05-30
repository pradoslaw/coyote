# Laravel Grid

[![Build Status](https://travis-ci.org/adam-boduch/laravel-grid.svg?branch=master)](https://travis-ci.org/adam-boduch/laravel-grid)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adam-boduch/laravel-grid/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adam-boduch/laravel-grid/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c0df3ba9-4c1f-4063-8347-8b51eca079fe/mini.png)](https://insight.sensiolabs.com/projects/c0df3ba9-4c1f-4063-8347-8b51eca079fe)
[![StyleCI](https://styleci.io/repos/64660184/shield?branch=master)](https://styleci.io/repos/64660184)

**Laravel Grid** is a package that helps you display table data. I could not find
package that would satisfy my needs so I decided to write one. Now I've been successfully using it in my two projects.
I hope you will enjoy it.

Example:

```php
namespace App\Http\Controllers;

use Boduch\Grid\Order;
use Boduch\Grid\Source\EloquentSource;

class UsersController extends Controller
{
    public function index()
    {
        $grid = app('grid.builder')
            ->createBuilder()
            ->setDefaultOrder(new Order('id', 'desc'))
            ->addColumn('id', [
                'sortable' => true
            ])
            ->addColumn('name')
            ->addColumn('email')
            ->addColumn('created_at')
            ->setSource(new EloquentSource(new \App\Models\User()));
            
        return view('users')->with('grid', $grid);
    }
    
}
````

**Features**

* Pagination
* Filtering
* Sorting
* Highly customizable
* Simple usage
* Different data source (Eloquent model, collection, array)

## Installation

**Requirements**

* PHP >= 7.0
* Laravel >= 5.2

**Installation steps**

1. run `composer require adam-boduch/laravel-grid`
2. open file `config/app.php`
3. add `Boduch\Grid\GridServiceProvider::class` into `providers` array

## Getting started

To keep your controllers clean, it's highly recommended to keep your grid classes as a separate php file. 

## Cookbook

### Using twig

```twig
{{ grid | raw }}
```

#### Laravel Grid and repository pattern

@todo

#### Laravel Grid and presentation pattern

@todo

#### Table cell modification

@todo

#### Different column name and filter name

@todo
