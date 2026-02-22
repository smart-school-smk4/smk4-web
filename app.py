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
import platform
import sys
import hashlib

# Deteksi platform dan import camera yang sesuai
IS_RASPBERRY_PI = platform.machine() in ['aarch64', 'armv7l', 'armv8']
if IS_RASPBERRY_PI:
    try:
        from picamera2 import Picamera2
        print("✅ Picamera2 detected for Raspberry Pi")
    except ImportError:
        print("⚠️ Picamera2 not installed. Install with: pip install picamera2")
        IS_RASPBERRY_PI = False

print("Available providers:", ort.get_available_providers())
print(f"Platform: {platform.machine()} | Raspberry Pi Mode: {IS_RASPBERRY_PI}")

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
LOCAL_TRAINED_PHOTOS_FILE = os.path.join(LOCAL_MODEL_DIR, 'trained_photos.json')  # Track processed photo IDs

# === KONFIGURASI PENYIMPANAN DETEKSI LOKAL ===
DETECTIONS_DIR = 'detections'
DETECTIONS_PHOTOS_DIR = os.path.join(DETECTIONS_DIR, 'photos')
DETECTIONS_LOG_FILE = os.path.join(DETECTIONS_DIR, 'detections.csv')

# === KONFIGURASI OPTIMASI UNTUK RASPBERRY PI ===
# Camera resolution: higher for better image quality
FRAME_WIDTH = 1280 if IS_RASPBERRY_PI else 640
FRAME_HEIGHT = 720 if IS_RASPBERRY_PI else 480
# Frame skip: higher value = smoother camera but less frequent detection
# RPi: Process every 4th frame (detects ~3-4 times per second @ 15fps)
# PC: Process every frame for maximum responsiveness
FRAME_SKIP = 4 if IS_RASPBERRY_PI else 1
# Detection size: kept at 640x640 for optimal face detection performance
DET_SIZE = (640, 640)  # Same for all platforms

# Buat folder jika belum ada
os.makedirs(LOCAL_MODEL_DIR, exist_ok=True)
os.makedirs(DETECTIONS_PHOTOS_DIR, exist_ok=True)

app = Flask(__name__)

# Inisialisasi FaceAnalysis dengan optimasi untuk platform
if IS_RASPBERRY_PI:
    # Raspberry Pi: gunakan CPU only dengan optimasi
    face_app = FaceAnalysis(
        name='buffalo_l',
        providers=['CPUExecutionProvider']
    )
    face_app.prepare(ctx_id=-1, det_size=DET_SIZE)  # CPU mode
else:
    # PC: gunakan CUDA jika tersedia
    face_app = FaceAnalysis(
        name='buffalo_l',
        providers=['CUDAExecutionProvider', 'CPUExecutionProvider']
    )
    face_app.prepare(ctx_id=0, det_size=DET_SIZE)

# === GLOBAL VARIABLES ===
# Thread-safe frame storage - SEPARATED buffers to prevent race conditions
# raw_frame: RGB format from camera (for InsightFace processing)
# display_frame: BGR format with drawings (for MJPEG streaming)
raw_frame = None
display_frame = None
raw_frame_lock = threading.Lock()  # Protects raw_frame access
display_frame_lock = threading.Lock()  # Protects display_frame access

# Camera instance - SINGLE GLOBAL INSTANCE to prevent resource conflicts
picam2 = None
cap = None
camera_running = False  # Flag to control camera thread lifecycle

# Face recognition variables
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
        
        # === NORMALIZE LOADED EMBEDDINGS (CRITICAL!) ===
        # Ensure all loaded embeddings are L2-normalized for consistency
        normalized_embeddings = []
        for emb in embeddings:
            emb_array = np.array(emb)
            norm = np.linalg.norm(emb_array)
            if norm > 0:
                normalized_embeddings.append(emb_array / norm)
            else:
                normalized_embeddings.append(emb_array)
        
        # Load info siswa
        with open(LOCAL_STUDENT_INFO_FILE, 'r', encoding='utf-8') as f:
            student_info_dict = json.load(f)
        
        print(f"📂 Load embeddings dari local: {len(normalized_embeddings)} data (NORMALIZED)")
        print(f"📂 Load info {len(student_info_dict)} siswa dari local")
        print(f"🕐 Timestamp: {timestamp}")
        
        return normalized_embeddings, labels, student_info_dict
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

