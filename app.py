import cv2
import numpy as np
import os
import requests
import time
from datetime import datetime
import pytz
from insightface.app import FaceAnalysis
from flask import Flask, Response
from dotenv import load_dotenv
import threading
from sklearn.svm import SVC
from sklearn.preprocessing import LabelEncoder
import pickle
import json
import csv
import onnxruntime as ort
print("Available providers:", ort.get_available_providers())

# === KONFIGURASI & INISIALISASI ===

load_dotenv()
DEVICE_ID = os.getenv('DEVICE_ID')
LARAVEL_BASE_URL = os.getenv('LARAVEL_BASE_URL')

if not DEVICE_ID or not LARAVEL_BASE_URL:
    raise Exception("KRITIS: DEVICE_ID dan LARAVEL_BASE_URL wajib diatur di .env")

LARAVEL_STUDENTS_API_URL = f"{LARAVEL_BASE_URL}/api/devices/{DEVICE_ID}/students"
LARAVEL_ATTENDANCE_API_URL = f"{LARAVEL_BASE_URL}/api/absensi-siswa"

# === KONFIGURASI LOCAL STORAGE ===
LOCAL_MODEL_DIR = 'local_models'
LOCAL_EMBEDDINGS_FILE = os.path.join(LOCAL_MODEL_DIR, 'embeddings.pkl')
LOCAL_STUDENT_INFO_FILE = os.path.join(LOCAL_MODEL_DIR, 'student_info.json')
LOCAL_CLASSIFIER_FILE = os.path.join(LOCAL_MODEL_DIR, 'classifier.pkl')
LOCAL_LABEL_ENCODER_FILE = os.path.join(LOCAL_MODEL_DIR, 'label_encoder.pkl')

# === KONFIGURASI PENYIMPANAN DETEKSI LOKAL ===
DETECTIONS_DIR = 'detections'
DETECTIONS_PHOTOS_DIR = os.path.join(DETECTIONS_DIR, 'photos')
DETECTIONS_LOG_FILE = os.path.join(DETECTIONS_DIR, 'detections.csv')

# Buat folder jika belum ada
os.makedirs(LOCAL_MODEL_DIR, exist_ok=True)
os.makedirs(DETECTIONS_PHOTOS_DIR, exist_ok=True)

app = Flask(__name__)
face_app = FaceAnalysis(
    name='buffalo_l',
    providers=['CUDAExecutionProvider', 'CPUExecutionProvider']
)
face_app.prepare(ctx_id=0)

cap = cv2.VideoCapture(0)
output_frame = None
lock = threading.Lock()
known_face_gallery = []
student_info = {}

clf = None
le = None
current_mode = 'masuk'  # Mode absensi saat ini (masuk/keluar), akan diupdate via polling

print("=" * 60)
print(f"✅ DEVICE ID      : {DEVICE_ID}")
print(f"✅ GET SISWA API  : {LARAVEL_STUDENTS_API_URL}")
print(f"✅ POST ABSENSI   : {LARAVEL_ATTENDANCE_API_URL}")
print(f"✅ LOCAL MODELS   : {LOCAL_MODEL_DIR}")
print("=" * 60)

# === SIMPAN & LOAD EMBEDDING LOCAL ===

def save_embeddings_local(embeddings, labels, student_info_dict):
    """Simpan embeddings, labels, dan info siswa ke file local"""
    try:
        # Simpan embeddings dan labels
        data = {
            'embeddings': embeddings,
            'labels': labels,
            'timestamp': datetime.now().isoformat()
        }
        with open(LOCAL_EMBEDDINGS_FILE, 'wb') as f:
            pickle.dump(data, f)
        
        # Simpan info siswa (nama, NIS, dll)
        with open(LOCAL_STUDENT_INFO_FILE, 'w', encoding='utf-8') as f:
            json.dump(student_info_dict, f, indent=2, ensure_ascii=False)
        
        print(f"💾 Embeddings berhasil disimpan ke local: {len(embeddings)} data")
        print(f"💾 Info {len(student_info_dict)} siswa disimpan ke local")
        return True
    except Exception as e:
        print(f"❌ Gagal simpan embeddings local: {e}")
        return False

