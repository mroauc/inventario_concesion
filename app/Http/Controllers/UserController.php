<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Flash;
use Response;
use Hash;

class UserController extends AppBaseController
{
    /** @var $userRepository UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the User.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $users = \App\Models\User::where('id_concession', auth()->user()->id_concession)
            ->with('roles')
            ->get();

        return view('users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new User.
     *
     * @return Response
     */
    public function create()
    {
        $concessions = \App\Models\Concession::all();
        $roles = $this->rolesDisponibles();
        return view('users.create', compact('concessions', 'roles'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $request
     *
     * @return Response
     */
    public function store(CreateUserRequest $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = $this->userRepository->create($input);
        foreach ($input['concession'] as $id_concession => $value) {
            $user->id_concession = $id_concession;
        }
        $user->save();

        $role = $request->input('role', 'administrador');
        if (in_array($role, $this->rolesDisponibles())) {
            $user->syncRoles([$role]);
        }

        Flash::success('Usuario creado correctamente.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('Usuario no encontrado.');

            return redirect(route('users.index'));
        }

        $concessions = \App\Models\Concession::all();
        $roles = $this->rolesDisponibles();
        return view('users.edit', compact('user', 'concessions', 'roles'));
    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param UpdateUserRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateUserRequest $request)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }
        $input =  $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }
        $user = $this->userRepository->update($input, $id);
        if (isset($input['concessions'])) {
            $array_sync = [];
            foreach ($input['concessions'] as $id_concession => $value) {
                $array_sync[] = $id_concession;
            }
            $user->concessions()->sync($id_concession);
        }

        if ($request->filled('role') && in_array($request->role, $this->rolesDisponibles())) {
            $user->syncRoles([$request->role]);
        }

        Flash::success('Usuario actualizado correctamente.');

        return redirect(route('users.index'));
    }

    /**
     * Remove the specified User from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $user = $this->userRepository->find($id);

        if (empty($user)) {
            Flash::error('Usuario no encontrado.');

            return redirect(route('users.index'));
        }

        $this->userRepository->delete($id);

        Flash::success('Usuario eliminado correctamente.');

        return redirect(route('users.index'));
    }

    private function rolesDisponibles(): array
    {
        // super_admin solo puede asignarse desde tinker/seeder
        if (auth()->user()->hasRole('super_admin')) {
            return ['super_admin', 'administrador', 'operador_servicio'];
        }
        return ['administrador', 'operador_servicio'];
    }
}
