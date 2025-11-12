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

## Payments

### Add Ecpay Payment

Install Ecpay SDK

```bash
composer require ecpay/sdk
```

Register Ecpay payments

```php
    'payment' => [
        // ...

        'gateways' => [
            // ...
            'ecpay_credit' => fn () => create(
                \Lyrasoft\EventBooking\Payment\EcpayPayment::class,
                type: EcpayPaymentType::CREDIT,
            ),
            'ecpay_atm' => fn () => create(
                \Lyrasoft\EventBooking\Payment\EcpayPayment::class,
                type: EcpayPaymentType::ATM
            ),
            'ecpay_cvs' => fn () => create(
                \Lyrasoft\EventBooking\Payment\EcpayPayment::class,
                type: EcpayPaymentType::CVS
            ),
        ],
    ],
```

And add this to `.env` and `.env.dist`

```dotenv
EVENT_ECPAY_MERCHANT_ID=2000132
EVENT_ECPAY_HASH_KEY=5294y06JbISpM5x9
EVENT_ECPAY_HASH_IV=v77hoKGq4kWxNNIS
```

Add exclude to `CsrfMiddleware` for Ecpay notify URL

```php
// routes/front.route.php

    ->middleware(
        CsrfMiddleware::class,
        excludes: [
            // ...
            'front::event_payment_task',
        ]
    )
```
