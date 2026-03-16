<?php
session_start();

$PIN = getenv('BOOKMARK_PIN') ?: '2608';

// Handle PIN submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (isset($body['pin']) && $body['pin'] === $PIN) {
        $_SESSION['authenticated'] = true;
        echo 'OK';
    } else {
        http_response_code(403);
        echo 'Forbidden';
    }
    exit;
}

// Already authenticated — serve the app
if (!empty($_SESSION['authenticated'])) {
    readfile(__DIR__ . '/bookmark.html');
    exit;
}

// Show PIN login page
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookmark Manager</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#f8f9fc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
         display:flex;align-items:center;justify-content:center;min-height:100vh}
    .card{background:#fff;border-radius:20px;padding:48px 40px;
          box-shadow:0 8px 40px rgba(0,0,0,0.12);text-align:center;width:300px}
    h1{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:8px}
    p{color:#888;font-size:14px;margin-bottom:32px}
    .dots{display:flex;gap:16px;justify-content:center;margin-bottom:32px}
    .dot{width:16px;height:16px;border-radius:50%;border:2px solid #ccc;
         background:transparent;transition:all .15s}
    .dot.filled{background:#7c3aed;border-color:#7c3aed}
    .pad{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
    .key{padding:16px;font-size:20px;font-weight:600;color:#1a1a2e;
         background:#f0f2f8;border:none;border-radius:12px;cursor:pointer;transition:all .15s}
    .key:hover{background:#e0e0f0}
    .key:active{transform:scale(.95)}
    .key.del{font-size:16px;color:#888}
    .err{color:#e53e3e;font-size:13px;margin-top:16px;min-height:20px}
  </style>
</head>
<body>
  <div class="card">
    <h1>Bookmark Manager</h1>
    <p>Enter your 4-digit PIN</p>
    <div class="dots">
      <div class="dot" id="d0"></div>
      <div class="dot" id="d1"></div>
      <div class="dot" id="d2"></div>
      <div class="dot" id="d3"></div>
    </div>
    <div class="pad">
      <button class="key" onclick="press('1')">1</button>
      <button class="key" onclick="press('2')">2</button>
      <button class="key" onclick="press('3')">3</button>
      <button class="key" onclick="press('4')">4</button>
      <button class="key" onclick="press('5')">5</button>
      <button class="key" onclick="press('6')">6</button>
      <button class="key" onclick="press('7')">7</button>
      <button class="key" onclick="press('8')">8</button>
      <button class="key" onclick="press('9')">9</button>
      <div></div>
      <button class="key" onclick="press('0')">0</button>
      <button class="key del" onclick="del()">⌫</button>
    </div>
    <div class="err" id="err"></div>
  </div>
  <script>
    let pin = '';
    function update() {
      for (let i = 0; i < 4; i++)
        document.getElementById('d'+i).classList.toggle('filled', i < pin.length);
    }
    function press(d) {
      if (pin.length >= 4) return;
      pin += d; update();
      if (pin.length === 4) submit();
    }
    function del() { pin = pin.slice(0,-1); update(); }
    async function submit() {
      const res = await fetch('', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({pin}),
      });
      if (res.ok) { window.location.reload(); }
      else {
        document.getElementById('err').textContent = 'Incorrect PIN — try again.';
        pin = ''; update();
      }
    }
    document.addEventListener('keydown', e => {
      if (e.key >= '0' && e.key <= '9') press(e.key);
      else if (e.key === 'Backspace') del();
    });
  </script>
</body>
</html>
