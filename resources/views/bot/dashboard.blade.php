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

          <h5>Settings</h5>

          <input id="targetProfit" class="form-control mb-2" placeholder="Target Profit %" />
          <input id="minBuy" class="form-control mb-2" placeholder="Min Buy USD" />

          <button onclick="saveSettings()" class="btn btn-primary w-100">
            Save
          </button>

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

<script>
async function loadDashboard() {
    const res = await fetch('/api/bot/status');
    const data = await res.json();

    document.getElementById('price').innerText = '$' + data.price.toFixed(2);
    document.getElementById('rsi').innerText = data.rsi.toFixed(2);

    if (data.last_trade) {
        const entry = parseFloat(data.last_trade.price);
        const current = data.price;
        const profit = ((current - entry) / entry) * 100;

        document.getElementById('entry').innerText = entry.toFixed(2);
        document.getElementById('target').innerText = data.target_price?.toFixed(2) || '-';
        document.getElementById('profit').innerText = profit.toFixed(2);
    }

    document.getElementById('targetProfit').value = data.settings.target_profit;
    document.getElementById('minBuy').value = data.settings.min_buy;
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
    loadTrades();
}, 5000);

loadDashboard();
loadTrades();
</script>
<x-admin.jsloader></x-admin.jsloader>