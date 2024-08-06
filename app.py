from flask import Flask, render_template, Response, request, jsonify, redirect, session, url_for
import cv2
import time
import os
import mysql.connector
from ultralytics import YOLO
from threading import Thread, Lock
import shutil

app = Flask(__name__)
app.secret_key = 'sas-2022-2024'

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smarthomesurveillance"
    )

# Load the custom YOLOv8 models for fire and drowsiness detection
fire_model = YOLO('fire/train4/weights/best.pt')
drowsiness_model = YOLO('kantuk/weights/best.pt')

# Initialize global variables
cap = None
selected_camera = None
is_recording = False
video_writer = None
output_directory = 'video'
backup_directory = 'C:/xampp_personal/htdocs/SHS_web/video'
lock = Lock()

if not os.path.exists(output_directory):
    os.makedirs(output_directory)

if not os.path.exists(backup_directory):
    os.makedirs(backup_directory)

def list_cameras():
    index = 0
    cameras = []

    # List local device cameras
    while True:
        temp_cap = cv2.VideoCapture(index)
        if not temp_cap.read()[0]:
            break
        else:
            cameras.append(f"Device Camera {index}")
        temp_cap.release()
        index += 1

    # List RTSP cameras from database
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT id_rtsp, kameraRTSP FROM kamera_rtsp")
        rtsp_cameras = cursor.fetchall()
        for cam in rtsp_cameras:
            cameras.append(f"RTSP Camera {cam[0]}: {cam[1]}")
        cursor.close()
        conn.close()
    except mysql.connector.Error as err:
        print(f"Error: {err}")

    return cameras

def start_camera(camera_index):
    global cap
    with lock:
        if cap is not None:
            cap.release()
            cap = None

        if camera_index.startswith('Device Camera'):
            device_index = int(camera_index.split()[-1])
            cap = cv2.VideoCapture(device_index)
        elif camera_index.startswith('RTSP Camera'):
            rtsp_id = int(camera_index.split()[2].strip(':'))
            try:
                conn = get_db_connection()
                cursor = conn.cursor()
                cursor.execute("SELECT kameraRTSP FROM kamera_rtsp WHERE id_rtsp = %s", (rtsp_id,))
                rtsp_url = cursor.fetchone()[0]
                cursor.close()
                conn.close()
                cap = cv2.VideoCapture(rtsp_url)
            except mysql.connector.Error as err:
                print(f"Error: {err}")
                return

        if cap is None or not cap.isOpened():
            print(f"Error: Cannot open camera {camera_index}")
            cap = None  # Set cap to None if it failed to open

def insert_data(kameraRTSP):
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = "INSERT INTO kamera_rtsp (kameraRTSP) VALUES (%s)"
    val = (kameraRTSP,)
    cursor.execute(sql, val)
    conn.commit()
    cursor.close()

def buzzer(status):
    conn = get_db_connection()
    cursor = conn.cursor()
    sql = ("UPDATE buzzer set status=%s WHERE id_buzzer=1")
    val = (status,)
    cursor.execute(sql, val)
    conn.commit()
    cursor.close()
    conn.close()

def buzzer_status():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT status FROM buzzer LIMIT 1")
    result = cursor.fetchone()[0]
    conn.close()
    return result

def buzzer_control():
    while True:
        status = buzzer_status()
        if status == 'Hidup':
            print("Buzzer ON")
            # Code On Buzzer
        else:
            print("Buzzer OFF")
            # Code Off Buzzer
        time.sleep(1)

def stop_camera():
    global cap
    with lock:
        if cap is not None:
            cap.release()
            cap = None

def insert_notification(message):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        timestamp = time.strftime('%Y-%m-%d %H:%M:%S')
        cursor.execute("INSERT INTO notifications (message, timestamp) VALUES (%s, %s)", (message, timestamp))
        conn.commit()
        cursor.close()
        conn.close()
        print(f"Inserted notification: {message} at {timestamp}")
    except mysql.connector.Error as err:
        print(f"Error: {err}")

def insert_video(namaVideo):
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("INSERT INTO video (video) VALUES (%s)", (namaVideo,))
        conn.commit()
        cursor.close()
        conn.close()
        print(f"Inserted video: {namaVideo}")
    except mysql.connector.Error as err:
        print(f"Error: {err}")

@app.route('/')
def index():
    message = session.pop('message', None)
    success = session.pop('success', None)
    return render_template('beranda.php', message=message, success=success)

@app.route('/list_cameras', methods=['GET'])
def get_cameras():
    cameras = list_cameras()
    return jsonify(cameras)

