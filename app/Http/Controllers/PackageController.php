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

    public function store(Request $request)
    {
        // Validasi data yang diterima
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'total_photos' => 'required|numeric',
            'edited_photos' => 'required|numeric',
            'includes' => 'required|array', // Pastikan includes adalah array
            'suitable_for' => 'required|string',
        ]);

        // Menyimpan data ke database
        $package = new Package();
        $package->name = $data['name'];
        $package->price = $data['price'];
        $package->duration = $data['duration'];
        $package->total_photos = $data['total_photos'];
        $package->edited_photos = $data['edited_photos'];
        $package->includes = $data['includes']; // Menyimpan array JSON langsung
        $package->suitable_for = $data['suitable_for'];
        $package->save();

        // Mengembalikan respons setelah data berhasil disimpan
        return response()->json($package, 201); // 201 Artinya data berhasil disimpan
    }

    public function update(Request $request, $id)
    {
        // Validasi data yang diterima
        $data = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'total_photos' => 'required|numeric',
            'edited_photos' => 'required|numeric',
            'includes' => 'required|array', // Pastikan includes adalah array
            'suitable_for' => 'required|string',
        ]);

        // Mencari data package berdasarkan ID
        $package = Package::findOrFail($id);

        // Mengupdate data package dengan data yang diterima
        $package->name = $data['name'];
        $package->price = $data['price'];
        $package->duration = $data['duration'];
        $package->total_photos = $data['total_photos'];
        $package->edited_photos = $data['edited_photos'];
        $package->includes = $data['includes']; // Menyimpan array JSON langsung
        $package->suitable_for = $data['suitable_for'];
        $package->save(); // Menyimpan perubahan ke database

        // Mengembalikan respons setelah data berhasil diupdate
        return response()->json($package);
    }

    public function destroy($id)
    {
        // Mencari data package berdasarkan ID
        $package = Package::findOrFail($id);

        // Menghapus data package
        $package->delete();

        // Mengembalikan respons setelah data berhasil dihapus
        return response()->json(['message' => 'Package deleted successfully']);
    }
}
