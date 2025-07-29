import cv2
import os
import sys

# Path ke haarcascade
CASCADE_PATH = os.path.join(os.path.dirname(__file__), 'haarcascade_frontalface_default.xml')

def crop_face(image_path, save_path=None, padding=30):
    img = cv2.imread(image_path)
    if img is None:
        print(f"Gagal buka gambar: {image_path}")
        return False

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    face_cascade = cv2.CascadeClassifier(CASCADE_PATH)
    faces = face_cascade.detectMultiScale(gray, 1.2, 5)

    if len(faces) == 0:
        print(f"Tidak ditemukan wajah di: {image_path}")
        return False

    # Ambil wajah paling besar
    (x, y, w, h) = max(faces, key=lambda rect: rect[2]*rect[3])
    x1 = max(x - padding, 0)
    y1 = max(y - padding, 0)
    x2 = min(x + w + padding, img.shape[1])
    y2 = min(y + h + padding, img.shape[0])
    face_img = img[y1:y2, x1:x2]

    if save_path:
        cv2.imwrite(save_path, face_img)
        print(f"Crop wajah disimpan ke: {save_path}")
    else:
        # Jika tidak ada save_path, simpan ke file asli dengan suffix _crop
        path, ext = os.path.splitext(image_path)
        default_save = path + '_crop' + ext
        cv2.imwrite(default_save, face_img)
        print(f"Crop wajah disimpan ke: {default_save}")
    return True

if __name__ == '__main__':
    # Jika dipanggil dari CLI
    if len(sys.argv) >= 2:
        img_path = sys.argv[1]
        save_path = sys.argv[2] if len(sys.argv) > 2 else None
        crop_face(img_path, save_path)
    else:
        # Batch processing seluruh dataset (opsional)
        folder = os.path.join(os.path.dirname(__file__), 'dataset')
        for karyawan_id in os.listdir(folder):
            karyawan_folder = os.path.join(folder, karyawan_id)
            if not os.path.isdir(karyawan_folder):
                continue
            for filename in os.listdir(karyawan_folder):
                if not filename.lower().endswith(('.png', '.jpg', '.jpeg')):
                    continue
                filepath = os.path.join(karyawan_folder, filename)
                savepath = os.path.join(karyawan_folder, 'face_' + filename)
                crop_face(filepath, savepath)
