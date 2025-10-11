<x-filament-panels::page>
    <div
        x-data="{
            showModal: false,
            selectedBuilding: null,
            openModal(building) {
                this.selectedBuilding = building;
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
                this.selectedBuilding = null;
            }
        }"
        class="relative bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl mx-auto shadow-lg"
        style="width: 700px; height: 450px;"
    >
        @php
            $minX = $this->buildings->min('x_position') ?? 0;
            $maxX = $this->buildings->max('x_position') ?: 1;
            $minY = $this->buildings->min('y_position') ?? 0;
            $maxY = $this->buildings->max('y_position') ?: 1;
            $margin = 5;
        @endphp

        {{-- Building markers --}}
        @foreach ($this->buildings as $building)
            @php
                $leftPercent = (($building->x_position - $minX) / max(1, $maxX - $minX)) * (100 - 2 * $margin) + $margin;
                $topPercent  = (($building->y_position - $minY) / max(1, $maxY - $minY)) * (100 - 2 * $margin) + $margin;
            @endphp

            <div
                x-on:click="openModal({ 
                    id: '{{ $building->id }}',
                    name: '{{ $building->name }}',
                    code: '{{ $building->code }}',
                    access_points_count: '{{ $building->access_points_count }}',
                })"
                class="absolute bg-amber-400 text-gray-900 font-semibold 
                       dark:bg-amber-500 dark:text-gray-100
                       rounded-lg px-3 py-2 shadow-md hover:shadow-lg 
                       hover:scale-105 cursor-pointer transition 
                       transform -translate-x-1/2 -translate-y-1/2"
                style="
                    left: {{ number_format($leftPercent, 4) }}%;
                    top: {{ number_format($topPercent, 4) }}%;
                    width: 100px;
                    text-align: center;
                "
                title="{{ $building->name }} ({{ $building->access_points_count }} AP)"
            >
                <strong>{{ $building->code }}</strong>
            </div>
        @endforeach

        {{-- Modal Popup --}}
        <template x-if="showModal">
            <div
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                x-on:click.self="closeModal()"
            >
                <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 
                            rounded-xl p-6 w-96 shadow-2xl border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4" x-text="selectedBuilding.name"></h2>
                    <p><strong>Kode:</strong> <span x-text="selectedBuilding.code"></span></p>
                    <p><strong>Jumlah Access Point:</strong> <span x-text="selectedBuilding.access_points_count"></span></p>

                    <div class="flex justify-end mt-4 space-x-2">
                        <button
                            class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                                   px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                            x-on:click="closeModal()"
                        >
                            Tutup
                        </button>

                        <a
                            :href="`/admin/rooms?building_id=${selectedBuilding.id}`"
                            class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded transition"
                        >
                            Lihat Denah Ruangan
                        </a>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-filament-panels::page>
