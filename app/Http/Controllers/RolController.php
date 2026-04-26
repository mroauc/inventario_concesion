<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Flash;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function show(Role $rol)
    {
        $rol->load('permissions');
        $todosLosPermisos = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0];
        });

        return view('roles.show', compact('rol', 'todosLosPermisos'));
    }

    public function update(Request $request, Role $rol)
    {
        // Proteger super_admin de modificaciones
        if ($rol->name === 'super_admin') {
            return back()->with('error', 'Los permisos de super_admin no se pueden modificar.');
        }

        $permisos = $request->input('permisos', []);
        $rol->syncPermissions($permisos);

        \App\Models\Log::create([
            'content'       => 'Permisos del rol "' . $rol->name . '" actualizados.',
            'activity'      => 'Edición',
            'id_user'       => auth()->id(),
            'id_concession' => auth()->user()->id_concession,
        ]);

        Flash::success('Permisos del rol actualizados correctamente.');
        return redirect()->route('roles.show', $rol);
    }
}