def save_trained_photos_tracking(trained_photos: dict):
    """Simpan tracking foto yang sudah diproses ke local.
    
    Format: {student_id: [photo_id1, photo_id2, ...]}
    Digunakan untuk menghindari re-embedding foto yang sama.
    """
    try:
        with open(LOCAL_TRAINED_PHOTOS_FILE, 'w', encoding='utf-8') as f:
            json.dump(trained_photos, f, indent=2, ensure_ascii=False)
        print(f"💾 Tracking {sum(len(v) for v in trained_photos.values())} foto disimpan")
        return True
    except Exception as e:
        print(f"❌ Gagal simpan tracking foto: {e}")
        return False

def load_trained_photos_tracking() -> dict:
    """Load tracking foto yang sudah diproses dari local.
    
    Returns:
        dict: {student_id: [photo_id1, photo_id2, ...]} atau {} jika belum ada
    """
    try:
        if os.path.exists(LOCAL_TRAINED_PHOTOS_FILE):
            with open(LOCAL_TRAINED_PHOTOS_FILE, 'r', encoding='utf-8') as f:
                data = json.load(f)
            print(f"📂 Load tracking {sum(len(v) for v in data.values())} foto dari local")
            return data
        else:
            print("ℹ️ Belum ada tracking foto, mulai dari awal")
            return {}
    except Exception as e:
        print(f"❌ Gagal load tracking foto: {e}")
        return {}

# === LOAD GALERI & LATIH SVM (INCREMENTAL) ===

