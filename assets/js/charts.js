function drawChart(canvas, rows, color, maxValue, label) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const padding = 44;
    const plotWidth = width - padding * 2;
    const plotHeight = height - padding * 2;

    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = '#dce3e7';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 5; i++) {
        const y = padding + (plotHeight / 5) * i;
        ctx.beginPath();
        ctx.moveTo(padding, y);
        ctx.lineTo(width - padding, y);
        ctx.stroke();
    }

    ctx.fillStyle = '#304858';
    ctx.font = '14px Arial';
    ctx.fillText(label, padding, 24);

    if (!rows.length) {
        ctx.fillStyle = '#6c7a83';
        ctx.fillText('Belum ada data.', padding, height / 2);
        return;
    }

    const points = rows.map((row, index) => {
        const x = rows.length === 1 ? padding + plotWidth / 2 : padding + (plotWidth / (rows.length - 1)) * index;
        const value = Number(row.value);
        const y = padding + plotHeight - (Math.min(value, maxValue) / maxValue) * plotHeight;
        return { x, y, value, date: row.date };
    });

    ctx.strokeStyle = color;
    ctx.lineWidth = 3;
    ctx.beginPath();
    points.forEach((point, index) => {
        if (index === 0) ctx.moveTo(point.x, point.y);
        else ctx.lineTo(point.x, point.y);
    });
    ctx.stroke();

    points.forEach((point) => {
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.arc(point.x, point.y, 5, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#263640';
        ctx.font = '12px Arial';
        ctx.fillText(point.value, point.x - 8, point.y - 10);
    });

    ctx.fillStyle = '#6c7a83';
    ctx.font = '11px Arial';
    points.forEach((point, index) => {
        if (index === 0 || index === points.length - 1 || index % 3 === 0) {
            ctx.fillText(point.date.slice(5), point.x - 14, height - 16);
        }
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

        drawChart(moodCanvas, data.mood, '#406070', 5, 'Rata-rata mood 14 hari terakhir');
        drawChart(sleepCanvas, data.sleep, '#3f7a58', 12, 'Rata-rata jam tidur 14 hari terakhir');
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
