<?php

namespace Database\Seeders;

use App\Models\EstadoVisita;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            RoleSeeder::class,
            ViewSeeder::class,
            RoleModuleSeeder::class,
            RoleViewSeeder::class,
        ]);
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'sistemas',
            'email' => 'sistemas@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 1,
        ]);
        User::factory()->create([
            'name' => 'Melanie Burga',
            'email' => 'super.counter@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'Jerson',
            'email' => 'motorizadonorte@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 5,
        ]);
        User::factory()->create([
            'name' => 'Almendra Alcantara',
            'email' => 'laboratorio.tarde@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 4,
        ]);
        User::factory()->create([
            'name' => 'COUNTER NORTE',
            'email' => 'counternorte@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'Juan Carlos Tarazona',
            'email' => 'laboratorio@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 4,
        ]);
        User::factory()->create([
            'name' => 'COUNTER CENTRO',
            'email' => 'countercentro@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'COUNTER SUR',
            'email' => 'countersur@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'Ben Murayari',
            'email' => 'motorizadosur@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 5,
        ]);
        User::factory()->create([
            'name' => 'Jair Reyes',
            'email' => 'motorizadocentro@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 5,
        ]);
        User::factory()->create([
            'name' => 'counter tarde',
            'email' => 'counter.tarde@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 2,
        ]);
        User::factory()->create([
            'name' => 'Rocio Herrera',
            'email' => 'contabilidad@grobdi.com.pe',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 3,
        ]);
        User::factory()->create([
            'name' => 'visitadora sur',
            'email' => 'visitadora.sur@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 6,
        ]);
        User::factory()->create([
            'name' => 'Jefe Operaciones',
            'email' => 'jefe.operaciones@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 7,
        ]);
        User::factory()->create([
            'name' => 'Jefe Comercial',
            'email' => 'jefe.comercial@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 8,
        ]);
        User::factory()->create([
            'name' => 'Coordinador de Lineas',
            'email' => 'coordinador.lineas@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 9,
        ]);
        User::factory()->create([
            'name' => 'Gerencia General',
            'email' => 'gerencia.general@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 10,
        ]);
        User::factory()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 11,
        ]);
        User::factory()->create([
            'name' => 'Administracion',
            'email' => 'administracion@grobdi.com',
            'password' => bcrypt('12345678'),
            'active' => 1,
            'role_id' => 13,
        ]);
        $this->call(ZoneSeeder::class);
        $this->call(MotorizadoUserZoneSeeder::class);
        $this->call(DepartamentosSeeder::class);
        $this->call(ProvinciaSeeder::class);
        $this->call(DistritoSeeder::class);
        $this->call(DaySeeder::class);
        $this->call(UnidadesYClasificacionesSeeder::class);
        $this->call(EstadoVisitaSeeder::class);
        $this->call(ConfiguracionesSeeder::class);
        $this->call(TipoMonedaSeeder::class);
        $this->call(VolumenesPorClasificacionSeeder::class);
        $this->call(CategoriaDoctorSeeder::class);
        $this->call(TipoMuestraSeeder::class);
        $this->call(ClasificacionPresentacionSeeder::class);
        $this->call(PedidosSeeder::class);
        $this->call(MuestrasEstadoSeeder::class);
    }
}