def load_face_gallery():
    """Load face gallery dengan dukungan incremental embedding.
    
    INCREMENTAL LOGIC:
    1. Load existing embeddings dan tracking dari local storage
    2. Fetch student data dari Laravel API
    3. Compare: photo_id dari API vs trained_photos tracking
    4. Only download & process NEW photos (yang belum di-track)
    5. Combine old embeddings + new embeddings
    6. Retrain SVM classifier dengan data gabungan
    7. Save updated embeddings, tracking, dan model
    
    PERFORMANCE BENEFIT:
    - Raspberry Pi: Drastis mengurangi waktu startup
    - Hanya proses foto baru, bukan semua foto setiap kali
    - Bandwidth optimal: skip download foto yang sudah ada
    """
    global known_face_gallery, clf, le, student_info
    print("🔄 Memuat galeri wajah (INCREMENTAL MODE)...")

    # === STEP 1: LOAD EXISTING DATA FROM LOCAL ===
    existing_embeddings, existing_labels, existing_student_info = load_embeddings_local()
    trained_photos = load_trained_photos_tracking()
    
    # Initialize containers for combined data
    if existing_embeddings and existing_labels:
        all_embeddings = list(existing_embeddings)  # Copy existing
        all_labels = list(existing_labels)
        print(f"📦 Loaded {len(all_embeddings)} existing embeddings from local")
    else:
        all_embeddings = []
        all_labels = []
        print("ℹ️ No existing embeddings found, starting fresh")
    
    if existing_student_info:
        student_info = existing_student_info.copy()
    else:
        student_info = {}

    try:
        # === STEP 2: FETCH STUDENT DATA FROM API ===
        headers = {"Accept": "application/json"}
        response = requests.get(LARAVEL_STUDENTS_API_URL, timeout=15, headers=headers)
        
        print(f"📡 Request URL: {LARAVEL_STUDENTS_API_URL}")
        print(f"📥 Status Code: {response.status_code}")
        
        if response.status_code != 200:
            print(f"❌ Gagal mengambil data dari server. Status: {response.status_code}")
            print("🔄 Menggunakan data local yang sudah ada (OFFLINE MODE)...")
            
            # Fallback: Use existing local data
            if all_embeddings and all_labels and student_info:
                if load_model_local():
                    known_face_gallery = [
                        {'id': label, 'embedding': emb} 
                        for emb, label in zip(all_embeddings, all_labels)
                    ]
                    print("✅ Berhasil load data dari local storage (OFFLINE MODE)")
                    return
                else:
                    # Build gallery from existing embeddings (no SVM needed for cosine similarity)
                    if len(all_embeddings) >= 1:
                        le = LabelEncoder()
                        y = le.fit_transform(all_labels)
                        clf = SVC(probability=True, kernel='linear', C=1.0)
                        clf.fit(all_embeddings, y)
                        
                        known_face_gallery = [
                            {'id': label, 'embedding': emb} 
                            for emb, label in zip(all_embeddings, all_labels)
                        ]
                        
                        save_model_local()
                        print("✅ Gallery rebuilt dari embeddings local")
                        return
            
            print("❌ Tidak ada data local tersedia. Sistem tidak dapat berjalan.")
            return

        # === STEP 3: PARSE API RESPONSE ===
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
        
        # === STEP 4: INCREMENTAL PROCESSING - ONLY NEW PHOTOS ===
        new_embeddings_count = 0
        skipped_photos_count = 0
        temp_gallery = []
        using_url_hash = False  # Track if we're using URL hash fallback

        for student in students_data:
            student_id = str(student.get('id'))
            # Fix: API mengirim 'nama_siswa' bukan 'nama', dan 'nisn' bukan 'nis'
            student_name = student.get('nama_siswa', student.get('nama', student.get('name', 'Unknown')))
            student_nis = student.get('nisn', student.get('nis', ''))
            
            # Update student info (always refresh metadata)
            student_info[student_id] = {
                'nama': student_name,
                'nis': student_nis
            }
            
            # Get list of already-trained photo IDs for this student
            already_trained = set(trained_photos.get(student_id, []))
            
            for photo in student.get('fotos', []):
                photo_id = photo.get('id')  # Try to get photo ID from API
                photo_url = photo.get('url')
                
                if not photo_url:
                    continue
                
                # Normalize URL first
                if photo_url.startswith("/"):
                    photo_url = LARAVEL_BASE_URL.rstrip("/") + photo_url
                
                # === FALLBACK: Use URL hash as ID if API doesn't provide photo_id ===
                if not photo_id:
                    # Generate unique identifier from URL (MD5 hash)
                    photo_id = hashlib.md5(photo_url.encode()).hexdigest()[:16]
                    using_url_hash = True
                else:
                    # Convert to string for consistent tracking
                    photo_id = str(photo_id)
                
                # === INCREMENTAL CHECK: Skip if already processed ===
                if photo_id in already_trained:
                    skipped_photos_count += 1
                    continue  # Skip this photo, already embedded

                # === PROCESS NEW PHOTO ===
                try:
                    img_response = requests.get(photo_url, stream=True, timeout=10)
                    if img_response.status_code == 200:
                        img_array = np.asarray(bytearray(img_response.content), dtype=np.uint8)
                        img = cv2.imdecode(img_array, cv2.IMREAD_COLOR)
                        faces = face_app.get(img)
                        
                        if faces:
                            for f in faces:
                                embedding = f.embedding
                                
                                # === NORMALIZE EMBEDDING (CRITICAL!) ===
                                # L2 normalization ensures consistency with recognition phase
                                embedding_normalized = embedding / np.linalg.norm(embedding)
                                
                                all_embeddings.append(embedding_normalized)  # Add normalized embedding
                                all_labels.append(student_id)
                                temp_gallery.append({'id': student_id, 'embedding': embedding_normalized})
                                new_embeddings_count += 1
                            
                            # Track this photo as processed (photo_id is always available now)
                            if student_id not in trained_photos:
                                trained_photos[student_id] = []
                            trained_photos[student_id].append(photo_id)
                            
                            # Show whether using API ID or URL hash
                            id_type = "ID" if photo.get('id') else "hash"
                            print(f"✅ NEW: Siswa ID {student_id} ({student_name}) - photo {photo_id[:8]}... ({id_type}) - {len(faces)} embedding")
                        else:
                            print(f"⚠️ No face detected in photo {photo_id} for student {student_id}")
                    else:
                        print(f"⚠️ Gagal download foto {photo_id} siswa ID {student_id}")
                except Exception as e:
                    print(f"❌ Error proses foto {photo_id} siswa {student_id}: {e}")

        # === STEP 5: SUMMARY & STATS ===
        print("="*60)
        print(f"📊 INCREMENTAL EMBEDDING SUMMARY:")
        print(f"   ✅ New embeddings processed: {new_embeddings_count}")
        print(f"   ⏭️  Photos skipped (already trained): {skipped_photos_count}")
        print(f"   📦 Total embeddings now: {len(all_embeddings)}")
        print(f"   👥 Total students: {len(student_info)}")
        total_tracked = sum(len(v) for v in trained_photos.values())
        print(f"   🔖 Total photos tracked: {total_tracked}")
        if using_url_hash:
            print(f"   ⚠️  Using URL hash for tracking (API tidak mengirim photo ID)")
        print("="*60)

        # === STEP 6: UPDATE GLOBAL STATE ===
        # Rebuild gallery from ALL embeddings (old + new)
        known_face_gallery = [
            {'id': label, 'embedding': emb} 
            for emb, label in zip(all_embeddings, all_labels)
        ]

        # === STEP 7: SAVE EMBEDDINGS (NO SVM NEEDED - USE COSINE SIMILARITY) ===
        if all_embeddings and all_labels:
            # Save combined embeddings to local
            save_embeddings_local(all_embeddings, all_labels, student_info)
            save_trained_photos_tracking(trained_photos)  # Save tracking
            
            if len(set(all_labels)) < 1:
                print("⚠️ Tidak ada siswa ditemukan. Tambahkan siswa.")
                return

            # Keep SVM for backward compatibility, but we'll use cosine similarity in recognition
            le = LabelEncoder()
            y = le.fit_transform(all_labels)
            clf = SVC(probability=True, kernel='linear', C=1.0)  # Linear kernel better for normalized embeddings
            clf.fit(all_embeddings, y)
            
            # Save model to local (for fallback)
            save_model_local()
            
            mode = "INCREMENTAL UPDATE" if new_embeddings_count > 0 else "NO NEW DATA"
            print(f"✅ Embeddings loaded: {len(all_embeddings)} total ({mode})")
            print(f"✅ Ready for COSINE SIMILARITY matching (ArcFace standard)")
            print(f"✅ Data {len(student_info)} siswa berhasil dimuat & disimpan ke local")
        else:
            print("⚠️ Tidak ada embedding yang berhasil dikumpulkan.")
            
    except requests.exceptions.RequestException as e:
        print(f"❌ Error koneksi ke server Laravel: {e}")
        print("🔄 Mencoba load dari local storage...")
        
        # Fallback to local
        if all_embeddings and all_labels and student_info:
            if load_model_local():
                known_face_gallery = [
                    {'id': label, 'embedding': emb} 
                    for emb, label in zip(all_embeddings, all_labels)
                ]
                print("✅ Berhasil load data dari local storage (OFFLINE MODE)")
            else:
                # Build gallery from existing embeddings
                if len(all_embeddings) >= 1:
                    le = LabelEncoder()
                    y = le.fit_transform(all_labels)
                    clf = SVC(probability=True, kernel='linear', C=1.0)
                    clf.fit(all_embeddings, y)
                    
                    known_face_gallery = [
                        {'id': label, 'embedding': emb} 
                        for emb, label in zip(all_embeddings, all_labels)
                    ]
                    
                    save_model_local()
                    print("✅ Model retrained dari embeddings local")

