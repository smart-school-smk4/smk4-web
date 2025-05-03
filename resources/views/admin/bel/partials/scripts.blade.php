<script>
// Enhanced JavaScript with better organization and error handling

// ======================
// UTILITY FUNCTIONS
// ======================

function showLoading(title = 'Process...') {
    Swal.fire({
        title: title,
        html: 'Harap Tunggu Sementara Kami Memproses Permintaan Anda...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    Toast.fire({ icon, title });
}

// ======================
// UI FUNCTIONS
// ======================

// Live Clock
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').textContent = 
        now.getHours().toString().padStart(2, '0') + ':' + 
        now.getMinutes().toString().padStart(2, '0') + ':' + 
        now.getSeconds().toString().padStart(2, '0');
}
setInterval(updateClock, 1000);

// Toggle Dropdown
function toggleDropdown() {
    document.getElementById('actionDropdown').classList.toggle('hidden');
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    if (!e.target.closest('.relative.inline-block')) {
        document.getElementById('actionDropdown').classList.add('hidden');
    }
});

// ======================
// SCHEDULE MANAGEMENT
// ======================

// Delete confirmation
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Ini Tidak Dapat Di Batalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya ',
                cancelButtonText: 'TIdak',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('success', result.value.message || 'Jadwal Bel Berhasil Di Hapus');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            });
        });
    });
});

