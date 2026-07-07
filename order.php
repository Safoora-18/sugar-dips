<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require 'products.php';

$user  = $_SESSION['user_name']  ?? '';
$phone = $_SESSION['user_phone'] ?? '';
$cart  = $_SESSION['cart']       ?? [];

if (empty($cart)) {
  header('Location: menu.php');
  exit;
}

$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
$count = array_sum(array_column($cart, 'qty'));
$today = date('Y-m-d');

// ── AI Recommendations ────────────────────────────────────────────────
$cartIds  = array_column($cart, 'id');
$cartCats = [];

foreach ($products as $p) {
  if (in_array($p['id'], $cartIds)) $cartCats[] = $p['category'];
}
$cartCats = array_unique($cartCats);

$ruleMap = [
  'Brownies'    => 'Cheesecakes',
  'Cheesecakes' => 'Dates',
  'Dates'       => 'Specials',
  'Specials'    => 'Cookies',
  'Cookies'     => 'Brownies'
];

$recommendations = [];
foreach ($cartCats as $cat) {
  $target = $ruleMap[$cat] ?? null;
  if (!$target) continue;
  foreach ($products as $p) {
    if ($p['category'] === $target && !in_array($p['id'], $cartIds) && count($recommendations) < 3) {
      $recommendations[] = $p;
      break;
    }
  }
}
if (count($recommendations) < 3) {
  foreach ([2,8,14,6,15] as $pid) {
    if (count($recommendations) >= 3) break;
    foreach ($products as $p) {
      if ($p['id'] === $pid && !in_array($pid, $cartIds)) {
        $recommendations[] = $p; break;
      }
    }
  }
}

