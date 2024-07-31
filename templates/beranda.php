<?php
session_start();
ob_start();
if(!isset($_SESSION['id_user'])) {
    header('location: https://smarthomesurveillance-web.ngrok.app/SAS_web/login.php');
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Smart Home Surveillance</title>
    <meta name="theme-color" content="#001b2f">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="718x734" href="{{ url_for('static', filename='assets/img/logo.png') }}">
    <link rel="stylesheet" href="{{ url_for('static', filename='assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=ADLaM+Display&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Alfa+Slab+One&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Anton&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rubik+Bubbles&amp;subset=cyrillic,cyrillic-ext,hebrew,latin-ext&amp;display=swap">
    <link rel="stylesheet" href="{{ url_for('static', filename='assets/fonts/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ url_for('static', filename='assets/fonts/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ url_for('static', filename='assets/fonts/fontawesome5-overrides.min.css') }}">
    <link rel="stylesheet" href="{{ url_for('static', filename='assets/css/Scrollspy.css') }}">
</head>

<body style="background: rgb(13,110,253);">
    <section id="Header">
        <nav class="navbar navbar-expand fixed-top bg-primary pt-3 ps-1">
            <div class="container-fluid"><a class="navbar-brand" href="#"><img class="img-fluid" src="{{ url_for('static', filename='assets/img/logo.png') }}" width="45px">&nbsp;<span class="text-white" style="font-size: 15px;font-family: 'ADLaM Display', serif;">Smart Home Surveillance</span></a><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navcol-1"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navcol-1">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#KeluarModal" href="#"><i class="fas fa-sign-out-alt"></i>&nbsp;Keluar</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="modal fade" role="dialog" tabindex="-1" id="KeluarModal">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><strong>Konfirmasi</strong></h5><button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <h1 style="font-size: 45px;"><i class="fas fa-exclamation-triangle"></i></h1>
                        <p>Apakah anda ingin keluar ?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light btn-sm border rounded" type="button" data-bs-dismiss="modal"><i class="fas fa-times"></i>&nbsp;Tidak</button>
                        <a class="btn btn-dark btn-sm" href="https://smarthomesurveillance-web.ngrok.app/SHS_web/logout.php" role="button"><i class="fas fa-check"></i>&nbsp;Ya, saya ingin keluar</a></div>
                </div>
            </div>
        </div>
    </section>
    <section id="Main" style="margin-top: 30%;margin-bottom: 30%;">
        <div class="container">
            <form id="cameraForm">
            <div class="row">
                <div class="col-6 pe-0" style="margin-left: -0.5vw;">
                    <select class="form-select-sm form-select" id="cameraSelect">
                        <option value="" selected="">Pilih Kamera</option>
                    </select></div>
                <div class="col-3" style="padding-left: 1vw;"><button class="btn btn-dark btn-sm" type="submit"><i class="fas fa-hand-pointer"></i>&nbsp;Pilih</button></div>
                <div class="col-3" style="margin-left: -8vw;padding-left: 1vw;"><button class="btn btn-light btn-sm" onclick="reloadPage()" type="button">&nbsp;<span class="text-truncate"><i class="fa fa-refresh"></i>&nbsp;Muat Ulang</span></button></div>
            </div>
            </form>
            <div class="row pt-2">
                <div class="col">
                    <div class="card">
                        <div class="card-body text-center" id="videoContainer">
                            <h1 class="pt-3"><i class="fas fa-video-slash"></i></h1>
                            <p>CCTV Tidak Ada</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row pt-2" id="recordControls">
                <div class="col-3">
                    <p class="lead fw-bold text-white" id="timesleap">00:00:00</p>
                </div>
                <div class="col-4"><button id="startRecord" class="btn btn-dark btn-sm" type="button" disabled><span class="text-truncate"><i class="fas fa-record-vinyl"></i>&nbsp;Mulai Rekam</span></button></div>
                <div class="col"><button id="stopRecord" class="btn btn-danger btn-sm" type="button" disabled><span class="text-truncate"><i class="fas fa-stop"></i>&nbsp;Berhenti Rekam</span></button></div>
            </div>
        </div>
        <div class="container pt-3">
            <div class="row">
                <div class="col-8">
                    <h5 class="text-white" style="font-family: 'ADLaM Display', serif;">Rekaman Catatan</h5>
                </div>
                <div class="col-4 align-self-end pb-2">
                    <form method="post" action="{{ url_for('hapus_catatan')}}"><button class="btn btn-warning btn-sm" type="submit"><i class="fas fa-trash"></i>&nbsp;Bersihkan</button></form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-9 col-md-9 col-xl-10">
                            <div class="scrollspy-example" data-spy="scroll" data-target="#list-example" data-offset="0" style="overflow: scroll;height: 20vh;">
                                <ul class="list-group" id="data-notification">
                                </ul>
                            </div>
                        </div><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.body.onload = function () {
        $('[data-spy="scroll"]').each(function () {
            var $spy = $(this).scrollspy('refresh')
        });
    };
</script>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="Navbar">
        <nav class="navbar navbar-expand fixed-bottom bg-black mt-0 mb-0 pt-0" data-bs-theme="dark" style="background: rgb(0,27,47);border-color: rgb(0,27,47);color: rgb(0,27,47);">
            <div class="container-fluid"><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navcol-2"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navcol-2">
                    <ul class="navbar-nav mx-auto mt-0 pt-1">
                        <li class="nav-item pb-0 me-3"><a class="nav-link active text-center" href="https://smarthomesurveillance-flask.ngrok.app"><i class="fas fa-home" style="font-size: 20px;"></i>
                                <p style="font-size: 11px;">Beranda</p>
                            </a></li>
                        <li class="nav-item pb-0 me-3"><a class="nav-link text-center" href="#" data-bs-toggle="modal" data-bs-target="#CameraModal"><i class="fas fa-video" style="font-size: 20px;"></i>
                                <p class="text-truncate" style="font-size: 11px;">Kamera RTSP</p>
                            </a></li>
                        <li class="nav-item pb-0 me-3"><a class="nav-link text-center" href="https://smarthomesurveillance-web.ngrok.app/SHS_web/video.php"><i class="fas fa-film" style="font-size: 20px;"></i>
                                <p style="font-size: 11px;">Video</p>
                            </a></li>
                        <li class="nav-item pb-0 me-3"><a class="nav-link text-center" href="https://smarthomesurveillance-web.ngrok.app/SHS_web/buzzer.php"><i class="fas fa-volume-up" style="font-size: 20px;"></i>
                                <p style="font-size: 11px;">Buzzer</p>
                            </a></li>
                        <li class="nav-item pb-0"><a class="nav-link text-center" href="https://smarthomesurveillance-web.ngrok.app/SHS_web/profil.php"><i class="fas fa-user" style="font-size: 20px;"></i>
                                <p style="font-size: 11px;">Profil</p>
                            </a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="modal fade" role="dialog" tabindex="-1" id="CameraModal">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><strong>Kamera RTSP</strong></h5><button class="btn-close" type="button" aria-label="Close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="/input_data" method="post">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col">
                                    <label for="kameraRTSP" class="form-label">URL RTSP :</label>
                                    <input class="form-control form-control-sm" type="text" id="kameraRTSP" name="kameraRTSP" placeholder="rtsp://username:password@IpAddress:Port">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col text-center">
                                    <hr><a class="btn btn-outline-dark btn-sm" role="button" href="https://smarthomesurveillance-web.ngrok.app/SHS_web/kamera_rtsp.php"><i class="fa fa-eye"></i>&nbsp;Lihat Data Kamera RTSP</a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light btn-sm border rounded" type="reset"><i class="fa fa-refresh"></i>&nbsp;Reset</button>
                            <button class="btn btn-dark btn-sm" type="submit" role="button"><i class="fas fa-save"></i>&nbsp;Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                    {% if message %}
                    <div class="toast-container position-fixed top-0 end-0 p-3">
                        <div id="myToast" class="toast align-items-center text-white {{ 'bg-success' if success else 'bg-danger' }} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body text-white">
                                    <strong>{{ message }}</strong>
                                </div>
                                <button type="button" class="btn-close btn-close-dark me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                             </div>
                        </div>
                    </div>
                    {% endif %}
    </section>
    <script src="{{ url_for('static', filename='assets/js/jquery.min.js') }}"></script>
    <script src="{{ url_for('static', filename='assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ url_for('static', filename='assets/js/bs-init.js') }}"></script>
    <script>
        let timer;
        let elapsedSeconds = 0;
        let videoContainer = document.getElementById('videoContainer');
        let isRecording = false;  // Menyimpan status perekaman

        function startTimer() {
            timer = setInterval(function() {
                elapsedSeconds++;
                const hours = String(Math.floor(elapsedSeconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((elapsedSeconds % 3600) / 60)).padStart(2, '0');
                const seconds = String(elapsedSeconds % 60).padStart(2, '0');
                document.getElementById('timesleap').innerText = `${hours}:${minutes}:${seconds}`;
            }, 1000);
        }

        function stopTimer() {
            clearInterval(timer);
            elapsedSeconds = 0;
            document.getElementById('timesleap').innerText = '00:00:00';
        }

        async function selectCamera(cameraIndex) {
            videoContainer.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            try {
                const response = await fetch('/select_camera', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ camera_index: cameraIndex }),
                });
                const data = await response.json();
                if (data.message === 'Camera selected successfully') {
                    videoContainer.innerHTML = '<img src="/video_feed" class="img-fluid" />';
                    // Mengatur status tombol sesuai dengan status perekaman
                    if (isRecording) {
                        document.getElementById('startRecord').disabled = true;
                        document.getElementById('stopRecord').disabled = false;
                    } else {
                        document.getElementById('startRecord').disabled = false;
                        document.getElementById('stopRecord').disabled = true;
                    }
                } else {
                    videoContainer.innerHTML = '<h1>Kamera Belum Dipilih</h1>';
                }
            } catch (error) {
                console.error('Error selecting camera:', error);
                videoContainer.innerHTML = '<h1>Error Memilih Kamera</h1>';
            }
        }

        document.getElementById('cameraForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const cameraIndex = document.getElementById('cameraSelect').value;
            if (cameraIndex !== "") {
                selectCamera(cameraIndex);
            }
        });

        document.getElementById('startRecord').addEventListener('click', async function() {
            try {
                const response = await fetch('/start_record', { method: 'POST' });
                const data = await response.json();
                if (data.status === 'recording') {
                    isRecording = true;
                    document.getElementById('startRecord').disabled = true;
                    document.getElementById('stopRecord').disabled = false;
                    startTimer();
                }
            } catch (error) {
                console.error('Error starting recording:', error);
            }
        });

        document.getElementById('stopRecord').addEventListener('click', async function() {
            try {
                const response = await fetch('/stop_record', { method: 'POST' });
                const data = await response.json();
                if (data.status === 'stopped') {
                    isRecording = false;
                    document.getElementById('startRecord').disabled = false;
                    document.getElementById('stopRecord').disabled = true;
                    stopTimer();
                }
            } catch (error) {
                console.error('Error stopping recording:', error);
            }
        });

        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('/list_cameras');
                const cameras = await response.json();
                const cameraSelect = document.getElementById('cameraSelect');
                cameras.forEach(camera => {
                    const option = document.createElement('option');
                    option.value = camera;
                    option.text = camera;
                    cameraSelect.appendChild(option);
                });
                document.getElementById('startRecord').disabled = true;
                document.getElementById('stopRecord').disabled = true;
            } catch (error) {
                console.error('Error fetching camera list:', error);
            }
        });

        function reloadPage() {
            location.reload('https://smarthomesurveillance-flask.ngrok.app');
        }

        function fetchDataNotification() {
    $.ajax({
        url: "/dataNotification",
        method: "GET",
        success: function(dataNotification) {
            let list = $("#data-notification");
            list.empty();
            if (dataNotification.length === 0) {
                list.append("<h3 class='text-center text-secondary'>Data Masih Kosong</h3>");
            } else {
                dataNotification.forEach(function(result) {
                    list.append(`
                        <li class='list-group-item'>
                            <div class='row'>
                                <div class='col'>
                                    <span>${result.message}</span>
                                </div>
                                <div class='col text-end'>
                                    <span class='text-truncate'>
                                        <sub>${result.timestamp}</sub>
                                    </span>
                                </div>
                            </div>
                        </li>
                    `);
                });
            }
        }
    });
}

$(document).ready(function() {
    fetchDataNotification();
    setInterval(fetchDataNotification, 1000);
});

document.addEventListener('DOMContentLoaded', function () {
        var myToast = document.getElementById('myToast');
        if (myToast) {
            var toast = new bootstrap.Toast(myToast);
            toast.show();
        }
    });
    </script>
</body>
</html>