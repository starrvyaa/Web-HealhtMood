const chartTheme = {
    mood: '#6d5df3',
    moodFillTop: 'rgba(109, 93, 243, 0.28)',
    moodFillBottom: 'rgba(109, 93, 243, 0.02)',
    sleep: '#5d4bf2',
    sleepTrack: 'rgba(109, 93, 243, 0.14)',
    grid: 'rgba(48, 72, 88, 0.08)',
    label: '#9aa8b0',
    title: '#142f42'
};

function formatDay(dateValue) {
    const date = new Date(`${dateValue}T00:00:00`);
    if (Number.isNaN(date.getTime())) return String(dateValue).slice(5);
    return date.toLocaleDateString('id-ID', { weekday: 'short' });
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
    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 13px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Belum ada data.', width / 2, height / 2);
}

function drawGrid(ctx, area, lines, maxValue, suffix = '') {
    ctx.strokeStyle = chartTheme.grid;
    ctx.lineWidth = 1;
    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 11px Arial';
    ctx.textAlign = 'right';

    for (let i = 0; i <= lines; i++) {
        const value = maxValue - (maxValue / lines) * i;
        const y = area.top + (area.height / lines) * i;
        ctx.beginPath();
        ctx.setLineDash([6, 8]);
        ctx.moveTo(area.left, y);
        ctx.lineTo(area.right, y);
        ctx.stroke();
        ctx.setLineDash([]);
        ctx.fillText(`${Math.round(value)}${suffix}`, area.left - 10, y + 4);
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
        left: 54,
        top: 52,
        right: width - 24,
        bottom: height - 38
    };
    area.width = area.right - area.left;
    area.height = area.bottom - area.top;

    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = chartTheme.title;
    ctx.font = '800 15px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(options.title, 22, 28);

    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 12px Arial';
    ctx.textAlign = 'right';
    ctx.fillText('Minggu ini', width - 22, 28);

    if (!rows.length) {
        drawEmpty(ctx, width, height);
        return;
    }

    const maxValue = options.maxValue;
    drawGrid(ctx, area, 4, maxValue);

    const points = rows.slice(-7).map((row, index, list) => {
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
    ctx.lineWidth = 3.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.stroke();

    const best = points.reduce((top, point) => point.value > top.value ? point : top, points[0]);
    ctx.fillStyle = '#fff';
    ctx.shadowColor = 'rgba(109, 93, 243, 0.2)';
    ctx.shadowBlur = 16;
    drawRoundRect(ctx, best.x - 42, Math.max(36, best.y - 54), 84, 36, 8);
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.fillStyle = options.color;
    ctx.font = '800 13px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`${best.value.toFixed(1)}`, best.x, Math.max(58, best.y - 32));
    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 10px Arial';
    ctx.fillText('tertinggi', best.x, Math.max(72, best.y - 18));

    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 11px Arial';
    points.forEach((point) => ctx.fillText(point.label, point.x, height - 14));
}

function drawBarChart(canvas, rows, options) {
    const { ctx, width, height } = fitCanvas(canvas);
    const area = {
        left: 48,
        top: 52,
        right: width - 24,
        bottom: height - 36
    };
    area.width = area.right - area.left;
    area.height = area.bottom - area.top;

    ctx.clearRect(0, 0, width, height);

    ctx.fillStyle = chartTheme.title;
    ctx.font = '800 15px Arial';
    ctx.textAlign = 'left';
    ctx.fillText(options.title, 22, 28);

    ctx.fillStyle = chartTheme.label;
    ctx.font = '600 12px Arial';
    ctx.textAlign = 'right';
    ctx.fillText('Minggu ini', width - 22, 28);

    if (!rows.length) {
        drawEmpty(ctx, width, height);
        return;
    }

    const maxValue = options.maxValue;
    drawGrid(ctx, area, 4, maxValue, 'j');

    const data = rows.slice(-7);
    const slot = area.width / data.length;
    const barWidth = Math.min(24, slot * 0.32);

    data.forEach((row, index) => {
        const value = Math.max(0, Math.min(maxValue, Number(row.value) || 0));
        const x = area.left + slot * index + slot / 2 - barWidth / 2;
        const trackHeight = area.height * 0.82;
        const trackY = area.bottom - trackHeight;
        const barHeight = Math.max(10, (value / maxValue) * trackHeight);
        const y = area.bottom - barHeight;

        ctx.fillStyle = chartTheme.sleepTrack;
        drawRoundRect(ctx, x, trackY, barWidth, trackHeight, barWidth / 2);
        ctx.fill();

        const gradient = ctx.createLinearGradient(0, y, 0, area.bottom);
        gradient.addColorStop(0, '#7d6cff');
        gradient.addColorStop(1, options.color);
        ctx.fillStyle = gradient;
        drawRoundRect(ctx, x, y, barWidth, barHeight, barWidth / 2);
        ctx.fill();

        ctx.fillStyle = chartTheme.label;
        ctx.font = '600 11px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(formatDay(row.date), x + barWidth / 2, height - 14);
    });
}

async function loadCharts() {
    const moodCanvas = document.getElementById('moodChart');
    const sleepCanvas = document.getElementById('sleepChart');
    const updated = document.getElementById('chartUpdated');

    if (!moodCanvas || !sleepCanvas) return;

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

        drawBarChart(sleepCanvas, data.sleep, {
            color: chartTheme.sleep,
            maxValue: 12,
            title: 'Pola Tidur'
        });
        drawAreaChart(moodCanvas, data.mood, {
            color: chartTheme.mood,
            maxValue: 5,
            title: 'Grafik Mood'
        });

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

loadCharts();
setInterval(loadCharts, 10000);
window.addEventListener('resize', loadCharts);
