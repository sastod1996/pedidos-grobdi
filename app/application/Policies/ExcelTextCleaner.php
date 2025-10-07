<?php

namespace App\Application\Policies;

class ExcelTextCleaner
{
    /**
     * Caracteres mal codificados y sus equivalentes correctos
     */
    private static array $characterMap = [
        // Ñ y ñ
        'Ã‘' => 'Ñ',
        'Ã±' => 'ñ',
        'ÂÑ' => 'Ñ',
        'Âñ' => 'ñ',

        // Vocales con tilde
        'Ã¡' => 'á',
        'Ã©' => 'é',
        'Ã­' => 'í',
        'Ã³' => 'ó',
        'Ãº' => 'ú',
        'Ã' => 'Á',
        'Ã‰' => 'É',
        'Ã' => 'Í',
        'Ã“' => 'Ó',
        'Ãš' => 'Ú',

        // Vocales con diéresis
        'Ã¼' => 'ü',
        'Ãœ' => 'Ü',

        // Otros caracteres comunes
        'Â¿' => '¿',
        'Â¡' => '¡',
        'Â°' => '°',
        'Âª' => 'ª',
        'Âº' => 'º',

        // Comillas y otros símbolos
        'â€œ' => '"',
        'â€' => '"',
        'â€˜' => "'",
        'â€™' => "'",
        'â€¦' => '…',
        'â€“' => '–',
        'â€”' => '—',
    ];

    /**
     * Limpia texto mal codificado que viene de Excel
     *
     * @param string|null $text Texto a limpiar
     * @return string Texto limpio
     */
    public static function clean(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $cleaned = $text;

        // 1. Intentar detectar y convertir desde diferentes encodings
        $cleaned = self::convertFromCommonEncodings($cleaned);

        // 2. Reemplazar caracteres mal codificados usando el mapa
        $cleaned = self::replaceMalformedCharacters($cleaned);

        // 3. Limpiar caracteres de control y espacios extra
        $cleaned = self::cleanControlCharacters($cleaned);

        // 4. Normalizar espacios
        $cleaned = self::normalizeSpaces($cleaned);

        return trim($cleaned);
    }

    /**
     * Convierte texto desde encodings comunes que usa Excel
     */
    private static function convertFromCommonEncodings(string $text): string
    {
        $encodings = ['Windows-1252', 'ISO-8859-1', 'ISO-8859-15', 'CP1252'];

        foreach ($encodings as $encoding) {
            if (self::isEncoding($text, $encoding)) {
                $converted = @iconv($encoding, 'UTF-8//TRANSLIT', $text);
                if ($converted !== false) {
                    return $converted;
                }
            }
        }

        return $text;
    }

    /**
     * Verifica si el texto está en un encoding específico
     */
    private static function isEncoding(string $text, string $encoding): bool
    {
        return @iconv($encoding, $encoding, $text) === $text;
    }

    /**
     * Reemplaza caracteres mal codificados usando el mapa de caracteres
     */
    private static function replaceMalformedCharacters(string $text): string
    {
        return str_replace(
            array_keys(self::$characterMap),
            array_values(self::$characterMap),
            $text
        );
    }

    /**
     * Limpia caracteres de control y espacios problemáticos
     */
    private static function cleanControlCharacters(string $text): string
    {
        // Remover caracteres de control (excepto tab, newline, carriage return)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

        // Reemplazar caracteres de control de Word/Excel
        $text = str_replace(['\r\n', '\r'], '\n', $text);

        return $text;
    }

    /**
     * Normaliza espacios múltiples
     */
    private static function normalizeSpaces(string $text): string
    {
        // Reemplazar múltiples espacios por uno solo
        $text = preg_replace('/\s+/', ' ', $text);

        // Reemplazar espacios no rompibles por espacios normales
        $text = str_replace([' ', ' '], ' ', $text);

        return trim($text);
    }