# === OPTIMIZED CAMERA CAPTURE THREAD ===
# This runs in the background continuously capturing frames
# Key optimization: Decouples camera I/O from Flask request handling

def initialize_camera():
    """Initialize camera based on platform - SINGLE INSTANCE ONLY"""
    global picam2, cap, camera_running
    
    if IS_RASPBERRY_PI:
        try:
            # Raspberry Pi: Use Picamera2 with optimized settings
            picam2 = Picamera2()
            camera_config = picam2.create_preview_configuration(
                main={"size": (FRAME_WIDTH, FRAME_HEIGHT), "format": "RGB888"},
                controls={"FrameRate": 15}  # 15 FPS is stable for RPi5
            )
            picam2.configure(camera_config)
            picam2.start()
            camera_running = True
            print(f"📷 Picamera2 initialized: {FRAME_WIDTH}x{FRAME_HEIGHT} @ 15fps")
            return True
        except Exception as e:
            print(f"❌ Failed to initialize Picamera2: {e}")
            return False
    else:
        try:
            # PC/Laptop: Use USB camera via OpenCV
            cap = cv2.VideoCapture(0)
            if not cap.isOpened():
                print("❌ Cannot open USB camera")
                return False
            
            # Set camera properties explicitly for consistency
            cap.set(cv2.CAP_PROP_FRAME_WIDTH, FRAME_WIDTH)
            cap.set(cv2.CAP_PROP_FRAME_HEIGHT, FRAME_HEIGHT)
            cap.set(cv2.CAP_PROP_FPS, 30)
            cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)  # Minimize buffer lag
            
            camera_running = True
            print(f"📷 USB Camera initialized: {FRAME_WIDTH}x{FRAME_HEIGHT}")
            return True
        except Exception as e:
            print(f"❌ Failed to initialize USB camera: {e}")
            return False


