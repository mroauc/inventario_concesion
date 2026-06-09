<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LandingVisit;

class TrackLandingVisit
{
    public function handle(Request $request, Closure $next, string $pagina)
    {
        $response = $next($request);

        // Solo registrar peticiones GET exitosas (no bots de SEO obvios)
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            LandingVisit::create([
                'pagina'     => $pagina,
                'ip'         => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
                'referrer'   => substr($request->headers->get('referer') ?? '', 0, 255),
            ]);
        }

        return $response;
    }
}
