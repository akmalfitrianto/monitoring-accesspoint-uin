<x-filament-panels::page>
    <div x-data="{
        viewMode: 'buildings',
        showModal: false,
        selectedBuilding: null,
        buildings: @js($this->buildings),
        rooms: [],
        accessPoints: [],
        floors: [],
        currentFloor: 1,
        showInfoPanel: true,
        showReportModal: false,
        selectedAP: null,
        reportDescription: '',
        technicians: [],
        selectedTechnician: null,
        loadingTechnicians: false,
        loadingReport: false,
        
    
        async loadRooms(buildingId) {
            try {
                const response = await fetch(`/admin/api/buildings/${buildingId}/rooms`);
                const data = await response.json();
    
                let maxFloorInData = 1;
                data.rooms.forEach(room => {
                    if (room.floor) maxFloorInData = Math.max(maxFloorInData, room.floor);
                });
                data.access_points.forEach(ap => {
                    if (ap.floor) maxFloorInData = Math.max(maxFloorInData, ap.floor);
                });
    
                const numberOfFloors = Math.max(data.total_floors || 1, maxFloorInData);
    
                this.floors = Array.from({ length: numberOfFloors }, (_, i) => i + 1);
    
                this.rooms = data.rooms || [];
                this.accessPoints = data.access_points || [];
                this.currentFloor = this.floors[0] || 1;
                this.viewMode = 'rooms';
            } catch (error) {
                console.error('Error loading rooms:', error);
                alert('Gagal memuat data ruangan');
            }
        },
    
        openModal(building) {
            this.selectedBuilding = {
                ...building,
                total_floors: building.total_floors || 1
            };
            this.showModal = true;
        },
    
        closeModal() {
            this.showModal = false;
            this.selectedBuilding = null;
        },
    
        backToBuildings() {
            this.viewMode = 'buildings';
            this.rooms = [];
            this.accessPoints = [];
        },
    
        async loadTechnicians() {
            this.loadingTechnicians = true;
            try {
                const response = await fetch('/admin/api/technicians');
                if (response.ok) {
                    this.technicians = await response.json();
                } else {
                    console.error('Failed to load technicians');
                }
            } catch (error) {
                console.error('Error loading technicians:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memuat teknisi',
                    text: 'Tidak dapat mengambil daftar teknisi',
                    confirmButtonColor: '#d33',
                });
            } finally {
                this.loadingTechnicians = false;
            }
        },
    
        openReportModal(ap) {
            this.selectedAP = ap;
            this.reportDescription = '';
            this.selectedTechnician = null;
            this.showReportModal = true;
            this.loadTechnicians();
        },
    
        async submitReport() {
            if (this.loadingReport) return;
            this.loadingReport = true;
    
            if (this.selectedAP.status === 'maintenance') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Access Point sedang maintenance!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.loadingReport = false;
                return;
            }
    
            if (!this.reportDescription.trim()) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Deskripsi kosong!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.loadingReport = false;
                return;
            }
    
            if (!this.selectedTechnician) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'warning',
                    title: 'Teknisi belum dipilih',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                this.loadingReport = false;
                return;
            }
    
            try {
                const response = await fetch('/admin/api/tickets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        access_point_id: this.selectedAP.id,
                        description: this.reportDescription,
                        assigned_to: this.selectedTechnician,
                    })
                });
    
                if (response.ok) {
                    const result = await response.json();
    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: result.message || 'Laporan berhasil dikirim!',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
    
                    this.showReportModal = false;
                    this.reportDescription = '';
                    this.selectedTechnician = null;
    
                    // ubah status AP jadi maintenance secara langsung di tampilan
                    this.selectedAP.status = 'maintenance';
    
                    // reload data ruangan kalau diperlukan
                    if (this.selectedBuilding?.id) {
                        await this.loadRooms(this.selectedBuilding.id);
                    }
    
                } else {
                    const err = await response.json().catch(() => ({}));
    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: err.message || 'Gagal mengirim laporan!',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                }
    
            } catch (error) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Kesalahan jaringan!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                console.error(error);
            } finally {
                this.loadingReport = false;
            }
        },
    
    
        calculateAPPosition(ap, index, allAPsInFloor) {
    
            const samePositionAPs = allAPsInFloor.filter(a =>
                a.x_position === ap.x_position && a.y_position === ap.y_position
            );
    
            if (samePositionAPs.length > 1) {
    
                const angle = (index / samePositionAPs.length) * Math.PI * 2;
                const radius = 2; // offset 2%
                return {
                    x: ap.x_position + (Math.cos(angle) * radius),
                    y: ap.y_position + (Math.sin(angle) * radius)
                };
            }
    
            return {
                x: ap.x_position,
                y: ap.y_position
            };
        }
    }" class="space-y-4">
        {{-- Header dengan judul --}}
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold" style="color: #019486;" x-show="viewMode === 'buildings'">
                Denah Gedung Kampus
            </h1>
            <h1 class="text-2xl font-bold" style="color: #019486;" x-show="viewMode === 'rooms'">
                <span x-text="`Lantai ${currentFloor}`"></span>
            </h1>
        </div>

        {{-- MODE: DENAH GEDUNG --}}
        <template x-if="viewMode === 'buildings'">
            <div class="relative mx-auto" style="width: 100%; max-width: 1200px;">

                {{-- disini cuy --}}

                <div style="display: flex;">

                    {{-- disini jua --}}

                    {{-- Main Canvas --}}
                    <div class="relative border-4 rounded-xl shadow-2xl overflow-hidden"
                        style="width: 100%; max-width: 1200px; height: 700px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-color: #d1d5db;">

                        {{-- Grid Pattern untuk referensi visual --}}
                        <div class="absolute inset-0"
                            style="background-image: linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px); background-size: 50px 50px; opacity: 0.5;">
                        </div>

                        {{-- Info panel kiri atas --}}
                        <div class="absolute top-4 left-4 rounded-lg text-xs shadow-md"
                            style="background: rgba(255,255,255,0.95); padding: 12px; color: #374151;">
                            <div style="font-weight: 700; margin-bottom: 8px; color: #111827; font-size: 13px;">📍 Denah
                                Kampus</div>
                            <div style="margin-bottom: 4px;">Total Gedung:
                                <strong>{{ $this->buildings->count() }}</strong></div>
                            <div>Total AP: <strong>{{ $this->buildings->sum('access_points_count') }}</strong></div>
                        </div>

                        {{-- Buildings Markers --}}
                        @foreach ($this->buildings as $building)
                            @php
                                $xPos = max(0, min(100, $building->x_position ?? 50));
                                $yPos = max(0, min(100, $building->y_position ?? 50));
                                $width = $building->grid_width ?? 100;
                                $height = $building->grid_height ?? 80;

                                // Hitung font size berdasarkan ukuran gedung
                                $avgSize = ($width + $height) / 2;
                                $fontSize = max(9, min(14, $avgSize / 6));
                                $subFontSize = max(8, $fontSize - 2);
                                $padding = max(2, min(8, $avgSize / 15));
                            @endphp

                            <div x-on:click="openModal({
                            id: '{{ $building->id }}',
                            name: '{{ addslashes($building->name) }}',
                            code: '{{ $building->code }}',
                            access_points_count: '{{ $building->access_points_count }}',
                            total_floors: '{{ $building->total_floors }}',
                        })"
                                class="absolute flex flex-col items-center justify-center rounded-lg shadow-md cursor-pointer transition-all"
                                style="
                            left: {{ $xPos }}%;
                            top: {{ $yPos }}%;
                            width: {{ $width }}px;
                            height: {{ $height }}px;
                            background: #8afff4;
                            border: 2px solid #02635a;
                            color: #78350f;
                            font-weight: 700;
                            font-size: {{ $fontSize }}px;
                            transform: translate(-50%, -50%);
                            padding: {{ $padding }}px;
                            box-sizing: border-box;
                        "
                                title="{{ $building->name }}"
                                onmouseover="this.style.transform='translate(-50%, -50%) scale(1.05)'; this.style.boxShadow='0 10px 15px rgba(0,0,0,0.3)'"
                                onmouseout="this.style.transform='translate(-50%, -50%) scale(1)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                                <div style="line-height: 1.2;">{{ $building->code }}</div>
                                <div
                                    style="font-size: {{ $subFontSize }}px; font-weight: 500; line-height: 1.2; margin-top: 2px;">
                                    {{ $building->access_points_count }} AP
                                </div>

                                {{-- Tooltip muncul saat hover --}}
                                <div class="absolute opacity-0 transition-opacity duration-200 pointer-events-none"
                                    style="bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); z-index: 100;"
                                    onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                                    <div class="rounded-lg shadow-xl text-xs whitespace-nowrap"
                                        style="background: #1f2937; color: white; padding: 6px 10px;">
                                        {{ $building->name }}
                                        <div style="font-size: 10px; opacity: 0.7; margin-top: 2px;">
                                            Position: ({{ $xPos }}%, {{ $yPos }}%) | Size:
                                            {{ $width }}×{{ $height }}px
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- andakannya disini --}}
                </div>
        </template>

        {{-- Floor Pagination --}}
        <div x-show="viewMode === 'rooms'" style="text-align: center; margin-bottom: 16px;">
            <button x-on:click="currentFloor = Math.max(1, currentFloor - 1)" :disabled="currentFloor === 1"
                style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; padding: 4px 8px; margin-right: 4px;">‹</button>

            <template x-for="floor in floors" :key="floor">
                <button x-on:click="currentFloor = floor"
                    :style="`
                                    width: 8px;
                                    height: 8px;
                                    border-radius: 50%;
                                    border: none;
                                    cursor: pointer;
                                    padding: 0;
                                    margin: 0 4px;
                                    background: ${currentFloor === floor ? '#019486' : '#d1d5db'};
                                    transition: all 0.2s;
                                `"
                    onmouseover="this.style.transform='scale(1.3)'"
                    onmouseout="this.style.transform='scale(1)'"></button>
            </template>

            <button x-on:click="currentFloor = Math.min(floors[floors.length - 1], currentFloor + 1)"
                :disabled="currentFloor === floors[floors.length - 1]"
                style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; padding: 4px 8px; margin-left: 4px;">›</button>
        </div>

        {{-- MODE: DENAH RUANGAN --}}
        <template x-if="viewMode === 'rooms'">
            <div class="relative border-4 rounded-xl mx-auto shadow-2xl overflow-hidden"
                style="width: 100%; max-width: 1200px; height: 700px; background: #ffffff; border-color: #d1d5db;">

                {{-- Back button --}}
                <button x-on:click="backToBuildings"
                    class="absolute top-4 left-4 rounded-lg transition flex items-center gap-2 shadow-lg z-10"
                    style="background: #019486; color: white; padding: 8px 16px; font-weight: 600; font-size: 14px;"
                    onmouseover="this.style.background='#02635a'" onmouseout="this.style.background='#019486'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Denah Gedung
                </button>

                {{-- Grid Pattern --}}
                <div class="absolute inset-0"
                    style="background-image: linear-gradient(rgba(0,0,0,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.05) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.3;">
                </div>

                {{-- Info panel --}}
                <div x-show="showInfoPanel" class="absolute rounded-lg shadow-lg z-10"
                    style="background: rgba(255,255,255,0.95); padding: 16px; backdrop-filter: blur(4px); top: 0px; right: 0px; max-width: 250px; border-radius: 0 4px 0 0;">
                    <div style="font-size: 13px; color: #6b7280;">
                        <div style="font-weight: 700; margin-bottom: 8px; color: #111827;">Informasi:</div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <div
                                style="width: 16px; height: 16px; background: #93c5fd; border: 2px solid #2563eb; border-radius: 2px;">
                            </div>
                            <span>Ruangan</span>
                        </div>
                        <div style="font-weight: 600; margin-top: 8px; margin-bottom: 4px; color: #111827;">Access
                            Point:</div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <div
                                style="width: 12px; height: 12px; background: #10b981; border: 2px solid white; border-radius: 50%;">
                            </div>
                            <span>Active</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <div
                                style="width: 12px; height: 12px; background: #fbbf24; border: 2px solid white; border-radius: 50%;">
                            </div>
                            <span>Maintenance</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <div
                                style="width: 12px; height: 12px; background: #ef4444; border: 2px solid white; border-radius: 50%;">
                            </div>
                            <span>Offline</span>
                        </div>
                        <div style="padding-top: 8px; border-top: 1px solid #e5e7eb;">
                            <div style="font-weight: 600; margin-bottom: 4px;"
                                x-text="'Total Ruangan Lantai ' + currentFloor + ': ' + rooms.filter(r => r.floor === currentFloor).length">
                            </div>
                            <div style="font-weight: 600;"
                                x-text="'Total AP: ' + accessPoints.filter(a => a.floor === currentFloor).length"></div>
                        </div>
                    </div>
                </div>

                {{-- Rooms --}}
                <template x-for="room in rooms.filter(r => r.floor === currentFloor)" :key="room.id">
                    <div class="absolute flex items-center justify-center rounded shadow-md transition-all cursor-pointer"
                        :style="`
                                                    left: ${room.x_position}%;
                                                    top: ${room.y_position}%;
                                                    width: ${room.width}px;
                                                    height: ${room.height}px;
                                                    background: #93c5fd;
                                                    border: 2px solid #2563eb;
                                                    color: #1e3a8a;
                                                    font-size: 11px;
                                                    font-weight: 700;
                                                    transform: translate(-50%, -50%);
                                                `"
                        :title="room.name" x-text="room.code"
                        onmouseover="this.style.transform='translate(-50%, -50%) scale(1.05)'; this.style.boxShadow='0 10px 15px rgba(0,0,0,0.3)'"
                        onmouseout="this.style.transform='translate(-50%, -50%) scale(1)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                    </div>
                </template>

                {{-- Access Points --}}
                <template x-for="(ap, apIndex) in accessPoints.filter(a => a.floor === currentFloor)"
                    :key="ap.id">
                    <div>
                        <div class="absolute rounded-full cursor-pointer transition-transform"
                            x-on:click="openReportModal(ap)"
                            :style="`
                                                            left: ${calculateAPPosition(ap, apIndex, accessPoints.filter(a => a.floor === currentFloor)).x}%;
                                                            top: ${calculateAPPosition(ap, apIndex, accessPoints.filter(a => a.floor === currentFloor)).y}%;
                                                            width: 14px;
                                                            height: 14px;
                                                            background: ${ap.status === 'active' ? '#10b981' : ap.status === 'maintenance' ? '#fbbf24' : '#ef4444'};
                                                            border: 2px solid white;
                                                            box-shadow: 0 2px 8px rgba(0,0,0,0.3), 0 0 0 1px ${ap.status === 'active' ? '#10b981' : ap.status === 'maintenance' ? '#fbbf24' : '#ef4444'};
                                                            transform: translate(-50%, -50%);
                                                            animation: ${ap.status === 'active' ? 'pulse 2s infinite' : 'none'};
                                                        `"
                            :title="`${ap.name} (${ap.status})`"
                            onmouseover="this.style.transform='translate(-50%, -50%) scale(1.8)'"
                            onmouseout="this.style.transform='translate(-50%, -50%) scale(1)'">
                            {{-- Tooltip --}}
                            <div style="position: absolute; bottom:100%; left: 50%; transform: translateX(-50%); 
                        white-space: nowrap; background: #1f2937; color: white; padding: 6px 10px;
                        border-radius: 4px; font-size: 11px; opacity: 0; pointer-events: none;
                        transition: opacity 0.2s; margin-bottom: 8px; z-index: 100;"
                                class="ap-tooltip">
                                <div x-text="`${ap.name}`"></div>
                                <div style="font-size: 10px; opacity: 0.8;" x-text="`Status: ${ap.status}`"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Modal Laporan Tiket --}}
        <template x-if="showReportModal">
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
                x-on:click.self="showReportModal = false">
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4 text-gray-800">Laporkan Masalah AP</h2>

                    <div class="mb-4 text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                        <strong class="text-gray-800" x-text="selectedAP.name"></strong><br>
                        Status saat ini: <span class="font-medium" x-text="selectedAP.status"></span>
                    </div>

                    <!-- Pilih Teknisi -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Teknisi <span class="text-red-500">*</span>
                        </label>

                        <!-- Loading State -->
                        <div x-show="loadingTechnicians"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm">
                            Memuat teknisi...
                        </div>

                        <!-- Select Dropdown -->
                        <select x-show="!loadingTechnicians" x-model="selectedTechnician"
                            style="color: #111827 !important; background-color: white !important;"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                            <option value="" style="color: #6b7280 !important;">-- Pilih Teknisi --</option>
                            <template x-for="tech in technicians" :key="tech.id">
                                <option :value="tech.id" x-text="tech.name" style="color: #111827 !important;">
                                </option>
                            </template>
                        </select>

                        <p class="text-xs text-gray-500 mt-1"
                            x-show="!loadingTechnicians && technicians.length === 0">
                            Belum ada teknisi yang terdaftar dalam sistem
                        </p>
                    </div>

                    <!-- Deskripsi Masalah -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi Masalah <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="reportDescription" style="color: #111827 !important; background-color: white !important;"
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            rows="4" placeholder="Deskripsikan masalah di Access Point ini..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button x-on:click="showReportModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-sm font-medium text-gray-700 transition">
                            Batal
                        </button>
                        <button x-on:click="submitReport" :disabled="loadingReport"
                            class="px-4 py-2 rounded-lg text-white text-sm font-medium transition hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            style="background: linear-gradient(135deg, #019486 0%, #02635a 100%);">
                            <template x-if="!loadingReport">
                                <span>Kirim Laporan</span>
                            </template>
                            <template x-if="loadingReport">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Mengirim...
                                </span>
                            </template>
                        </button>

                    </div>
                </div>
            </div>
        </template>


        {{-- Modal Popup --}}
        <template x-if="showModal">
            <div class="fixed inset-0 flex items-center justify-center z-50 p-4"
                style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);" x-on:click.self="closeModal()"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100">
                <div class="w-full rounded-2xl shadow-2xl border-2"
                    style="max-width: 28rem; background: white; padding: 24px; border-color: #e5e7eb;"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="font-bold mb-1" style="font-size: 22px; color: #111827;"
                                x-text="selectedBuilding.name"></h2>
                            <p style="font-size: 13px; color: #9ca3af;">Informasi Gedung</p>
                        </div>
                        <button x-on:click="closeModal()" class="transition" style="color: #9ca3af; padding: 4px;"
                            onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div style="margin-bottom: 24px;">
                        <div class="flex items-center gap-3 rounded-lg"
                            style="background: #f9fafb; padding: 12px; margin-bottom: 12px;">
                            <div style="font-size: 24px;">🏫</div>
                            <div>
                                <div style="font-size: 11px; color: #9ca3af; margin-bottom: 2px;">Kode Gedung</div>
                                <div style="font-weight: 600; color: #111827;" x-text="selectedBuilding.code"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg" style="background: #f9fafb; padding: 12px;">
                            <div style="font-size: 24px;">📡</div>
                            <div>
                                <div style="font-size: 11px; color: #9ca3af; margin-bottom: 2px;">Jumlah Access Point
                                </div>
                                <div style="font-weight: 600; color: #111827;"
                                    x-text="selectedBuilding.access_points_count + ' AP'"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg" style="background: #f9fafb; padding: 12px;">
                            <div style="font-size: 24px;">🏢</div>
                            <div>
                                <div style="font-size: 11px; color: #9ca3af; margin-bottom: 2px;">Jumlah Lantai</div>
                                <div style="font-weight: 600; color: #111827;"
                                    x-text="selectedBuilding.total_floors + ' Lantai'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button class="flex-1 rounded-lg transition"
                            style="background: #f3f4f6; color: #374151; padding: 10px 16px; font-weight: 600; font-size: 14px;"
                            x-on:click="closeModal()" onmouseover="this.style.background='#e5e7eb'"
                            onmouseout="this.style.background='#f3f4f6'">
                            Tutup
                        </button>

                        <button class="flex-1 rounded-lg transition shadow-lg"
                            style="background: linear-gradient(135deg, #019486 0%, #02635a 100%); color: white; padding: 10px 16px; font-weight: 600; font-size: 14px;"
                            x-on:click="loadRooms(selectedBuilding.id); closeModal();"
                            onmouseover="this.style.background='linear-gradient(135deg, #02635a 0%, #02635a 100%)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #019486 0%, #02635a 100%)'">
                            Lihat Denah Ruangan →
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- CSS untuk animasi pulse --}}
    <style>
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</x-filament-panels::page>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
