<x-filament-panels::page>
    <div class="space-y-6">

        {{-- <h1 class="text-2xl font-bold text-white">Dashboard Monitoring Jaringan</h1>  --}}

        {{-- Bagian Statistik di Atas --}}
        <x-filament-widgets::widgets
            :widgets="[
                \App\Filament\Widgets\StatsOverview::class,
            ]"
            class="grid grid-cols-1 gap-4"
        />

        {{-- Bagian Grafik di Bawah --}}
        <x-filament-widgets::widgets
            :widgets="[
                \App\Filament\Widgets\NetworkStatusChart::class,
            ]"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
        />

    </div>
</x-filament-panels::page>
