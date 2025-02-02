<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all(); // Mengambil semua data paket dari database
        return response()->json($packages); // Mengembalikan data dalam format JSON
    }

    public function show($id)
{
    $package = Package::findOrFail($id);
    return response()->json($package);
}
}

