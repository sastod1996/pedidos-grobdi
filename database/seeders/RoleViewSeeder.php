<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleViewSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles_views')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::statement(<<<'SQL'
INSERT INTO `roles_views` (`id`, `role_id`, `view_id`, `created_at`, `updated_at`) VALUES
(1,1,1,NULL,NULL),
(2,1,2,NULL,NULL),
(3,1,3,NULL,NULL),
(4,1,4,NULL,NULL),
(5,1,5,NULL,NULL),
(6,1,6,NULL,NULL),
(7,1,7,NULL,NULL),
(8,1,8,NULL,NULL),
(9,1,9,NULL,NULL),
(10,1,10,NULL,NULL),
(11,1,11,NULL,NULL),
(12,1,12,NULL,NULL),
(13,1,13,NULL,NULL),
(14,1,14,NULL,NULL),
(15,1,15,NULL,NULL),
(16,1,16,NULL,NULL),
(17,1,17,NULL,NULL),
(18,1,18,NULL,NULL),
(19,1,19,NULL,NULL),
(20,1,20,NULL,NULL),
(21,1,21,NULL,NULL),
(22,1,22,NULL,NULL),
(23,1,23,NULL,NULL),
(24,1,24,NULL,NULL),
(25,1,25,NULL,NULL),
(26,1,26,NULL,NULL),
(27,6,27,NULL,NULL),
(28,6,29,NULL,NULL),
(29,6,30,NULL,NULL),
(30,6,33,NULL,NULL),
(31,6,76,NULL,NULL),
(32,6,77,NULL,NULL),
(33,6,78,NULL,NULL),
(34,6,79,NULL,NULL),
(35,6,80,NULL,NULL),
(36,6,81,NULL,NULL),
(37,6,82,NULL,NULL),
(38,6,83,NULL,NULL),
(39,6,26,NULL,NULL),
(40,6,84,NULL,NULL),
(41,6,85,NULL,NULL),
(42,6,86,NULL,NULL),
(43,2,26,NULL,NULL),
(44,5,107,NULL,NULL),
(45,5,108,NULL,NULL),
(46,5,109,NULL,NULL),
(47,2,89,NULL,NULL),
(48,2,93,NULL,NULL),
(49,2,94,NULL,NULL),
(50,2,95,NULL,NULL),
(51,2,96,NULL,NULL),
(52,2,97,NULL,NULL),
(53,2,98,NULL,NULL),
(54,2,99,NULL,NULL),
(55,2,100,NULL,NULL),
(56,2,101,NULL,NULL),
(57,2,102,NULL,NULL),
(58,2,103,NULL,NULL),
(59,2,104,NULL,NULL),
(60,2,105,NULL,NULL),
(61,2,106,NULL,NULL),
(62,11,43,NULL,NULL),
(63,11,44,NULL,NULL),
(64,11,45,NULL,NULL),
(65,11,46,NULL,NULL),
(66,11,47,NULL,NULL),
(67,11,48,NULL,NULL),
(68,11,49,NULL,NULL),
(69,11,50,NULL,NULL),
(70,11,51,NULL,NULL),
(71,11,52,NULL,NULL),
(72,11,53,NULL,NULL),
(73,11,54,NULL,NULL),
(74,11,55,NULL,NULL),
(75,11,56,NULL,NULL),
(76,11,57,NULL,NULL),
(77,11,58,NULL,NULL),
(78,11,59,NULL,NULL),
(79,11,60,NULL,NULL),
(80,11,61,NULL,NULL),
(81,11,62,NULL,NULL),
(82,11,63,NULL,NULL),
(83,11,64,NULL,NULL),
(84,11,65,NULL,NULL),
(85,11,66,NULL,NULL),
(86,11,67,NULL,NULL),
(87,11,68,NULL,NULL),
(88,11,69,NULL,NULL),
(89,11,70,NULL,NULL),
(90,11,71,NULL,NULL),
(91,11,72,NULL,NULL),
(92,11,73,NULL,NULL),
(93,11,74,NULL,NULL),
(94,11,87,NULL,NULL),
(95,11,88,NULL,NULL),
    (198,11,141,NULL,NULL),
