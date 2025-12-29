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

// Toggle status non-blocking dengan fetch async/await
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-status-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', async function(e) {
            const scheduleId = this.dataset.scheduleId;
            const route = this.dataset.route;
            const statusLabel = this.closest('label').querySelector('.status-label');
            const isChecked = this.checked;
            
            try {
                // Show loading state
                statusLabel.innerHTML = '<span class="animate-pulse">...</span>';
                
                const response = await fetch(route, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_active: isChecked })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Update UI langsung tanpa reload
                    statusLabel.textContent = isChecked ? 'Active' : 'Inactive';
                    showToast('success', `Status diubah menjadi ${isChecked ? 'Active' : 'Inactive'}`);
                } else {
                    // Jika gagal, kembalikan checkbox ke state sebelumnya
                    this.checked = !isChecked;
                    statusLabel.textContent = !isChecked ? 'Active' : 'Inactive';
                    showToast('error', data.message || 'Gagal mengubah status');
                }
            } catch (error) {
                // Jika error, kembalikan checkbox ke state sebelumnya
                this.checked = !isChecked;
                statusLabel.textContent = !isChecked ? 'Active' : 'Inactive';
                showToast('error', 'Terjadi kesalahan: ' + error.message);
            }
        });
    });
});

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
        title: '<div class="flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mr-3 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg><span class="text-2xl font-bold bg-gradient-to-r from-yellow-600 to-orange-600 bg-clip-text text-transparent">Bunyikan Bel Manual</span></div>',
        html: `
            <div class="text-left bg-gradient-to-br from-yellow-50 to-orange-50 p-6 rounded-xl">
                <div class="mb-2 text-center">
                    <p class="text-gray-600 text-sm mb-4">Pilih file audio yang akan diputar pada speaker</p>
                </div>
                <div class="mb-4">
                    <label for="swal-file-number" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" />
                        </svg>
                        Sound File
                    </label>
                    <select id="swal-file-number" class="mt-1 block w-full rounded-lg border-2 border-purple-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 py-3 px-4 text-base font-medium bg-white hover:border-purple-400 transition duration-200">
                        <option value="" class="text-gray-500">🎵 Pilih File Audio...</option>
                        @for($i = 1; $i <= 30; $i++)
                            <option value="{{ sprintf('%04d', $i) }}">🔊 File {{ sprintf('%04d', $i) }}</option>
                        @endfor
                    </select>
                </div>
                
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<span class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg>Bunyikan Bel</span>',
        cancelButtonText: '<span class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>Batal</span>',
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        focusConfirm: false,
        customClass: {
            popup: 'rounded-2xl shadow-2xl',
            confirmButton: 'rounded-lg px-6 py-3 font-semibold shadow-lg hover:shadow-xl transition duration-200',
            cancelButton: 'rounded-lg px-6 py-3 font-semibold shadow hover:shadow-lg transition duration-200'
        },
        preConfirm: () => {
            const fileNumber = document.getElementById('swal-file-number').value;
            
            if (!fileNumber) {
                Swal.showValidationMessage('⚠️ Silahkan pilih file audio terlebih dahulu');
                return false;
            }
            
            return { file_number: fileNumber, volume: 20 };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            ringBell(result.value.file_number, result.value.volume);
        }
    });
}

// Ring bell function
function ringBell(fileNumber, volume = 15) {
    Swal.fire({
        title: '<div class="flex flex-col items-center"><div class="animate-bounce mb-3"><svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-yellow-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" /></svg></div><span class="text-xl font-bold text-gray-800">Membunyikan Bel...</span></div>',
        html: '<div class="text-center"><p class="text-gray-600 mb-3">Mengirim perintah ke ESP32 </p><div class="flex justify-center items-center space-x-1"><div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0s"></div><div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.2s"></div><div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.4s"></div></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        customClass: {
            popup: 'rounded-2xl shadow-2xl'
        },
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
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
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '<span class="text-green-600">Berhasil!</span>',
                html: '<p class="text-gray-700 text-lg">🔔 Bel sedang dibunyikan</p>',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl'
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membunyikan Bel',
                text: data.message || 'Terjadi kesalahan',
                confirmButtonColor: '#EF4444',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl'
                }
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-2xl shadow-2xl'
            }
        });
    });
}

// Sync schedules function
function syncSchedules() {
    Swal.fire({
        title: '<div class="flex flex-col items-center"><div class="mb-3"><svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500 animate-spin" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" /></svg></div><span class="text-xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">Sinkronisasi Jadwal</span></div>',
        html: '<div class="text-center"><p class="text-gray-600 mb-4">Mengirim jadwal ke ESP32 ...</p><div class="flex justify-center items-center space-x-2"><div class="w-2 h-2 bg-green-500 rounded-full animate-bounce" style="animation-delay: 0s"></div><div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div><div class="w-2 h-2 bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div><div class="w-2 h-2 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 0.3s"></div></div></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        customClass: {
            popup: 'rounded-2xl shadow-2xl'
        },
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch("{{ route('api.bel.sync') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '<span class="text-green-600">Sinkronisasi Berhasil!</span>',
                html: '<p class="text-gray-700">✅ Jadwal telah diperbarui ke device</p>',
                timer: 2500,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl'
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Sinkronisasi Gagal',
                text: data.message || 'Terjadi kesalahan',
                confirmButtonColor: '#EF4444',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl'
                }
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-2xl shadow-2xl'
            }
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
    getLiveStatus();
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