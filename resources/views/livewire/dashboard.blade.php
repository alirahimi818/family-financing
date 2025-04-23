<!-- resources/views/livewire/dashboard.blade.php -->
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Hidden data for charts -->
    <div id="chart-data"
         data-month="{{ json_encode($month, JSON_HEX_QUOT | JSON_HEX_TAG) }}"
         data-year="{{ json_encode($year, JSON_HEX_QUOT | JSON_HEX_TAG) }}"
         data-income="{{ json_encode($incomeData, JSON_HEX_QUOT | JSON_HEX_TAG) }}"
         data-expense="{{ json_encode($expenseData, JSON_HEX_QUOT | JSON_HEX_TAG) }}"
         data-categories="{{ json_encode($categoryData, JSON_HEX_QUOT | JSON_HEX_TAG) }}"
         data-tags="{{ json_encode($tagData, JSON_HEX_QUOT | JSON_HEX_TAG) }}">
    </div>

    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>

    <!-- Filters -->
    <div class="mb-6 flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
        <select wire:model.live="month"
                class="w-full sm:w-1/4 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endforeach
        </select>
        <select wire:model.live="year"
                class="w-full sm:w-1/4 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @foreach($availableYears as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-medium text-gray-600">Total Inventory</h2>
            <p class="text-2xl font-semibold text-gray-800 mt-2">{{ number_format($totalInventory, 2) }} €</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-medium text-gray-600">Active Periodic Transactions</h2>
            <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Income Total:</span>
                    <span class="text-xl font-semibold text-green-600">{{ number_format($activePeriodicIncomeTotal, 2) }} €</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Expense Total:</span>
                    <span class="text-xl font-semibold text-red-600">{{ number_format($activePeriodicExpenseTotal, 2) }} €</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Count:</span>
                    <span class="text-xl font-semibold text-emerald-700">{{ $activePeriodicCount }}</span>
                </div>
            </div>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-medium text-gray-600">Total Transactions</h2>
            <p class="text-2xl font-semibold text-gray-800 mt-2">{{ $totalTransactions }}</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition">
            <h2 class="text-lg font-medium text-gray-600">Monthly Net Change</h2>
            <p class="text-2xl font-semibold {{ $monthlyNetChange >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2">
                {{ number_format($monthlyNetChange, 2) }} €
            </p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Line Chart -->
        <div wire:ignore class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-600 mb-4">Monthly Transaction Trends</h2>
            <canvas id="transactionTrendChart" height="300"></canvas>
        </div>
        <!-- Pie Chart -->
        <div wire:ignore class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-600 mb-4">Category Distribution</h2>
            <canvas id="categoryDistributionChart" height="200"></canvas>
        </div>
        <!-- Bar Chart (Tag Expenses) -->
        <div wire:ignore class="bg-white shadow-md rounded-lg p-6 col-span-1 lg:col-span-2">
            <h2 class="text-lg font-medium text-gray-600 mb-4">Tag Expense Distribution</h2>
            <div class="overflow-x-auto">
                <canvas id="tagExpenseChart" height="400"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-600 mb-4">Recent Transactions</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wider">
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Tags</th>
                    <th class="p-3 text-left">Date</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentTransactions as $transaction)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-800">{{ $transaction->title ?? '-' }}</td>
                        <td class="p-3 text-gray-800">{{ number_format($transaction->amount, 2) }}</td>
                        <td class="p-3 text-gray-800">{{ ucfirst($transaction->type) }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->category?->name ?? '-' }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->tags->pluck('name')->join(', ') ?: '-' }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-3 text-center text-gray-500">No recent transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    @script
    <script>
        // Global selectedTag and tagVisibility
        window.selectedTag = null;
        window.tagVisibility = {};

        // Color palette for ~30 tags
        const tagColors = [
            'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)', 'rgba(83, 102, 255, 0.8)', 'rgba(255, 83, 112, 0.8)',
            'rgba(40, 167, 69, 0.8)', 'rgba(255, 117, 24, 0.8)', 'rgba(128, 0, 128, 0.8)',
            'rgba(0, 128, 128, 0.8)', 'rgba(255, 215, 0, 0.8)', 'rgba(139, 69, 19, 0.8)',
            'rgba(70, 130, 180, 0.8)', 'rgba(220, 20, 60, 0.8)', 'rgba(34, 139, 34, 0.8)',
            'rgba(218, 165, 32, 0.8)', 'rgba(106, 90, 205, 0.8)', 'rgba(255, 69, 0, 0.8)',
            'rgba(0, 191, 255, 0.8)', 'rgba(199, 21, 133, 0.8)', 'rgba(50, 205, 50, 0.8)',
            'rgba(245, 245, 220, 0.8)', 'rgba(138, 43, 226, 0.8)', 'rgba(255, 182, 193, 0.8)',
            'rgba(100, 149, 237, 0.8)', 'rgba(255, 160, 122, 0.8)', 'rgba(32, 178, 170, 0.8)'
        ];

        // Global initializeCharts function
        window.initializeCharts = function (month, year, incomeData, expenseData, categoryData, tagData) {
            try {

                // Destroy existing charts
                if (window.trendChart) window.trendChart.destroy();
                if (window.categoryChart) window.categoryChart.destroy();
                if (window.tagChart) window.tagChart.destroy();

                // Line Chart
                const ctxTrend = document.getElementById('transactionTrendChart')?.getContext('2d');
                if (!ctxTrend) throw new Error('Transaction trend chart canvas not found');
                window.trendChart = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [
                            {
                                label: 'Income',
                                data: incomeData || [],
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Expense',
                                data: expenseData || [],
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {position: 'top'},
                            tooltip: {mode: 'index', intersect: false}
                        },
                        scales: {
                            y: {beginAtZero: true}
                        }
                    }
                });

                // Pie Chart
                const ctxCategory = document.getElementById('categoryDistributionChart')?.getContext('2d');
                if (!ctxCategory) throw new Error('Category distribution chart canvas not found');
                window.categoryChart = new Chart(ctxCategory, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(categoryData || {}),
                        datasets: [{
                            data: Object.values(categoryData || {}),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 159, 64, 0.8)'
                            ],
                            borderColor: 'rgba(255, 255, 255, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {position: 'top'},
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        let value = context.raw || 0;
                                        return `${label}: ${value.toFixed(2)}`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Bar Chart (Tag Expenses)
                const ctxTag = document.getElementById('tagExpenseChart')?.getContext('2d');
                if (!ctxTag) throw new Error('Tag expense chart canvas not found');

                // Assign colors and data to tags
                const tagLabels = Object.keys(tagData);
                const originalTagValues = Object.values(tagData).map(value => isNaN(value) ? 0 : parseFloat(value));

                // Initialize tagVisibility (all visible by default)
                tagLabels.forEach(label => {
                    if (!(label in window.tagVisibility)) {
                        window.tagVisibility[label] = true;
                    }
                });

                // Compute tagValues based on visibility
                const tagValues = tagLabels.map((label, index) =>
                    window.tagVisibility[label] ? originalTagValues[index] : 0
                );

                const backgroundColors = tagLabels.map((label, index) => {
                    const baseColor = tagColors[index % tagColors.length];
                    return label === window.selectedTag ? baseColor.replace('0.8', '1') : baseColor;
                });
                const borderColors = tagLabels.map((label, index) => {
                    const baseColor = tagColors[index % tagColors.length].replace('0.8', '1');
                    return label === window.selectedTag ? 'rgba(0, 0, 0, 1)' : baseColor;
                });

                window.tagChart = new Chart(ctxTag, {
                    type: 'bar',
                    data: {
                        labels: tagLabels,
                        datasets: [{
                            label: 'Expenses by Tag',
                            data: tagValues,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    generateLabels: (chart) => {
                                        const dataset = chart.data.datasets[0];
                                        return chart.data.labels.map((label, index) => ({
                                            text: label,
                                            fillStyle: dataset.backgroundColor[index],
                                            strokeStyle: dataset.borderColor[index],
                                            lineWidth: 1,
                                            hidden: !window.tagVisibility[label],
                                            index: index
                                        }));
                                    }
                                },
                                onClick: (e, legendItem, legend) => {
                                    const index = legendItem.index;
                                    const label = legend.chart.data.labels[index];
                                    // Toggle visibility
                                    window.tagVisibility[label] = !window.tagVisibility[label];
                                    // Update data based on visibility
                                    legend.chart.data.datasets[0].data = tagLabels.map((lbl, idx) =>
                                        window.tagVisibility[lbl] ? originalTagValues[idx] : 0
                                    );
                                    legend.chart.update();
                                    console.log(`Toggled visibility for tag: ${label}, Visible: ${window.tagVisibility[label]}`);
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let value = context.raw || 0;
                                        return `Expense: ${value.toFixed(2)} €`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45,
                                    maxTicksLimit: 30,
                                    callback: function (value, index, values) {
                                        let label = this.getLabelForValue(value);
                                        return label.length > 15 ? label.substring(0, 15) + '...' : label;
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return value.toFixed(2) + ' €';
                                    }
                                }
                            }
                        }
                    }
                });

                // Client-side tag selection
                document.getElementById('tagExpenseChart').onclick = function (evt) {
                    try {
                        const activeElement = window.tagChart.getElementsAtEventForMode(evt, 'nearest', {intersect: true}, false);
                        if (activeElement.length > 0) {
                            window.selectedTag = window.tagChart.data.labels[activeElement[0].index];
                            window.tagChart.data.datasets[0].backgroundColor = tagLabels.map((label, index) => {
                                const baseColor = tagColors[index % tagColors.length];
                                return label === window.selectedTag ? baseColor.replace('0.8', '1') : baseColor;
                            });
                            window.tagChart.data.datasets[0].borderColor = tagLabels.map((label, index) => {
                                const baseColor = tagColors[index % tagColors.length].replace('0.8', '1');
                                return label === window.selectedTag ? 'rgba(0, 0, 0, 1)' : baseColor;
                            });
                            window.tagChart.update();
                            console.log('Selected Tag:', window.selectedTag);
                        }
                    } catch (error) {
                        console.error('Error in tag chart click handler:', error);
                    }
                };

            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        };

        // Function to fetch and parse chart data from DOM
        window.loadChartData = function () {
            try {
                const chartDataEl = document.getElementById('chart-data');
                if (!chartDataEl) throw new Error('Chart data element not found');

                // Parse each attribute with try-catch
                let month, year, incomeData, expenseData, categoryData, tagData;
                try {
                    month = JSON.parse(chartDataEl.dataset.month || '0');
                } catch (e) {
                    console.error('Error parsing data-month:', e, chartDataEl.dataset.month);
                    month = 0;
                }
                try {
                    year = JSON.parse(chartDataEl.dataset.year || '0');
                } catch (e) {
                    console.error('Error parsing data-year:', e, chartDataEl.dataset.year);
                    year = 0;
                }
                try {
                    incomeData = JSON.parse(chartDataEl.dataset.income || '[]');
                } catch (e) {
                    console.error('Error parsing data-income:', e, chartDataEl.dataset.income);
                    incomeData = [];
                }
                try {
                    expenseData = JSON.parse(chartDataEl.dataset.expense || '[]');
                } catch (e) {
                    console.error('Error parsing data-expense:', e, chartDataEl.dataset.expense);
                    expenseData = [];
                }
                try {
                    categoryData = JSON.parse(chartDataEl.dataset.categories || '{}');
                } catch (e) {
                    console.error('Error parsing data-categories:', e, chartDataEl.dataset.categories);
                    categoryData = {};
                }
                try {
                    tagData = JSON.parse(chartDataEl.dataset.tags || '{}');
                } catch (e) {
                    console.error('Error parsing data-tags:', e, chartDataEl.dataset.tags);
                    tagData = {};
                }

                // Call initializeCharts with parsed data
                window.initializeCharts(month, year, incomeData, expenseData, categoryData, tagData);
            } catch (error) {
                console.error('Error loading chart data:', error);
            }
        };

        // Debounce function to prevent multiple rapid calls
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Debounced loadChartData
        const debouncedLoadChartData = debounce(window.loadChartData, 100);

        // Listen for Livewire updates
        window.Livewire.on('refreshCharts', () => {
            debouncedLoadChartData();
        });
    </script>
    @endscript
    <script>
        // Initialize charts on load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.loadChartData();
            }, 1000);
        });
    </script>
</div>
