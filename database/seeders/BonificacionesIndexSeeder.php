<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BonificacionesIndexSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure module 'Bonificaciones' exists (minimal, do not modify other modules)
        $module = DB::table('modules')->where('name', 'Bonificaciones')->first();
        if (! $module) {
            $moduleId = DB::table('modules')->insertGetId([
                'name' => 'Bonificaciones',
                'description' => 'Módulo de bonificaciones y metas de visitadoras',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $moduleId = $module->id;
        }

        // Only create the index view (menu entry) for Bonificaciones
        $viewUrl = 'bonificaciones.index';
        $existingView = DB::table('views')->where('url', $viewUrl)->first();
        if (! $existingView) {
            $viewId = DB::table('views')->insertGetId([
                'url' => $viewUrl,
                'is_menu' => 1,
                'description' => 'Bonificaciones',
                'icon' => 'fas fa-coins',
                'state' => 1,
                'module_id' => $moduleId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            $viewId = $existingView->id;
        }

        // Assign the index view permission to admin role (role_id = 1)
        $adminRoleId = 1;
        $exists = DB::table('roles_views')
            ->where('role_id', $adminRoleId)
            ->where('view_id', $viewId)
            ->first();

        if (! $exists) {
            DB::table('roles_views')->insert([
                'role_id' => $adminRoleId,
                'view_id' => $viewId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
