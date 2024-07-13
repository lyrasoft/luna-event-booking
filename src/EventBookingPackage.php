<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking;

use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;
use Windwalker\Utilities\StrNormalize;

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

        $this->installModules($installer, 'event', ['admin', 'model']);
        $this->installModules($installer, 'event_attend', ['admin', 'model']);
        $this->installModules($installer, 'event_checkin', ['admin']);
        $this->installModules($installer, 'event_order', ['front', 'admin', 'model']);
        $this->installModules($installer, 'event_plan', ['admin', 'model']);
        $this->installModules($installer, 'event_stage', ['front', 'admin', 'model']);
        $this->installModules($installer, 'venue', ['admin', 'model']);
        $this->installModules($installer, 'event_attending', ['front']);
    }

    protected function installModules(
        PackageInstaller $installer,
        string $name,
        array $modules = ['front', 'admin', 'model']
    ): void {
        $pascal = StrNormalize::toPascalCase($name);

        if (in_array('admin', $modules, true)) {
            $installer->installModules(
                [
                    static::path("src/Module/Admin/$pascal/**/*") => "@source/Module/Admin/$pascal",
                ],
                ['Lyrasoft\\EventBooking\\Module\\Admin' => 'App\\Module\\Admin'],
                ['modules', $name . '_admin'],
            );
        }

        if (in_array('front', $modules, true)) {
            $installer->installModules(
                [
                    static::path("src/Module/Front/$pascal/**/*") => "@source/Module/Front/$pascal",
                ],
                ['Lyrasoft\\EventBooking\\Module\\Front' => 'App\\Module\\Front'],
                ['modules', $name . '_front']
            );
        }

        if (in_array('model', $modules, true)) {
            $installer->installModules(
                [
                    static::path("src/Entity/$pascal.php") => '@source/Entity',
                    static::path("src/Repository/{$pascal}Repository.php") => '@source/Repository',
                ],
                [
                    'Lyrasoft\\EventBooking\\Entity' => 'App\\Entity',
                    'Lyrasoft\\EventBooking\\Repository' => 'App\\Repository',
                ],
                ['modules', $name . '_model']
            );
        }
    }

    public function config(string $name, ?string $delimiter = '.'): mixed
    {
        return $this->app->config('event_booking' . $delimiter . $name, $delimiter);
    }
}