// ── Time Slots ────────────────────────────────────────────────────────
$timeSlots = [
  '10:00 AM - 12:00 PM' => '🌅 Morning   (10 AM – 12 PM)',
  '12:00 PM - 3:00 PM'  => '☀️ Afternoon (12 PM – 3 PM)',
  '3:00 PM - 6:00 PM'   => '🌤 Evening   (3 PM – 6 PM)',
  '6:00 PM - 9:00 PM'   => '🌙 Night     (6 PM – 9 PM)',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order — Sugar Dips</title>
  <link rel="stylesheet" href="style.css"/>

  <style>
    /* ── Fix Razorpay blocked by cart overlay ── */
    .razorpay-container,
    .razorpay-backdrop,
    iframe.razorpay-checkout-frame {
      z-index: 99999 !important;
    }
    .cart-overlay  { z-index: 900 !important; }
    .cart-sidebar  { z-index: 901 !important; }

    /* ── Time slot grid ── */
    .slot-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.6rem;
      margin-top: 0.5rem;
    }
    .slot-card {
      border: 2px solid var(--border);
      border-radius: 12px;
      padding: 0.65rem 0.9rem;
      cursor: pointer;
      font-size: 0.85rem;
      font-family: var(--font-body);
      background: white;
      color: var(--text);
      text-align: left;
      transition: all 0.2s;
      line-height: 1.5;
    }
    .slot-card:hover  { border-color: var(--pink); background: var(--pink-soft); }
    .slot-card.active { border-color: var(--pink); background: var(--pink-soft); font-weight: 600; color: var(--pink); }
    .slot-card input  { display: none; }

    /* ── Coupon row ── */
    .coupon-row {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.4rem;
    }
    .coupon-row input { flex: 1; text-transform: uppercase; letter-spacing: 0.05em; }
    .coupon-msg { font-size: 0.82rem; min-height: 1.1rem; margin-bottom: 0.5rem; }

    /* ── Price breakdown ── */
    .price-row {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      color: var(--muted);
      padding: 0.2rem 0;
    }
    .price-row.discount { color: #059669; font-weight: 600; }
    .price-divider {
      border: none;
      border-top: 1.5px dashed var(--border);
      margin: 0.6rem 0;
    }

    @media (max-width: 480px) {
      .slot-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">
    <img src="assets/logo.png" alt="Sugar Dips"/>
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="track.php">📦 My Orders</a></li>
    
  </ul>
  <div class="nav-actions">
    <a href="cart.php" class="cart-btn">
      🛒 <span class="cart-badge"><?= $count ?></span>
    </a>
    <span class="user-name">
      👤 <?= htmlspecialchars(explode(' ', $user)[0]) ?>
    </span>
    <a href="logout.php" class="btn-outline"
       style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
  </div>
</nav>

<div class="page order-page" style="margin-top:60px;">
  <div class="order-layout">

    <!-- ══════════ LEFT — FORM ══════════ -->
    <div>
      <h1 class="order-title">📋 Place Your Order</h1>
      <p class="order-sub">Fill in your delivery details below</p>

      <div class="order-form">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" id="fname" class="form-input"
                   value="<?= htmlspecialchars($user) ?>"
                   placeholder="Your name"/>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number *</label>
            <input type="tel" id="fphone" class="form-input"
                   value="<?= htmlspecialchars($phone) ?>"
                   placeholder="+91 9876543210"/>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Delivery Address *</label>
          <textarea id="faddress" class="form-textarea" rows="3"
                    placeholder="Full address with landmark..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Preferred Delivery Date *</label>
          <input type="date" id="fdate" class="form-input" min="<?= $today ?>"/>
        </div>

        <!-- ── Time Slot ── -->
        <div class="form-group">
          <label class="form-label">Delivery Time Slot *</label>
          <div class="slot-grid">
            <?php foreach ($timeSlots as $value => $label): ?>
            <label class="slot-card" id="slot-<?= md5($value) ?>">
              <input type="radio" name="time_slot"
                     value="<?= htmlspecialchars($value) ?>"
                     onchange="selectSlot(this)"/>
              <?= $label ?>
            </label>
            <?php endforeach; ?>
          </div>
          <div id="slot-error"
               style="color:#dc2626;font-size:0.8rem;margin-top:0.4rem;display:none;">
            ⚠️ Please select a delivery time slot
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Special Instructions</label>
          <textarea id="fnotes" class="form-textarea" rows="2"
                    placeholder="Any allergies or special requests..."></textarea>
        </div>

      </div>

      <!-- Recommendations -->
      <?php if (!empty($recommendations)): ?>
      <div class="ai-box">
        <div class="ai-title">🤖 You might also like</div>
        <div class="ai-grid">
          <?php foreach ($recommendations as $r): ?>
          <div class="ai-card">
            <img src="<?= htmlspecialchars($r['image']) ?>"
                 alt="<?= htmlspecialchars($r['name']) ?>"/>
            <div>
              <div class="ai-name"><?= htmlspecialchars($r['name']) ?></div>
              <div class="ai-price">₹<?= $r['price'] ?></div>
            </div>
            <button class="ai-add"
              onclick="addRecommendation(
                <?= $r['id'] ?>,
                '<?= addslashes($r['name']) ?>',
                <?= $r['price'] ?>,
                '<?= $r['image'] ?>'
              )">+ Add</button>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══════════ RIGHT — SUMMARY ══════════ -->
    <div class="summary-card">

      <div class="summary-title">🧾 Order Summary</div>

      <!-- Items list -->
      <?php foreach ($cart as $item): ?>
      <div class="summary-row">
        <span><?= htmlspecialchars($item['name']) ?> ×<?= $item['qty'] ?></span>
        <span>₹<?= $item['price'] * $item['qty'] ?></span>
      </div>
      <?php endforeach; ?>

      <hr class="price-divider"/>

      <!-- Subtotal -->
      <div class="price-row">
        <span>Subtotal</span>
        <span>₹<?= $total ?></span>
      </div>

      <!-- Discount row (hidden until coupon applied) -->
      <div class="price-row discount" id="discount-row" style="display:none;">
        <span>🎟 Discount</span>
        <span>− ₹<span id="discount-amt">0</span></span>
      </div>

      <!-- ── Coupon Box ── -->
      <div style="margin:0.9rem 0 0.25rem;">
        <label class="form-label" style="display:block;margin-bottom:0.4rem;">
          🎟 Have a coupon code?
        </label>
        <div class="coupon-row">
          <input type="text" id="coupon-input" class="form-input"
                 placeholder="e.g. SWEET10 or FLAT50"/>
          <button onclick="applyCoupon()" class="btn-outline"
                  style="padding:0.5rem 0.9rem;white-space:nowrap;font-size:0.85rem;">
            Apply
          </button>
        </div>
        <div class="coupon-msg" id="coupon-msg"></div>
      </div>

      <hr class="price-divider"/>

      <!-- Grand Total -->
      <div class="summary-total">
        <span>Total Amount</span>
        <span>₹<span id="grand-total"><?= $total ?></span></span>
      </div>

      <!-- Error message -->
      <div id="order-error" class="error-msg" style="display:none;"></div>

      <!-- Pay Button -->
      <button id="confirm-btn" class="btn-pink checkout-btn" onclick="payNow()">
        ✓ Confirm &amp; Pay
      </button>

      <p style="font-size:0.72rem;color:var(--muted);text-align:center;margin-top:0.75rem;">
        🔒 Secure payment via Razorpay
      </p>

    </div>
  </div>
</div>

<!-- Razorpay SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>

// ── State ─────────────────────────────────────────────────────────────
let selectedTimeSlot = '';
let appliedDiscount  = 0;
let appliedCoupon    = '';
const BASE_TOTAL     = <?= $total ?>;

// ── Time slot ─────────────────────────────────────────────────────────
function selectSlot(radio) {
  document.querySelectorAll('.slot-card').forEach(c => c.classList.remove('active'));
  radio.closest('.slot-card').classList.add('active');
  selectedTimeSlot = radio.value;
  document.getElementById('slot-error').style.display = 'none';
}

// ── Coupon ────────────────────────────────────────────────────────────
function applyCoupon() {
  const code  = document.getElementById('coupon-input').value.trim();
  const msgEl = document.getElementById('coupon-msg');

  if (!code) {
    msgEl.style.color   = '#dc2626';
    msgEl.textContent   = '⚠️ Please enter a coupon code';
    return;
  }

  fetch('apply_coupon.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ code: code, total: BASE_TOTAL })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      appliedDiscount  = data.discount;
      appliedCoupon    = code.toUpperCase();
      msgEl.style.color   = '#059669';
      msgEl.textContent   = '✅ ' + data.message;
      document.getElementById('discount-amt').textContent  = data.discount;
      document.getElementById('discount-row').style.display = 'flex';
    } else {
      appliedDiscount  = 0;
      appliedCoupon    = '';
      msgEl.style.color   = '#dc2626';
      msgEl.textContent   = '❌ ' + data.message;
      document.getElementById('discount-row').style.display = 'none';
    }
    // Update grand total display
    const finalAmt = Math.max(0, BASE_TOTAL - appliedDiscount);
    document.getElementById('grand-total').textContent = finalAmt;
  })
  .catch(() => {
    msgEl.style.color = '#dc2626';
    msgEl.textContent = '❌ Connection error. Try again.';
  });
}

