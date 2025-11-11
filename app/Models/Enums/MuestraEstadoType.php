<?php

namespace App\Models\Enums;

enum MuestraEstadoType: string
{
    case APROVE_COORDINADOR = 'aprobado_coordinador';
    case APROVE_JEFE_COMERCIAL = 'aprobado_jefe_comercial';
    case SET_PRICE = 'precio_asignado';
    case APROVE_JEFE_OPERACIONES = 'aprobado_jefe_operaciones';
    case PRODUCED = 'muestra_elaborada';
}