// Bulk delete confirmation
function confirmDeleteAll() {
    Swal.fire({
        title: 'Hapus Semua Jadwwal?',
        text: "Anda Akan Mengahpus Semua Jadwal!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus Semua',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch("{{ route('bel.delete-all') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error}`
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            showToast('success', result.value.message || 'Semua Jadwal Berhasil Di Hapus');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    });
}

// Activate all schedules
function activateAll() {
    showLoading('Aktifkan Semua Jadwal...');
    fetch("{{ route('api.bel.activate-all') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            showToast('success', data.message || 'Semua Jadwal Berhasil Di Aktifkan');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('error', data.message || 'Jadwal Gagal Di Aktifkan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// Deactivate all schedules
function deactivateAll() {
    showLoading('Menonaktifkan Semua Jadwal...');
    fetch("{{ route('api.bel.deactivate-all') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            showToast('success', data.message || 'Semua Jadwal Berhasil Di Nonaktifkan');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('error', data.message || 'Jadwal Gagal Di Nonaktifkan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// ======================
// BELL CONTROL FUNCTIONS
// ======================

// Ring bell modal
function showRingModal() {
    Swal.fire({
        title: 'Manual Bell Ring',
        html: `
            <div class="text-left">
                <div class="mb-4">
                    <label for="swal-file-number" class="block text-sm font-medium text-gray-700 mb-1">Sound File</label>
                    <select id="swal-file-number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                        <option value="">Select Sound File</option>
                        @for($i = 1; $i <= 50; $i++)
                            <option value="{{ sprintf('%04d', $i) }}">File {{ sprintf('%04d', $i) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="mb-4">
                    <label for="swal-volume" class="block text-sm font-medium text-gray-700 mb-1">Volume (1-30)</label>
                    <input type="number" id="swal-volume" min="1" max="30" value="20" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 text-sm">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ring Bell',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        preConfirm: () => {
            const fileNumber = document.getElementById('swal-file-number').value;
            const volume = document.getElementById('swal-volume').value;
            
            if (!fileNumber) {
                Swal.showValidationMessage('Pilih File');
                return false;
            }
            if (volume < 1 || volume > 30) {
                Swal.showValidationMessage('Volume Antar 1 - 30');
                return false;
            }
            
            return { file_number: fileNumber, volume: volume };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            ringBell(result.value.file_number, result.value.volume);
        }
    });
}

// Ring bell function
function ringBell(fileNumber, volume = 20) {
    showLoading('Ringing bell...');
    fetch("{{ route('api.bel.ring') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            file_number: fileNumber,
            volume: volume
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            showToast('success', 'Bel Berhasil Di Bunyikan');
        } else {
            showToast('error', data.message || 'Bel Gagal Di Bunyikan');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// Sync schedules function
function syncSchedules() {
    showLoading('Sinkronasi Jadwal Bel Dengan Device...');
    fetch("{{ route('api.bel.sync') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            showToast('success', data.message || 'Sinkronisasi Jadwal Berhasil');
        } else {
            showToast('error', data.message || 'Sinkronisasi Jadwal Gagal');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

// ======================
// DEVICE STATUS FUNCTIONS
// ======================

// Get live status
function getLiveStatus() {
    fetch("{{ route('api.bel.status') }}", {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDeviceStatus(data.data);
        }
    })
    .catch(error => console.error('Status update error:', error));
}

// Update device status
function updateDeviceStatus(data) {
    // Update MQTT Status
    if (data.mqtt_status !== undefined) {
        const mqttCard = document.querySelector('#mqttCard');
        const mqttStatusText = document.querySelector('#mqttStatusText');
        const mqttIconBg = document.querySelector('#mqttIconBg');
        const mqttIconSvg = document.querySelector('#mqttIconSvg');
        
        const isConnected = data.mqtt_status === true || data.mqtt_status === 'Connected';
        
        mqttCard.classList.toggle('border-green-500', isConnected);
        mqttCard.classList.toggle('border-red-500', !isConnected);
        
        mqttIconBg.classList.toggle('bg-green-100', isConnected);
        mqttIconBg.classList.toggle('bg-red-100', !isConnected);
        
        mqttIconSvg.classList.toggle('text-green-600', isConnected);
        mqttIconSvg.classList.toggle('text-red-600', !isConnected);
        
        mqttStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        mqttStatusText.classList.toggle('text-green-600', isConnected);
        mqttStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.mqtt_last_update) {
            document.querySelector('#mqttStatusDetails').textContent = 
                `Last updated: ${new Date(data.mqtt_last_update).toLocaleTimeString()}`;
        }
    }

    // Update RTC Status
    if (data.rtc !== undefined) {
        const rtcCard = document.querySelector('#rtcCard');
        const rtcIcon = document.querySelector('#rtcIcon');
        const rtcStatusText = document.querySelector('#rtcStatusText');
        const rtcTimeText = document.querySelector('#rtcTimeText');
        
        const isConnected = data.rtc === true;
        
        rtcCard.classList.toggle('border-green-500', isConnected);
        rtcCard.classList.toggle('border-red-500', !isConnected);
        
        rtcIcon.classList.toggle('bg-green-100', isConnected);
        rtcIcon.classList.toggle('bg-red-100', !isConnected);
        
        rtcIcon.querySelector('svg').classList.toggle('text-green-600', isConnected);
        rtcIcon.querySelector('svg').classList.toggle('text-red-600', !isConnected);
        
        rtcStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        rtcStatusText.classList.toggle('text-green-600', isConnected);
        rtcStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.rtc_time) {
            rtcTimeText.textContent = new Date(data.rtc_time).toLocaleString();
        }
    }

    // Update DFPlayer Status
    if (data.dfplayer !== undefined) {
        const dfplayerCard = document.querySelector('#dfplayerCard');
        const dfplayerIcon = document.querySelector('#dfplayerIcon');
        const dfplayerStatusText = document.querySelector('#dfplayerStatusText');
        
        const isConnected = data.dfplayer === true;
        
        dfplayerCard.classList.toggle('border-green-500', isConnected);
        dfplayerCard.classList.toggle('border-red-500', !isConnected);
        
        dfplayerIcon.classList.toggle('bg-green-100', isConnected);
        dfplayerIcon.classList.toggle('bg-red-100', !isConnected);
        
        dfplayerIcon.querySelector('svg').classList.toggle('text-green-600', isConnected);
        dfplayerIcon.querySelector('svg').classList.toggle('text-red-600', !isConnected);
        
        dfplayerStatusText.textContent = isConnected ? 'Connected' : 'Disconnected';
        dfplayerStatusText.classList.toggle('text-green-600', isConnected);
        dfplayerStatusText.classList.toggle('text-red-600', !isConnected);
        
        if (data.dfplayer_files) {
            document.querySelector('#dfplayerDetails').textContent = 
                `${data.dfplayer_files} sound files available`;
        }
    }
}

// ======================
// NEXT SCHEDULE COUNTDOWN
// ======================

function updateNextSchedule() {
    fetch("{{ route('api.bel.next-schedule') }}")
    .then(response => {
        if (!response.ok) throw new Error('Gagal Memuat Jadwal');
        return response.json();
    })
    .then(data => {
        const countdownEl = document.getElementById('nextScheduleCountdown');
        const timeEl = document.getElementById('nextScheduleTime');
        
        if (data.success && data.next_schedule && data.next_schedule.is_active) {
            // Get current time in UTC+7 (Indonesia timezone)
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const indonesiaTime = new Date(utc + (3600000 * 7));
            
            const [hours, minutes, seconds] = data.next_schedule.time.split(':').map(Number);
            
            // Create target time in Indonesia timezone
            let targetTime = new Date(indonesiaTime);
            targetTime.setHours(hours, minutes, seconds || 0, 0);
            
            // Adjust day if needed
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const currentDayIndex = indonesiaTime.getDay();
            const targetDayName = data.next_schedule.hari;
            const targetDayIndex = days.indexOf(targetDayName);
            
            let daysToAdd = (targetDayIndex - currentDayIndex + 7) % 7;
            if (daysToAdd === 0 && targetTime <= indonesiaTime) {
                daysToAdd = 7; // Next week same day
            }
            targetTime.setDate(indonesiaTime.getDate() + daysToAdd);
            
            // Update display
            timeEl.textContent = `${targetDayName}, ${data.next_schedule.time} (File ${data.next_schedule.file_number})`;
            
            // Countdown function
            const updateCountdown = () => {
                // Get current Indonesia time for comparison
                const current = new Date();
                const currentUTC = current.getTime() + (current.getTimezoneOffset() * 60000);
                const currentIndonesiaTime = new Date(currentUTC + (3600000 * 7));
                
                const diff = targetTime - currentIndonesiaTime;
                
                if (diff > 0) {
                    const h = Math.floor(diff / 3600000);
                    const m = Math.floor((diff % 3600000) / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    
                    countdownEl.innerHTML = `
                        <span class="text-blue-600 font-medium">${h.toString().padStart(2, '0')}</span>h 
                        <span class="text-blue-600 font-medium">${m.toString().padStart(2, '0')}</span>m 
                        <span class="text-blue-600 font-medium">${s.toString().padStart(2, '0')}</span>s
                    `;
                } else {
                    countdownEl.innerHTML = '<span class="text-green-600 font-bold">SEDANG BERLANGSUNG</span>';
                    clearInterval(window.countdownInterval);
                    setTimeout(updateNextSchedule, 5000);
                }
            };
            
            // Clear previous interval and start new one
            if (window.countdownInterval) clearInterval(window.countdownInterval);
            updateCountdown();
            window.countdownInterval = setInterval(updateCountdown, 1000);
            
        } else {
            countdownEl.textContent = 'Tidak Ada Jadwal Aktif';
            timeEl.textContent = '';
            if (window.countdownInterval) clearInterval(window.countdownInterval);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('nextScheduleCountdown').textContent = 'Error loading Jadwal';
        document.getElementById('nextScheduleTime').textContent = '';
    });
}

// ======================
// INITIALIZATION
// ======================

document.addEventListener('DOMContentLoaded', function() {
    updateClock();
    updateNextSchedule();
    
    // Refresh every minute to stay accurate
    setInterval(updateNextSchedule, 60000);
    setInterval(getLiveStatus, 60000); // Update status every 30 seconds
    
    // Add animation to status cards on hover
    document.querySelectorAll('#mqttCard, #rtcCard, #dfplayerCard').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.querySelector('svg').classList.add('animate-pulse');
        });
        card.addEventListener('mouseleave', () => {
            card.querySelector('svg').classList.remove('animate-pulse');
        });
    });
});
</script>