// ── Add recommendation ────────────────────────────────────────────────
function addRecommendation(id, name, price, image) {
  fetch('cart_action.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action:'add', id, name, price, image, customization:'', qty:1 })
  }).then(() => location.reload());
}

// ── Show error helper ─────────────────────────────────────────────────
function showErr(msg) {
  const errEl = document.getElementById('order-error');
  errEl.textContent   = msg;
  errEl.style.display = 'block';
  const btn = document.getElementById('confirm-btn');
  btn.disabled  = false;
  btn.innerHTML = '✓ Confirm &amp; Pay';
  setTimeout(() => errEl.style.display = 'none', 5000);
}

// ── Main Pay Function ─────────────────────────────────────────────────
function payNow() {

  // 1. Read form fields
  const name    = document.getElementById('fname').value.trim();
  const phone   = document.getElementById('fphone').value.trim();
  const address = document.getElementById('faddress').value.trim();
  const date    = document.getElementById('fdate').value;
  const notes   = document.getElementById('fnotes').value.trim();

  // 2. Validate
  if (!name || !phone || !address || !date) {
    showErr('Please fill all required fields (name, phone, address, date)');
    return;
  }
  if (!selectedTimeSlot) {
    document.getElementById('slot-error').style.display = 'block';
    document.getElementById('slot-error').scrollIntoView({ behavior:'smooth', block:'center' });
    return;
  }

  // 3. Disable button
  const btn    = document.getElementById('confirm-btn');
  btn.disabled  = true;
  btn.innerHTML = '⏳ Opening payment...';

  const cartItems = <?= json_encode(array_values($cart)) ?>;
  const finalAmt  = Math.max(1, BASE_TOTAL - appliedDiscount); // min ₹1

  // 4. Close cart sidebar so it doesn't block Razorpay
  if (typeof closeCart === 'function') closeCart();

  // 5. Open Razorpay
  const options = {
    key:         'rzp_test_SmxqykgpsFsd0e',  // ← Your Key ID
    amount:      finalAmt * 100,              // paise
    currency:    'INR',
    name:        'Sugar Dips 🍫',
    description: 'Dessert Order',
    image:       'assets/logo.png',
    redirect:    false,                       // keeps popup, no redirect
    prefill: {
      name:    name,
      contact: phone
    },
    theme: { color: '#e83e8c' },

    // ── Called after successful payment ─────────────────────────────
    handler: function(response) {
      btn.innerHTML = '⏳ Confirming order...';

      // Save order to DB after payment succeeds
      fetch('place_order.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name,
          phone,
          address,
          date,
          time_slot:          selectedTimeSlot,
          notes,
          items:              cartItems,
          coupon_code:        appliedCoupon,
          discount:           appliedDiscount,
          payment_status:     'paid',
          razorpay_payment_id: response.razorpay_payment_id
        })
      })
      .then(res => res.text())
      .then(text => {
        try {
          const data = JSON.parse(text);
          if (data.success) {
            window.location.href = 'confirm.php';
          } else {
            showErr(data.message || 'Order save failed. Contact support.');
          }
        } catch(e) {
          showErr('Unexpected response from server.');
        }
      })
      .catch(() => showErr('Connection error while saving order.'));
    },

    // ── Called if user closes payment popup ──────────────────────────
    modal: {
      ondismiss: function() {
        btn.disabled  = false;
        btn.innerHTML = '✓ Confirm &amp; Pay';
      }
    }
  };

  const rzp = new Razorpay(options);

  // Handle payment failure (wrong card, etc.)
  rzp.on('payment.failed', function(response) {
    showErr('Payment failed: ' + response.error.description);
  });

  rzp.open();
}

</script>

</body>
</html>