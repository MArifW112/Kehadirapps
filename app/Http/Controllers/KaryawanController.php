<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use App\Models\KaryawanFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File; // Tetap ada jika Anda masih menggunakannya untuk base_path, dll.
use Illuminate\Support\Facades\Log;   // <<< Pastikan ini di-import
use GuzzleHttp\Client;             // <<< Pastikan ini di-import

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
        Log::info('==== [KaryawanController@store] Diterima Request ====');
        Log::info('Request fields:', $request->all());

        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'alamat'   => 'nullable|string',
            'no_hp'    => 'nullable|string',
            'jabatan'  => 'nullable|string',
            'foto.*'   => 'nullable|image|max:4096', // multiple image support
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
            'user_id'       => $user->id,
            'nama_karyawan' => $request->name,
            'email'         => $request->email,
            'alamat'        => $request->alamat,
            'no_hp'         => $request->no_hp,
            'jabatan'       => $request->jabatan,
        ]);

        // Simpan foto-foto (jika ada) dan panggil API Python untuk cropping
        if ($request->hasFile('foto')) {
            $pythonApiUrl = env('PYTHON_API_URL');
            if (empty($pythonApiUrl)) {
                Log::error('[KaryawanController@store] PYTHON_API_URL tidak terdefinisi di .env Laravel!');
                $karyawan->delete(); $user->delete();
                return redirect()->back()->withInput()->with('error', 'Konfigurasi server tidak lengkap (API Python URL missing).');
            }

            $client = new Client();
            $successfulUploads = [];

            foreach ($request->file('foto') as $index => $image) {
                // Simpan foto asli sementara di lokasi temporer Laravel (disk 'private')
                $tempPath = $image->store('temp_uploads', 'private'); // 'private' disk biasanya di storage/app
                $tempFullPath = Storage::disk('private')->path($tempPath);
                Log::info("[KaryawanController@store] Foto asli disimpan sementara di: $tempFullPath");

                // --- DEBUGGING FILE TEMPORER DAN FOPEN ---
                Log::info("[KaryawanController@store] Debug File Temporer:", [
                    'temp_path_storage' => $tempPath,
                    'temp_full_path_physical' => $tempFullPath,
                    'file_exists_physical' => file_exists($tempFullPath) ? 'true' : 'false',
                    'file_is_readable' => is_readable($tempFullPath) ? 'true' : 'false',
                    'file_size_bytes' => file_exists($tempFullPath) ? filesize($tempFullPath) : 'N/A',
                ]);

                $fileHandle = @fopen($tempFullPath, 'r'); // Menggunakan @ untuk menekan warning jika file tidak ada
                if ($fileHandle === false) {
                    Log::error("[KaryawanController@store] GAGAL MEMBUKA FILE TEMPORER UNTUK DIKIRIM KE PYTHON!", [
                        'temp_full_path' => $tempFullPath,
                        'karyawan_id' => $karyawan->id
                    ]);
                    Storage::disk('private')->delete($tempPath);
                    continue; // Lanjutkan ke foto berikutnya jika ada error file ini
                }
                // --- AKHIR DEBUGGING FILE TEMPORER ---

                try {
                    // --- DEBUGGING PANGGILAN GUZZLE ---
                    Log::info("[KaryawanController@store] Memanggil API Python /crop-face:", [
                        'target_url' => "$pythonApiUrl/crop-face",
                        'http_method_sent' => 'POST', // Konfirmasi bahwa kita memanggil POST
                        'karyawan_id_sent' => $karyawan->id,
                        'original_filename' => $image->getClientOriginalName(),
                    ]);
                    // --- AKHIR DEBUGGING PANGGILAN GUZZLE ---

                    $response = $client->post("$pythonApiUrl/crop-face", [
                        'multipart' => [ // Kirim sebagai multipart form-data untuk file dan field lainnya
                            [
                                'name'     => 'foto',
                                'contents' => $fileHandle, // Gunakan resource file handle
                                'filename' => $image->getClientOriginalName()
                            ],
                            [
                                'name'     => 'karyawan_id',
                                'contents' => $karyawan->id
                            ]
                        ],
                        'verify' => false // Opsional: Nonaktifkan verifikasi SSL untuk development
                    ]);

                    $statusCode = $response->getStatusCode();
                    $body = $response->getBody()->getContents();
                    $result = json_decode($body, true);

                    Log::info("[KaryawanController@store] API Python /crop-face Response Status: $statusCode");
                    Log::info("[KaryawanController@store] API Python /crop-face Response Body:", ['body' => $result]);

                    // Pastikan respons sukses dari Python API
                    if ($statusCode === 200 && isset($result['status']) && $result['status'] === 'success') {
                        // Python API sudah menyimpan foto yang ter-crop ke Persistent Volume.
                        // 'path' yang dikembalikan adalah path relatif dari FACE_DB_ROOT
                        $croppedPathRelative = 'face_db/' . $result['path'];

                        KaryawanFoto::create([
                            'karyawan_id' => $karyawan->id,
                            'path'        => $croppedPathRelative, // Simpan path ke foto yang sudah di-crop di face_db
                        ]);
                        $successfulUploads[] = $croppedPathRelative;
                        Log::info("[KaryawanController@store] Foto cropped berhasil disimpan dan path dicatat: $croppedPathRelative");

                    } else {
                        Log::warning("[KaryawanController@store] Gagal crop foto (index $index): " . ($result['message'] ?? 'Unknown error dari API Python'));
                    }

                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                    Log::error("[KaryawanController@store] Guzzle RequestException saat crop foto (index $index): " . $e->getMessage(), [
                        'response_status' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                        'response_body' => $responseBody,
                        'request_url' => $e->getRequest()->getUri()->__toString(),
                        'request_method' => $e->getRequest()->getMethod(),
                    ]);
                } catch (\Exception $e) {
                    Log::error("[KaryawanController@store] General Exception saat crop foto (index $index): " . $e->getMessage());
                } finally {
                    // Pastikan file handle ditutup
                    if (isset($fileHandle) && is_resource($fileHandle)) {
                        fclose($fileHandle);
                    }
                    // Hapus file temporer setelah diproses (berhasil atau gagal)
                    Storage::disk('private')->delete($tempPath);
                    Log::info("[KaryawanController@store] File temporer dihapus: $tempFullPath");
                }
            }

            if (empty($successfulUploads)) {
                Log::warning('[KaryawanController@store] Tidak ada foto wajah yang berhasil diunggah dan disimpan!');
                $karyawan->delete(); $user->delete();
                return redirect()->back()->withInput()->with('error', 'Gagal mengunggah dan memproses foto wajah. Pastikan foto mengandung wajah yang jelas.');
            }
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan & user berhasil dibuat.');
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        Log::info('==== [KaryawanController@update] Diterima Request ====');
        Log::info('Request fields:', $request->all());

        $request->validate([
            'nama_karyawan' => 'required|string',
            'email'         => 'required|email|unique:karyawans,email,' . $karyawan->id,
            'alamat'        => 'nullable|string',
            'no_hp'         => 'nullable|string',
            'jabatan'       => 'nullable|string',
            'foto.*'        => 'nullable|image|max:4096',
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
            $pythonApiUrl = env('PYTHON_API_URL');
            if (empty($pythonApiUrl)) {
                Log::error('[KaryawanController@update] PYTHON_API_URL tidak terdefinisi di .env Laravel!');
                return redirect()->back()->withInput()->with('error', 'Konfigurasi server tidak lengkap (API Python URL missing).');
            }

            $client = new Client();

            foreach ($request->file('foto') as $index => $image) {
                $tempPath = $image->store('temp_uploads', 'private');
                $tempFullPath = Storage::disk('private')->path($tempPath);
                Log::info("[KaryawanController@update] Foto asli disimpan sementara di: $tempFullPath");

                // --- DEBUGGING FILE TEMPORER DAN FOPEN ---
                Log::info("[KaryawanController@update] Debug File Temporer:", [
                    'temp_path_storage' => $tempPath,
                    'temp_full_path_physical' => $tempFullPath,
                    'file_exists_physical' => file_exists($tempFullPath) ? 'true' : 'false',
                    'file_is_readable' => is_readable($tempFullPath) ? 'true' : 'false',
                    'file_size_bytes' => file_exists($tempFullPath) ? filesize($tempFullPath) : 'N/A',
                ]);

                $fileHandle = @fopen($tempFullPath, 'r');
                if ($fileHandle === false) {
                    Log::error("[KaryawanController@update] GAGAL MEMBUKA FILE TEMPORER UNTUK DIKIRIM KE PYTHON!", [
                        'temp_full_path' => $tempFullPath,
                        'karyawan_id' => $karyawan->id
                    ]);
                    Storage::disk('private')->delete($tempPath);
                    continue;
                }
                // --- AKHIR DEBUGGING FILE TEMPORER ---

                try {
                    // --- DEBUGGING PANGGILAN GUZZLE ---
                    Log::info("[KaryawanController@update] Memanggil API Python /crop-face:", [
                        'target_url' => "$pythonApiUrl/crop-face",
                        'http_method_sent' => 'POST',
                        'karyawan_id_sent' => $karyawan->id,
                        'original_filename' => $image->getClientOriginalName(),
                    ]);
                    // --- AKHIR DEBUGGING PANGGILAN GUZZLE ---

                    $response = $client->post("$pythonApiUrl/crop-face", [
                        'multipart' => [
                            [
                                'name'     => 'foto',
                                'contents' => $fileHandle,
                                'filename' => basename($tempFullPath)
                            ],
                            [
                                'name'     => 'karyawan_id',
                                'contents' => $karyawan->id
                            ]
                        ],
                        'verify' => false
                    ]);

                    $statusCode = $response->getStatusCode();
                    $body = $response->getBody()->getContents();
                    $result = json_decode($body, true);

                    Log::info("[KaryawanController@update] API Python /crop-face Response Status: $statusCode");
                    Log::info("[KaryawanController@update] API Python /crop-face Response Body:", ['body' => $result]);

                    if ($statusCode === 200 && isset($result['status']) && $result['status'] === 'success') {
                        $croppedPathRelative = 'face_db/' . $result['path'];

                        KaryawanFoto::create([
                            'karyawan_id' => $karyawan->id,
                            'path'        => $croppedPathRelative,
                        ]);
                        Log::info("[KaryawanController@update] Foto cropped berhasil disimpan dan path dicatat: $croppedPathRelative");
                    } else {
                        Log::warning("[KaryawanController@update] Gagal crop foto (index $index): " . ($result['message'] ?? 'Unknown error dari API Python'));
                    }

                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                    Log::error("[KaryawanController@update] Guzzle RequestException saat crop foto (index $index): " . $e->getMessage(), [
                        'response_status' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                        'response_body' => $responseBody,
                        'request_url' => $e->getRequest()->getUri()->__toString(),
                        'request_method' => $e->getRequest()->getMethod(),
                    ]);
                } catch (\Exception $e) {
                    Log::error("[KaryawanController@update] General Exception saat crop foto (index $index): " . $e->getMessage());
                } finally {
                    if (isset($fileHandle) && is_resource($fileHandle)) {
                        fclose($fileHandle); // Pastikan file handle ditutup
                    }
                    Storage::disk('private')->delete($tempPath);
                    Log::info("[KaryawanController@update] File temporer dihapus: $tempFullPath");
                }
            }
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Data karyawan diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        Log::info('==== [KaryawanController@destroy] Menghapus Karyawan ID: ' . $karyawan->id . ' ====');
        foreach ($karyawan->fotos as $foto) {
            if (Storage::disk('public')->exists($foto->path)) {
                Storage::disk('public')->delete($foto->path);
                Log::info("[KaryawanController@destroy] Foto DB dihapus dari Persistent Volume: " . $foto->path);
            } else {
                Log::warning("[KaryawanController@destroy] Foto tidak ditemukan di Persistent Volume, mungkin sudah dihapus sebelumnya: " . $foto->path);
            }
            $foto->delete();
        }

        $karyawan->user()->delete();
        $karyawan->delete();
        Log::info('[KaryawanController@destroy] Karyawan dan User berhasil dihapus.');

        $faceDbDirInVolume = 'face_db/' . $karyawan->id;
        if (Storage::disk('public')->exists($faceDbDirInVolume) && count(Storage::disk('public')->files($faceDbDirInVolume)) === 0) {
            Storage::disk('public')->deleteDirectory($faceDbDirInVolume);
            Log::info("[KaryawanController@destroy] Folder face_db karyawan dihapus karena kosong: " . $faceDbDirInVolume);
        }

        return redirect()->route('admin.karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function deleteFoto($id)
    {
        Log::info('==== [KaryawanController@deleteFoto] Menghapus Foto ID: ' . $id . ' ====');
        $foto = KaryawanFoto::findOrFail($id);

        if (Storage::disk('public')->exists($foto->path)) {
            Storage::disk('public')->delete($foto->path);
            Log::info("[KaryawanController@deleteFoto] Foto dihapus dari Persistent Volume: " . $foto->path);
        } else {
            Log::warning("[KaryawanController@deleteFoto] Foto tidak ditemukan di Persistent Volume, mungkin sudah dihapus sebelumnya: " . $foto->path);
        }

        $foto->delete();
        Log::info("[KaryawanController@deleteFoto] Foto dari database Laravel dihapus.");

        $faceDbDirInVolume = 'face_db/' . $foto->karyawan_id;
        if (Storage::disk('public')->exists($faceDbDirInVolume) && count(Storage::disk('public')->files($faceDbDirInVolume)) === 0) {
            Storage::disk('public')->deleteDirectory($faceDbDirInVolume);
            Log::info("[KaryawanController@deleteFoto] Folder face_db karyawan dihapus karena kosong: " . $faceDbDirInVolume);
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function edit(Karyawan $karyawan)
    {
        $karyawan->load('fotos');
        return view('admin.karyawan.edit', compact('karyawan'));
    }

    public function show($id)
    {
        $karyawan = Karyawan::with(['fotos', 'user'])->findOrFail($id);
        return view('admin.karyawan.show', compact('karyawan'));
    }
}
