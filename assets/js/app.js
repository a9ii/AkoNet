/**
 * AkoNet Web Monitor - Main JavaScript
 */

// ---- Auto Refresh ----
let refreshTimer = null;
let countdown = APP_CONFIG.refreshInterval;
let isRefreshing = false;

function startAutoRefresh() {
    countdown = APP_CONFIG.refreshInterval;
    updateCountdown();
    
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(() => {
        countdown--;
        updateCountdown();
        if (countdown <= 0) {
            loadDashboardData();
            countdown = APP_CONFIG.refreshInterval;
        }
    }, 1000);
}

function updateCountdown() {
    const el = document.getElementById('refreshCountdown');
    if (el) el.textContent = countdown;
}

function manualRefresh() {
    loadDashboardData();
    countdown = APP_CONFIG.refreshInterval;
}

// ---- Dashboard AJAX ----
function loadDashboardData() {
    if (isRefreshing) return;
    isRefreshing = true;

    const btn = document.getElementById('refreshBtn');
    if (btn) btn.classList.add('refreshing');

    const searchInput = document.getElementById('searchInput');
    const search = searchInput ? searchInput.value : '';

    fetch(APP_CONFIG.assetPrefix + 'api/dashboard.php?search=' + encodeURIComponent(search))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateSummaryCards(data.summary);
                updateTable(data.providers);
                updateLastUpdated();
            }
        })
        .catch(err => console.error('Refresh failed:', err))
        .finally(() => {
            isRefreshing = false;
            if (btn) btn.classList.remove('refreshing');
        });
}

function updateSummaryCards(summary) {
    const totalEl = document.getElementById('totalCount');
    const upEl = document.getElementById('upCount');
    const downEl = document.getElementById('downCount');
    
    if (totalEl) animateNumber(totalEl, parseInt(totalEl.textContent), summary.total);
    if (upEl) animateNumber(upEl, parseInt(upEl.textContent), summary.up_count);
    if (downEl) animateNumber(downEl, parseInt(downEl.textContent), summary.down_count);
}

function animateNumber(el, from, to) {
    const duration = 400;
    const start = performance.now();
    from = parseInt(from) || 0;
    to = parseInt(to) || 0;
    
    function update(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(from + (to - from) * eased);
        el.textContent = current;
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

function updateTable(providers) {
    const tbody = document.getElementById('providersBody');
    if (!tbody) return;

    if (providers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No providers found.</td></tr>';
        return;
    }

    tbody.innerHTML = providers.map(p => {
        const isDown = p.status === 'down';
        const initials = p.name.substring(0, 2).toUpperCase();

        // Avatar: show logo image (transparent bg) if it exists, else show initials (purple bg)
        const avatarHtml = p.logo
            ? `<div class="provider-avatar provider-avatar--image"><img src="${APP_CONFIG.assetPrefix}assets/img/logos/${escapeHtml(p.logo)}" alt="${escapeHtml(p.name)}" loading="lazy"></div>`
            : `<div class="provider-avatar">${initials}</div>`;

        const statusBadge = isDown
            ? '<span class="pulse-danger"><span class="badge status-down px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>DOWN</span></span>'
            : (p.status === 'up'
                ? '<span class="badge status-up px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>UP</span>'
                : '<span class="badge status-unknown px-3 py-2"><i class="bi bi-question-circle-fill me-1"></i>UNKNOWN</span>');
        
        const pingVal = (!p.ping || p.ping == 0) ? '<span class="text-muted">—</span>' : (() => {
            const cls = p.ping < 50 ? 'text-success' : (p.ping < 100 ? 'text-warning' : 'text-danger');
            return `<span class="${cls}">${parseFloat(p.ping).toFixed(2)} ms</span>`;
        })();

        const lossVal = (p.packet_loss === null || p.packet_loss === undefined) ? '<span class="text-muted">—</span>' : (() => {
            const cls = p.packet_loss == 0 ? 'text-success' : (p.packet_loss < 10 ? 'text-warning' : 'text-danger');
            return `<span class="${cls}">${parseFloat(p.packet_loss).toFixed(2)}%</span>`;
        })();

        return `
            <tr class="${isDown ? 'row-down' : ''}" onclick="window.location='provider.php?id=${p.id}'" style="cursor:pointer;">
                <td>
                    <a href="provider.php?id=${p.id}" class="provider-name">
                        ${avatarHtml}
                        <div>
                            <div>${escapeHtml(p.name)}</div>
                            <small class="text-muted">${escapeHtml(p.host || '')}</small>
                        </div>
                    </a>
                </td>
                <td>${statusBadge}</td>
                <td>${pingVal}</td>
                <td>${lossVal}</td>
                <td class="text-center">
                    <a href="provider.php?id=${p.id}" class="btn btn-sm btn-outline-primary border-0" title="View details">
                        <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </td>
            </tr>`;
    }).join('');
}

function updateLastUpdated() {
    const el = document.getElementById('lastUpdateTime');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ---- Search Debounce ----
let searchTimeout = null;
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadDashboardData();
            }, 400);
        });
    }

    // Start auto-refresh on dashboard page
    if (document.getElementById('providersBody')) {
        startAutoRefresh();
    }

    // ---- Provider Detail Charts ----
    if (typeof PROVIDER_ID !== 'undefined') {
        loadProviderCharts(PROVIDER_ID);
    }
});