    /**
     * Limpia específicamente nombres de distritos/provincias
     * Útil para datos geográficos que vienen de Excel
     */
    public static function cleanGeographicName(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $cleaned = self::clean($text);

        // Correcciones previas para variantes mal codificadas muy comunes
        // Estas ocurren cuando la palabra tiene bytes mal interpretados
        $preReplacements = [
            'JESÃšS MARIA' => 'JESÚS MARIA',
            'JESÃšS MARÃA' => 'JESÚS MARÍA',
            'JESÃºS MARIA' => 'JESÚS MARIA',
            'JESÃºS MARÃA' => 'JESÚS MARÍA',
            'JESÃšS MARÍA' => 'JESÚS MARÍA',
            'JESUS MARÃA' => 'JESUS MARÍA',
        ];
        $cleaned = str_replace(array_keys($preReplacements), array_values($preReplacements), $cleaned);

        // Normalizar casos específicos comunes en Perú
        $replacements = [
            // Departamentos
            'LIMA METROPOLITANA' => 'LIMA',
            'MADRE DE DIOS' => 'MADRE DE DIOS',
            'SAN MARTIN' => 'SAN MARTÍN',
            'HUANUCO' => 'HUÁNUCO',
            'ANCASH' => 'ANCASH',
            'HUANCAVELICA' => 'HUANCAVELICA',
            'PASCO' => 'PASCO',

            // Provincias comunes
            'CAÑETE' => 'CAÑETE',
            'HUARAL' => 'HUARAL',
            'BARRANCA' => 'BARRANCA',
            'CAJATAMBO' => 'CAJATAMBO',
            'CANTA' => 'CANTA',
            'HUAROCHIRI' => 'HUAROCHIRI',
            'HUARMEY' => 'HUARMEY',
            'YAUYOS' => 'YAUYOS',
            'OYON' => 'OYÓN',
            'HUAURA' => 'HUAURA',

            // Distritos de Lima (correcciones de acentos más comunes)
            'JESUS MARIA' => 'JESÚS MARÍA',
            'JESÚS MARIA' => 'JESÚS MARÍA',
            'JESUS MARÍA' => 'JESÚS MARÍA',
            'VILLA MARIA DEL TRIUNFO' => 'VILLA MARÍA DEL TRIUNFO',
            'SANTA MARIA DEL MAR' => 'SANTA MARÍA DEL MAR',
            'SAN MARTIN DE PORRES' => 'SAN MARTÍN DE PORRES',
            'BRENA' => 'BREÑA',
            'RIMAC' => 'RÍMAC',
            'ANCON' => 'ANCÓN',
            'LURIN' => 'LURÍN',
        ];

        $cleaned = str_replace(
            array_keys($replacements),
            array_values($replacements),
            mb_strtoupper($cleaned)
        );

        return $cleaned;
    }

    /**
     * Limpia nombres de personas (visitadoras, etc.)
     */
    public static function cleanPersonName(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $cleaned = self::clean($text);

        // Convertir a título (primera letra de cada palabra en mayúscula)
        $cleaned = mb_convert_case($cleaned, MB_CASE_TITLE, 'UTF-8');

        // Corregir casos específicos comunes
        $replacements = [
            ' De ' => ' de ',
            ' Del ' => ' del ',
            ' La ' => ' la ',
            ' Las ' => ' las ',
            ' Los ' => ' los ',
            ' Y ' => ' y ',
            ' O ' => ' o ',
            ' A ' => ' a ',
            ' E ' => ' e ',
            ' U ' => ' u ',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $cleaned
        );
    }

    /**
     * Método para limpiar arrays de datos
     */
    public static function cleanArray(array $data, array $fieldsToClean = []): array
    {
        if (empty($fieldsToClean)) {
            // Si no se especifican campos, limpiar todos los campos string
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = self::clean($value);
                } elseif (is_array($value)) {
                    $data[$key] = self::cleanArray($value);
                }
            }
        } else {
            // Limpiar solo los campos especificados
            foreach ($fieldsToClean as $field) {
                if (isset($data[$field]) && is_string($data[$field])) {
                    $data[$field] = self::clean($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * Agrega caracteres personalizados al mapa de reemplazo
     */
    public static function addCustomReplacements(array $replacements): void
    {
        self::$characterMap = array_merge(self::$characterMap, $replacements);
    }

    /**
     * Obtiene el mapa actual de caracteres
     */
    public static function getCharacterMap(): array
    {
        return self::$characterMap;
    }
}