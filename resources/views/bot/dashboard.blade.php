<x-admin.cssloader></x-admin.cssloader>

<div class="container mt-4">

  <div class="row">

    <!-- BOT STATUS -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5>Bot Status</h5>
          <h3 id="price">--</h3>
          <p>RSI: <span id="rsi">--</span></p>
          <p>Mode: <span id="mode">WAIT</span></p>
        </div>
      </div>
    </div>

    <!-- POSITION -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5>Position</h5>
          <p>Entry: $<span id="entry">--</span></p>
          <p>Target: $<span id="target">--</span></p>
          <p>Profit: <span id="profit">--</span>%</p>
        </div>
      </div>
    </div>

    <!-- SETTINGS -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">

          <h5 class="mb-3">Bot Settings</h5>

          <div class="form-group mb-3">
            <label>Target Profit (%)</label>
            <input id="targetProfit" type="number" class="form-control">
          </div>

          <div class="form-group mb-3">
            <label>Minimum Buy (USDT)</label>
            <input id="minBuy" type="number" class="form-control">
          </div>

          <button onclick="saveSettings()" class="btn btn-primary w-100">
            Save Settings
          </button>

        </div>
      </div>
    </div>

  </div>

  <!-- CHART + REASON -->
  <div class="row mt-4">

    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h5>BTC Live Chart</h5>
          <canvas id="chart"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5>Bot Decision</h5>
          <p id="reason">Loading...</p>
        </div>
      </div>
    </div>

  </div>

  <!-- TRADES -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-body">

          <h5>Trade History</h5>

          <table class="table">
            <thead>
              <tr>
                <th>Side</th>
                <th>Price</th>
                <th>Amount</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody id="trades"></tbody>
          </table>

        </div>
      </div>
    </div>
  </div>

</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart;
let isEditing = false;
let lastMode = null;

// ===============================
// 📊 DASHBOARD
// ===============================
async function loadDashboard() {
    try {
        const res = await fetch('/api/bot/status');
        const data = await res.json();

        // PRICE
        document.getElementById('price').innerText =
            data.price ? '$' + Number(data.price).toFixed(2) : '--';

        // RSI
        document.getElementById('rsi').innerText =
            data.rsi ? Number(data.rsi).toFixed(2) : '--';

        // MODE
        const modeEl = document.getElementById('mode');
        modeEl.innerText = data.mode ? data.mode.toUpperCase() : 'WAIT';

        if (data.mode === 'buy') modeEl.style.color = 'green';
        else if (data.mode === 'sell') modeEl.style.color = 'red';
        else modeEl.style.color = 'orange';

        // 🔔 SELL ALERT
        if (lastMode !== data.mode && data.mode === 'sell') {
            alert('🔴 SELL SIGNAL TRIGGERED');
        }
        lastMode = data.mode;

        // SETTINGS
        if (!isEditing) {
            document.getElementById('targetProfit').value = data.settings.target_profit ?? 2;
            document.getElementById('minBuy').value = data.settings.min_buy ?? 5;
        }

        // POSITION
        if (data.last_trade) {
            const entry = parseFloat(data.last_trade.price);
            const current = data.price;

            const profit = ((current - entry) / entry) * 100;

            document.getElementById('entry').innerText = entry.toFixed(2);
            document.getElementById('target').innerText =
                data.target_price ? data.target_price.toFixed(2) : '--';
            document.getElementById('profit').innerText =
                data.profit_percent ?? profit.toFixed(2);
        }

        // 🧠 BOT REASON
        document.getElementById('reason').innerText =
            data.reason ?? 'No signal';

    } catch (e) {
        console.error(e);
    }
}

// ===============================
// 📈 CHART
// ===============================
async function loadChart() {
    try {
        const res = await fetch('/api/bot/chart');
        const prices = await res.json();

        if (!prices || prices.length === 0) return;

        const labels = prices.map((_, i) => i);

        if (chart) chart.destroy();

        chart = new Chart(document.getElementById('chart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'BTC Price',
                    data: prices,
                    borderWidth: 2,
                    tension: 0.3
                }]
            }
        });

    } catch (e) {
        console.error('Chart error', e);
    }
}

// ===============================
// 📜 TRADES
// ===============================
async function loadTrades() {
    const res = await fetch('/api/bot/trades');
    const trades = await res.json();

    let html = '';

    trades.forEach(t => {
        html += `
        <tr>
            <td>${t.side}</td>
            <td>$${parseFloat(t.price).toFixed(2)}</td>
            <td>${t.amount}</td>
            <td>${t.created_at}</td>
        </tr>`;
    });

    document.getElementById('trades').innerHTML = html;
}

// ===============================
// 💾 SETTINGS
// ===============================
async function saveSettings() {
    await fetch('/api/bot/settings', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            bot_target_profit: document.getElementById('targetProfit').value,
            bot_min_buy_usd: document.getElementById('minBuy').value
        })
    });

    alert('Saved');
}

// ===============================
// 🧠 INPUT CONTROL
// ===============================
document.getElementById('targetProfit').addEventListener('focus', () => isEditing = true);
document.getElementById('minBuy').addEventListener('focus', () => isEditing = true);

document.getElementById('targetProfit').addEventListener('blur', () => isEditing = false);
document.getElementById('minBuy').addEventListener('blur', () => isEditing = false);

// ===============================
// 🔁 INTERVALS
// ===============================
setInterval(loadDashboard, 4000);
setInterval(loadTrades, 8000);
setInterval(loadChart, 10000);

// INITIAL LOAD
loadDashboard();
loadTrades();
loadChart();

</script>

<x-admin.jsloader></x-admin.jsloader>