// ---- Provider Charts ----
function loadProviderCharts(providerId) {
    fetch(APP_CONFIG.assetPrefix + 'api/provider_details.php?id=' + providerId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderPacketLossChart(data.monitoring_logs);
                renderDowntime24hChart(data.downtime_24h);
                renderDowntimeDailyChart(data.downtime_daily);
            }
        })
        .catch(err => console.error('Charts failed:', err));
}

function renderPacketLossChart(logs) {
    const container = document.getElementById('chartPacketLoss');
    if (!container) return;

    const series = logs.map(l => ({
        x: new Date(l.checked_at).getTime(),
        y: parseFloat(l.packet_loss) || 0
    }));

    const options = {
        series: [{ name: 'Packet Loss', data: series }],
        chart: {
            type: 'area',
            height: 300,
            background: 'transparent',
            foreColor: '#94a3b8',
            toolbar: { show: true, tools: { download: true, selection: false, zoom: true, zoomin: true, zoomout: true, pan: false, reset: true } },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        colors: ['#f59e0b'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            type: 'datetime',
            labels: { style: { colors: '#64748b', fontSize: '11px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            min: 0,
            max: function(max) { return Math.max(max + 5, 10); },
            labels: {
                formatter: val => val.toFixed(1) + '%',
                style: { colors: '#64748b', fontSize: '11px' }
            }
        },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 4,
            padding: { left: 10, right: 10 }
        },
        tooltip: {
            theme: 'dark',
            x: { format: 'HH:mm' },
            y: { formatter: val => val.toFixed(2) + '%' }
        },
        dataLabels: { enabled: false }
    };

    if (series.length === 0) {
        container.innerHTML = '<div class="empty-state py-5"><i class="bi bi-graph-up"></i><p>No data available for the last 24 hours.</p></div>';
        return;
    }

    new ApexCharts(container, options).render();
}

function renderDowntime24hChart(downtimes) {
    const container = document.getElementById('chartDowntime24h');
    if (!container) return;

    if (!downtimes || downtimes.length === 0) {
        container.innerHTML = '<div class="empty-state py-5"><i class="bi bi-emoji-smile"></i><p>No downtime in the last 24 hours!</p></div>';
        return;
    }

    const data = downtimes.map(d => ({
        x: 'Downtime',
        y: [
            new Date(d.started_at).getTime(),
            d.ended_at ? new Date(d.ended_at).getTime() : new Date().getTime()
        ]
    }));

    const options = {
        series: [{ data: data }],
        chart: {
            type: 'rangeBar',
            height: 280,
            background: 'transparent',
            foreColor: '#94a3b8',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        plotOptions: {
            bar: { horizontal: true, barHeight: '60%', rangeBarGroupRows: true }
        },
        colors: ['#ef4444'],
        fill: {
            type: 'gradient',
            gradient: { gradientToColors: ['#f87171'], inverseColors: false, stops: [0, 100] }
        },
        xaxis: {
            type: 'datetime',
            labels: {
                format: 'HH:mm',
                style: { colors: '#64748b', fontSize: '11px' }
            },
            axisBorder: { show: false }
        },
        yaxis: {
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 4
        },
        tooltip: {
            theme: 'dark',
            x: { format: 'dd MMM HH:mm' }
        }
    };

    new ApexCharts(container, options).render();
}

function renderDowntimeDailyChart(daily) {
    const container = document.getElementById('chartDowntimeDaily');
    if (!container) return;

    if (!daily || daily.length === 0) {
        container.innerHTML = '<div class="empty-state py-5"><i class="bi bi-emoji-smile"></i><p>No downtime recorded recently.</p></div>';
        return;
    }

    const options = {
        series: [{
            name: 'Downtime',
            data: daily.map(d => ({ x: d.day, y: parseInt(d.total_minutes) || 0 }))
        }],
        chart: {
            type: 'bar',
            height: 280,
            background: 'transparent',
            foreColor: '#94a3b8',
            toolbar: { show: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 }
        },
        plotOptions: {
            bar: { borderRadius: 6, columnWidth: '50%' }
        },
        colors: ['#6366f1'],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                gradientToColors: ['#818cf8'],
                type: 'vertical',
                stops: [0, 100]
            }
        },
        xaxis: {
            categories: daily.map(d => d.day),
            labels: { style: { colors: '#64748b', fontSize: '11px' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            min: 0,
            labels: {
                formatter: val => val + ' min',
                style: { colors: '#64748b', fontSize: '11px' }
            }
        },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 4
        },
        tooltip: {
            theme: 'dark',
            y: { formatter: val => val + ' minutes' }
        },
        dataLabels: { enabled: false }
    };

    new ApexCharts(container, options).render();
}
