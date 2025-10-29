<?php

namespace App\Providers;

use App\Domain\Interfaces\ReportsRepositoryInterface;
use App\Infrastructure\Repository\ReportsRepository;
use App\Models\View;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            ReportsRepositoryInterface::class,
            ReportsRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->app['events']->listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = Auth::user();

            if ($user && $user->role) {
                $role = $user->role;

                foreach ($role->modules as $module) {
                    $submenu = [];

                    foreach ($role->views->where('module_id', $module->id)->where('is_menu', true) as $view) {
                        $submenu[] = [
                            'text' => $view->description,
                            'route' => $view->url,
                            'icon' => $view->icon ?? 'far fa-circle',
                        ];
                    }

                    if (count($submenu) > 0) {
                        $event->menu->add([
                            'text' => $module->name,
                            'icon' => $module->icon ?? 'fas fa-folder',
                            'submenu' => $submenu,
                        ]);
                    }
                }
            }
        });

        // Gate::before(function (User $user, string $ability) {
        //     if ($user->role->name === 'admin') {
        //         return true;
        //     }
        // });
        // 🔹 Crea gates dinámicamente según las views
        if (Schema::hasTable('views')) {
            foreach (View::all() as $view) {
                Gate::define($view->url, function ($user) use ($view) {
                    return $user->role->views->contains($view);
                });
            }
        }
        //     Gate::define('motorizados', function (User $user) {
        //         return $user->role->name === 'motorizado';
        //     });
        //     Gate::define('contabilidad', function (User $user) {
        //         return $user->role->name === 'contabilidad';
        //     });
        //     Gate::define('laboratorio', function (User $user) {
        //         return $user->role->name === 'laboratorio';
        //     });
        //     Gate::define('tecnico_produccion', function (User $user) {
        //         return $user->role->name === 'tecnico_produccion';
        //     });
        //     Gate::define('counter', function (User $user) {
        //         return $user->role->name === 'counter';
        //     });
        //     Gate::define('visitador', function (User $user) {
        //         return $user->role->name === 'visitador';
        //     });
        //     Gate::define('jefe-operaciones', function (User $user) {
        //          if($user->role->name === 'jefe-operaciones'){
        //              return true;
        //          }
        //     });
        //     Gate::define('counter-jefe_operaciones', function (User $user) {
        //         if($user->role->name === 'jefe-operaciones' or $user->role->name === 'counter'){
        //             return true;
        //         }
        //    });
        //     Gate::define('gerencia-general', function (User $user) {
        //         return $user->role->name === 'gerencia-general';
        //     });
        //     Gate::define('coordinador-lineas', function (User $user) {
        //         return $user->role->name === 'coordinador-lineas';
        //     });
        //     Gate::define('jefe-comercial', function (User $user) {
        //         return $user->role->name === 'jefe-comercial';
        //     });
        //     Gate::define('supervisor', function (User $user) {
        //         return $user->role->name === 'supervisor';
        //     });
        //     Gate::define('administracion', function (User $user) {
        //         return $user->role->name === 'Administracion';
        //     });
        Paginator::useBootstrapFive();
    }
}
