const chartTheme = {
    navy: '#123246',
    ink: '#173143',
    muted: '#7c919d',
    panel: '#9fc5dc',
    panelSoft: 'rgba(159, 197, 220, 0.24)',
    grid: 'rgba(48, 72, 88, 0.10)',
    mood: '#2f6f91',
    moodFillTop: 'rgba(47, 111, 145, 0.26)',
    moodFillBottom: 'rgba(47, 111, 145, 0.02)',
    sleep: '#304858',
    sleepFill: '#6da8cc',
    sleepTrack: 'rgba(159, 197, 220, 0.34)'
};

let latestChartData = null;

function formatDay(dateValue) {
    const date = new Date(`${dateValue}T00:00:00`);
    if (Number.isNaN(date.getTime())) return String(dateValue).slice(5);
    return date.toLocaleDateString('id-ID', { weekday: 'short' });
}

function getFilterValue(type) {
    const filter = document.querySelector(`[data-chart-filter="${type}"]`);
    return filter ? Number(filter.value) : 7;
}

function filterRows(rows, type) {
    const days = getFilterValue(type);
    return rows.slice(-days);
}

function fitCanvas(canvas) {
    const rect = canvas.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;
    const width = Math.max(320, Math.round(rect.width || canvas.width));
    const height = Math.max(230, Math.round(rect.height || 260));
    canvas.width = width * dpr;
    canvas.height = height * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    return { ctx, width, height };
}

function drawRoundRect(ctx, x, y, width, height, radius) {
    const r = Math.min(radius, width / 2, height / 2);
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + width, y, x + width, y + height, r);
    ctx.arcTo(x + width, y + height, x, y + height, r);
    ctx.arcTo(x, y + height, x, y, r);
    ctx.arcTo(x, y, x + width, y, r);
    ctx.closePath();
}

function drawEmpty(ctx, width, height) {
    ctx.fillStyle = chartTheme.muted;
    ctx.font = '600 13px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Belum ada data.', width / 2, height / 2);
}

function drawHorizontalGrid(ctx, area, lines, maxValue, suffix = '') {
    ctx.strokeStyle = chartTheme.grid;
    ctx.fillStyle = chartTheme.muted;
    ctx.lineWidth = 1;
    ctx.font = '600 10px Arial';
    ctx.textAlign = 'center';

    for (let i = 0; i <= lines; i++) {
        const ratio = i / lines;
        const x = area.left + area.width * ratio;
        const value = Math.round(maxValue * ratio);

        ctx.beginPath();
        ctx.setLineDash([5, 7]);
        ctx.moveTo(x, area.top);
        ctx.lineTo(x, area.bottom);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillText(`${value}${suffix}`, x, area.bottom + 18);
    }
}

function drawVerticalGrid(ctx, area, lines, maxValue) {
    ctx.strokeStyle = chartTheme.grid;
    ctx.fillStyle = chartTheme.muted;
    ctx.lineWidth = 1;
    ctx.font = '600 10px Arial';
    ctx.textAlign = 'right';

    for (let i = 0; i <= lines; i++) {
        const value = maxValue - (maxValue / lines) * i;
        const y = area.top + (area.height / lines) * i;
        ctx.beginPath();
        ctx.setLineDash([5, 7]);
        ctx.moveTo(area.left, y);
        ctx.lineTo(area.right, y);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillText(Math.round(value), area.left - 10, y + 4);
    }
}

function makeSmoothPath(ctx, points) {
    points.forEach((point, index) => {
        if (index === 0) {
            ctx.moveTo(point.x, point.y);
            return;
        }

        const previous = points[index - 1];
        const midX = (previous.x + point.x) / 2;
        ctx.bezierCurveTo(midX, previous.y, midX, point.y, point.x, point.y);
    });
}