def capture_frames_background():
    """
    Background thread that continuously reads from camera.
    
    WHY THIS IMPROVES PERFORMANCE:
    1. Runs independently - doesn't block Flask HTTP requests
    2. Always grabs the latest frame (no stale buffer buildup)
    3. Uses dedicated lock to prevent race conditions
    4. Auto-reconnects if camera disconnects
    
    COLOR PIPELINE RESPONSIBILITY:
    - Picamera2 outputs RGB format (R, G, B channel order)
    - This thread stores frames in RGB format to raw_frame (NO conversion)
    - InsightFace expects RGB input (correct format here)
    - RGB→BGR conversion happens in face recognition thread
    """
    global raw_frame, raw_frame_lock, display_frame, display_frame_lock, camera_running
    
    print("🎬 Camera capture thread started")
    reconnect_attempts = 0
    first_frame_captured = False
    
    while camera_running:
        try:
            if IS_RASPBERRY_PI:
                if picam2 is None:
                    print("⚠️ Picamera2 not initialized, attempting reconnect...")
                    if initialize_camera():
                        reconnect_attempts = 0
                    else:
                        reconnect_attempts += 1
                        time.sleep(5)
                        continue
                
                # Capture frame from Picamera2 (Arducam CSI)
                frame = picam2.capture_array()
                
                if frame is None or frame.size == 0:
                    print("⚠️ Picamera2 returned empty frame")
                    time.sleep(0.1)
                    continue
                
                # === FIX: ARDUCAM CSI OUTPUTS BGR, NOT RGB ===
                # Despite "RGB888" format setting, Arducam CSI actually outputs BGR
                # Convert BGR to RGB for InsightFace compatibility
                frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
                
                # === STORE RGB TO RAW_FRAME ===
                # InsightFace requires RGB input for accurate face detection
                # BGR conversion happens in face recognition thread for drawing
                with raw_frame_lock:
                    raw_frame = frame_rgb.copy()
                
                # === ALSO UPDATE DISPLAY_FRAME FOR IMMEDIATE STREAMING ===
                # Convert to BGR for display (even before face detection)
                # This ensures video stream works immediately
                frame_bgr = cv2.cvtColor(frame_rgb, cv2.COLOR_RGB2BGR)
                with display_frame_lock:
                    display_frame = frame_bgr.copy()
                
                if not first_frame_captured:
                    print(f"✅ First frame captured (RPi): shape={frame.shape}, dtype={frame.dtype}")
                    first_frame_captured = True
                    reconnect_attempts = 0
                
            else:
                if cap is None or not cap.isOpened():
                    print("⚠️ USB camera disconnected, attempting reconnect...")
                    if initialize_camera():
                        reconnect_attempts = 0
                    else:
                        reconnect_attempts += 1
                        time.sleep(5)
                        continue
                
                # Read frame from USB camera (BGR format)
                ret, bgr_frame = cap.read()
                if not ret:
                    print("⚠️ Failed to read frame from camera")
                    reconnect_attempts += 1
                    if reconnect_attempts > 10:
                        print("❌ Too many failed attempts, reinitializing camera...")
                        cap.release()
                        time.sleep(2)
                        initialize_camera()
                        reconnect_attempts = 0
                    time.sleep(0.1)
                    continue
                
                if bgr_frame is None or bgr_frame.size == 0:
                    print("⚠️ USB camera returned empty frame")
                    time.sleep(0.1)
                    continue
                
                # === CONVERT BGR→RGB FOR USB CAMERA ===
                # USB cameras output BGR, but we need RGB for InsightFace
                # Store RGB to raw_frame (consistent with Picamera2 path)
                frame_rgb = cv2.cvtColor(bgr_frame, cv2.COLOR_BGR2RGB)
                
                with raw_frame_lock:
                    raw_frame = frame_rgb.copy()
                
                # === ALSO UPDATE DISPLAY_FRAME FOR IMMEDIATE STREAMING ===
                # Already in BGR format, just copy
                with display_frame_lock:
                    display_frame = bgr_frame.copy()
                
                if not first_frame_captured:
                    print(f"✅ First frame captured (USB): shape={bgr_frame.shape}, dtype={bgr_frame.dtype}")
                    first_frame_captured = True
                
                reconnect_attempts = 0
            
            # Small delay to prevent CPU saturation
            # Raspberry Pi: ~15 FPS = 66ms per frame
            time.sleep(0.033 if IS_RASPBERRY_PI else 0.016)
            
        except Exception as e:
            print(f"❌ Error in camera capture: {e}")
            time.sleep(1)


# === FACE DETECTION & RECOGNITION (PROCESSING THREAD) ===
# This thread runs face detection on captured frames

