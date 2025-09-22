<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AnuncioService
{
    private string $filePath;

    /**
     * Mapa de estados de publicación: clave de URL => estados aceptados (case-insensitive)
     */
    private array $statusLabelMap = [
        'borrador' => ['borrador'],
        'esperando_pago' => ['en_espera_de_pago'],
        'esperando_aprobacion' => ['en_espera_de_aprobacion'],
        'aprobado' => ['aprobado'],
        'rechazado' => ['rechazado'],
    ];

    public function __construct()
    {
        $this->filePath = base_path('anuncios.json');
    }

    /**
     * Obtiene todos los anuncios del archivo JSON
     */
    public function getAllAnuncios(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return [];
        }

        return $this->transformAnuncios($data);
    }

    /**
     * Busca un anuncio por ID
     */
    public function findAnuncioById(int $id): ?array
    {
        $anuncios = $this->getAllAnuncios();
        return collect($anuncios)->firstWhere('id', $id);
    }

    /**
     * Filtra anuncios por criterios
     */
    public function filterAnuncios(array $anuncios, array $filters): array
    {
        $query = $filters['q'] ?? null;
        $desde = $filters['desde'] ?? null;
        $hasta = $filters['hasta'] ?? null;

        if (!$query && !$desde && !$hasta) {
            return $anuncios;
        }

        return array_values(array_filter($anuncios, function ($item) use ($query, $desde, $hasta) {
            $passesText = $this->passesTextFilter($item, $query);
            $passesDesde = $this->passesDesdeFilter($item, $desde);
            $passesHasta = $this->passesHastaFilter($item, $hasta);

            return $passesText && $passesDesde && $passesHasta;
        }));
    }

    /**
     * Filtra por estado de publicación según el mapa
     */
    public function filterByStatus(array $anuncios, ?string $status): array
    {
        if (!$status || !array_key_exists($status, $this->statusLabelMap)) {
            return $anuncios;
        }

        $acceptedLabels = array_map('mb_strtolower', $this->statusLabelMap[$status]);

        return array_values(array_filter($anuncios, function ($item) use ($acceptedLabels) {
            $publicacionEstado = isset($item['publicacion_estado']) ? mb_strtolower((string) $item['publicacion_estado']) : '';
            return in_array($publicacionEstado, $acceptedLabels, true);
        }));
    }

    /**
     * Conteo de anuncios por cada estado de publicación definido en el mapa
     */
    public function getStatusCounts(array $anuncios): array
    {
        $counts = [];
        foreach ($this->statusLabelMap as $key => $labels) {
            $counts[$key] = 0;
        }

        foreach ($anuncios as $item) {
            $publicacionEstadoLower = isset($item['publicacion_estado']) ? mb_strtolower((string) $item['publicacion_estado']) : '';
            foreach ($this->statusLabelMap as $key => $labels) {
                if (in_array($publicacionEstadoLower, array_map('mb_strtolower', $labels), true)) {
                    $counts[$key]++;
                    break;
                }
            }
        }

        return $counts;
    }

    /**
     * Ordena anuncios por criterio y dirección
     */
    public function sortAnuncios(array $anuncios, ?string $sort, string $dir = 'asc'): array
    {
        if (!in_array($sort, ['fecha', 'estado'], true)) {
            return $anuncios;
        }

        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        usort($anuncios, function ($a, $b) use ($sort, $dir) {
            if ($sort === 'fecha') {
                $fa = optional(Carbon::parse($a['fecha_inicio'] ?? $a['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $fb = optional(Carbon::parse($b['fecha_inicio'] ?? $b['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $cmp = $fa <=> $fb;
            } else { // estado de publicación
                $ea = $a['publicacion_estado'] ?? '';
                $eb = $b['publicacion_estado'] ?? '';
                $cmp = strcasecmp($ea, $eb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        return $anuncios;
    }

    /**
     * Transforma los anuncios añadiendo URLs de fotos y fase de publicación
     */
    private function transformAnuncios(array $data): array
    {
        return array_map(function ($item) {
            $foto = isset($item['foto']) ? str_replace('public/', '', $item['foto']) : null;
            $item['foto_url'] = $foto ? asset($foto) : null;

            [$fase, $color] = $this->getPublicationPhaseFromJson($item);
            $item['publicacion_fase'] = $fase;        // Borrador | En espera de Pago | En espera de Aprobación | Aprobado | Rechazado
            $item['publicacion_color'] = $color;      // secondary | warning | info | success | danger

            return $item;
        }, $data);
    }

    /**
     * Obtiene la fase de publicación directamente del JSON
     */
    private function getPublicationPhaseFromJson(array $item): array
    {
        $publicacionEstado = trim((string)($item['publicacion_estado'] ?? ''));
        
        // Mapeo de estados de publicación a colores
        $publicationStateMap = [
            'borrador' => ['Borrador', 'secondary'],
            'en_espera_de_pago' => ['En espera de Pago', 'warning'],
            'en_espera_de_aprobacion' => ['En espera de Aprobación', 'info'],
            'aprobado' => ['Aprobado', 'success'],
            'rechazado' => ['Rechazado', 'danger'],
        ];

        $estadoLower = mb_strtolower($publicacionEstado);
        
        foreach ($publicationStateMap as $key => $config) {
            if ($estadoLower === $key) {
                return $config;
            }
        }

        // Estado por defecto si no se encuentra
        return ['Sin Estado', 'secondary'];
    }


    /**
     * Verifica si un anuncio pasa el filtro de texto
     */
    private function passesTextFilter(array $item, ?string $query): bool
    {
        if ($query === null || $query === '') {
            return true;
        }

        $texto = $item['descripcion'] ?? '';
        return stripos($texto, $query) !== false;
    }

    /**
     * Verifica si un anuncio pasa el filtro de fecha desde
     * Criterio: el rango del anuncio debe SOLAPAR con la fecha desde/hasta solicitada.
     */
    private function passesDesdeFilter(array $item, ?string $desde): bool
    {
        if (!$desde) {
            return true;
        }

        try {
            $desdeDate = Carbon::parse($desde)->startOfDay();
            $fin = !empty($item['fecha_fin']) ? Carbon::parse($item['fecha_fin'])->endOfDay() : null;
            $inicio = !empty($item['fecha_inicio']) ? Carbon::parse($item['fecha_inicio'])->startOfDay() : null;

            if ($inicio === null && $fin === null) {
                // Fallback a fecha_publicacion si no hay rango
                $fecha = !empty($item['fecha_publicacion']) ? Carbon::parse($item['fecha_publicacion'])->startOfDay() : null;
                return $fecha ? $fecha->greaterThanOrEqualTo($desdeDate) : false;
            }

            // Solapa si fin >= desde
            return $fin ? $fin->greaterThanOrEqualTo($desdeDate) : true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifica si un anuncio pasa el filtro de fecha hasta
     * Criterio: el rango del anuncio debe SOLAPAR con la fecha solicitada.
     */
    private function passesHastaFilter(array $item, ?string $hasta): bool
    {
        if (!$hasta) {
            return true;
        }

        try {
            $hastaDate = Carbon::parse($hasta)->endOfDay();
            $inicio = !empty($item['fecha_inicio']) ? Carbon::parse($item['fecha_inicio'])->startOfDay() : null;
            $fin = !empty($item['fecha_fin']) ? Carbon::parse($item['fecha_fin'])->endOfDay() : null;

            if ($inicio === null && $fin === null) {
                // Fallback a fecha_publicacion si no hay rango
                $fecha = !empty($item['fecha_publicacion']) ? Carbon::parse($item['fecha_publicacion'])->startOfDay() : null;
                return $fecha ? $fecha->lessThanOrEqualTo($hastaDate) : false;
            }

            // Solapa si inicio <= hasta
            return $inicio ? $inicio->lessThanOrEqualTo($hastaDate) : true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
