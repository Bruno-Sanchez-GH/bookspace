import '../css/bootstrap.scss';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Chart from 'chart.js/auto';

const chartColors = {
    navy: '#1c2a3f',
    cyan: '#8cc9da',
    coral: '#e48a73',
    slate: '#637086',
    soft: '#e9f2f5',
};

const parseChartData = (value) => {
    try {
        return JSON.parse(value);
    } catch {
        return null;
    }
};

const chartElement = document.getElementById('dashboard-charts');

if (chartElement) {
    const searches = parseChartData(chartElement.dataset.searchesByMonth);
    const saved = parseChartData(chartElement.dataset.savedByCategory);
    const activity = parseChartData(chartElement.dataset.userActivity);
    const distribution = parseChartData(chartElement.dataset.consultedDistribution);

    if (searches) {
        new Chart(document.getElementById('searchesChart'), {
            type: 'line',
            data: {
                labels: searches.labels,
                datasets: [{
                    label: 'Búsquedas',
                    data: searches.values,
                    borderColor: chartColors.coral,
                    backgroundColor: 'rgba(228, 138, 115, 0.18)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }

    if (saved) {
        new Chart(document.getElementById('savedChart'), {
            type: 'bar',
            data: {
                labels: saved.labels,
                datasets: [{
                    label: 'Libros guardados',
                    data: saved.values,
                    backgroundColor: ['#1c2a3f', '#8cc9da', '#e48a73', '#405a7e', '#9ddad1'],
                    borderRadius: 12,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
        });
    }

    if (activity) {
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: activity.labels,
                datasets: [{
                    label: 'Usuarios activos',
                    data: activity.values,
                    borderColor: chartColors.cyan,
                    backgroundColor: 'rgba(140, 201, 218, 0.24)',
                    fill: true,
                    tension: 0.4,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }

    if (distribution) {
        new Chart(document.getElementById('distributionChart'), {
            type: 'doughnut',
            data: {
                labels: distribution.labels,
                datasets: [{
                    data: distribution.values,
                    backgroundColor: ['#1c2a3f', '#8cc9da', '#e48a73', '#6d7d94', '#9ddad1'],
                    borderWidth: 0,
                }],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });
    }
}
