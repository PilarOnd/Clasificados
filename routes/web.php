<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mis-anuncios', function () {
    $filePath = base_path('anuncios.json');
    $anuncios = [];

    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $anuncios = array_map(function ($item) {
                $foto = isset($item['foto']) ? str_replace('public/', '', $item['foto']) : null;
                $item['foto_url'] = $foto ? asset($foto) : null;
                return $item;
            }, $data);
        }
    }

    // Filtros
    $query = request('q');
    $desde = request('desde');
    $hasta = request('hasta');

    if ($query || $desde || $hasta) {
        $anuncios = array_values(array_filter($anuncios, function ($item) use ($query, $desde, $hasta) {
            $passesText = true;
            if ($query !== null && $query !== '') {
                $texto = $item['descripcion'] ?? '';
                $passesText = stripos($texto, $query) !== false;
            }

            $passesDesde = true;
            if ($desde) {
                try {
                    $fechaItem = \Illuminate\Support\Carbon::parse($item['fecha_publicacion'] ?? null);
                    $passesDesde = $fechaItem->toDateString() >= $desde;
                } catch (\Throwable $e) {
                    $passesDesde = false;
                }
            }

            $passesHasta = true;
            if ($hasta) {
                try {
                    $fechaItem = \Illuminate\Support\Carbon::parse($item['fecha_publicacion'] ?? null);
                    $passesHasta = $fechaItem->toDateString() <= $hasta;
                } catch (\Throwable $e) {
                    $passesHasta = false;
                }
            }

            return $passesText && $passesDesde && $passesHasta;
        }));
    }

    // Ordenamiento
    $sort = request('sort'); // 'fecha' | 'estado'
    $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
    if (in_array($sort, ['fecha', 'estado'], true)) {
        usort($anuncios, function ($a, $b) use ($sort, $dir) {
            if ($sort === 'fecha') {
                $fa = optional(\Illuminate\Support\Carbon::parse($a['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $fb = optional(\Illuminate\Support\Carbon::parse($b['fecha_publicacion'] ?? null))->timestamp ?? 0;
                $cmp = $fa <=> $fb;
            } else { // estado
                $ea = $a['estado'] ?? '';
                $eb = $b['estado'] ?? '';
                $cmp = strcasecmp($ea, $eb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });
    }

    return view('anuncios', compact('anuncios'));
})->name('mis-anuncios');