def load_embeddings_local():
    """Load embeddings dan info siswa dari file local"""
    try:
        if not os.path.exists(LOCAL_EMBEDDINGS_FILE) or not os.path.exists(LOCAL_STUDENT_INFO_FILE):
            print("⚠️ File embeddings local tidak ditemukan")
            return None, None, None
        
        # Load embeddings dan labels
        with open(LOCAL_EMBEDDINGS_FILE, 'rb') as f:
            data = pickle.load(f)
        
        embeddings = data.get('embeddings', [])
        labels = data.get('labels', [])
        timestamp = data.get('timestamp', 'Unknown')
        
        # Load info siswa
        with open(LOCAL_STUDENT_INFO_FILE, 'r', encoding='utf-8') as f:
            student_info_dict = json.load(f)
        
        print(f"📂 Load embeddings dari local: {len(embeddings)} data")
        print(f"📂 Load info {len(student_info_dict)} siswa dari local")
        print(f"🕐 Timestamp: {timestamp}")
        
        return embeddings, labels, student_info_dict
    except Exception as e:
        print(f"❌ Gagal load embeddings local: {e}")
        return None, None, None

def save_model_local():
    """Simpan trained model (SVM classifier & label encoder) ke local"""
    try:
        if clf is not None:
            with open(LOCAL_CLASSIFIER_FILE, 'wb') as f:
                pickle.dump(clf, f)
        
        if le is not None:
            with open(LOCAL_LABEL_ENCODER_FILE, 'wb') as f:
                pickle.dump(le, f)
        
        print("💾 Model classifier & label encoder disimpan ke local")
        return True
    except Exception as e:
        print(f"❌ Gagal simpan model: {e}")
        return False

