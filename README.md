[![Build Status](https://travis-ci.org/funayaki/cakephp-cart.svg?branch=master)](https://travis-ci.org/funayaki/cakephp-cart)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/funayaki/cakephp-cart/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/funayaki/cakephp-cart/?branch=master)

# Cart plugin for CakePHP

## Requirements

- CakePHP 3.5.0 or later

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```shell
composer require funayaki/cakephp-cart:dev-master
```

Implement EntityBuyableAwareInterface:

```php
class Item extends Entity implements EntityBuyableAwareInterface
{

    public function getPrice()
    {
        return $this->price;
    }

    public function getBuyableLimit()
    {
        return $this->buyable_limit;
    }
}
```

Load CartComponent:

```php
<?php
class AppController extends Controller
{

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Cart.Cart');
    }
}
```

## Usage

Add item to cart:

```php
$this->Cart->add($item);
```

Update item quantity in cart:

```php
$this->Cart->edit($item, 5);
```

Get item(s) in cart:

```php
$this->Cart->get($item);
$this->Cart->get();
```

Calculate item total price in cart:

```php
$this->Cart->total($item);
```

Calculate total price in cart:

```php
$this->Cart->total();
```

Count quantity item(s) in cart:

```php
$this->Cart->count($item);
$this->Cart->count();
```

Delete item from cart:

```php
$this->Cart->delete($item);
```

Delete all items from cart:

```php
$this->Cart->clear();
```
