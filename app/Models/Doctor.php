<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class Doctor extends Model
{
    use HasFactory;
    protected $table = 'doctor';

    protected $fillable = [
        'name',
        'especialidad_id',
        'name_secretariat',
        'name_softlynn',
    ];
    public const TIPOMEDICO = [
        'Comprador',
        'Prescriptor',
        'En Proceso'
    ];
    public static function ScrappingDoctor($cmp)
    {
        $response = Http::asForm()->post('https://aplicaciones.cmp.org.pe/conoce_a_tu_medico/datos-colegiado.php', [
            'cmp' => $cmp,
        ]);

        $html = $response->body();
        $crawler = new Crawler($html);
        $datos = [];

        // dd($crawler);
        $crawler->filter('table tr')->each(function ($node) use (&$datos) {
            $columns = $node->filter('td')->each(function ($col) {
                return trim($col->text());
            });
            // if (count($columns) === 5) {
            //     $datos[] = $columns;

            // }
            $link = $node->filter('td a')->count() > 0 ? $node->filter('td a')->attr('href') : null;

            if (count($columns) === 5) {
                $datos[] = [
                    'cols' => $columns,
                    'link' => $link,
                ];
            }
        });
        foreach ($datos as &$registro) {
            if (!empty($registro['link'])) {
                $url = $registro['link'];

                // Asegúrate de que el link sea completo
                if (strpos($url, 'http') !== 0) {
                    $url = 'https://aplicaciones.cmp.org.pe/conoce_a_tu_medico/' . ltrim($url, '/');
                }

                $linkHtml = Http::get($url)->body();
                $linkCrawler = new Crawler($linkHtml);
                $tablaInterna = [];

                $linkCrawler->filter('table tr')->each(function ($tr) use (&$tablaInterna) {
                    $cols = $tr->filter('td')->each(function ($td) {
                        return trim($td->text());
                    });

                    if (!empty($cols)) {
                        $tablaInterna[] = $cols;
                    }
                });
                $registro['tabla_interna'] = $tablaInterna;
            }
        }
        unset($registro);
        return $datos;
    }
    public function days()
    {
        return $this->belongsToMany(Day::class, 'doctor_day')
            ->withPivot('turno');
    }
    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
    public function categoriadoctor()
    {
        return $this->belongsTo(CategoriaDoctor::class);
    }
    public function centrosalud()
    {
        return $this->belongsTo(CentroSalud::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function muestras()
    {
        return $this->hasMany(Muestras::class, 'id_doctor');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class, 'id_doctor');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['name_softlynn'] = $value;
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim(
                implode(' ', array_filter([
                    $this->name,
                    $this->first_lastname,
                    $this->second_lastname
                ]))
            )
        );
    }
}
