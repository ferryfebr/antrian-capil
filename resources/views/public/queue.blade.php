<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - Disdukcapil</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            /* REMOVED: overflow: hidden; - INI YANG MENYEBABKAN TIDAK BISA SCROLL */
        }
        
        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid #dc3545;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
     
        
        
        
        .current-queue {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(220,53,69,0.3);
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { box-shadow: 0 10px 30px rgba(220,53,69,0.3); }
            to { box-shadow: 0 15px 40px rgba(220,53,69,0.5); }
        }
        
        .queue-number {
            font-size: 4rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .waiting-queue {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-height: 70vh; /* TAMBAHAN: Batas tinggi maksimal */
            overflow-y: auto; /* TAMBAHAN: Scroll dalam container */
        }
        
        /* TAMBAHAN: Custom scrollbar untuk waiting queue */
        .waiting-queue::-webkit-scrollbar {
            width: 8px;
        }
        
        .waiting-queue::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .waiting-queue::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .waiting-queue::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .queue-item {
            border-radius: 10px;
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .queue-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .service-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .marquee {
            background: #ffc107;
            color: #000;
            padding: 10px 0;
            white-space: nowrap;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .marquee-content {
            display: inline-block;
            animation: marquee 30s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .stats-card {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .time-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .call-animation {
            animation: callPulse 1s ease-in-out infinite;
        }
        
        @keyframes callPulse {
            0% { 
                background-color: #dc3545;
                transform: scale(1);
            }
            50% { 
                background-color: #ff6b7d;
                transform: scale(1.02);
            }
            100% { 
                background-color: #dc3545;
                transform: scale(1);
            }
        }
        
        .waiting-animation {
            animation: waitingBounce 2s ease-in-out infinite;
        }
        
        @keyframes waitingBounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .no-queue {
            background: rgba(255,255,255,0.9);
            border-radius: 20px;
            text-align: center;
            padding: 3rem;
        }
        
        /* TAMBAHAN: Scroll indicator untuk waiting queue */
        .scroll-indicator {
            position: absolute;
            bottom: 10px;
            right: 20px;
            color: rgba(0,0,0,0.5);
            font-size: 0.8rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        /* TAMBAHAN: Responsive adjustments */
        @media (max-width: 768px) {
            .queue-number { font-size: 2.5rem; }
            .current-queue { margin-bottom: 1rem; }
            .back-button {
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }
            .waiting-queue {
                max-height: 60vh;
            }
        }
        
        /* TAMBAHAN: Better spacing for content */
        .main-content {
            padding-bottom: 2rem;
        }
    </style>
</head>
<body>
   

    <!-- Header -->
    <div class="header py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-building fa-2x text-primary me-3"></i>
                        <div>
                            <h4 class="mb-0 text-primary">DISDUKCAPIL</h4>
                            <small class="text-muted">Sistem Antrian Online</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <h5 class="mb-0 text-dark">DISPLAY ANTRIAN</h5>
                    <small class="text-muted">Real-time Queue Display</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="time-display text-primary" id="currentDateTime"></div>
                    <!-- TAMBAHAN: Navigation menu -->
                    <div class="btn-group btn-group-sm mt-2" role="group">
                        <a href="{{ route('public.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-home me-1"></i>Beranda
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marquee Info -->
    <div class="marquee">
        <div class="marquee-content">
            <i class="fas fa-info-circle me-2"></i>
            Selamat datang di Disdukcapil • Siapkan dokumen yang diperlukan • 
            Harap menunggu hingga nomor antrian Anda dipanggil • 
            Pelayanan: Senin - Jumat 08:00 - 15:00 • 
            Untuk informasi lebih lanjut hubungi petugas
        </div>
    </div>

    <div class="container-fluid py-4 main-content">
        <div class="row">
            <!-- Current Queue -->
            <div class="col-md-5 mb-4">
                <div id="currentQueueSection">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
            
            <!-- Waiting Queue -->
            <div class="col-md-7">
                <div class="waiting-queue p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-primary mb-0">
                            <i class="fas fa-clock me-2"></i>Antrian Menunggu
                        </h5>
                        <span class="badge bg-primary" id="waitingCount">0</span>
                    </div>
                    
                    <div id="waitingQueueList">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    
                    <!-- TAMBAHAN: Scroll indicator -->
                    <div class="scroll-indicator" id="scrollIndicator" style="display: none;">
                        <i class="fas fa-arrow-down"></i> Scroll untuk melihat lebih banyak
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stats-card card text-center">
                    <div class="card-body">
                        <i class="fas fa-list-ol fa-2x text-primary mb-2"></i>
                        <div class="h4 text-primary" id="totalToday">0</div>
                        <small>Total Hari Ini</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <div class="h4 text-warning" id="waitingToday">0</div>
                        <small>Menunggu</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center">
                    <div class="card-body">
                        <i class="fas fa-check fa-2x text-success mb-2"></i>
                        <div class="h4 text-success" id="completedToday">0</div>
                        <small>Selesai</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card card text-center">
                    <div class="card-body">
                        <i class="fas fa-volume-up fa-2x text-info mb-2"></i>
                        <div class="h4 text-info" id="calledToday">0</div>
                        <small>Dipanggil</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio for notifications -->
    <audio id="callSound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+D8w3UlBiuBzvLZiTYIF2ez7+CZTgwOUarm7L5nHgU7k9n1znktBSV+3" type="audio/wav">
    </audio>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let lastCalledQueue = null;
        let isFirstLoad = true;

       

        

        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            document.getElementById('currentDateTime').textContent = 
                now.toLocaleDateString('id-ID', options);
        }

        // Load queue data
        async function loadQueueData() {
            try {
                const response = await fetch('{{ route("antrian.current") }}');
                const data = await response.json();
                
                updateCurrentQueue(data.current);
                updateWaitingQueue(data.waiting);
                
                // Check for new calls
                if (data.current && (!lastCalledQueue || lastCalledQueue.id_antrian !== data.current.id_antrian)) {
                    if (!isFirstLoad) {
                        playCallSound();
                        showCallNotification(data.current);
                    }
                    lastCalledQueue = data.current;
                }
                
                isFirstLoad = false;
            } catch (error) {
                console.error('Error loading queue data:', error);
            }
        }

        // Load statistics
        async function loadStatistics() {
            try {
                const response = await fetch('{{ route("api.stats.today") }}');
                const stats = await response.json();
                
                document.getElementById('totalToday').textContent = stats.total_antrian;
                document.getElementById('waitingToday').textContent = stats.menunggu;
                document.getElementById('completedToday').textContent = stats.selesai;
                document.getElementById('calledToday').textContent = stats.dipanggil;
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        }

        // Update current queue display
        function updateCurrentQueue(currentQueue) {
            const section = document.getElementById('currentQueueSection');
            
            if (currentQueue) {
                section.innerHTML = `
                    <div class="current-queue p-4 text-center call-animation">
                        <h4 class="mb-3">
                            <i class="fas fa-volume-up me-2"></i>SEDANG DIPANGGIL
                        </h4>
                        <div class="queue-number mb-3">${currentQueue.nomor_antrian}</div>
                        <div class="mb-3">
                            <h5 class="mb-2">${currentQueue.pengunjung.nama_pengunjung}</h5>
                            <span class="service-badge bg-light text-dark">
                                ${currentQueue.layanan.nama_layanan}
                            </span>
                        </div>
                        <div class="small opacity-75">
                            <i class="fas fa-clock me-1"></i>
                            Dipanggil: ${new Date(currentQueue.waktu_dipanggil).toLocaleTimeString('id-ID')}
                        </div>
                    </div>
                `;
            } else {
                section.innerHTML = `
                    <div class="no-queue">
                        <i class="fas fa-pause-circle fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Tidak Ada Antrian</h4>
                        <p class="text-muted">Saat ini tidak ada antrian yang sedang dipanggil</p>
                    </div>
                `;
            }
        }

        // Update waiting queue list
        function updateWaitingQueue(waitingQueues) {
            const list = document.getElementById('waitingQueueList');
            const count = document.getElementById('waitingCount');
            const scrollIndicator = document.getElementById('scrollIndicator');
            
            count.textContent = waitingQueues.length;

            if (waitingQueues.length > 0) {
                let html = '<div class="row">';
                
                waitingQueues.forEach((queue, index) => {
                    const isNext = index === 0;
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="queue-item p-3 bg-light ${isNext ? 'border-warning waiting-animation' : ''}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-primary">${queue.nomor_antrian}</h6>
                                        <div class="small text-muted">${queue.pengunjung.nama_pengunjung}</div>
                                        <span class="badge bg-info small">${queue.layanan.kode_layanan}</span>
                                    </div>
                                    <div class="text-end">
                                        ${isNext ? '<span class="badge bg-warning text-dark">SELANJUTNYA</span>' : ''}
                                        <div class="small text-muted">
                                            ${new Date(queue.waktu_antrian).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                
                if (waitingQueues.length > 10) {
                    html += `
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Menampilkan 10 antrian teratas dari ${waitingQueues.length} total antrian
                            </small>
                        </div>
                    `;
                }
                
                list.innerHTML = html;
                
                // TAMBAHAN: Show scroll indicator if content overflows
                const waitingQueueContainer = document.querySelector('.waiting-queue');
                if (waitingQueueContainer.scrollHeight > waitingQueueContainer.clientHeight) {
                    scrollIndicator.style.display = 'block';
                } else {
                    scrollIndicator.style.display = 'none';
                }
            } else {
                list.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">Semua Antrian Selesai!</h5>
                        <p class="text-muted">Tidak ada antrian yang menunggu saat ini</p>
                    </div>
                `;
                scrollIndicator.style.display = 'none';
            }
        }

        // Play call sound
        function playCallSound() {
            const audio = document.getElementById('callSound');
            if (audio) {
                audio.play().catch(e => console.log('Audio play failed:', e));
            }
        }

        // Show call notification
        function showCallNotification(queue) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'position-fixed top-0 start-50 translate-middle-x alert alert-danger alert-dismissible fade show';
            notification.style.zIndex = '9999';
            notification.style.marginTop = '20px';
            notification.innerHTML = `
                <i class="fas fa-volume-up me-2"></i>
                <strong>ANTRIAN DIPANGGIL:</strong> ${queue.nomor_antrian} - ${queue.pengunjung.nama_pengunjung}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 10000);
        }

        // Handle visibility change (when tab becomes visible)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Refresh data when tab becomes visible
                loadQueueData();
                loadStatistics();
            }
        });

        // TAMBAHAN: Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC key to go back
            if (e.key === 'Escape') {
                goBack();
            }
            // F11 for fullscreen
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            loadQueueData();
            loadStatistics();
            
            // Update every 5 seconds
            setInterval(() => {
                updateDateTime();
                loadQueueData();
            }, 5000);
            
            // Update statistics every 30 seconds
            setInterval(loadStatistics, 30000);
            
            // Auto reload page every 30 minutes to prevent memory leaks
            setTimeout(() => {
                window.location.reload();
            }, 30 * 60 * 1000);
        });

        // Handle connection errors
        window.addEventListener('online', function() {
            console.log('Connection restored');
            loadQueueData();
            loadStatistics();
        });

        window.addEventListener('offline', function() {
            console.log('Connection lost');
        });

        // MODIFIED: Remove some restrictions for better UX in normal browser mode
        // Only prevent right-click in fullscreen
        document.addEventListener('contextmenu', function(e) {
            if (document.fullscreenElement) {
                e.preventDefault();
            }
        });

        // Only disable dev tools shortcuts in fullscreen
        document.addEventListener('keydown', function(e) {
            if (document.fullscreenElement) {
                // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U in fullscreen
                if (e.keyCode === 123 || 
                    (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || 
                    (e.ctrlKey && e.keyCode === 85)) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>