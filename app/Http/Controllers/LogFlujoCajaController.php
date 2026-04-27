<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogFlujoCaja;

class LogFlujoCajaController extends Controller
{
    public function index()
    {
        return view('flujo_caja.logs');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value', '');
        $order  = $request->input('order.0.dir', 'desc');

        $idConcession = auth()->user()->id_concession;

        $query = LogFlujoCaja::with('user')
            ->where('id_concession', $idConcession);

        $total = $query->count();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $filtered = $query->count();

        $logs = $query->orderBy('created_at', $order === 'asc' ? 'asc' : 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data = $logs->map(fn($log) => [
            'activity'   => e($log->activity),
            'content'    => e($log->content),
            'user'       => e($log->user->name ?? '-'),
            'created_at' => $log->created_at->format('d/m/Y H:i'),
        ]);

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }
}
