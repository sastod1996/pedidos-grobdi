<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Días del Mes</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .week {
            text-align: center;
            background-color: #f9f9f9;
        }

        .empty {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    @can('enrutamiento.agregarlista')
    <h1>Días del Mes</h1>

    <table class="table-grobdi">
        <thead>
            <tr>
                <th>Lunes</th>
                <th>Martes</th>
                <th>Miércoles</th>
                <th>Jueves</th>
                <th>Viernes</th>
                <th>Sábado</th>
                <th>Domingo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($semanas as $semana)
                <tr>
                    @for ($i = 0; $i < 7; $i++)
                        <td class="{{ isset($semana[$i]) ? '' : 'empty' }}">
                            @isset($semana[$i])
                                {{ $semana[$i]->format('d') }}
                            @endisset
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
    @endcan
</body>
</html>
