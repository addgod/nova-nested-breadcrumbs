# Nova Breadcrumbs
Breadcrumbs for Laravel Nova

[![Latest Stable Version](https://badgen.net/packagist/v/chris-ware/nova-breadcrumbs)](https://packagist.org/packages/chris-ware/nova-breadcrumbs)
[![Total Downloads](https://badgen.net/packagist/dt/chris-ware/nova-breadcrumbs)](ttps://packagist.org/packages/chris-ware/nova-breadcrumbs)
[![License](https://badgen.net/packagist/license/chris-ware/nova-breadcrumbs)](https://packagist.org/packages/chris-ware/nova-breadcrumbs)
[![StyleCI](https://github.styleci.io/repos/160367785/shield?branch=master)](https://github.styleci.io/repos/160367785)

## Installation

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash
composer require addgod/nova-nested-breadcrumbs
```

Next, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php
// in app/Providers/NovaServiceProvder.php

// ...
public function tools()
{
    return [
        // ...
        \Addgod\NestedBreadcrumbs\NestedBreadcrumbs::make(),
    ];
}
```

If you need to extend this packages middleware it is possible to do so from the config. Just publish the config as so.

```bash
php artisan vendor:publish --provider="Addgod\NestedBreadcrumbs\ToolServiceProvider"
```


Finally, add the Breadcrumbs trait to your App\Nova\Resource class.

```php
use Addgod\NestedBreadcrumbs\Traits\Breadcrumbs;

abstract class Resource extends NovaResource
{
    use Breadcrumbs;
```

And to define the parent that should be shown add the following function to any resource.

```php
/**
 * Get the parent to be displayed in the breadcrumbs.
 *
 * @return \Illuminate\Database\Eloquent\Model|null
 */
public function breadcrumbParent()
{
    return $this->model()->parentModel;
}
```

## Customisation

You can disable the bundled styles and load your own by adding the following method to the call:

```php
// in app/Providers/NovaServiceProvder.php

// ...
public function tools()
{
    return [
        // ...
        \Addgod\NestedBreadcrumbs\NestedBreadcrumbs::make()->withoutStyles(),
    ];
}
```

You can also customise both the label and title for a Resource. Label refers to the Resource's Name, so a Users Resource would have a Users label. Title refers to the title of the Resource as set by the $title property on the resource, so by default it would be the resource ID. Both of these can be overwritten for Breadcrumbs only by overwriting the functions on the trait that start with breadcrumb.

You can also conditionally disable breadcrumbs, using the static breadcrumbs method presented in the trait.

## Information

This package works using the custom header elements available on all Nova views. Due to the fact that the data available to these views differ, the rendering of breadcrumbs is API powered, to ensure that for all views, the necessary data is available and displayed. 

## Screenshots
Screenshots can be seen [here](screenshots.md).
