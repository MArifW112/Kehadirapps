import sys
import os
import json
from deepface import DeepFace

# Usage: python predict_deepface.py <foto_cek_path> <karyawan_id>
if len(sys.argv) != 3:
    print(json.dumps({"error": "Usage: python predict_deepface.py <foto_path> <karyawan_id>"}))
    exit(1)

foto_path = sys.argv[1]
karyawan_id = sys.argv[2]
db_dir = os.path.join(os.path.dirname(__file__), 'face_db', str(karyawan_id))

if not os.path.exists(db_dir):
    print(json.dumps({"match": 0, "error": f"Tidak ada folder dataset untuk karyawan_id: {karyawan_id}"}))
    exit(0)

found_match = False
best_score = None
matched_file = None

for dbfile in os.listdir(db_dir):
    if not dbfile.lower().endswith(('.jpg', '.jpeg', '.png')):
        continue
    dbfile_path = os.path.join(db_dir, dbfile)
    try:
        result = DeepFace.verify(img1_path=foto_path, img2_path=dbfile_path, enforce_detection=True)
        score = result.get("distance", 1)
        verified = result.get("verified", False)
        if verified:
            found_match = True
            best_score = 1.0 - score  # semakin mirip semakin mendekati 1
            matched_file = dbfile
            break  # ambil yang match pertama saja
    except Exception as e:
        continue

if found_match:
    print(json.dumps({
        "match": 1,
        "matched_file": matched_file,
        "score": best_score
    }))
else:
    print(json.dumps({
        "match": 0,
        "score": best_score if best_score is not None else 0
    }))
