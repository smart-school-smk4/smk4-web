<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;

class SiswaApiController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->get();

        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }
}