<x-admin.cssloader></x-admin.cssloader>
<div class="container mt-4">

  <div class="row">

    <!-- BOT STATUS -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5>Bot Status</h5>
          <h3 id="price">$0</h3>
          <p>RSI: <span id="rsi"></span></p>
          <p>Mode: <span id="mode"></span></p>
        </div>
      </div>
    </div>

    <!-- POSITION -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h5>Position</h5>
          <p>Entry: $<span id="entry"></span></p>
          <p>Target: $<span id="target"></span></p>
          <p>Profit: <span id="profit"></span>%</p>
        </div>
      </div>
    </div>

    <!-- SETTINGS -->
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
 
      <h5 class="mb-3">Bot Settings</h5>

      <!-- Target Profit -->
      <div class="form-group mb-3">
        <label for="targetProfit" class="form-label">
          Target Profit (%)
        </label>
        <input 
          id="targetProfit"
          type="number"
          step="0.1"
          class="form-control"
          placeholder="e.g 2"
        >
      </div>

            <!-- Min Buy -->
            <div class="form-group mb-3">
                <label for="minBuy" class="form-label">
                Minimum Buy Amount (USDT)
                </label>
                <input 
                id="minBuy"
                type="number"
                step="0.1"
                class="form-control"
                placeholder="e.g 5"
                >
            </div>

            <button onclick="saveSettings()" class="btn btn-primary w-100">
                Save Settings
            </button> 

        </div>
      </div>
    </div>

  </div>

  <div class="row mt-4">

  <!-- 📈 CHART -->
  <div class="col-md-8">
    <div class="card shadow">
      <div class="card-body">
        <h5>BTC Live Chart</h5>
        <canvas id="chart"></canvas>
      </div>
    </div>
  </div>

  <!-- 🧠 BOT REASON -->
  <div class="col-md-4">
    <div class="card shadow">
      <div class="card-body">
        <h5>Bot Decision</h5>
        <p id="reason">Loading...</p>
      </div>
    </div>
  </div>

</div>


  <!-- TRADE TABLE -->
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    async function loadDashboard() {
        try {
            const res = await fetch('/api/bot/status');
            const data = await res.json();

            document.getElementById('price').innerText = '$' + Number(data.price).toFixed(2);
            document.getElementById('rsi').innerText = Number(data.rsi).toFixed(2);

            // ✅ SETTINGS (only when not editing)
            if (!isEditing) {
                document.getElementById('targetProfit').value = data.settings.target_profit ?? 2;
                document.getElementById('minBuy').value = data.settings.min_buy ?? 5;
            }

            // ✅ POSITION
            if (data.last_trade) {
                const entry = parseFloat(data.last_trade.price);
                const current = data.price;
                const profit = ((current - entry) / entry) * 100;

                document.getElementById('entry').innerText = entry.toFixed(2);
                document.getElementById('target').innerText = data.target_price ? data.target_price.toFixed(2) : '-';
                document.getElementById('profit').innerText =   data.profit_percent.toFixed(2);
            }

            // ✅ MODE
            const modeEl = document.getElementById('mode');
            modeEl.innerText = data.mode.toUpperCase();

            modeEl.style.fontWeight = 'bold';

            if (data.mode === 'buy') modeEl.style.color = 'green';
            else if (data.mode === 'sell') modeEl.style.color = 'red';
            else modeEl.style.color = 'orange';

        } catch (e) {
            console.error('Dashboard error:', e);
        }
    }

async function loadTrades() {
    const res = await fetch('/api/bot/trades');
    const trades = await res.json();

    let html = '';
    

    trades.forEach(t => {
        html += `
        <tr>
            <td>${t.side.toUpperCase()}</td>
            <td>$${parseFloat(t.price).toFixed(2)}</td>
            <td>${t.amount}</td>
            <td>${t.created_at}</td>
        </tr>`;
    });

    document.getElementById('trades').innerHTML = html;
}

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

    setInterval(() => {
        loadDashboard();
    }, 5000);

    setInterval(() => {
        loadTrades();
    }, 8000);


    let chart;

    document.getElementById('reason').innerText = data.reason;

    async function loadChart() {
        const res = await fetch('/api/bot/chart');
        const prices = await res.json();

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
    }

    let lastMode = null;

    if (lastMode !== data.mode && data.mode === 'sell') {
        alert('🔴 SELL SIGNAL TRIGGERED');
    }

    lastMode = data.mode;
loadDashboard();
loadTrades();
</script>

<x-admin.jsloader></x-admin.jsloader>