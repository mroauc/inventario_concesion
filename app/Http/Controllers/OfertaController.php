<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OfertaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('ofertas.index');
    }

    public function datatables(Request $request)
    {
        $draw   = $request->input('draw', 1);
        $start  = $request->input('start', 0);
        $length = $request->input('length', 15);
        $search = $request->input('search.value', '');

        $query = Oferta::where('id_concession', auth()->user()->id_concession);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('precio', 'like', "%{$search}%");
            });
        }

        $total    = Oferta::where('id_concession', auth()->user()->id_concession)->count();
        $filtered = $query->count();

        $orderCol  = $request->input('order.0.column', 4);
        $orderDir  = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $columnMap = [1 => 'nombre', 2 => 'precio', 3 => 'vendido', 4 => 'created_at'];
        if (isset($columnMap[$orderCol])) {
            $query->orderBy($columnMap[$orderCol], $orderDir);
        } else {
            $query->orderByDesc('created_at');
        }

        $ofertas = $query->skip($start)->take($length)->get();

        $data = $ofertas->map(function ($oferta) {
            $foto = $oferta->fotoPrincipal();
            $miniatura = $foto
                ? '<img src="' . asset('storage/' . $foto) . '" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:4px;">'
                : '<span class="text-muted"><i class="far fa-image"></i></span>';

            $precio = '$' . number_format($oferta->precio, 0, ',', '.');

            $badges = '<span class="badge badge-' . ($oferta->vendido ? 'secondary' : 'success') . '">'
                    . ($oferta->vendido ? 'Vendido' : 'Disponible') . '</span>';
            if (!$oferta->estado) {
                $badges .= ' <span class="badge badge-warning">Oculta</span>';
            }

            $labelToggle = $oferta->vendido ? 'Marcar disponible' : 'Marcar vendido';
            $iconToggle  = $oferta->vendido ? 'fa-rotate-left' : 'fa-tag';

            $acciones = '
                <div class="btn-group">
                    <a href="' . route('ofertas.show', $oferta->id) . '" class="btn btn-default btn-xs"><i class="far fa-eye"></i></a>
                    <a href="' . route('ofertas.edit', $oferta->id) . '" class="btn btn-default btn-xs"><i class="far fa-edit"></i></a>
                    <form method="POST" action="' . route('ofertas.vendido', $oferta->id) . '" style="display:inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <button type="submit" class="btn btn-default btn-xs" title="' . $labelToggle . '"><i class="fas ' . $iconToggle . '"></i></button>
                    </form>
                    <form method="POST" action="' . route('ofertas.destroy', $oferta->id) . '" style="display:inline">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm(\'¿Está seguro de eliminar esta oferta?\')"><i class="far fa-trash-alt"></i></button>
                    </form>
                </div>';

            return [
                $miniatura,
                e($oferta->nombre),
                $precio,
                $badges,
                e($oferta->created_at->format('d/m/Y')),
                $acciones,
            ];
        });

        return response()->json([
            'draw'            => (int) $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function create()
    {
        return view('ofertas.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->reglas());

        // Las casillas llegan indexadas (0,1,2) y pueden venir con huecos:
        // se compactan para que las fotos queden consecutivas.
        $subidas = array_values(array_filter($request->file('fotos', []) ?: []));

        Oferta::create([
            'nombre'        => $request->nombre,
            'descripcion'   => $request->descripcion,
            'precio'        => $request->precio,
            'vendido'       => $request->boolean('vendido'),
            'estado'        => $request->boolean('estado', true),
            'fotos'         => $this->guardarFotos($subidas) ?: null,
            'id_concession' => auth()->user()->id_concession,
        ]);

        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta creada exitosamente.');
    }

    public function show(Oferta $oferta)
    {
        abort_if($oferta->id_concession != auth()->user()->id_concession, 403);
        return view('ofertas.show', compact('oferta'));
    }

    public function edit(Oferta $oferta)
    {
        abort_if($oferta->id_concession != auth()->user()->id_concession, 403);
        return view('ofertas.edit', compact('oferta'));
    }

    public function update(Request $request, Oferta $oferta)
    {
        abort_if($oferta->id_concession != auth()->user()->id_concession, 403);

        $request->validate($this->reglas());

        $datos = [
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio'      => $request->precio,
            'vendido'     => $request->boolean('vendido'),
            'estado'      => $request->boolean('estado', true),
        ];

        $datos['fotos'] = $this->mezclarFotos($request, $oferta);

        $oferta->update($datos);

        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta actualizada exitosamente.');
    }

    public function destroy(Oferta $oferta)
    {
        abort_if($oferta->id_concession != auth()->user()->id_concession, 403);

        $this->borrarFotos($oferta->fotos);
        $oferta->update(['fotos' => null]);
        $oferta->delete();

        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta eliminada exitosamente.');
    }

    public function toggleVendido(Oferta $oferta)
    {
        abort_if($oferta->id_concession != auth()->user()->id_concession, 403);

        $oferta->update(['vendido' => !$oferta->vendido]);

        \App\Models\Log::create([
            'content'       => "Oferta \"{$oferta->nombre}\" marcada como " . ($oferta->vendido ? 'vendida' : 'disponible') . '.',
            'activity'      => 'Edición',
            'id_user'       => auth()->user()->id,
            'id_concession' => auth()->user()->id_concession,
        ]);

        return redirect()->route('ofertas.index')
            ->with('success', 'Oferta marcada como ' . ($oferta->vendido ? 'vendida' : 'disponible') . '.');
    }

    private function reglas(): array
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio'      => 'required|numeric|min:0',
            // Una entrada por casilla; las vacías llegan como null
            'fotos'       => 'nullable|array|max:3',
            'fotos.*'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    /**
     * Combina, posición por posición, las fotos ya guardadas con las nuevas.
     *
     * Cada casilla del formulario es independiente: subir un archivo reemplaza
     * solo esa posición, y el flag fotos_eliminadas marca las que se quitaron.
     * Devuelve el arreglo final compactado (sin huecos), o null si no queda ninguna.
     */
    private function mezclarFotos(Request $request, Oferta $oferta): ?array
    {
        $actuales  = $oferta->fotos ?? [];
        $subidas   = $request->file('fotos', []) ?: [];
        $eliminar  = $request->input('fotos_eliminadas', []);

        $finales  = [];
        $aBorrar  = [];

        for ($i = 0; $i < 3; $i++) {
            $actual = $actuales[$i] ?? null;
            $nueva  = $subidas[$i] ?? null;

            if ($nueva) {
                // Reemplazo: la de esta posición se va del disco
                if ($actual) {
                    $aBorrar[] = $actual;
                }
                $finales[] = $this->guardarFotos([$nueva])[0];
                continue;
            }

            if (!empty($eliminar[$i]) && $actual) {
                $aBorrar[] = $actual;
                continue;
            }

            if ($actual) {
                $finales[] = $actual;
            }
        }

        $this->borrarFotos($aBorrar);

        return $finales ?: null;
    }

    /** Lienzo fijo 4:3, el mismo que usa la card de la landing. */
    private const FOTO_ANCHO = 1200;
    private const FOTO_ALTO  = 900;

    /**
     * Redimensiona y guarda hasta 3 fotos. Devuelve los paths relativos al disco public.
     *
     * Todas quedan en un lienzo 4:3 de 1200x900: la imagen se escala para caber
     * entera (sin recortar) y se rellena con blanco lo que sobra. Así una foto
     * vertical de producto no se ve gigante ni deformada junto a una apaisada.
     *
     * ponytail: un solo tamaño, sin thumbnails. Agregar una versión pequeña si
     * la grilla de la landing se pone lenta.
     */
    private function guardarFotos(?array $archivos): array
    {
        if (empty($archivos)) {
            return [];
        }

        $manager = new ImageManager(new Driver());
        $paths   = [];

        foreach (array_slice($archivos, 0, 3) as $archivo) {
            $nombre = uniqid('of_') . '.jpg';
            $imagen = $manager->read($archivo->getRealPath());

            // Una foto más chica que el lienzo no se agranda (quedaría borrosa):
            // se centra sobre el fondo blanco a su tamaño original.
            if ($imagen->width() < self::FOTO_ANCHO && $imagen->height() < self::FOTO_ALTO) {
                $imagen->resizeCanvas(self::FOTO_ANCHO, self::FOTO_ALTO, 'ffffff', 'center');
            } else {
                $imagen->contain(self::FOTO_ANCHO, self::FOTO_ALTO, 'ffffff');
            }

            Storage::disk('public')->put("ofertas/{$nombre}", (string) $imagen->toJpeg(80));
            $paths[] = "ofertas/{$nombre}";
        }

        return $paths;
    }

    private function borrarFotos(?array $paths): void
    {
        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }
}
