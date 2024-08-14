<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateConcessionRequest;
use App\Http\Requests\UpdateConcessionRequest;
use App\Repositories\ConcessionRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class ConcessionController extends AppBaseController
{
    /** @var ConcessionRepository $concessionRepository*/
    private $concessionRepository;

    public function __construct(ConcessionRepository $concessionRepo)
    {
        $this->concessionRepository = $concessionRepo;
    }

    /**
     * Display a listing of the Concession.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $concessions = $this->concessionRepository->all();

        return view('concessions.index')
            ->with('concessions', $concessions);
    }

    /**
     * Show the form for creating a new Concession.
     *
     * @return Response
     */
    public function create()
    {
        $representatives = \App\Models\Representative::all();
        return view('concessions.create')->with('representatives', $representatives);
    }

    /**
     * Store a newly created Concession in storage.
     *
     * @param CreateConcessionRequest $request
     *
     * @return Response
     */
    public function store(CreateConcessionRequest $request)
    {
        $input = $request->all();
        // dd($input);

        // $concession = $this->concessionRepository->create($input);
        $concession = \App\Models\Concession::create([
            'name' => $input['name'],
            'address' => $input['address'],
            'id_representative' => $input['id_representative']
        ]);

        Flash::success('Concession saved successfully.');

        return redirect(route('concessions.index'));
    }

    /**
     * Display the specified Concession.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $concession = $this->concessionRepository->find($id);

        if (empty($concession)) {
            Flash::error('Concession not found');

            return redirect(route('concessions.index'));
        }

        return view('concessions.show')->with('concession', $concession);
    }

    /**
     * Show the form for editing the specified Concession.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $concession = $this->concessionRepository->find($id);
        $representatives = \App\Models\Representative::all();

        if (empty($concession)) {
            Flash::error('Concession not found');

            return redirect(route('concessions.index'));
        }

        return view('concessions.edit')->with('concession', $concession)->with('representatives', $representatives);
    }

    /**
     * Update the specified Concession in storage.
     *
     * @param int $id
     * @param UpdateConcessionRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateConcessionRequest $request)
    {
        $concession = $this->concessionRepository->find($id);

        if (empty($concession)) {
            Flash::error('Concession not found');

            return redirect(route('concessions.index'));
        }

        $concession = $this->concessionRepository->update($request->all(), $id);

        Flash::success('Concession updated successfully.');

        return redirect(route('concessions.index'));
    }

    /**
     * Remove the specified Concession from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $concession = $this->concessionRepository->find($id);

        if (empty($concession)) {
            Flash::error('Concession not found');

            return redirect(route('concessions.index'));
        }

        $this->concessionRepository->delete($id);

        Flash::success('Concession deleted successfully.');

        return redirect(route('concessions.index'));
    }
}
