<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use App\Models\KaryawanFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage; // Pastikan ini di-import
use Illuminate\Support\Facades\File; // Tetap ada jika Anda masih menggunakannya untuk base_path, dll.

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
                // Simpan foto utama karyawan ke disk 'public' (yang kini adalah Persistent Volume)
                $path = $image->store('foto_karyawan', 'public');
                KaryawanFoto::create([
                    'karyawan_id' => $karyawan->id,
                    'path'        => $path,
                ]);

                // === Salin ke face_db (DeepFace Gallery) di Persistent Volume ===
                // Tentukan sub-direktori face_db di dalam root disk 'public' (volume)
                $faceDbSubDir = 'face_db/' . $karyawan->id;
                $filenameForFaceDb = 'face_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destPathInVolume = $faceDbSubDir . '/' . $filenameForFaceDb;

                // Pastikan direktori face_db untuk karyawan ini ada di Persistent Volume
                if (!Storage::disk('public')->exists($faceDbSubDir)) {
                    Storage::disk('public')->makeDirectory($faceDbSubDir);
                }

                // Salin foto dari 'foto_karyawan' ke 'face_db' di dalam disk 'public' (volume)
                Storage::disk('public')->copy($path, $destPathInVolume);

                // Dapatkan path fisik ke file di Persistent Volume untuk script Python
                // env('RAILWAY_VOLUME_MOUNT_PATH') akan memberi kita root mount point
                $physicalPathForPython = env('RAILWAY_VOLUME_MOUNT_PATH') . '/' . $destPathInVolume;

                // Ubah path interpreter Python ke 'python3' untuk Linux
                $python = 'python3';
                $cropScript = base_path('ai_face_recog/helper.py'); // Script helper ini masih dari kode aplikasi
                $cmd = "\"$python\" \"$cropScript\" \"$physicalPathForPython\" \"$physicalPathForPython\"";
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
                // Simpan foto utama karyawan ke disk 'public' (yang kini adalah Persistent Volume)
                $path = $image->store('foto_karyawan', 'public');
                KaryawanFoto::create([
                    'karyawan_id' => $karyawan->id,
                    'path'        => $path,
                ]);

                // === Salin ke face_db (DeepFace Gallery) di Persistent Volume ===
                $faceDbSubDir = 'face_db/' . $karyawan->id;
                $filenameForFaceDb = 'face_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destPathInVolume = $faceDbSubDir . '/' . $filenameForFaceDb;

                if (!Storage::disk('public')->exists($faceDbSubDir)) {
                    Storage::disk('public')->makeDirectory($faceDbSubDir);
                }
                Storage::disk('public')->copy($path, $destPathInVolume);

                // Dapatkan path fisik ke file di Persistent Volume untuk script Python
                $physicalPathForPython = env('RAILWAY_VOLUME_MOUNT_PATH') . '/' . $destPathInVolume;

                // Ubah path interpreter Python ke 'python3' untuk Linux
                $python = 'python3';
                $cropScript = base_path('ai_face_recog/helper.py');
                $cmd = "\"$python\" \"$cropScript\" \"$physicalPathForPython\" \"$physicalPathForPython\"";
                exec($cmd, $output, $resultCode);
            }
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        // Hapus foto dari storage & database
        foreach ($karyawan->fotos as $foto) {
            Storage::disk('public')->delete($foto->path); // Menghapus dari Persistent Volume

            // === Hapus juga dari face_db di Persistent Volume ===
            // Asumsi foto yang ada di foto_karyawan/ juga ada salinannya di face_db/
            $faceDbDirInVolume = 'face_db/' . $karyawan->id;
            $basename = pathinfo($foto->path, PATHINFO_BASENAME); // Mengambil nama file dasar dari path utama

            // Cari file-file yang namanya mirip di folder face_db di volume
            // Storage::disk('public')->files() akan list file dari root disk public
            foreach (Storage::disk('public')->files($faceDbDirInVolume) as $faceDbFile) {
                if (str_contains(basename($faceDbFile), pathinfo($basename, PATHINFO_FILENAME))) {
                    Storage::disk('public')->delete($faceDbFile); // Hapus dari Persistent Volume
                }
            }
            $foto->delete(); // Hapus dari database Laravel
        }

        // Hapus data karyawan & user
        $karyawan->user()->delete();
        $karyawan->delete();

        // Opsional: hapus folder face_db jika sudah kosong dan ada di Persistent Volume
        $faceDbDirInVolume = 'face_db/' . $karyawan->id;
        if (Storage::disk('public')->exists($faceDbDirInVolume) && count(Storage::disk('public')->files($faceDbDirInVolume)) === 0) {
            Storage::disk('public')->deleteDirectory($faceDbDirInVolume);
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    // deleteFoto() tetap sama, tapi sekarang akan menghapus dari Persistent Volume
    public function deleteFoto($id)
    {
        $foto = KaryawanFoto::findOrFail($id);
        Storage::disk('public')->delete($foto->path); // Menghapus dari Persistent Volume

        // === Hapus juga dari face_db di Persistent Volume ===
        $faceDbDirInVolume = 'face_db/' . $foto->karyawan_id;
        $basename = pathinfo($foto->path, PATHINFO_BASENAME);
        foreach (Storage::disk('public')->files($faceDbDirInVolume) as $faceDbFile) {
            if (str_contains(basename($faceDbFile), pathinfo($basename, PATHINFO_FILENAME))) {
                Storage::disk('public')->delete($faceDbFile); // Hapus dari Persistent Volume
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
