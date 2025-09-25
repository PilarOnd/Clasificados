<?php

namespace App\Http\Controllers;

use App\Services\AnuncioService;

class DashboardController extends Controller
{
    public function index(AnuncioService $anuncioService)
    {
        $total = count($anuncioService->getAllAnuncios());
        return view('dashboard', ['totalAnuncios' => $total]);
    }
}


