<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking;

use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Entity\Venue;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

class EventBookingPackage extends AbstractPackage implements ServiceProviderInterface
{
    public function __construct(protected ApplicationInterface $app)
    {
        //
    }

    public function register(Container $container): void
    {
        $container->share(static::class, $this);

        $container->mergeParameters(
            'renderer.paths',
            [
                static::path('views'),
            ],
            Container::MERGE_OVERRIDE
        );

        $container->mergeParameters(
            'renderer.edge.components',
            [
                'event-edit-nav' => 'event.components.event-edit-nav'
            ]
        );
    }

    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(static::path('etc/*.php'), 'config');
        $installer->installLanguages(static::path('resources/languages/**/*.ini'), 'lang');
        $installer->installMigrations(static::path('resources/migrations/**/*'), 'migrations');
        $installer->installSeeders(static::path('resources/seeders/**/*'), 'seeders');
        $installer->installRoutes(static::path('routes/**/*.php'), 'routes');

        $installer->installMVCModules(Event::class, ['Admin']);
        $installer->installMVCModules(EventAttend::class, ['Admin']);
        $installer->installMVCModules('EventCheckin', ['Admin'], false);
        $installer->installMVCModules(EventOrder::class);
        $installer->installMVCModules(EventPlan::class, ['Admin']);
        $installer->installMVCModules(EventStage::class);
        $installer->installMVCModules(Venue::class, ['Admin']);
        $installer->installMVCModules('EventAttending', ['Front'], false);
    }

    public function config(string $name, ?string $delimiter = '.'): mixed
    {
        return $this->app->config('event_booking' . $delimiter . $name, $delimiter);
    }
}
