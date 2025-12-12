@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-700">@yield('title')</h2>
        <button onclick="toggleForm()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Data
        </button>
    </div>

    <!-- Form Tambah Data (Hidden by default) -->
    <div id="formContainer" class="hidden mb-6 bg-gray-50 p-4 rounded-lg">
        @yield('form')
    </div>

    <!-- Grafik Tren -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Tren Distribusi Bulanan</h3>
        <div class="bg-white p-4 rounded-lg shadow-xs">
            <canvas id="trenChart" height="120"></canvas>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto">
        @yield('table')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Toggle form visibility
    function toggleForm() {
        document.getElementById('formContainer').classList.toggle('hidden');
    }

    // Inisialisasi chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trenChart').getContext('2d');
        const trenChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                    label: '@yield('chart-label')',
                    data: @json($totals),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endsection