def recognize_and_compare():
    """
    Face recognition processing thread.
    
    OPTIMIZATION NOTES:
    1. Works on already-captured frames (non-blocking)
    2. Frame skipping reduces CPU load on Raspberry Pi
    3. Only processes when model is ready
    4. Attendance cooldown prevents spam
    
    COLOR PIPELINE RESPONSIBILITY:
    - Receives RGB frames from raw_frame buffer
    - Passes RGB to InsightFace (correct format for face detection)
    - Converts RGB→BGR AFTER face recognition
    - Performs OpenCV drawing on BGR frame
    - Stores BGR frame to display_frame (for MJPEG streaming)
    """
    global raw_frame, raw_frame_lock, display_frame, display_frame_lock
    last_seen = {}
    COOLDOWN_SECONDS = 30
    frame_count = 0

    print("🔍 Face recognition thread started")
    frames_processed = 0
    
    while camera_running:
        # Wait for classifier to be ready (SVC needs clf and le)
        if clf is None or le is None:
            print("⏳ Waiting for classifier to load...")
            time.sleep(5)
            continue

        # Get current RGB frame safely from raw_frame buffer
        with raw_frame_lock:
            if raw_frame is None:
                if frames_processed == 0:
                    print("⚠️ Waiting for first frame from camera...")
                time.sleep(0.1)
                continue
            # Work on a copy to avoid blocking camera thread
            rgb_frame = raw_frame.copy()
        
        if frames_processed == 0:
            print(f"✅ Face recognition started processing frames (shape={rgb_frame.shape})")

        # Frame skipping optimization - process every Nth frame only
        # Reduces CPU usage significantly on Raspberry Pi
        frame_count += 1
        if frame_count % FRAME_SKIP != 0:
            # Even if we skip face detection, update display_frame for smooth streaming
            # (camera thread already updates it, so this is just a fallback)
            time.sleep(0.01)
            continue
        
        frames_processed += 1

        # === PASS RGB TO INSIGHTFACE (CRITICAL!) ===
        # InsightFace (ArcFace) expects RGB input for accurate face detection
        # DO NOT pass BGR here - it will cause incorrect embeddings
        faces = face_app.get(rgb_frame)
        current_time = time.time()
        
        # === CONVERT RGB→BGR FOR OPENCV DRAWING ===
        # After face recognition, convert to BGR for OpenCV operations
        # OpenCV's drawing functions (rectangle, putText) expect BGR format
        bgr_frame = cv2.cvtColor(rgb_frame, cv2.COLOR_RGB2BGR)

        for face in faces:
            bbox = face.bbox.astype(int)
            
            # Extract embedding and apply L2 normalization (same as training!)
            embedding = face.embedding
            embedding_normalized = embedding / np.linalg.norm(embedding)

            # === USE SVC FOR MATCHING (MACHINE LEARNING APPROACH) ===
            # SVC uses hyperplane separation in the embedding space
            if clf is not None and le is not None:
                try:
                    # Predict using SVC
                    proba = clf.predict_proba([embedding_normalized])[0]
                    best_idx = np.argmax(proba)
                    prob = float(proba[best_idx])
                    label = le.inverse_transform([best_idx])[0]
                except Exception as e:
                    print(f"❌ Error SVC prediction: {e}")
                    prob = 0.0
                    label = None
            else:
                prob = 0.0
                label = None

            # Threshold for SVC probability: typically 0.5-0.7 is good
            # I'll use 0.5 for a balance between accuracy and false positives
            if prob > 0.5 and label is not None:
                color = (0, 255, 0)
                
                # Get student info
                siswa = student_info.get(label, {})
                nama_siswa = siswa.get('nama', 'Unknown')
                nis_siswa = siswa.get('nis', '')
                
                # Format label text
                if nis_siswa:
                    label_text = f'ID:{label} | {nis_siswa} | {nama_siswa} ({prob:.2f})'
                else:
                    label_text = f'ID:{label} | {nama_siswa} ({prob:.2f})'

                # Cooldown check - prevent duplicate attendance within 30s
                if label not in last_seen or (current_time - last_seen[label]) > COOLDOWN_SECONDS:
                    print(f"✅ Siswa ID {label} - {nama_siswa} dikenali. Kirim absensi.")
                    
                    # Extract crop wajah untuk dikirim ke website (RGB->BGR conversion)
                    foto_wajah_bgr = None
                    try:
                        face_img_rgb = rgb_frame[max(0,bbox[1]):max(0,bbox[3]), max(0,bbox[0]):max(0,bbox[2])]
                        foto_wajah_bgr = cv2.cvtColor(face_img_rgb, cv2.COLOR_RGB2BGR)
                    except Exception as e:
                        print(f"⚠️ Gagal crop foto wajah: {e}")
                    
                    # Save local detection (face crop + CSV log) - OPTIONAL untuk logging
                    try:
                        save_local_detection(student_id=str(label), name=nama_siswa, prob=float(prob), image=foto_wajah_bgr)
                    except Exception as e:
                        print(f"⚠️ Gagal menyimpan deteksi lokal: {e}")
                    
                    # Send attendance + foto wajah ke website (non-blocking)
                    threading.Thread(
                        target=send_attendance, 
                        args=(label, foto_wajah_bgr), 
                        daemon=True
                    ).start()
                    last_seen[label] = current_time
            else:
                color = (0, 0, 255)
                label_text = 'Tidak dikenal'

            # === DRAW ON BGR FRAME (OpenCV functions require BGR) ===
            # All drawing operations use BGR color space
            cv2.rectangle(bgr_frame, (bbox[0], bbox[1]), (bbox[2], bbox[3]), color, 2)
            
            # Larger text for higher resolution (1280x720)
            # Font scale increased to match larger camera resolution
            font_scale = 0.8 if IS_RASPBERRY_PI else 0.6
            thickness = 2 if IS_RASPBERRY_PI else 2
            
            (text_width, text_height), baseline = cv2.getTextSize(
                label_text, cv2.FONT_HERSHEY_SIMPLEX, font_scale, thickness
            )
            
            # Draw text background
            cv2.rectangle(
                bgr_frame,
                (bbox[0], bbox[1] - text_height - 10),
                (bbox[0] + text_width, bbox[1]),
                color,
                -1  # Filled
            )
            
            # Draw text
            cv2.putText(
                bgr_frame, 
                label_text, 
                (bbox[0], bbox[1] - 5),
                cv2.FONT_HERSHEY_SIMPLEX, 
                font_scale, 
                (0, 0, 0),  # Black text for contrast
                thickness
            )

        # === STORE BGR FRAME TO DISPLAY_FRAME FOR MJPEG STREAMING ===
        # Update display buffer with BGR frame (OpenCV imencode expects BGR)
        with display_frame_lock:
            display_frame = bgr_frame.copy()

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

