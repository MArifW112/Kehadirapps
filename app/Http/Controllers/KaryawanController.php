<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use App\Models\KaryawanFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with('fotos')->latest()->get();
        return view('admin.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'alamat'   => 'nullable|string',
            'no_hp'    => 'nullable|string',
            'jabatan'  => 'nullable|string',
            'foto.*'   => 'nullable|image|max:2048', // multiple image support
        ]);

        // Buat user baru
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        // Buat data karyawan
        $karyawan = Karyawan::create([
            'user_id'      => $user->id,
            'nama_karyawan'=> $request->name,
            'email'        => $request->email,
            'alamat'       => $request->alamat,
            'no_hp'        => $request->no_hp,
            'jabatan'      => $request->jabatan,
        ]);

        // Simpan foto-foto (jika ada)
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $image) {
                $path = $image->store('foto_karyawan', 'public');
                KaryawanFoto::create([
                    'karyawan_id' => $karyawan->id,
                    'path'        => $path,
                ]);

                // === Copy ke face_db (DeepFace Gallery) ===
                $faceDbDir = base_path('ai_face_recog/face_db/' . $karyawan->id);
                if (!File::exists($faceDbDir)) {
                    File::makeDirectory($faceDbDir, 0777, true, true);
                }
                $source = storage_path('app/public/' . $path);
                $filename = 'face_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $dest = $faceDbDir . '/' . $filename;
                File::copy($source, $dest);
                $python = 'C:\Users\LENOVO\AppData\Local\Programs\Python\Python312\python.exe';
                $cropScript = base_path('ai_face_recog/helper.py');
                $cmd = "\"$python\" \"$cropScript\" \"$dest\" \"$dest\"";
                exec($cmd, $output, $resultCode);
            }
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan & user berhasil dibuat.');
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nama_karyawan' => 'required|string',
            'email'         => 'required|email|unique:karyawans,email,' . $karyawan->id,
            'alamat'        => 'nullable|string',
            'no_hp'         => 'nullable|string',
            'jabatan'       => 'nullable|string',
            'foto.*'        => 'nullable|image|max:2048',
        ]);

        $karyawan->update([
            'nama_karyawan' => $request->nama_karyawan,
            'email'         => $request->email,
            'alamat'        => $request->alamat,
            'no_hp'         => $request->no_hp,
            'jabatan'       => $request->jabatan,
        ]);

        // Upload foto tambahan (tidak menghapus foto lama)
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $image) {
                $path = $image->store('foto_karyawan', 'public');
                KaryawanFoto::create([
                    'karyawan_id' => $karyawan->id,
                    'path'        => $path,
                ]);

                // === Copy ke face_db ===
                $faceDbDir = base_path('ai_face_recog/face_db/' . $karyawan->id);
                if (!File::exists($faceDbDir)) {
                    File::makeDirectory($faceDbDir, 0777, true, true);
                }
                $source = storage_path('app/public/' . $path);
                $filename = 'face_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $dest = $faceDbDir . '/' . $filename;
                File::copy($source, $dest);
                $python = 'C:\Users\LENOVO\AppData\Local\Programs\Python\Python312\python.exe';
                $cropScript = base_path('ai_face_recog/helper.py');
                $cmd = "\"$python\" \"$cropScript\" \"$dest\" \"$dest\"";
                exec($cmd, $output, $resultCode);
            }
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        // Hapus foto dari storage & database
        foreach ($karyawan->fotos as $foto) {
            Storage::disk('public')->delete($foto->path);

            // Hapus juga dari face_db (jika ada)
            $faceDbDir = base_path('ai_face_recog/face_db/' . $karyawan->id);
            if (File::exists($faceDbDir)) {
                $basename = pathinfo($foto->path, PATHINFO_BASENAME);
                foreach (File::files($faceDbDir) as $file) {
                    if (str_contains($file->getFilename(), pathinfo($basename, PATHINFO_FILENAME))) {
                        File::delete($file->getPathname());
                    }
                }
            }
            $foto->delete();
        }

        // Hapus data karyawan & user
        $karyawan->user()->delete();
        $karyawan->delete();

        // Opsional: hapus folder face_db jika sudah kosong dan ada
        $faceDbDir = base_path('ai_face_recog/face_db/' . $karyawan->id);
        if (File::isDirectory($faceDbDir) && count(File::files($faceDbDir)) === 0) {
            File::deleteDirectory($faceDbDir);
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    // deleteFoto() tetap sama, tapi arahkan ke face_db
    public function deleteFoto($id)
    {
        $foto = KaryawanFoto::findOrFail($id);
        Storage::disk('public')->delete($foto->path);

        // Hapus juga dari face_db jika ada
        $faceDbDir = base_path('ai_face_recog/face_db/' . $foto->karyawan_id);
        $basename = pathinfo($foto->path, PATHINFO_BASENAME);
        foreach (File::files($faceDbDir) as $file) {
            if (str_contains($file->getFilename(), pathinfo($basename, PATHINFO_FILENAME))) {
                File::delete($file->getPathname());
            }
        }
        $foto->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
    public function edit(Karyawan $karyawan)
{
    $karyawan->load('fotos');
    return view('admin.karyawan.edit', compact('karyawan'));
}
public function show($id)
{
    // Ambil data karyawan beserta relasi foto dan user-nya
    $karyawan = Karyawan::with(['fotos', 'user'])->findOrFail($id);
    return view('admin.karyawan.show', compact('karyawan'));
}
}
