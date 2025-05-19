<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index(){
        $guru = Guru::all();
        return view('admin.guru.index', compact('guru'));
    }

    public function create(){
        return view('admin.guru.create');
    }

    public function store(Request $request){
        $request->validate([
            'nama_guru' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp_guru' => 'required|numeric',
            'email_guru' => 'required|email|unique:guru,email_guru',
        ]);

        Guru::create([
            'nama_guru' => $request->nama_guru,
            'jabatan' => $request->jabatan,
            'no_hp_guru' => $request->no_hp_guru,
            'email_guru' => $request->email_guru
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan');
    }

    public function edit($id){
        $guru = Guru::findOrFail($id);
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru){
        $request->validate([
            'nama_guru' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'no_hp_guru' => 'required|numeric',
            'email_guru' => 'required|email|unique:guru,email_guru,' . $guru->id,
        ]);

        $guru->update([
            'nama_guru' => $request->nama_guru,
            'jabatan' => $request->jabatan,
            'no_hp_guru' => $request->no_hp_guru,
            'email_guru' => $request->email_guru
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil di update');
    }

    public function destroy(Guru $guru){
        $guru->delete();
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus');
    }
}