def send_attendance(student_id, foto_wajah_bgr=None):
    """Kirim data absensi beserta foto crop wajah ke Laravel API
    
    Args:
        student_id: ID siswa yang terdeteksi
        foto_wajah_bgr: Numpy array foto crop wajah dalam format BGR (optional)
    """
    try:
        # Set timezone ke WIB (Asia/Jakarta)
        wib = pytz.timezone('Asia/Jakarta')
        waktu_sekarang = datetime.now(wib)
        
        # Encode foto wajah ke base64 jika ada
        foto_base64 = None
        if foto_wajah_bgr is not None and foto_wajah_bgr.size > 0:
            try:
                # Encode foto ke JPEG dengan quality 90
                encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), 90]
                (success, encoded_image) = cv2.imencode('.jpg', foto_wajah_bgr, encode_param)
                if success:
                    # Convert ke base64 string
                    import base64
                    foto_base64 = base64.b64encode(encoded_image).decode('utf-8')
                    print(f"📸 Foto wajah berhasil di-encode (size: {len(foto_base64)} chars)")
            except Exception as e:
                print(f"⚠️ Gagal encode foto wajah: {e}")
        
        # Gunakan mode otomatis dari polling
        payload1 = {
            'id_siswa': int(student_id),
            'id_devices': int(DEVICE_ID),
            'type': current_mode,  # Gunakan mode yang sudah di-polling
            'foto_wajah': foto_base64  # Tambahkan foto wajah (base64 string atau None)
        }
        print(f"📤 Kirim absensi (id_devices): id_siswa={student_id}, type={current_mode}, foto={'✅' if foto_base64 else '❌'}")
        response = requests.post(LARAVEL_ATTENDANCE_API_URL, json=payload1, timeout=10)
        print(f"📬 Status: {response.status_code}, Body: {response.text[:200]}...")  # Truncate untuk log

        # Jika backend meminta devices_id (422), coba ulang dengan key berbeda
        if response.status_code == 422 and 'devices_id' in response.text:
            payload2 = {
                'id_siswa': int(student_id),
                'devices_id': int(DEVICE_ID),
                'type': current_mode,
                'foto_wajah': foto_base64
            }
            print(f"🔁 Retry absensi (devices_id): id_siswa={student_id}")
            response2 = requests.post(LARAVEL_ATTENDANCE_API_URL, json=payload2, timeout=10)
            print(f"📬 Status: {response2.status_code}, Body: {response2.text[:200]}...")
    except Exception as e:
        print(f"❌ Gagal kirim absensi: {e}")

# === OPTIMIZED FLASK STREAMING ROUTE ===

