# Arquitectura de Reportes - Propuesta

## 📋 Descripción General

Esta arquitectura implementa un sistema modular y escalable para la gestión de reportes en Laravel, siguiendo los principios SOLID y buenas prácticas de desarrollo.

## 🏗️ Estructura de Directorios

```
app/
├── Http/Controllers/
│   └── Reportes/
│       └── ReporteController.php          # Controller principal
├── Services/Reportes/
│   ├── ReporteServiceInterface.php        # Interfaz base
│   ├── BaseReporteService.php             # Clase base con lógica común
│   ├── VentasReporteService.php           # Servicio específico para ventas
│   ├── DoctoresReporteService.php         # Servicio específico para doctores
│   └── VisitadorasReporteService.php      # Servicio específico para visitadoras
├── Repositories/Reportes/
│   ├── ReporteRepositoryInterface.php     # Interfaz para repositorios
│   └── BaseReporteRepository.php          # Clase base para repositorios
├── DTOs/Reportes/
│   ├── ReporteData.php                    # DTO base
│   ├── VentasData.php                     # DTO específico para ventas
│   ├── DoctoresData.php                   # DTO específico para doctores
│   └── VisitadorasData.php                # DTO específico para visitadoras
└── Export/Reportes/
    └── GenerarReporteExcel.php            # Job para exportación asíncrona(Al final)
resources/views/reporte/
├── ventas.blade.php
|
├── doctores.blade.php
└── visitadoras.blade.php
```
****
## 🎯 Principios Implementados

### 1. **Separación de Responsabilidades (SRP)**
- **Controllers**: Solo manejan HTTP requests/responses
- **Services**: Contienen la lógica de negocio
- **Repositories**: Acceso a datos
- **DTOs**: Estructuran los datos
- **Jobs**: Procesos asíncronos

### 2. **Inversión de Dependencias (DIP)**
- Interfaces definen contratos
- Implementaciones son intercambiables
- Fácil testing con mocks

### 3. **Abierto/Cerrado (OCP)**
- Clases base extensibles
- Nuevos reportes sin modificar código existente
- Configuración centralizada

## 🚀 Cómo Usar

### Crear un Nuevo Reporte

1. **Crear DTO** en `app/DTOs/Reportes/`
```php
class NuevoReporteData extends ReporteData
{
    // Implementar estructura de datos específica
}
```

2. **Crear Servicio** en `app/Services/Reportes/`
```php
class NuevoReporteService extends BaseReporteService
{
    protected function createReporteData(array $filtros = []): ReporteData
    {
        return new NuevoReporteData($filtros);
    }
}
```

3. **Actualizar Configuración** en `config/reportes.php`
```php
'nuevo_reporte' => [
    'nombre' => 'Nuevo Reporte',
    'servicio' => 'App\Services\Reportes\NuevoReporteService',
    'dto' => 'App\DTOs\Reportes\NuevoReporteData',
],
```

4. **Agregar al Controller**
```php
public function nuevoReporte(Request $request)
{
    $filtros = $request->all();
    $data = $this->nuevoService->getData($filtros);
    return view('reporte.nuevo', $data->toArray());
}
```

## 📊 Beneficios de Esta Arquitectura

### ✅ **Mantenibilidad**
- Código organizado y fácil de entender
- Separación clara de responsabilidades
- Fácil localización de bugs

### ✅ **Escalabilidad**
- Agregar nuevos reportes sin afectar existentes
- Configuración centralizada
- Reutilización de componentes

### ✅ **Testabilidad**
- Interfaces permiten mocks
- Servicios independientes
- DTOs facilitan assertions

### ✅ **Performance**
- Caching automático
- Jobs asíncronos para reportes pesados
- Lazy loading en repositorios

### ✅ **Flexibilidad**
- Filtros dinámicos
- Múltiples formatos de exportación
- Configuración por entorno

## 🔧 Configuración

### Cache
```php
// config/reportes.php
'cache' => [
    'ttl' => env('REPORTES_CACHE_TTL', 3600), // 1 hora
    'driver' => env('CACHE_DRIVER', 'redis'),
],
```

### Queue para Jobs
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),
```

## 📈 Endpoints API

### Datos Dinámicos
```
GET /api/reportes/ventas?filtro1=valor1&filtro2=valor2
GET /api/reportes/doctores
GET /api/reportes/visitadoras
```

### Configuración de Filtros
```
GET /api/reportes/filtros/ventas
GET /api/reportes/filtros/doctores
GET /api/reportes/filtros/visitadoras
```

## 🎨 Vistas

Las vistas usan:
- **AdminLTE** para UI consistente
- **Chart.js** para gráficos
- **Flatpickr** para datepickers
- **Bootstrap** para responsive design

### Patrón Recomendado
```blade
@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="row mb-4">
        <!-- Formulario de filtros -->
    </div>

    <!-- Gráficas -->
    <div class="row mb-4">
        <!-- Canvas para Chart.js -->
    </div>

    <!-- Tablas -->
    <div class="row mb-4">
        <!-- Tabla de datos -->
    </div>
</div>
@endsection
```

## 🔄 Próximos Pasos

1. **Implementar Repositorios**: Conectar a base de datos real
2. **Agregar Más Filtros**: Fechas, rangos, búsquedas avanzadas
3. **Dashboard Interactivo**: Widgets en tiempo real
4. **Notificaciones**: Email cuando reportes estén listos
5. **APIs RESTful**: Para integraciones externas
6. **Testing**: Unit tests y feature tests completos

## 📝 Notas Importantes

- **Cache**: Limpiar manualmente cuando cambien datos maestros
- **Jobs**: Monitorear queue para reportes pesados
- **DTOs**: Mantener sincronizados con cambios en datos
- **Config**: Usar variables de entorno para configuración sensible

Esta arquitectura proporciona una base sólida para crecer y mantener el sistema de reportes de manera profesional y escalable.