function drawAreaChart(canvas, rows, options) {
    const { ctx, width, height } = fitCanvas(canvas);
    const area = {
        left: 52,
        top: 24,
        right: width - 18,
        bottom: height - 34
    };
    area.width = area.right - area.left;
    area.height = area.bottom - area.top;

    ctx.clearRect(0, 0, width, height);

    if (!rows.length) {
        drawEmpty(ctx, width, height);
        return;
    }

    const maxValue = options.maxValue;
    drawVerticalGrid(ctx, area, 5, maxValue);

    const points = rows.map((row, index, list) => {
        const value = Math.max(0, Math.min(maxValue, Number(row.value) || 0));
        return {
            x: area.left + (area.width / Math.max(1, list.length - 1)) * index,
            y: area.bottom - (value / maxValue) * area.height,
            value,
            label: formatDay(row.date)
        };
    });

    const gradient = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    gradient.addColorStop(0, chartTheme.moodFillTop);
    gradient.addColorStop(1, chartTheme.moodFillBottom);

    ctx.beginPath();
    makeSmoothPath(ctx, points);
    ctx.lineTo(points[points.length - 1].x, area.bottom);
    ctx.lineTo(points[0].x, area.bottom);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();

    ctx.beginPath();
    makeSmoothPath(ctx, points);
    ctx.strokeStyle = options.color;
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.stroke();

    const best = points.reduce((top, point) => point.value > top.value ? point : top, points[0]);
    ctx.fillStyle = '#fff';
    ctx.shadowColor = 'rgba(18, 50, 70, 0.14)';
    ctx.shadowBlur = 14;
    drawRoundRect(ctx, best.x - 38, Math.max(18, best.y - 48), 76, 32, 8);
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.fillStyle = chartTheme.navy;
    ctx.font = '800 12px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`${best.value.toFixed(1)}`, best.x, Math.max(39, best.y - 28));
    ctx.fillStyle = chartTheme.muted;
    ctx.font = '600 9px Arial';
    ctx.fillText('tertinggi', best.x, Math.max(52, best.y - 16));

    ctx.fillStyle = chartTheme.muted;
    ctx.font = '600 10px Arial';
    points.forEach((point) => ctx.fillText(point.label, point.x, height - 12));
}

function drawHorizontalBarChart(canvas, rows, options) {
    const { ctx, width, height } = fitCanvas(canvas);
    const data = rows.slice(-7);
    const area = {
        left: 58,
        top: 24,
        right: width - 46,
        bottom: height - 34
    };
    area.width = area.right - area.left;
    area.height = area.bottom - area.top;

    ctx.clearRect(0, 0, width, height);

    if (!data.length) {
        drawEmpty(ctx, width, height);
        return;
    }

    const maxValue = options.maxValue;
    drawHorizontalGrid(ctx, area, 4, maxValue, 'j');

    const rowGap = area.height / data.length;
    const barHeight = Math.min(18, rowGap * 0.45);

    data.forEach((row, index) => {
        const value = Math.max(0, Math.min(maxValue, Number(row.value) || 0));
        const y = area.top + rowGap * index + rowGap / 2 - barHeight / 2;
        const fillWidth = Math.max(value > 0 ? 12 : 0, (value / maxValue) * area.width);

        ctx.fillStyle = chartTheme.ink;
        ctx.font = '700 11px Arial';
        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.fillText(formatDay(row.date), area.left - 12, y + barHeight / 2);

        ctx.fillStyle = chartTheme.sleepTrack;
        drawRoundRect(ctx, area.left, y, area.width, barHeight, barHeight / 2);
        ctx.fill();

        const gradient = ctx.createLinearGradient(area.left, 0, area.right, 0);
        gradient.addColorStop(0, chartTheme.sleep);
        gradient.addColorStop(1, chartTheme.sleepFill);
        ctx.fillStyle = gradient;
        drawRoundRect(ctx, area.left, y, fillWidth, barHeight, barHeight / 2);
        ctx.fill();

        ctx.fillStyle = chartTheme.ink;
        ctx.font = '700 10px Arial';
        ctx.textAlign = 'left';
        ctx.fillText(value ? `${value.toFixed(1)}j` : '-', area.right + 8, y + barHeight / 2);
    });
}

function renderCharts() {
    if (!latestChartData) return;

    const moodCanvas = document.getElementById('moodChart');
    const sleepCanvas = document.getElementById('sleepChart');
    if (!moodCanvas || !sleepCanvas) return;

    drawHorizontalBarChart(sleepCanvas, filterRows(latestChartData.sleep, 'sleep'), {
        color: chartTheme.sleep,
        maxValue: 12
    });

    drawAreaChart(moodCanvas, filterRows(latestChartData.mood, 'mood'), {
        color: chartTheme.mood,
        maxValue: 5
    });
}

async function loadCharts() {
    const updated = document.getElementById('chartUpdated');

    try {
        const response = await fetch('api_stats.php', { cache: 'no-store' });
        const data = await response.json();

        if (data.error) {
            if (updated) {
                updated.textContent = data.error;
                updated.classList.add('error');
            }
            return;
        }

        latestChartData = data;
        renderCharts();

        if (updated) {
            updated.textContent = `Grafik terakhir diperbarui pukul ${data.updated_at}`;
            updated.classList.remove('error');
        }
    } catch (error) {
        if (updated) {
            updated.textContent = 'Grafik gagal dimuat.';
            updated.classList.add('error');
        }
    }
}

document.querySelectorAll('[data-chart-filter]').forEach((filter) => {
    filter.addEventListener('change', renderCharts);
});

loadCharts();
setInterval(loadCharts, 10000);
window.addEventListener('resize', renderCharts);