# === LOGGING DETEKSI LOKAL ===
def save_local_detection(student_id: str, name: str, prob: float, image: np.ndarray):
    """Simpan hasil deteksi lokal: crop wajah ke file dan catat CSV dengan timestamp.
    File foto: detections/photos/{student_id}_{YYYYmmdd_HHMMSS}.jpg
    Log CSV: detections/detections.csv (timestamp,id,name,prob,photo_path)
    """
    # Timestamp WIB
    wib = pytz.timezone('Asia/Jakarta')
    ts = datetime.now(wib)
    ts_str = ts.strftime('%Y%m%d_%H%M%S')
    filename = f"{student_id}_{ts_str}.jpg"
    photo_path = os.path.join(DETECTIONS_PHOTOS_DIR, filename)

    # Simpan foto jika image valid
    try:
        if image is not None and image.size > 0:
            cv2.imwrite(photo_path, image)
        else:
            photo_path = ''
    except Exception:
        photo_path = ''

    # Tulis CSV (append). Jika belum ada, tulis header
    header = ['timestamp', 'id_siswa', 'nama', 'prob', 'photo_path']
    write_header = not os.path.exists(DETECTIONS_LOG_FILE)
    with open(DETECTIONS_LOG_FILE, 'a', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        if write_header:
            writer.writerow(header)
        writer.writerow([
            ts.strftime('%Y-%m-%d %H:%M:%S'),
            student_id,
            name,
            f"{prob:.4f}",
            photo_path
        ])

def load_model_local():
    """Load trained model dari local"""
    global clf, le
    try:
        if os.path.exists(LOCAL_CLASSIFIER_FILE) and os.path.exists(LOCAL_LABEL_ENCODER_FILE):
            with open(LOCAL_CLASSIFIER_FILE, 'rb') as f:
                clf = pickle.load(f)
            
            with open(LOCAL_LABEL_ENCODER_FILE, 'rb') as f:
                le = pickle.load(f)
            
            print("📂 Model classifier & label encoder berhasil di-load dari local")
            return True
        else:
            print("⚠️ File model local tidak ditemukan")
            return False
    except Exception as e:
        print(f"❌ Gagal load model local: {e}")
        return False

# === LOAD GALERI & LATIH SVM ===

def load_face_gallery():
    global known_face_gallery, clf, le, student_info
    print("🔄 Memuat galeri wajah dari server Laravel...")

    try:
        headers = {"Accept": "application/json"}
        response = requests.get(LARAVEL_STUDENTS_API_URL, timeout=15, headers=headers)
        # Logging detail untuk debug
        print(f"📡 Request URL: {LARAVEL_STUDENTS_API_URL}")
        print(f"📥 Status Code: {response.status_code}")
        preview = response.text
        if isinstance(preview, str):
            print(f"📄 Response preview: {preview[:300]}")
        
        if response.status_code != 200:
            print(f"❌ Gagal mengambil data dari server. Status: {response.status_code}")
            print("� Mencoba load dari local storage...")
            
            # Load dari local jika server gagal
            embeddings, labels, student_info_dict = load_embeddings_local()
            if embeddings and labels and student_info_dict:
                student_info = student_info_dict
                
                # Load model jika ada
                if load_model_local():
                    known_face_gallery = [
                        {'id': label, 'embedding': emb} 
                        for emb, label in zip(embeddings, labels)
                    ]
                    print("✅ Berhasil load data dari local storage (OFFLINE MODE)")
                    return
                else:
                    # Train ulang dari embeddings local
                    if len(set(labels)) >= 2:
                        le = LabelEncoder()
                        y = le.fit_transform(labels)
                        clf = SVC(probability=True)
                        clf.fit(embeddings, y)
                        
                        known_face_gallery = [
                            {'id': label, 'embedding': emb} 
                            for emb, label in zip(embeddings, labels)
                        ]
                        
                        save_model_local()
                        print("✅ Model berhasil di-train dari embeddings local")
                        return
            
            print("❌ Tidak ada data local tersedia. Sistem tidak dapat berjalan.")
            return

        # Robust parsing: dukung {data: [...]} atau array langsung
        try:
            payload = response.json()
        except ValueError:
            print("❗ Response bukan JSON valid. Hentikan proses galeri.")
            return

        if isinstance(payload, dict):
            students_data = payload.get('data', payload.get('students', []))
        elif isinstance(payload, list):
            students_data = payload
        else:
            print(f"❗ Struktur JSON tidak dikenali: {type(payload)}")
            return
        
        embeddings, labels = [], []
        temp_gallery = []
        temp_info = {}

        for student in students_data:
            student_id = student.get('id')
            student_name = student.get('nama', student.get('name', 'Unknown'))
            student_nis = student.get('nis', '')
            
            # Simpan info siswa
            temp_info[str(student_id)] = {
                'nama': student_name,
                'nis': student_nis
            }
            
            for photo in student.get('fotos', []):
                photo_url = photo.get('url')
                if not photo_url:
                    continue
                
                # === FIX UTAMA: NORMALISASI URL ===
                if photo_url.startswith("/"):
                    photo_url = LARAVEL_BASE_URL.rstrip("/") + photo_url


                try:
                    img_response = requests.get(photo_url, stream=True, timeout=10)
                    if img_response.status_code == 200:
                        img_array = np.asarray(bytearray(img_response.content), dtype=np.uint8)
                        img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
                        faces = face_app.get(img)
                        if faces:
                            for f in faces:
                                embedding = f.embedding
                                embeddings.append(embedding)
                                labels.append(str(student_id))
                                temp_gallery.append({'id': student_id, 'embedding': embedding})
                            print(f"✅ Siswa ID {student_id} ({student_name}) - {len(faces)} embedding berhasil diproses.")
                    else:
                        print(f"⚠️ Gagal download foto siswa ID {student_id}")
                except Exception as e:
                    print(f"❌ Error proses foto siswa {student_id}: {e}")

        known_face_gallery = temp_gallery
        student_info = temp_info

        if embeddings and labels:
            # Simpan embeddings ke local
            save_embeddings_local(embeddings, labels, temp_info)
            
            if len(set(labels)) < 2:
                print("⚠️ Gagal melatih model: hanya ditemukan 1 kelas. Tambahkan lebih banyak siswa.")
                known_face_gallery = temp_gallery
                return

            le = LabelEncoder()
            y = le.fit_transform(labels)
            clf = SVC(probability=True)
            clf.fit(embeddings, y)
            
            # Simpan model ke local
            save_model_local()
            
            print(f"✅ Model SVC dilatih dengan {len(embeddings)} embedding (ONLINE MODE)")
            print(f"✅ Data {len(student_info)} siswa berhasil dimuat & disimpan ke local")
        else:
            print("⚠️ Tidak ada embedding yang berhasil dikumpulkan.")
    except requests.exceptions.RequestException as e:
        print(f"❌ Error koneksi ke server Laravel: {e}")
        print("🔄 Mencoba load dari local storage...")
        
        # Fallback ke local
        embeddings, labels, student_info_dict = load_embeddings_local()
        if embeddings and labels and student_info_dict:
            student_info = student_info_dict
            
            if load_model_local():
                known_face_gallery = [
                    {'id': label, 'embedding': emb} 
                    for emb, label in zip(embeddings, labels)
                ]
                print("✅ Berhasil load data dari local storage (OFFLINE MODE)")

# === DETEKSI DAN REKOGNISI WAJAH ===

def recognize_and_compare():
    global output_frame, lock
    last_seen = {}
    COOLDOWN_SECONDS = 30

    while True:
        if not cap.isOpened() or not known_face_gallery or clf is None or le is None:
            time.sleep(5)
            load_face_gallery()
            continue

        ret, frame = cap.read()
        if not ret:
            continue

        faces = face_app.get(frame)
        current_time = time.time()

        for face in faces:
            bbox = face.bbox.astype(int)
            embedding = face.embedding.reshape(1, -1)

            pred = clf.predict(embedding)
            prob = clf.predict_proba(embedding).max( )
            label = le.inverse_transform(pred)[0]

            if prob > 0.6:
                color = (0, 255, 0)
                
                # Ambil info siswa
                siswa = student_info.get(label, {})
                nama_siswa = siswa.get('nama', 'Unknown')
                nis_siswa = siswa.get('nis', '')
                
                # Format label dengan ID, NIS, dan Nama
                if nis_siswa:
                    label_text = f'ID:{label} | {nis_siswa} | {nama_siswa} ({prob:.2f})'
                else:
                    label_text = f'ID:{label} | {nama_siswa} ({prob:.2f})'

                if label not in last_seen or (current_time - last_seen[label]) > COOLDOWN_SECONDS:
                    print(f"✅ Siswa ID {label} - {nama_siswa} dikenali. Kirim absensi.")
                    # Simpan deteksi lokal (crop wajah + log CSV)
                    try:
                        face_img = frame[max(0,bbox[1]):max(0,bbox[3]), max(0,bbox[0]):max(0,bbox[2])]
                        save_local_detection(student_id=str(label), name=nama_siswa, prob=float(prob), image=face_img)
                    except Exception as e:
                        print(f"⚠️ Gagal menyimpan deteksi lokal: {e}")
                    threading.Thread(target=send_attendance, args=(label,)).start()
                    last_seen[label] = current_time
            else:
                color = (0, 0, 255)
                label_text = 'Tidak dikenal'

            # Gambar kotak
            cv2.rectangle(frame, (bbox[0], bbox[1]), (bbox[2], bbox[3]), color, 2)
            
            # Background untuk text agar lebih jelas
            (text_width, text_height), baseline = cv2.getTextSize(
                label_text, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2
            )
            cv2.rectangle(
                frame,
                (bbox[0], bbox[1] - text_height - 10),
                (bbox[0] + text_width, bbox[1]),
                color,
                -1  # Filled rectangle
            )
            
            # Text dengan warna hitam di atas background
            cv2.putText(
                frame, 
                label_text, 
                (bbox[0], bbox[1] - 5),
                cv2.FONT_HERSHEY_SIMPLEX, 
                0.6, 
                (0, 0, 0),  # Hitam agar kontras dengan background
                2
            )

        with lock:
            output_frame = frame.copy()

# === KIRIM ABSENSI ===

def poll_attendance_mode():
    """Polling mode absensi (masuk/keluar) dari Laravel secara periodik"""
    global current_mode
    while True:
        try:
            mode_url = f"{LARAVEL_BASE_URL}/api/devices/{DEVICE_ID}/mode"
            response = requests.get(mode_url, timeout=5)
            if response.status_code == 200:
                data = response.json()
                if data.get('success'):
                    new_mode = data.get('mode', 'masuk')
                    if new_mode != current_mode:
                        current_mode = new_mode
                        print(f"🔄 Mode absensi diubah: {current_mode.upper()}")
        except Exception as e:
            print(f"⚠️ Gagal polling mode: {e}")
        
        time.sleep(30)  # Poll setiap 30 detik

def send_attendance(student_id):
    try:
        # Set timezone ke WIB (Asia/Jakarta)
        wib = pytz.timezone('Asia/Jakarta')
        waktu_sekarang = datetime.now(wib)
        
        # Gunakan mode otomatis dari polling
        payload1 = {
            'id_siswa': int(student_id),
            'id_devices': int(DEVICE_ID),
            'type': current_mode  # Gunakan mode yang sudah di-polling
        }
        print(f"📤 Kirim absensi (id_devices): {payload1}")
        response = requests.post(LARAVEL_ATTENDANCE_API_URL, json=payload1, timeout=10)
        print(f"📬 Status: {response.status_code}, Body: {response.text}")

        # Jika backend meminta devices_id (422), coba ulang dengan key berbeda
        if response.status_code == 422 and 'devices_id' in response.text:
            payload2 = {
                'id_siswa': int(student_id),
                'devices_id': int(DEVICE_ID),
                'type': current_mode
            }
            print(f"🔁 Retry absensi (devices_id): {payload2}")
            response2 = requests.post(LARAVEL_ATTENDANCE_API_URL, json=payload2, timeout=10)
            print(f"📬 Status: {response2.status_code}, Body: {response2.text}")
    except Exception as e:
        print(f"❌ Gagal kirim absensi: {e}")

# === STREAMING FLASK ROUTE ===

def generate_frames():
    global output_frame, lock
    while True:
        with lock:
            if output_frame is None:
                continue
            (flag, encodedImage) = cv2.imencode(".jpg", output_frame)
            if not flag:
                continue
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + bytearray(encodedImage) + b'\r\n')

