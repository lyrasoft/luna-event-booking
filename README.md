# LYRASOFT EventBooking Package

## Installation

Install from composer

```shell
composer require lyrasoft/event-booking
```

EventBooking dependents on [lyrasoft/sequence](https://github.com/lyrasoft/luna-sequence) 
Please read their README and configure them first.

Then copy files to project

```shell
php windwalker pkg:install lyrasoft/event-booking -t routes -t migrations -t seeders
```

### Seeders

Add these files to `resources/seeders/main.php`

```php
return [
    // ...
    
    __DIR__ . '/venue-seeder.php',
    __DIR__ . '/event-seeder.php',
    __DIR__ . '/event-order-seeder.php',
];
```

Add these types to `category-seeder.php`

```php
    static function () use ($seeder, $orm, $db) {
        $types = [
            // ...
            
            'event' => [
                'max_level' => 1,
                'number' => 10,
            ],
            
            // Venue catagoey is optional
            'venue' => [
                'max_level' => 1,
                'number' => 5,
            ],
        ];
```

### Global Settings

WIP

### Session

As EventBooking may need to redirect to outside Payment service to process checkout, you must disable `SameSite` cookie poilicy
and set `secure` as `TRUE`.

```php
// etc/packages/session.php

return [
    'session' => [
        // ...

        'cookie_params' => [
            // ...
            'secure' => true, // <-- Set this to TRUE
            // ...
            'samesite' => CookiesInterface::SAMESITE_NONE, // Set this to `SAMESITE_NONE`
        ],
```

### Language Files

Add this line to admin & front middleware if you don't want to override languages:

```php
$this->lang->loadAllFromVendor('lyrasoft/shopgo', 'ini');

// OR
$this->lang->loadAllFromVendor(EventBookingPackage::class, 'ini');

```

Or run this command to copy languages files:

```shell
php windwalker pkg:install lyrasoft/shopgo -t lang
```

## Register Admin Menu

Edit `resources/menu/admin/sidemenu.menu.php`

```php
$menu->link('活動', '#')
    ->icon('fal fa-calendar');

$menu->registerChildren(
    function (MenuBuilder $menu) use ($nav, $lang) {        
        $menu->link('場館管理')
            ->to($nav->to('venue_list'))
            ->icon('fal fa-house-flag');
        
        $menu->link('活動分類')
            ->to($nav->to('category_list')->var('type', 'event'))
            ->icon('fal fa-sitemap');
        
        $menu->link('活動管理')
            ->to($nav->to('event_list'))
            ->icon('fal fa-calendar-days');
        
        $menu->link('報名者管理')
            ->to($nav->to('event_attend_list'))
            ->icon('fal fa-users');
        
        $menu->link('報名訂單管理')
            ->to($nav->to('event_order_list'))
            ->icon('fal fa-files');
    }
);

```
