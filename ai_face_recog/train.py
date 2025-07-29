import os
import numpy as np
import cv2
import json
from tensorflow.keras.utils import to_categorical
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, MaxPooling2D, Flatten, Dense, Dropout
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.callbacks import EarlyStopping, ModelCheckpoint
from sklearn.model_selection import train_test_split

DATASET_PATH = os.path.join(os.path.dirname(__file__), 'dataset')
IMG_SIZE = 100  # Harus sama dengan cropper & prediksi

X, y = [], []
label_map = {}

# 1. Load data & label
for idx, karyawan_id in enumerate(sorted(os.listdir(DATASET_PATH))):
    karyawan_folder = os.path.join(DATASET_PATH, karyawan_id)
    if not os.path.isdir(karyawan_folder):
        continue
    label_map[idx] = karyawan_id
    for filename in os.listdir(karyawan_folder):
        if not filename.startswith('face_'): continue
        filepath = os.path.join(karyawan_folder, filename)
        img = cv2.imread(filepath, cv2.IMREAD_GRAYSCALE)
        if img is None:
            continue
        img = cv2.resize(img, (IMG_SIZE, IMG_SIZE))
        X.append(img)
        y.append(idx)

X = np.array(X).reshape(-1, IMG_SIZE, IMG_SIZE, 1) / 255.0
y = np.array(y)
y = to_categorical(y, num_classes=len(label_map))

# 2. Split data train-test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42, stratify=y)

# 3. Augmentasi data
datagen = ImageDataGenerator(
    rotation_range=12,
    width_shift_range=0.07,
    height_shift_range=0.07,
    brightness_range=[0.7,1.3],
    zoom_range=0.10,
    horizontal_flip=True
)

# 4. Model CNN (sedikit lebih dalam)
model = Sequential([
    Conv2D(32, (3,3), activation='relu', input_shape=(IMG_SIZE, IMG_SIZE, 1)),
    MaxPooling2D(2,2),
    Conv2D(64, (3,3), activation='relu'),
    MaxPooling2D(2,2),
    Conv2D(128, (3,3), activation='relu'),
    MaxPooling2D(2,2),
    Flatten(),
    Dense(128, activation='relu'),
    Dropout(0.4),
    Dense(len(label_map), activation='softmax')
])

model.compile(optimizer='adam', loss='categorical_crossentropy', metrics=['accuracy'])

# 5. Callbacks
early_stop = EarlyStopping(patience=4, restore_best_weights=True, monitor='val_loss')
ckpt = ModelCheckpoint("best_model_face.h5", save_best_only=True, monitor='val_loss')

# 6. Training
history = model.fit(
    datagen.flow(X_train, y_train, batch_size=16),
    epochs=30,
    validation_data=(X_test, y_test),
    callbacks=[early_stop, ckpt]
)

# 7. Evaluasi
loss, acc = model.evaluate(X_test, y_test)
print(f"Test accuracy: {acc:.2%}")

# 8. Simpan model & label map (pakai json biar aman)
model.save('model_face.h5')
with open('label_map.json', 'w') as f:
    json.dump(label_map, f)

print("Training selesai! Model disimpan sebagai model_face.h5 dan best_model_face.h5")
print("Label map disimpan sebagai label_map.json")
