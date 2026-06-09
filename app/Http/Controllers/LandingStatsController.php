<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingVisit;
use Illuminate\Support\Facades\DB;

class LandingStatsController extends Controller
{
    public function index(Request $request)
    {
        $paginas = ['home', 'repuestos', 'conocenos', 'contacto'];

        // Totales por página
        $totalesPorPagina = LandingVisit::select('pagina', DB::raw('COUNT(*) as total'))
            ->groupBy('pagina')
            ->pluck('total', 'pagina');

        $totalGeneral = LandingVisit::count();

        // Visitas por día (últimos 30 días)
        $visitasPorDia = LandingVisit::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Visitas por página por día (últimos 30 días)
        $visitasPorPaginaDia = LandingVisit::select(
                DB::raw('DATE(created_at) as fecha'),
                'pagina',
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha', 'pagina')
            ->orderBy('fecha')
            ->get();

        // Top referrers
        $topReferrers = LandingVisit::select('referrer', DB::raw('COUNT(*) as total'))
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Visitas de hoy
        $hoy = LandingVisit::whereDate('created_at', today())->count();

        // Visitas esta semana
        $semana = LandingVisit::where('created_at', '>=', now()->startOfWeek())->count();

        // Últimas 20 visitas
        $ultimas = LandingVisit::orderByDesc('created_at')->limit(20)->get();

        return view('roait.stats', compact(
            'paginas',
            'totalesPorPagina',
            'totalGeneral',
            'visitasPorDia',
            'visitasPorPaginaDia',
            'topReferrers',
            'hoy',
            'semana',
            'ultimas'
        ));
    }
}
