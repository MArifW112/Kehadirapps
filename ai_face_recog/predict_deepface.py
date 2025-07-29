import sys
import os
import json
from deepface import DeepFace

# Ambil argumen dari Laravel
# argv[0] adalah nama skrip itu sendiri
# argv[1] adalah path_foto_absen (fotoFull)
# argv[2] adalah karyawan_id
# argv[3] adalah face_db_root_path (RAILWAY_VOLUME_MOUNT_PATH)

if len(sys.argv) < 4:
    print(json.dumps({"match": 0, "score": 0, "error": "Missing arguments. Usage: script.py <foto_path> <karyawan_id> <face_db_root_path>"}))
    sys.exit(1)

foto_absen_path = sys.argv[1]
karyawan_id = sys.argv[2]
face_db_root_path = sys.argv[3] # <<< INI ARGUMEN BARU

# Path ke direktori face_db di dalam volume
# Ini akan menjadi /app/storage/app/public/face_db
face_db_path = os.path.join(face_db_root_path, 'face_db') # <<< MODIFIKASI INI

# Path ke direktori foto karyawan spesifik di dalam face_db
karyawan_face_dir = os.path.join(face_db_path, str(karyawan_id)) # <<< MODIFIKASI INI

result = {"match": 0, "score": 0}

try:
    # Pastikan direktori karyawan ada dan berisi gambar
    if not os.path.exists(karyawan_face_dir) or not os.listdir(karyawan_face_dir):
        result["error"] = f"Direktori wajah karyawan ID {karyawan_id} tidak ditemukan atau kosong di {karyawan_face_dir}."
        print(json.dumps(result))
        sys.exit(0)

    # Lakukan verifikasi
    # model_name dan distance_metric bisa disesuaikan
    dfs = DeepFace.find(
        img_path = foto_absen_path,
        db_path = karyawan_face_dir, # <<< GUNAKAN PATH YANG BARU DIBANGUN
        model_name = "VGG-Face", # Atau "Facenet", "OpenFace", "DeepFace", "DeepID", "Dlib", "ArcFace"
        distance_metric = "cosine", # Atau "euclidean", "euclidean_l2"
        enforce_detection = False # Set True jika ingin memastikan hanya ada 1 wajah terdeteksi
    )

    # DeepFace.find mengembalikan list of dataframes.
    # Kita perlu memeriksa apakah ada hasil yang valid.
    if dfs and isinstance(dfs, list) and len(dfs) > 0 and not dfs[0].empty:
        # Ambil baris pertama dari dataframe (yang paling cocok)
        best_match = dfs[0].iloc[0]
        distance = best_match['VGG-Face_cosine'] # Sesuaikan dengan model dan metric Anda
        identity = best_match['identity'] # Path ke gambar yang cocok

        # DeepFace menggunakan "jarak" (distance), bukan "skor kemiripan".
        # Jarak yang lebih rendah berarti lebih mirip.
        # Anda perlu menentukan ambang batas jarak yang sesuai.
        # Misalnya, jika distance_metric="cosine", jarak 0 berarti sama persis.
        # Ambang batas DeepFace default untuk VGG-Face/cosine adalah 0.40
        threshold = 0.40 # Ini adalah ambang batas DeepFace default untuk VGG-Face/cosine

        if distance <= threshold:
            result["match"] = 1
            result["score"] = 1 - distance # Konversi jarak ke skor kemiripan (0-1)
            result["identity"] = identity
        else:
            result["match"] = 0
            result["score"] = 1 - distance
            result["identity"] = identity
            result["message"] = f"Jarak ({distance}) di atas ambang batas ({threshold})."
    else:
        result["error"] = "No face detected in the input image or no match found in the database."

except Exception as e:
    result["error"] = str(e)
    result["match"] = 0
    result["score"] = 0

print(json.dumps(result))
