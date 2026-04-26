<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            // Productos
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',
            'productos.importar',

            // Bodegas
            'bodegas.ver',
            'bodegas.crear',
            'bodegas.editar',
            'bodegas.eliminar',

            // Categorías
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',

            // Artefactos
            'artefactos.ver',
            'artefactos.crear',
            'artefactos.editar',
            'artefactos.eliminar',
            'artefactos.importar',

            // Tipo Artefactos
            'tipo_artefactos.ver',
            'tipo_artefactos.crear',
            'tipo_artefactos.editar',
            'tipo_artefactos.eliminar',

            // Órdenes de Servicio
            'ordenes.ver',
            'ordenes.crear',
            'ordenes.editar',
            'ordenes.eliminar',
            'ordenes.cerrar',

            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // Técnicos
            'tecnicos.ver',
            'tecnicos.crear',
            'tecnicos.editar',
            'tecnicos.eliminar',

            // Servicios (tipos de servicio)
            'servicios.ver',
            'servicios.crear',
            'servicios.editar',
            'servicios.eliminar',

            // Flujo de Caja
            'flujo_caja.ver',
            'flujo_caja.operar',
            'flujo_caja.reabrir',

            // Historial / Logs
            'historial.ver',

            // Usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'usuarios.asignar_rol',

            // Administración (solo super_admin)
            'concesiones.ver',
            'concesiones.crear',
            'concesiones.editar',
            'concesiones.eliminar',
            'representantes.ver',
            'representantes.crear',
            'representantes.editar',
            'representantes.eliminar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ─── SUPER ADMIN ────────────────────────────────────────────────────
        // Tiene todos los permisos sin excepción
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ─── ADMINISTRADOR ──────────────────────────────────────────────────
        // Gestiona su concesión: todo excepto concesiones y representantes
        $administrador = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $administrador->syncPermissions([
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar', 'productos.importar',
            'bodegas.ver', 'bodegas.crear', 'bodegas.editar', 'bodegas.eliminar',
            'categorias.ver', 'categorias.crear', 'categorias.editar', 'categorias.eliminar',
            'artefactos.ver', 'artefactos.crear', 'artefactos.editar', 'artefactos.eliminar', 'artefactos.importar',
            'tipo_artefactos.ver', 'tipo_artefactos.crear', 'tipo_artefactos.editar', 'tipo_artefactos.eliminar',
            'ordenes.ver', 'ordenes.crear', 'ordenes.editar', 'ordenes.eliminar', 'ordenes.cerrar',
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
            'tecnicos.ver', 'tecnicos.crear', 'tecnicos.editar', 'tecnicos.eliminar',
            'servicios.ver', 'servicios.crear', 'servicios.editar', 'servicios.eliminar',
            'flujo_caja.ver', 'flujo_caja.operar', 'flujo_caja.reabrir',
            'historial.ver',
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'usuarios.asignar_rol',
        ]);

        // ─── OPERADOR DE SERVICIO ────────────────────────────────────────────
        $operador = Role::firstOrCreate(['name' => 'operador_servicio', 'guard_name' => 'web']);
        $operador->syncPermissions([
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
            'artefactos.ver',
            'ordenes.ver', 'ordenes.crear', 'ordenes.editar', 'ordenes.cerrar',
            'clientes.ver', 'clientes.crear', 'clientes.editar',
            'servicios.ver',
            'flujo_caja.ver', 'flujo_caja.operar',
        ]);

        // ─── ASIGNAR ROL ADMINISTRADOR A TODOS LOS USUARIOS EXISTENTES ──────
        User::all()->each(function (User $user) {
            if (!$user->hasAnyRole(['super_admin', 'administrador', 'operador_servicio'])) {
                $user->assignRole('administrador');
            }
        });

        $this->command->info('Roles y permisos creados correctamente.');
        $this->command->info('Todos los usuarios existentes asignados como administrador.');
    }
}
