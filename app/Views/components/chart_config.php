<?php
/**
 * Configuration Chart.js
 * Fonctions JavaScript pour créer des graphiques cohérents
 */
?>

<script>
// Couleurs de l'application
const APP_COLORS = {
    primary: '#240046',
    secondary: '#ff5400',
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    neutral: '#6c757d'
};

// Palette de couleurs pour les graphiques
const CHART_COLORS = [
    APP_COLORS.primary,
    APP_COLORS.secondary,
    APP_COLORS.success,
    APP_COLORS.warning,
    APP_COLORS.danger,
    APP_COLORS.info,
    '#9d4edd',
    '#3c096c',
    '#ff6d00',
    '#34ce57'
];

/**
 * Configuration par défaut pour tous les graphiques
 */
Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
Chart.defaults.color = '#495057';

/**
 * Options communes pour les graphiques
 */
const chartCommonOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                padding: 15,
                font: {
                    size: 12
                },
                usePointStyle: true
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1
        }
    }
};

/**
 * Crée un graphique en ligne
 * @param {string} canvasId - ID du canvas
 * @param {array} labels - Labels de l'axe X
 * @param {array} datasets - Tableaux de données
 * @param {object} customOptions - Options personnalisées
 * @returns {Chart} Instance du graphique
 */
function createLineChart(canvasId, labels, datasets, customOptions = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const defaultDatasets = datasets.map((dataset, index) => ({
        label: dataset.label || 'Dataset ' + (index + 1),
        data: dataset.data,
        borderColor: dataset.borderColor || CHART_COLORS[index % CHART_COLORS.length],
        backgroundColor: dataset.backgroundColor || CHART_COLORS[index % CHART_COLORS.length] + '20',
        tension: dataset.tension || 0.4,
        fill: dataset.fill !== undefined ? dataset.fill : true,
        borderWidth: dataset.borderWidth || 2
    }));
    
    const options = {
        ...chartCommonOptions,
        ...customOptions,
        plugins: {
            ...chartCommonOptions.plugins,
            ...(customOptions.plugins || {})
        }
    };
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: defaultDatasets
        },
        options: options
    });
}

/**
 * Crée un graphique en barres
 * @param {string} canvasId - ID du canvas
 * @param {array} labels - Labels de l'axe X
 * @param {array} datasets - Tableaux de données
 * @param {object} customOptions - Options personnalisées
 * @returns {Chart} Instance du graphique
 */
function createBarChart(canvasId, labels, datasets, customOptions = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const defaultDatasets = datasets.map((dataset, index) => ({
        label: dataset.label || 'Dataset ' + (index + 1),
        data: dataset.data,
        backgroundColor: dataset.backgroundColor || CHART_COLORS[index % CHART_COLORS.length],
        borderColor: dataset.borderColor || CHART_COLORS[index % CHART_COLORS.length],
        borderWidth: dataset.borderWidth || 1,
        borderRadius: dataset.borderRadius || 6
    }));
    
    const options = {
        ...chartCommonOptions,
        ...customOptions,
        plugins: {
            ...chartCommonOptions.plugins,
            ...(customOptions.plugins || {})
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        if (customOptions.valueFormat === 'currency') {
                            return new Intl.NumberFormat('fr-CD', {
                                style: 'currency',
                                currency: 'CDF',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                        return value;
                    }
                },
                ...(customOptions.scales?.y || {})
            },
            x: customOptions.scales?.x || {}
        }
    };
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: defaultDatasets
        },
        options: options
    });
}

/**
 * Crée un graphique circulaire (pie/doughnut)
 * @param {string} canvasId - ID du canvas
 * @param {array} labels - Labels des sections
 * @param {array} data - Données
 * @param {string} type - 'pie' ou 'doughnut'
 * @param {object} customOptions - Options personnalisées
 * @returns {Chart} Instance du graphique
 */
function createPieChart(canvasId, labels, data, type = 'doughnut', customOptions = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const options = {
        ...chartCommonOptions,
        ...customOptions,
        plugins: {
            ...chartCommonOptions.plugins,
            ...(customOptions.plugins || {}),
            tooltip: {
                ...chartCommonOptions.plugins.tooltip,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        
                        if (customOptions.valueFormat === 'currency') {
                            return label + ': ' + new Intl.NumberFormat('fr-CD', {
                                style: 'currency',
                                currency: 'CDF',
                                minimumFractionDigits: 0
                            }).format(value) + ' (' + percentage + '%)';
                        }
                        
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    };
    
    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: CHART_COLORS,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: options
    });
}

/**
 * Formate un nombre en devise (Franc Congolais)
 * @param {number} value
 * @returns {string}
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('fr-CD', {
        style: 'currency',
        currency: 'CDF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value).replace('CDF', 'FC');
}

/**
 * Formate un nombre avec séparateurs de milliers
 * @param {number} value
 * @param {number} decimals
 * @returns {string}
 */
function formatNumber(value, decimals = 0) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(value);
}

/**
 * Détruit un graphique existant pour éviter les fuites mémoire
 * @param {Chart} chart - Instance du graphique
 */
function destroyChart(chart) {
    if (chart && typeof chart.destroy === 'function') {
        chart.destroy();
    }
}
</script>