def generate_frames():
    """
    Generator function for MJPEG streaming.
    
    PERFORMANCE OPTIMIZATIONS:
    1. Non-blocking: Only reads the latest available frame
    2. No camera I/O here - just encodes already-processed frame
    3. Thread-safe access using dedicated display_frame_lock
    4. Proper MJPEG multipart boundaries for browser compatibility
    5. JPEG encoding happens here (not in capture/recognition threads)
    
    COLOR PIPELINE RESPONSIBILITY:
    - Receives BGR frames from display_frame buffer (recognition thread output)
    - BGR is the correct format for cv2.imencode()
    - No color conversion needed here
    - MJPEG stream will display correct natural colors
    """
    global display_frame, display_frame_lock
    
    frames_streamed = 0
    first_frame_sent = False
    
    while True:
        # Thread-safe frame access from display buffer
        with display_frame_lock:
            if display_frame is None:
                # No processed frame available yet, wait briefly
                if frames_streamed == 0:
                    print("⏳ generate_frames() waiting for display_frame...")
                time.sleep(0.05)
                continue
            
            # Work on a copy to minimize lock time
            frame_to_encode = display_frame.copy()
        
        if not first_frame_sent:
            print(f"✅ First frame ready for streaming (shape={frame_to_encode.shape})")
            first_frame_sent = True
        
        # === ENCODE BGR FRAME AS JPEG (OUTSIDE LOCK) ===
        # cv2.imencode expects BGR format (correct format from recognition thread)
        # Quality 85 is a good balance for Raspberry Pi (lower = faster, but lower quality)
        encode_param = [int(cv2.IMWRITE_JPEG_QUALITY), 85 if IS_RASPBERRY_PI else 90]
        (flag, encodedImage) = cv2.imencode(".jpg", frame_to_encode, encode_param)
        
        if not flag:
            time.sleep(0.05)
            continue
        
        # Yield MJPEG frame with proper multipart boundary
        # This is the standard format browsers expect for streaming
        frames_streamed += 1
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
    """
    Streaming endpoint - returns MJPEG stream.
    
    CRITICAL: This route is NON-BLOCKING
    - Does not access camera directly
    - Does not perform face detection
    - Only streams pre-captured, pre-processed frames
    - Multiple clients can connect without performance degradation
    """
    return Response(
        generate_frames(),
        mimetype="multipart/x-mixed-replace; boundary=frame",
        headers={
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
    )

@app.route("/ip")
def get_ip():
    return os.popen('hostname -I').read().split()[0]

# === GRACEFUL SHUTDOWN HANDLING ===

def cleanup_resources():
    """
    Release camera and cleanup resources.
    Called on SIGINT (Ctrl+C) or application exit.
    """
    global camera_running, picam2, cap
    
    print("\n🛑 Shutting down gracefully...")
    camera_running = False
    
    time.sleep(1)  # Allow threads to finish current iteration
    
    if IS_RASPBERRY_PI and picam2 is not None:
        try:
            picam2.stop()
            picam2.close()
            print("📷 Picamera2 stopped and released")
        except Exception as e:
            print(f"⚠️ Error stopping Picamera2: {e}")
    elif cap is not None:
        try:
            cap.release()
            print("📷 USB Camera released")
        except Exception as e:
            print(f"⚠️ Error releasing camera: {e}")
    
    print("✅ Cleanup complete")


# === MAIN APPLICATION ENTRY POINT ===

if __name__ == '__main__':
    import signal
    
    # Register signal handler for graceful shutdown
    def signal_handler(sig, frame):
        cleanup_resources()
        sys.exit(0)
    
    signal.signal(signal.SIGINT, signal_handler)
    
    try:
        # Step 1: Initialize camera FIRST
        print("=" * 60)
        print("🚀 Starting Face Recognition System")
        print("=" * 60)
        
        if not initialize_camera():
            print("❌ CRITICAL: Cannot start without camera. Exiting.")
            sys.exit(1)
        
        # Step 2: Load face gallery and train model
        load_face_gallery()
        
        # Step 3: Start background threads
        # Camera capture thread - continuously grabs frames
        threading.Thread(
            target=capture_frames_background,
            daemon=True,
            name="CameraThread"
        ).start()
        
        # Face recognition thread - processes frames
        threading.Thread(
            target=recognize_and_compare,
            daemon=True,
            name="RecognitionThread"
        ).start()
        
        # Attendance mode polling thread
        threading.Thread(
            target=poll_attendance_mode,
            daemon=True,
            name="ModePollThread"
        ).start()
        
        print("=" * 60)
        print("✅ All background threads started successfully")
        print(f"📐 Camera Resolution: {FRAME_WIDTH}x{FRAME_HEIGHT}")
        print(f"🎯 Frame processing: Every {FRAME_SKIP} frame(s)")
        print(f"🔍 Face Detection size: {DET_SIZE}")
        print(f"🌐 Access video at: http://<raspberry-pi-ip>:5000/video_feed")
        if IS_RASPBERRY_PI:
            print(f"📷 Arducam CSI: BGR->RGB conversion enabled")
        print("=" * 60)
        
        # Step 4: Start Flask server
        # threaded=True allows handling multiple requests concurrently
        # debug=False is CRITICAL for production (debug mode causes issues)
        app.run(
            host='0.0.0.0',
            port=5000,
            debug=False,
            threaded=True,  # Enable concurrent request handling
            use_reloader=False  # Disable reloader (causes double initialization)
        )
        
    except KeyboardInterrupt:
        print("\n⚠️ Keyboard interrupt received")
    except Exception as e:
        print(f"❌ Fatal error: {e}")
    finally:
        cleanup_resources()