@app.route('/video_feed')
def video_feed():
    global cap
    if cap is None or not cap.isOpened():
        return 'Camera not selected or failed to open', 400
    return Response(gen_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

def gen_frames():
    global cap, is_recording, video_writer

    fire_detected_time = 0
    drowsiness_detected_time = 0
    fire_detection_start = None
    drowsiness_detection_start = None

    while True:
        with lock:
            if cap is None or not cap.isOpened():
                break
            ret, frame = cap.read()

        if not ret:
            print("Failed to read frame from webcam")
            break

        frame = cv2.resize(frame, (1280, 720))

        # Fire detection
        fire_results = fire_model(frame)
        fire_detected = False
        for result in fire_results:
            for box in result.boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                label = fire_model.names[int(box.cls[0])]
                confidence = box.conf[0]
                if label == 'fire':
                    fire_detected = True
                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 0, 255), 2)
                    cv2.putText(frame, f'{label} {confidence:.2f}', (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 0, 255), 2)

        if fire_detected:
            if fire_detection_start is None:
                fire_detection_start = time.time()
            fire_detected_time = time.time() - fire_detection_start
            if fire_detected_time >= 3:
                insert_notification('Terdeteksi Api')
                buzzer('Hidup')
                fire_detection_start = None
                fire_detected_time = 0
        else:
            fire_detection_start = None
            fire_detected_time = 0

        # Drowsiness detection
        drowsiness_results = drowsiness_model(frame)
        drowsiness_detected = False
        for result in drowsiness_results:
            for box in result.boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                label = drowsiness_model.names[int(box.cls[0])]
                confidence = box.conf[0]
                if label == 'drowsy':
                    drowsiness_detected = True
                    color = (0, 255, 255)
                    cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)
                    cv2.putText(frame, f'{label} {confidence:.2f}', (x1, y1 - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 2)

        if drowsiness_detected:
            if drowsiness_detection_start is None:
                drowsiness_detection_start = time.time()
            drowsiness_detected_time = time.time() - drowsiness_detection_start
            if drowsiness_detected_time >= 3:
                insert_notification('Terdeteksi Kantuk')
                buzzer('Hidup')
                drowsiness_detection_start = None
                drowsiness_detected_time = 0
        else:
            drowsiness_detection_start = None
            drowsiness_detected_time = 0

        if is_recording and video_writer is not None:
            video_writer.write(frame)

        ret, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

@app.route('/select_camera', methods=['POST'])
def select_camera():
    global selected_camera, cap
    data = request.get_json()
    selected_camera = data.get('camera_index')
    if selected_camera is not None:
        stop_camera()
        camera_thread = Thread(target=start_camera, args=(selected_camera,))
        camera_thread.start()
        camera_thread.join()
        if cap is not None and cap.isOpened():
            return jsonify({'message': 'Camera selected successfully'})
        return jsonify({'message': 'Failed to open selected camera'}), 400
    return jsonify({'message': 'Camera not selected'}), 400

@app.route('/start_record', methods=['POST'])
def start_record():
    global is_recording, video_writer
    if cap is None or not cap.isOpened():
        return jsonify({'status': 'camera_not_opened'})

    fourcc = cv2.VideoWriter_fourcc(*'avc1')
    filename = os.path.join(output_directory, f'recording_{time.strftime("%Y%m%d-%H%M%S")}.mp4')
    insert_video(f'recording_{time.strftime("%Y%m%d-%H%M%S")}.mp4')
    video_writer = cv2.VideoWriter(filename, fourcc, 20.0, (1280, 720))
    is_recording = True
    return jsonify({'status': 'recording'})

@app.route('/stop_record', methods=['POST'])
def stop_record():
    global is_recording, video_writer
    if is_recording:
        is_recording = False
        if video_writer is not None:
            video_writer.release()
            video_writer = None
        # Copy the file to the backup directory
        latest_file = max([os.path.join(output_directory, f) for f in os.listdir(output_directory)], key=os.path.getctime)
        shutil.copy(latest_file, backup_directory)
        return jsonify({'status': 'stopped'})
    return jsonify({'status': 'not_recording'})

@app.route('/dataNotification')
def dataNotification():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM notifications ORDER BY id_notifications DESC")
    rows = cursor.fetchall()
    cursor.close()
    conn.close()
    return jsonify(rows)

@app.route('/input_data', methods=['POST'])
def input_data():
    kameraRTSP = request.form['kameraRTSP']

    if kameraRTSP:
        insert_data(kameraRTSP)
        session['message'] = "Kamera RTSP Berhasil Ditambahkan"
        session['success'] = True
    else:
        session['message'] = "URL RTSP, Tidak Boleh Kosong!"
        session['success'] = False

    return redirect(url_for('index'))

@app.route('/hapus_catatan', methods=['POST'])
def hapus_catatan():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("DELETE FROM notifications")
    conn.commit()
    cursor.close()
    conn.close()
    return redirect(url_for('index'))

if __name__ == '__main__':
    buzzer_thread = Thread(target=buzzer_control, daemon=True)
    buzzer_thread.start()
    app.run(debug=True)