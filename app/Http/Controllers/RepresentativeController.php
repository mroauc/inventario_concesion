<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){
        $representatives = \App\Models\Representative::where('id_concession', auth()->user()->id_concession)->get();
        return view('representative.index')->with('representatives', $representatives);
    }

    public function create(){
        return view('representative.create');
    }

    public function store(Request $request){
        $input = $request->all();
        $representative = \App\Models\Representative::create([
            'name' => $input['name'],
            'rut' => $input['rut'],
            'phone' => $input['phone'],
            'city' => $input['city'],
            'address' => $input['address'],
            'email' => $input['email'],
            'id_concession' => auth()->user()->id_concession
        ]);
        return redirect(route('representative.index'));
    }

    public function edit(Request $request, $id){
        $representative = \App\Models\Representative::findOrFail($id);
        abort_if($representative->id_concession !== auth()->user()->id_concession, 403);
        return view('representative.edit')->with('representative', $representative);
    }

    public function update(Request $request, $id){
        $representative = \App\Models\Representative::findOrFail($id);
        abort_if($representative->id_concession !== auth()->user()->id_concession, 403);
        $input = $request->all();

        $representative->name = $input['name'];
        $representative->rut = $input['rut'];
        $representative->phone = $input['phone'];
        $representative->city = $input['city'];
        $representative->address = $input['address'];
        $representative->email = $input['email'];
        $representative->save();

        return redirect(route('representative.index'));
    }
}
