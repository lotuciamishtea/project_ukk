<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kategori;
use App\Models\Barang;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rsetKategori = Kategori::select('id','deskripsi','kategori',
            \DB::raw('(CASE
                WHEN kategori = "M" THEN "Modal"
                WHEN kategori = "A" THEN "Alat"
                WHEN kategori = "BHP" THEN "Bahan Habis Pakai"
                ELSE "Bahan Tidak Habis Pakai"
                END) AS ketKategori'))
            ->paginate(10);
        return view('v_kategori.index', compact('rsetKategori'));
        return DB::table('kategori')->get();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $aKategori = array('blank'=>'Pilih Kategori',
                            'M'=>'Barang Modal',
                            'A'=>'Alat',
                            'BHP'=>'Bahan Habis Pakai',
                            'BTHP'=>'Bahan Tidak Habis Pakai'
                            );
        return view('v_kategori.create',compact('aKategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validasi data
    $request->validate([
        'deskripsi' => 'required',
        'kategori' => 'required|in:M,A,BHP,BTHP',
    ]);

    // Cek apakah deskripsi sudah ada
    $existingCategory = Kategori::where('deskripsi', $request->deskripsi)->first();
    if ($existingCategory) {
        return redirect()->back()->withInput()->withErrors(['deskripsi' => 'Kategori ' . $request->deskripsi . ' Sudah Ada']);
    }

    // Buat kategori baru
    Kategori::create([
        'deskripsi' => $request->deskripsi,
        'kategori' => $request->kategori,
    ]);

    // Redirect ke index
    return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Disimpan!']);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $aKategori = array(
            'blank' => 'Pilih Kategori',
            'M' => 'Barang Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        );
    
        $rsetKategori = Kategori::find($id);
    
        return view('v_kategori.edit', compact('rsetKategori', 'aKategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $request->validate([
        'deskripsi' => 'required',
        'kategori' => 'required|in:M,A,BHP,BTHP',
    ]);        

    // Cek apakah deskripsi sudah ada selain kategori yang sedang di-update
    $existingCategory = Kategori::where('deskripsi', $request->deskripsi)
                                ->where('id', '!=', $id)
                                ->first();

    if ($existingCategory) {
        return redirect()->back()->withInput()->withErrors(['deskripsi' => 'Kategori ' . $request->deskripsi . ' Sudah Ada']);
    }

    $rsetKategori = Kategori::find($id);

    // Update post
    $rsetKategori->update([
        'deskripsi' => $request->deskripsi,
        'kategori' => $request->kategori,
    ]);

    // Redirect ke index dengan notifikasi
    return redirect()->route('kategori.index')->with(['success' => 'Data berhasil diperbarui!']);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        if (DB::table('barang')->where('kategori_id', $id)->exists()){
            return redirect()->route('kategori.index')->with(['Gagal' => 'Data Gagal Dihapus!']);
        } else {
            $rsetKategori = Kategori::find($id);
            $rsetKategori->delete();
            return redirect()->route('kategori.index')->with(['success' => 'Data Berhasil Dihapus!']);
        }

    }

    public function show(string $id)
{
    $rsetKategori = Kategori::find($id);

    if (!$rsetKategori) {
        return redirect()->route('kategori.index')->withErrors(['Kategori tidak ditemukan.']);
    }

    return view('v_kategori.show', compact('rsetKategori'));
}
}