@app.route("/")
def index():
    students_list = "<br>".join([
        f"ID {sid}: {info['nama']} ({info['nis']})" 
        for sid, info in student_info.items()
    ])
    
    # Cek apakah ada data local (untuk indikator mode)
    has_local = os.path.exists(LOCAL_EMBEDDINGS_FILE)
    mode = "🟢 ONLINE" if has_local else "🔴 SETUP"
    
    return f"""
    <h1>Presensi AI Aktif - {mode}</h1>
    <p><strong>Device ID:</strong> {DEVICE_ID}</p>
    <p><strong>Galeri:</strong> {len(known_face_gallery)} wajah</p>
    <p><strong>Siswa Terdaftar:</strong> {len(student_info)} orang</p>
    <hr>
    <h3>Daftar Siswa:</h3>
    {students_list if students_list else "Tidak ada siswa"}
    <hr>
    <a href="/video_feed">Lihat Video Stream</a> | <a href="/reload">Reload Data dari Server</a>
    """

@app.route("/reload")
def reload_data():
    threading.Thread(target=load_face_gallery).start()
    return "Data sedang di-reload dari server... <a href='/'>Kembali</a>"

@app.route("/video_feed")
def video_feed():
    return Response(generate_frames(), mimetype="multipart/x-mixed-replace; boundary=frame")

@app.route("/ip")
def get_ip():
    return os.popen('hostname -I').read().split()[0]

# === MAIN ===

if __name__ == '__main__':
    load_face_gallery()
    threading.Thread(target=recognize_and_compare, daemon=True).start()
    threading.Thread(target=poll_attendance_mode, daemon=True).start()  # Start mode polling
    print("🚀 Mode polling started - akan otomatis sesuai jadwal")
    app.run(host='0.0.0.0', port=5000, debug=False)
    cap.release()