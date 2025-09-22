<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AnuncioService
{
    private string $filePath;

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
                $fa = optional(Carbon::parse($a['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $fb = optional(Carbon::parse($b['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $cmp = $fa <=> $fb;
            } else { // estado
                $ea = $a['estado'] ?? '';
                $eb = $b['estado'] ?? '';
                $cmp = strcasecmp($ea, $eb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        return $anuncios;
    }

    /**
     * Transforma los anuncios añadiendo URLs de fotos
     */
    private function transformAnuncios(array $data): array
    {
        return array_map(function ($item) {
            $foto = isset($item['foto']) ? str_replace('public/', '', $item['foto']) : null;
            $item['foto_url'] = $foto ? asset($foto) : null;
            return $item;
        }, $data);
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
     */
    private function passesDesdeFilter(array $item, ?string $desde): bool
    {
        if (!$desde) {
            return true;
        }

        try {
            $fechaItem = Carbon::parse($item['fecha_publicacion'] ?? null);
            return $fechaItem->toDateString() >= $desde;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifica si un anuncio pasa el filtro de fecha hasta
     */
    private function passesHastaFilter(array $item, ?string $hasta): bool
    {
        if (!$hasta) {
            return true;
        }

        try {
            $fechaItem = Carbon::parse($item['fecha_publicacion'] ?? null);
            return $fechaItem->toDateString() <= $hasta;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