(96,4,110,NULL,NULL),
(97,4,111,NULL,NULL),
(98,4,112,NULL,NULL),
(99,4,113,NULL,NULL),
(100,4,26,NULL,NULL),
(101,11,26,NULL,NULL),
(102,5,26,NULL,NULL),
(103,11,27,NULL,NULL),
(104,11,29,NULL,NULL),
(105,11,30,NULL,NULL),
(106,11,31,NULL,NULL),
(107,11,32,NULL,NULL),
(108,11,33,NULL,NULL),
(109,11,35,NULL,NULL),
(110,11,36,NULL,NULL),
(111,11,40,NULL,NULL),
(112,3,26,NULL,NULL),
(113,3,27,NULL,NULL),
(114,3,39,NULL,NULL),
(115,3,114,NULL,NULL),
(116,3,115,NULL,NULL),
(117,3,116,NULL,NULL),
(118,6,50,NULL,NULL),
(119,8,26,NULL,NULL),
(120,8,27,NULL,NULL),
(121,8,33,NULL,NULL),
(122,8,41,NULL,NULL),
(123,7,26,NULL,NULL),
(124,7,27,NULL,NULL),
(125,7,33,NULL,NULL),
(126,7,34,NULL,NULL),
(127,7,42,NULL,NULL),
(128,4,27,NULL,NULL),
(129,4,37,NULL,NULL),
(130,4,38,NULL,NULL),
(131,4,33,NULL,NULL),
(132,12,26,NULL,NULL),
(133,12,89,NULL,NULL),
(134,12,90,NULL,NULL),
(135,12,91,NULL,NULL),
(136,12,92,NULL,NULL),
(137,1,27,NULL,NULL),
(138,11,34,NULL,NULL),
(139,11,75,NULL,NULL),
(140,12,117,NULL,NULL),
(141,12,118,NULL,NULL),
(142,12,119,NULL,NULL),
(143,12,120,NULL,NULL),
(144,12,121,NULL,NULL),
(145,12,122,NULL,NULL),
(146,12,123,NULL,NULL),
(147,12,124,NULL,NULL),
(148,12,125,NULL,NULL),
(149,12,126,NULL,NULL),
(150,2,127,NULL,NULL),
(151,1,128,NULL,NULL),
(152,1,129,NULL,NULL),
(153,1,130,NULL,NULL),
(154,1,50,NULL,NULL),
(199,1,141,NULL,NULL),
(155,1,131,NULL,NULL),
(156,1,89,NULL,NULL),
(157,1,90,NULL,NULL),
(158,1,91,NULL,NULL),
(159,1,92,NULL,NULL),
(160,1,93,NULL,NULL),
(161,1,94,NULL,NULL),
(162,1,95,NULL,NULL),
(163,1,104,NULL,NULL),
(164,1,105,NULL,NULL),
(165,1,106,NULL,NULL),
(166,1,117,NULL,NULL),
(167,1,118,NULL,NULL),
(168,1,119,NULL,NULL),
(169,1,120,NULL,NULL),
(170,1,121,NULL,NULL),
(171,1,122,NULL,NULL),
(172,1,123,NULL,NULL),
(173,1,124,NULL,NULL),
(174,1,125,NULL,NULL),
(175,1,126,NULL,NULL),
(176,1,132,NULL,NULL),
(177,1,133,NULL,NULL),
(178,1,134,NULL,NULL),
(179,4,28,NULL,NULL),
(180,1,135,NULL,NULL),
(181,1,136,NULL,NULL),
(182,1,137,NULL,NULL),
(183,1,103,NULL,NULL),
(184,3,128,NULL,NULL),
(188,3,133,NULL,NULL),
(189,3,134,NULL,NULL),
(190,3,135,NULL,NULL),
(191,3,136,NULL,NULL),
(192,3,137,NULL,NULL),
(193,1,29,NULL,NULL),
(194,1,30,NULL,NULL),
(195,1,138,NULL,NULL),
(196,1,139,NULL,NULL),
(197,1,140,NULL,NULL),
(200,1,79,NULL,NULL),
(201,1,80,NULL,NULL),
(202,1,81,NULL,NULL),
(203,1,82,NULL,NULL),
(204,1,83,NULL,NULL);
SQL);

        // Ensure the bonificaciones view exists; if not, create module + view then grant permissions.
        $bonView = DB::table('views')->where('url', 'bonificaciones.index')->first();
        if (! $bonView) {
            // Try to find or create the module 'Bonificaciones'
            $module = DB::table('modules')->where('name', 'Bonificaciones')->first();
            if (! $module) {
                $moduleId = DB::table('modules')->insertGetId([
                    'name' => 'Bonificaciones',
                    'description' => 'Módulo de bonificaciones y metas de visitadoras',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $moduleId = $module->id;
            }

            $viewId = DB::table('views')->insertGetId([
                'url' => 'bonificaciones.index',
                'is_menu' => 1,
                'description' => 'Bonificaciones',
                'icon' => 'fas fa-coins',
                'state' => 1,
                'module_id' => $moduleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bonView = DB::table('views')->where('id', $viewId)->first();
        }

        if ($bonView) {
            $existsAdmin = DB::table('roles_views')->where('role_id', 1)->where('view_id', $bonView->id)->first();
            if (! $existsAdmin) {
                DB::table('roles_views')->insert([
                    'role_id' => 1,
                    'view_id' => $bonView->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $existsVisitador = DB::table('roles_views')->where('role_id', 6)->where('view_id', $bonView->id)->first();
            if (! $existsVisitador) {
                DB::table('roles_views')->insert([
                    'role_id' => 6,
                    'view_id' => $bonView->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Ensure all bonificaciones/visitadoras related views exist and are granted to roles
        $neededViews = [
            'bonificaciones.index',
            'visitadoras.metas',
            'visitadoras.metas.form',
            'visitadoras.metas.store',
            'visitadoras.metas.details',
            'visitadoras.metas.not-reached-config.index',
            'visitadoras.metas.not-reached-config.store',
            'visitadoras.metas.not-reached-config.active',
            'visitadoras.metas.update.debited-amount',
            'visitadoras.metas.show',
        ];

        foreach ($neededViews as $viewUrl) {
            $view = DB::table('views')->where('url', $viewUrl)->first();
            if (! $view) {
                // create minimal view record; attach to Bonificaciones module if exists
                $module = DB::table('modules')->where('name', 'Bonificaciones')->first();
                $moduleId = $module ? $module->id : null;
                $viewId = DB::table('views')->insertGetId([
                    'url' => $viewUrl,
                    'is_menu' => 0,
                    'description' => ucfirst(str_replace(['.', '-'], ' ', $viewUrl)),
                    'icon' => null,
                    'state' => 1,
                    'module_id' => $moduleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $view = DB::table('views')->where('id', $viewId)->first();
            }

            if ($view) {
                foreach ([1, 6] as $roleId) {
                    $exists = DB::table('roles_views')->where('role_id', $roleId)->where('view_id', $view->id)->first();
                    if (! $exists) {
                        DB::table('roles_views')->insert([
                            'role_id' => $roleId,
                            'view_id' => $view->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
