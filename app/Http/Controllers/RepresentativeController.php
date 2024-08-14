<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    public function index(Request $request){
        $input = $request->all();
        $representatives = \App\Models\Representative::all();
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
            'email' => $input['email']
        ]);
        return redirect(route('representative.index'));
    }

    public function edit(Request $request, $id){
        $representative = \App\Models\Representative::findOrFail($id);
        if(!$representative){
            return 'NO EXISTE EL REPRESENTANTE';
        }
        return view('representative.edit')->with('representative', $representative);
    }

    public function update(Request $request, $id){
        $representative = \App\Models\Representative::findOrFail($id);
        $input = $request->all();
        if(!$representative){
            return 'NO EXISTE EL REPRESENTANTE';
        }

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
