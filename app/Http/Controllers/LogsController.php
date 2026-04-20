<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        return view('logs.index');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value', '');

        $query = \App\Models\Log::where('id_concession', auth()->user()->id_concession)
            ->with('user');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $total    = \App\Models\Log::where('id_concession', auth()->user()->id_concession)->count();
        $filtered = $query->count();

        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $request->input('order.0.column', 3);
        // Only created_at (col 3) is sortable
        $query->orderBy('created_at', $orderCol == 3 ? $orderDir : 'desc');

        $logs = $query->skip($start)->take($length)->get();

        $data = $logs->map(function ($log) {
            return [
                e($log->activity),
                e($log->content),
                e($log->user->name ?? '-'),
                $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-',
            ];
        });

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }
}
