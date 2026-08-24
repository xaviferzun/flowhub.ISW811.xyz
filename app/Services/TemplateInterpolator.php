<?php

namespace App\Services;

class TemplateInterpolator
{
    /**
     * FH-29 reemplaza marcadores {{trigger.campo}} en $template por el valor
     * correspondiente de $data. Si el campo no existe el marcador queda intacto
     * (senal visible de un error de configuracion, en vez de falar en silencio)
     */
    public function interpolate(string $template, array $data): string
    {
        return preg_replace_callback(
            '/\{\{\s*trigger\.([a-zA-Z0-9_]+)\s*\}\}/',
            function (array $matches) use ($data) {
                $field = $matches[1];

                return array_key_exists($field, $data)
                    ? (string) $data[$field]
                    : $matches[0];
            },
            $template
        );
    }
}