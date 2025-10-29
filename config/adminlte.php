<?php
// use Illuminate\Support\Facades\Auth;
// if (Auth::check()) {
return [

    /*
        |--------------------------------------------------------------------------
        | Title
        |--------------------------------------------------------------------------
        |
        | Here you can change the default title of your admin panel.
        |
        | For detailed instructions you can look the title section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'title' => 'Grobdi System',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
        |--------------------------------------------------------------------------
        | Favicon
        |--------------------------------------------------------------------------
        |
        | Here you can activate the favicon.
        |
        | For detailed instructions you can look the favicon section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
        |--------------------------------------------------------------------------
        | Google Fonts
        |--------------------------------------------------------------------------
        |
        | Here you can allow or not the use of external google fonts. Disabling the
        | google fonts may be useful if your admin panel internet access is
        | restricted somehow.
        |
        | For detailed instructions you can look the google fonts section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
        |--------------------------------------------------------------------------
        | Admin Panel Logo
        |--------------------------------------------------------------------------
        |
        | Here you can change the logo of your admin panel.
        |
        | For detailed instructions you can look the logo section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'logo' => '<b>Grobdi</b>',
    'logo_img' => 'images/logo_solo.png',
    'logo_img_class' => 'brand-image elevation-1',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
        |--------------------------------------------------------------------------
        | Authentication Logo
        |--------------------------------------------------------------------------
        |
        | Here you can setup an alternative logo to use on your login and register
        | screens. When disabled, the admin panel logo will be used instead.
        |
        | For detailed instructions you can look the auth logo section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
        |--------------------------------------------------------------------------
        | Preloader Animation
        |--------------------------------------------------------------------------
        |
        | Here you can change the preloader animation configuration. Currently, two
        | modes are supported: 'fullscreen' for a fullscreen preloader animation
        | and 'cwrapper' to attach the preloader animation into the content-wrapper
        | element and avoid overlapping it with the sidebars and the top navbar.
        |
        | For detailed instructions you can look the preloader section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'images/nave_espacial.png',
            'alt' => 'Grobdi Preloader Image',
            'effect' => 'animation__shake',
            'width' => 160,
            'height' => 500,
        ],
    ],

    /*
        |--------------------------------------------------------------------------
        | User Menu
        |--------------------------------------------------------------------------
        |
        | Here you can activate and change the user menu.
        |
        | For detailed instructions you can look the user menu section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
        |--------------------------------------------------------------------------
        | Layout
        |--------------------------------------------------------------------------
        |
        | Here we change the layout of your admin panel.
        |
        | For detailed instructions you can look the layout section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
        |
        */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
        |--------------------------------------------------------------------------
        | Authentication Views Classes
        |--------------------------------------------------------------------------
        |
        | Here you can change the look and behavior of the authentication views.
        |
        | For detailed instructions you can look the auth classes section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
        |
        */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
        |--------------------------------------------------------------------------
        | Admin Panel Classes
        |--------------------------------------------------------------------------
        |
        | Here you can change the look and behavior of the admin panel.
        |
        | For detailed instructions you can look the admin panel classes here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
        |
        */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-danger elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        |
        | Here we can modify the sidebar of the admin panel.
        |
        | For detailed instructions you can look the sidebar section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
        |
        */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
        |--------------------------------------------------------------------------
        | Control Sidebar (Right Sidebar)
        |--------------------------------------------------------------------------
        |
        | Here we can modify the right sidebar aka control sidebar of the admin panel.
        |
        | For detailed instructions you can look the right sidebar section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
        |
        */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
        |--------------------------------------------------------------------------
        | URLs
        |--------------------------------------------------------------------------
        |
        | Here we can modify the url settings of the admin panel.
        |
        | For detailed instructions you can look the urls section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
        |
        */

    'use_route_url' => false,
    'dashboard_url' => '/',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
        |--------------------------------------------------------------------------
        | Laravel Asset Bundling
        |--------------------------------------------------------------------------
        |
        | Here we can enable the Laravel Asset Bundling option for the admin panel.
        | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
        | When using 'vite_js_only', it's expected that your CSS is imported using
        | JavaScript. Typically, in your application's 'resources/js/app.js' file.
        | If you are not using any of these, leave it as 'false'.
        |
        | For detailed instructions you can look the asset bundling section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
        |
        */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
        |--------------------------------------------------------------------------
        | Menu Items
        |--------------------------------------------------------------------------
        |
        | Here we can modify the sidebar/top navigation of the admin panel.
        |
        | For detailed instructions you can look here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
        |
        */

    'menu' => [
        // Navbar items:
        // [
        //     'type' => 'navbar-search',
        //     'text' => 'Buscar',
        //     'topnav_right' => true,
        // ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar',
        ],
        // [
        //     'text' => 'blog',
        //     'url' => 'admin/blog',
        //     'can' => 'manage-blog',
        // ],
        // [
        //     'text' => 'pages',
        //     'url' => 'admin/pages',
        //     'icon' => 'far fa-fw fa-file',
        //     'label' => 10,
        //     'label_color' => 'success',
        // ],
        [
            'header' => 'Reportes',
            'can' => ['admin']
        ],
        [
            'text' => 'Reportes Comercial',
            'icon' => 'fas fa-chart-bar',
            'submenu' => [
                [
                    'text' => 'Rutas',
                    'url' => 'reports/rutas',
                    'icon' => 'fas fa-route',
                    'can' => ['admin', 'jefe-comercial']
                ],
                [
                    'text' => 'Ventas',
                    'url' => 'reports/ventas',
                    'icon' => 'fas fa-briefcase',
                    'can' => ['admin']
                ],
                [
                    'text' => 'Doctores',
                    'url' => 'reports/doctores',
                    'icon' => 'fas fa-fw fa-user-md',
                    'can' => ['admin']
                ],
            ]
        ],
        [
            'header' => 'Muestras',
            'can' => ['admin', 'visitador', 'coordinador-lineas', 'jefe-comercial', 'contabilidad', 'jefe-operaciones', 'laboratorio']
        ],
        [
            'text' => 'Muestras',
            'url' => 'muestras',
            'icon' => 'fas fa-pump-medical',
            'can' => ['admin', 'visitador', 'coordinador-lineas', 'jefe-comercial', 'contabilidad', 'jefe-operaciones', 'laboratorio']
        ],

        //sidebar Counter
        [
            'header' => 'Counter',
            'can' => ['counter', 'administracion']
        ],
        [
            'text' => 'Pedidos',
            'icon' => 'fas fa-fw fa-share',
            'submenu' => [
                [
                    'text' => 'Cargar pedidos',
                    'url' => 'cargarpedidos',
                    'icon' => 'fas fa-fw fa-upload',
                    'can' => ['counter', 'administracion']
                ],
                [
                    'text' => 'Historial pedidos',
                    'url' => 'historialpedidos',
                    'icon' => 'fas fa-fw fa-history',
                    'can' => ['jefe_operaciones', 'counter', 'administracion']
                ],
                [
                    'text' => 'Asignar Pedidos',
                    'url' => 'asignarpedidos',
                    'icon' => 'fas fa-fw fa-user',
                    'can' => ['counter', 'administracion']
                ],
            ],
        ],
        //sidebar de administracion
        [
            'header' => 'Administracion',
            'can' => 'administracion'
        ],
        [
            'text' => 'Articulos',
            'icon' => 'fas fa-cogs',
            'submenu' => [
                [
                    'text' => 'Insumos',
                    'url' => 'insumos',
                    'icon' => 'fas fa-vial',
                    'can' => 'administracion',
                ],
                [
                    'text' => 'Material',
                    'url' => 'material',
                    'icon' => 'fas fa-cube',
                    'can' => 'administracion',
                ],
                [
                    'text' => 'Envases',
                    'url' => 'envases',
                    'icon' => 'fas fa-pump-soap',
                    'can' => 'administracion',
                ],
                [
                    'text' => 'Merchandise',
                    'url' => 'merchandise',
                    'icon' => 'fas fa-box-open',
                    'can' => 'administracion',
                ],
                [
                    'text' => 'Utiles',
                    'url' => 'util',
                    'icon' => 'fas fa-paperclip',
                    'can' => 'administracion',
                ],

            ],
        ],
        [
            'text' => 'Compras',
            'url' => 'compras',
            'icon' => 'fas fa-shopping-bag',
            'can' => 'administracion',
        ],
        [
            'text' => 'Proveedores',
            'url' => 'proveedores',
            'icon' => 'fas fa-truck',
            'can' => 'administracion',
        ],
        [
            'text' => 'Guía de Ingreso',
            'url' => 'guia_ingreso',
            'icon' => 'fas fa-file-import',
            'can' => 'administracion',
        ],
        [
            'text' => 'Tipo de Cambio',
            'url' => '/resumen-tipo-cambio',
            'icon' => 'fas fa-exchange-alt',
            'can' => 'administracion',
        ],
        //sidebar de laboratorio
        [
            'header' => 'Laboratorio',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Pedidos',
            'url' => 'pedidoslaboratorio',
            'icon' => 'fas fa-fw fa-user',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Detalles Pedidos',
            'url' => 'pedidoslaboratoriodetalles',
            'icon' => 'fas fa-fw fa-user',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Historial pedidos',
            'url' => 'historialpedidos',
            'icon' => 'fas fa-fw fa-history',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Presentaciones',
            'url' => 'presentacionfarmaceutica',
            'icon' => 'fas fa-fw fa-flask',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Producto Final',
            'url' => 'producto_final',
            'icon' => 'fas fa-vial',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Base',
            'url' => 'bases',
            'icon' => 'fas fa-atom',
            'can' => 'laboratorio'
        ],
        [
            'text' => 'Volumen',
            'url' => 'volumen',
            'icon' => 'fas fa-balance-scale',
            'can' => 'laboratorio'
        ],

        //sidebar produccion
        [
            'header' => 'Produccion',
            'can' => 'tecnico_produccion'
        ],
        [
            'text' => 'Ordenes',
            'url' => 'pedidosproduccion',
            'icon' => 'fas fa-fw fa-flask',
            'can' => 'tecnico_produccion'
        ],
        [
            'header' => 'Contabilidad',
            'can' => 'contabilidad'
        ],
        [
            'text' => 'Pedidos',
            'url' => 'pedidoscontabilidad',
            'icon' => 'fas fa-fw fa-user',
            'can' => 'contabilidad'
        ],
        [
            'text' => 'Marcar Insumo Caro',
            'url' => '/insumo/marcar-caro',
            'icon' => 'fas fa-fw fa-dollar-sign',
            'can' => 'contabilidad'
        ],
        [
            'header' => 'Motorizado',
            'can' => 'motorizados'
        ],
        [
            'text' => 'Pedidos',
            'url' => 'pedidosmotorizado',
            'icon' => 'fas fa-fw fa-user',
            'can' => 'motorizados'
        ],
        //SISTEMAS
        [
            'header' => 'Ajustes',
            'can' => 'jefe-operaciones'
        ],
        [
            'text' => 'Usuarios',
            'url' => 'usuarios',
            'icon' => 'fas fa-fw fa-user',
            'can' => 'jefe-operaciones'
        ],
        [
            'text' => 'Roles',
            'url' => 'roles',
            'icon' => 'fas fa-fw fa-user-shield',
            'can' => 'jefe-operaciones'
        ],
        // [
        //     'text' => 'Permisos',
        //     'url' => 'permisos',
        //     'icon' => 'fas fa-fw fa-key',
        //     'can' => 'jefe-operaciones'
        // ],
        [
            'text' => 'Modulos',
            'url' => 'modules',
            'icon' => 'fas fa-fw fa-th-large',
            'can' => 'jefe-operaciones'
        ],
        [
            'text' => 'Vistas',
            'url' => 'views',
            'icon' => 'fas fa-fw fa-eye',
            'can' => 'jefe-operaciones'
        ],
        // [
        //     'text' => 'Bitacora',
        //     'url' => 'bitacora',
        //     'icon' => 'fas fa-fw fa-book',
        //     'can' => 'jefe-operaciones'
        // ],
        // [
        //     'text' => 'Logs',
        //     'url' => 'logs',
        //     'icon' => 'fas fa-fw fa-file-alt',
        //     'can' => 'jefe-operaciones'
        // ],
        // [
        //     'text' => 'Backups',
        //     'url' => 'backups',
        //     'icon' => 'fas fa-fw fa-database',
        //     'can' => 'jefe-operaciones'
        // ],
        // [
        //     'text' => 'Configuracion',
        //     'url' => 'ajustes',
        //     'icon' => 'fas fa-fw fa-cogs',
        //     'can' => 'jefe-operaciones'
        // ],
        //sidebar Supervisor
        [
            'header' => 'Supervisor',
            'can' => 'supervisor'
        ],
        [
            'text' => 'Mantenimiento',
            'icon' => 'fas fa-fw fa-wrench',
            'submenu' => [
                [
                    'text' => 'Centro de Salud',
                    'url' => 'centrosalud',
                    'icon' => 'fas fa-fw fa-h-square',
                    'can' => 'supervisor',
                ],
                [
                    'text' => 'Especialidad',
                    'url' => 'especialidad',
                    'icon' => 'fas fa-fw fa-medkit',
                    'can' => 'supervisor',
                ],
                [
                    'text' => 'Categorias',
                    'url' => 'categoriadoctor',
                    'icon' => 'fas fa-fw fa-medkit',
                    'can' => 'supervisor',
                ],
                [
                    'text' => 'Doctor',
                    'url' => 'doctor',
                    'icon' => 'fas fa-fw fa-user-md',
                    'can' => 'supervisor',
                ],
            ],
        ],
        [
            'text' => 'Enrutamiento',
            'icon' => 'fas fa-list-alt',
            'submenu' => [
                [
                    'text' => 'Listas',
                    'url' => 'lista',
                    'icon' => 'fas fa-list',
                    'can' => 'supervisor',
                ],
                [
                    'text' => 'Enrutamiento',
                    'url' => 'enrutamiento',
                    'icon' => 'fas fa-calendar',
                    'can' => 'supervisor',
                ],
            ],
        ],
        //sidebar de Visitadoras
        [
            'header' => 'Visitador Medico',
            'can' => 'visitador'

        ],
        [
            'text' => 'Rutas',
            'icon' => 'fa fa-map-marker',
            'submenu' => [
                [
                    'text' => 'Calendario',
                    'url' => 'calendariovisitadora',
                    'icon' => 'fa fa-calendar',
                    'can' => 'visitador'
                ],
                [
                    'text' => 'Mis rutas',
                    'url' => 'rutasvisitadora',
                    'icon' => 'fa fa-map',
                    'can' => 'visitador'
                ],
                [
                    'text' => 'Mapa de Rutas',
                    'url' => 'ruta-mapa',
                    'icon' => 'fa fa-map',
                    'can' => 'visitador'
                ],
            ],
        ],
        //Jefe de Operaciones
        [
            'header' => 'Jefe de Operaciones',
            'can' => 'jefe-operaciones'
        ],
        //Coordinador de Lineas
        [
            'header' => 'Coordinador de Lineas',
            'can' => 'coordinador-lineas'
        ],
        //Jefe comercial
        [
            'header' => 'Jefe Comercial',
            'can' => 'jefe-comercial'
        ],
        [
            'text' => 'Ventas x Clientes',
            'url' => 'ventascliente',
            'icon' => '	fas fa-pump-medical',
            'can' => 'jefe-comercial'
        ],
        //sidebar de gerencia General
        [
            'header' => 'Reportes',
            'can' => 'gerencia-general'
        ],
        [
            'text' => 'Muestras',
            'icon' => 'fas fa-pump-medical',
            'submenu' => [
                [
                    'text' => 'Clasificaciones',
                    'url' => 'reporte',
                    'icon' => 'fas fa-chart-bar',
                    'can' => 'gerencia-general',
                ],
                [
                    'text' => 'Frasco Muestra',
                    'url' => 'reporte/frasco-muestra',
                    'icon' => 'fas fa-chart-line',
                    'can' => 'gerencia-general',
                ],
                [
                    'text' => 'Frasco Original',
                    'url' => 'reporte/frasco-original',
                    'icon' => 'fas fa-chart-line',
                    'can' => 'gerencia-general',
                ],
            ],
        ],
        // [
        //     'text' => 'warning',
        //     'icon_color' => 'yellow',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'information',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'change_password',
        //     'url' => 'admin/settings',
        //     'icon' => 'fas fa-fw fa-lock',
        // ],

        // [
        //     'text' => 'multilevel',
        //     'icon' => 'fas fa-fw fa-share',
        //     'submenu' => [
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                 ],
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //     ],
        // ],
    ],

    /*
        |--------------------------------------------------------------------------
        | Menu Filters
        |--------------------------------------------------------------------------
        |
        | Here we can modify the menu filters of the admin panel.
        |
        | For detailed instructions you can look the menu filters section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
        |
        */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
        |--------------------------------------------------------------------------
        | Plugins Initialization
        |--------------------------------------------------------------------------
        |
        | Here we can modify the plugins used inside the admin panel.
        |
        | For detailed instructions you can look the plugins section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
        |
        */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Flatpickr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://npmcdn.com/flatpickr/dist/l10n/es.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                ],
            ],
        ],

        // SweetAlert2
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'Moment' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                ],
            ],
        ],
        'DateRangePicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js',
                ],
            ],
        ],
        'DatePicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js',
                ],
            ],
        ],
        'Toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',
                ],
            ],
        ],
    ],

    /*
        |--------------------------------------------------------------------------
        | IFrame
        |--------------------------------------------------------------------------
        |
        | Here we change the IFrame mode configuration. Note these changes will
        | only apply to the view that extends and enable the IFrame mode.
        |
        | For detailed instructions you can look the iframe mode section here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
        |
        */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
        |--------------------------------------------------------------------------
        | Livewire
        |--------------------------------------------------------------------------
        |
        | Here we can enable the Livewire support.
        |
        | For detailed instructions you can look the livewire here:
        | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
        |
        */

    'livewire' => false,
];

// }
