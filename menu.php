<?php
session_start();
require 'products.php';
$user   = $_SESSION['user_name'] ?? null;
$cart   = $_SESSION['cart']      ?? [];
$count  = array_sum(array_column($cart, 'qty'));
$filter = $_GET['cat'] ?? 'All';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Menu — Sugar Dips</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    /* ── Search bar ── */
    .search-wrap {
      max-width: 480px; margin: 0 auto 1.5rem; position: relative;
    }
    .search-wrap input {
      width: 100%; padding: 0.75rem 1rem 0.75rem 2.8rem;
      border: 2px solid var(--border); border-radius: 50px;
      font-size: 0.95rem; font-family: var(--font-body);
      outline: none; transition: border-color 0.2s;
      box-sizing: border-box;
    }
    .search-wrap input:focus { border-color: var(--pink); }
    .search-wrap .search-icon {
      position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
      font-size: 1rem; pointer-events: none;
    }
    .search-wrap .search-clear {
      position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      font-size: 1rem; color: var(--muted); display: none;
    }
    /* Search result count */
    .search-count {
      text-align: center; font-size: 0.82rem;
      color: var(--muted); margin-bottom: 1rem; min-height: 1.2rem;
    }
    /* No results */
    .no-results {
      text-align: center; padding: 3rem 1rem;
      color: var(--muted); display: none;
    }
    .no-results div { font-size: 3rem; margin-bottom: 0.75rem; }
    /* Highlight search match */
    mark {
      background: #fef3c7; border-radius: 2px;
      padding: 0 2px; color: var(--text);
    }
  </style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo"><img src="assets/Logo.png" alt="Sugar Dips"/></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="index.php#about">About</a></li>
    <li><a href="index.php#contact">Contact</a></li>
    <li><a href="track.php">📦 My Orders</a></li>
  </ul>
  <div class="nav-actions">
    <button onclick="openCart()" class="cart-btn">
      🛒
      <?php if ($count > 0) echo "<span class='cart-badge'>$count</span>"; ?>
    </button>
    <?php if ($user): ?>
      <span class="user-name">👤 <?= htmlspecialchars(explode(' ', $user)[0]) ?></span>
      <a href="logout.php" class="btn-outline" style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn-pink">Login</a>
    <?php endif; ?>
  </div>
</nav>

<?php include 'cart_sidebar.php'; ?>

<div class="page">
  <div class="menu-hero">
    <p class="section-label">✦ Our Menu</p>
    <h1>Sweet Treats for<br><em>Every Occasion</em></h1>
    <p>Handcrafted with premium ingredients &amp; lots of love</p>
  </div>

  <!-- ── Search Bar ── -->
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input
      type="text"
      id="search-input"
      placeholder="Search brownies, cheesecakes, dates..."
      oninput="handleSearch(this.value)"
      autocomplete="off"
    />
    <button class="search-clear" id="search-clear" onclick="clearSearch()">✕</button>
  </div>

  <!-- Result count (shown only while searching) -->
  <div class="search-count" id="search-count"></div>

  <!-- ── Category Filters ── -->
  <div class="filters" id="filter-bar">
    <?php
    $cats = ['All','Brownies','Cheesecakes','Specials','Dates','Cookies'];
    foreach ($cats as $cat):
      $active = $filter === $cat ? 'active' : '';
    ?>
      <a href="menu.php?cat=<?= $cat ?>" class="filter-btn <?= $active ?>"><?= $cat ?></a>
    <?php endforeach; ?>
  </div>

  <!-- ── Products Grid ── -->
  <div class="products-grid" id="products-grid">
    <?php foreach ($products as $p):
      if ($filter !== 'All' && $p['category'] !== $filter) continue;
      $inCart = isset($cart[$p['id']]);
    ?>
    <div class="product-card"
         data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
         data-desc="<?= strtolower(htmlspecialchars($p['desc'])) ?>"
         data-cat="<?= strtolower(htmlspecialchars($p['category'])) ?>">

      <div class="product-img-wrap">
        <img src="<?= $p['image'] ?>" alt="<?= $p['name'] ?>"/>
        <span class="cat-badge"><?= $p['category'] ?></span>
        <?php if (!empty($p['customizable'])): ?>
          <span class="custom-badge">✏️ Customizable</span>
        <?php endif; ?>
        <?php if ($inCart): ?>
          <span class="custom-badge" style="left:auto;right:8px;background:#22c55e">✓ In Cart</span>
        <?php endif; ?>
      </div>

      <div class="product-body">
        <div class="product-name search-target-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-desc search-target-desc"><?= htmlspecialchars($p['desc']) ?></div>
        <div class="product-footer">
          <span class="product-price">₹<?= $p['price'] ?></span>
          <?php if (!empty($p['customizable'])): ?>
            <button class="add-btn" onclick="openModal(<?= $p['id'] ?>)">✏️ Customise</button>
          <?php else: ?>
            <button class="add-btn" onclick="addToCart(
              <?= $p['id'] ?>,
              '<?= addslashes($p['name']) ?>',
              <?= $p['price'] ?>,
              '<?= $p['image'] ?>'
            )">+ Add</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- No results message -->
  <div class="no-results" id="no-results">
    <div>🍫</div>
    <p>No products found for "<strong id="no-results-term"></strong>"</p>
    <button onclick="clearSearch()" class="btn-outline" style="margin-top:0.5rem">Clear Search</button>
  </div>

</div>

<!-- ── Customization Modals (unchanged) ──────────────────────────────── -->
<?php foreach ($products as $p):
  if (empty($p['customizable'])) continue;
?>
<div class="modal-overlay" id="modal-<?= $p['id'] ?>">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title"><?= $p['name'] ?></div>
        <div class="modal-sub">
          <?php
          if     ($p['type']==='box6')   echo 'Choose exactly 6 flavours';
          elseif ($p['type']==='box9')   echo 'Choose exactly 9 flavours';
          elseif ($p['type']==='plain')  echo 'Pick any flavours & quantities';
          elseif ($p['type']==='kunafa') echo 'Choose your size';
          ?>
        </div>
      </div>
      <button class="modal-close" onclick="closeModal(<?= $p['id'] ?>)">✕</button>
    </div>

    <?php if (in_array($p['type'], ['box6','box9'])): ?>
    <div class="progress-wrap">
      <div class="progress-bar">
        <div class="progress-fill" id="pfill-<?= $p['id'] ?>" style="width:0%"></div>
      </div>
      <span class="progress-text" id="ptext-<?= $p['id'] ?>">
        0/<?= $p['type']==='box6' ? 6 : 9 ?> selected
      </span>
    </div>
    <?php endif; ?>

    <?php if ($p['type']==='plain'): ?>
    <div class="plain-counter" id="pcounter-<?= $p['id'] ?>" style="display:none">
      🍫 <span id="pcount-<?= $p['id'] ?>">0</span> brownie(s) selected
    </div>
    <?php endif; ?>

    <div class="flavour-list">
      <?php if ($p['type']==='kunafa'): ?>
        <?php foreach ([['Small','Perfect for 1-2 people',250],['Big','Great for sharing, serves 3-4',560]] as [$lbl,$desc,$price]): ?>
        <div class="size-row" id="size-<?= $p['id'] ?>-<?= $lbl ?>"
             onclick="selectSize(<?= $p['id'] ?>,'<?= $lbl ?>',<?= $price ?>)">
          <div><div class="size-label"><?= $lbl ?></div><div class="size-desc"><?= $desc ?></div></div>
          <div class="size-price">₹<?= $price ?></div>
          <div class="size-check" id="check-<?= $p['id'] ?>-<?= $lbl ?>"></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php
        $emojis = ['Plain'=>'🍫','Nutella'=>'🌰','Ferrero'=>'🍬','Twix'=>'🍭','Salted Caramel'=>'🧂','Oreo'=>'🖤','Pistachio'=>'🌿'];
        foreach ($flavours as $f):
          $fkey = str_replace(' ','_',$f);
        ?>
        <div class="flavour-row" id="frow-<?= $p['id'] ?>-<?= $fkey ?>">
          <div class="flavour-info">
            <span class="flavour-emoji"><?= $emojis[$f] ?></span>
            <div>
              <div class="flavour-name"><?= $f ?></div>
              <?php if ($p['type']==='plain'): ?>
                <div style="font-size:0.7rem;color:var(--muted)">₹<?= $p['price'] ?> each</div>
              <?php endif; ?>
            </div>
          </div>
          <div class="flavour-qty">
            <button class="qty-btn" onclick="decFlavour(<?= $p['id'] ?>,'<?= $f ?>','<?= $p['type'] ?>',<?= $p['type']==='box6'?6:($p['type']==='box9'?9:0) ?>)">−</button>
            <span class="qty-num" id="qty-<?= $p['id'] ?>-<?= $fkey ?>">0</span>
            <button class="qty-btn" onclick="incFlavour(<?= $p['id'] ?>,'<?= $f ?>','<?= $p['type'] ?>',<?= $p['type']==='box6'?6:($p['type']==='box9'?9:0) ?>)">+</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="modal-footer">
      <div>
        <div class="modal-price" id="mprice-<?= $p['id'] ?>" data-base="<?= $p['price'] ?>">₹<?= $p['price'] ?></div>
        <?php if ($p['type']==='plain'): ?>
          <div class="modal-price-sub" id="mpricesub-<?= $p['id'] ?>"></div>
        <?php endif; ?>
      </div>
      <button class="modal-add-btn" id="mbtn-<?= $p['id'] ?>"
        onclick="confirmCustom(<?= $p['id'] ?>,'<?= addslashes($p['name']) ?>',<?= $p['price'] ?>,'<?= $p['type'] ?>','<?= $p['image'] ?>')"
        <?= in_array($p['type'],['box6','box9']) ? 'disabled' : '' ?>>
        <?php
          if     ($p['type']==='box6')   echo 'Pick 6 more';
          elseif ($p['type']==='box9')   echo 'Pick 9 more';
          elseif ($p['type']==='kunafa') echo 'Select a size';
          else                           echo '+ Add to Cart';
        ?>
      </button>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
// ── SEARCH ────────────────────────────────────────────────────────────
function handleSearch(query) {
  const q        = query.trim().toLowerCase();
  const cards    = document.querySelectorAll('.product-card');
  const noRes    = document.getElementById('no-results');
  const countEl  = document.getElementById('search-count');
  const clearBtn = document.getElementById('search-clear');
  const filterBar= document.getElementById('filter-bar');

  clearBtn.style.display = q ? 'block' : 'none';

  if (!q) {
    // Reset everything
    cards.forEach(c => {
      c.style.display = 'block';
      c.querySelector('.search-target-name').innerHTML =
        c.querySelector('.search-target-name').textContent;
      c.querySelector('.search-target-desc').innerHTML =
        c.querySelector('.search-target-desc').textContent;
    });
    noRes.style.display    = 'none';
    countEl.textContent    = '';
    filterBar.style.display = 'flex';
    return;
  }

  // Hide category filters while searching
  filterBar.style.display = 'none';

  let visible = 0;
  cards.forEach(card => {
    const name = card.dataset.name;
    const desc = card.dataset.desc;
    const cat  = card.dataset.cat;

    if (name.includes(q) || desc.includes(q) || cat.includes(q)) {
      card.style.display = 'block';
      visible++;
      // Highlight matches
      highlightText(card.querySelector('.search-target-name'), q);
      highlightText(card.querySelector('.search-target-desc'), q);
    } else {
      card.style.display = 'none';
    }
  });

  if (visible === 0) {
    noRes.style.display = 'block';
    document.getElementById('no-results-term').textContent = query;
    countEl.textContent = '';
  } else {
    noRes.style.display = 'none';
    countEl.textContent = `Showing ${visible} result${visible !== 1 ? 's' : ''} for "${query}"`;
  }
}

function highlightText(el, query) {
  const original = el.textContent;
  const regex    = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
  el.innerHTML   = original.replace(regex, '<mark>$1</mark>');
}

function clearSearch() {
  document.getElementById('search-input').value = '';
  handleSearch('');
}

// ── CART ──────────────────────────────────────────────────────────────
function addToCart(id, name, price, image, customization='', qty=1) {
  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'add', id, name, price, image, customization, qty})
  }).then(() => location.reload());
}

// ── MODALS ────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById('modal-'+id).classList.add('active'); }
function closeModal(id) { document.getElementById('modal-'+id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('active'); });
});

// ── FLAVOUR STATE ─────────────────────────────────────────────────────
const flavourState = {};
const flavours     = ['Plain','Nutella','Ferrero','Twix','Salted Caramel','Oreo','Pistachio'];

function incFlavour(pid, flavour, type, max) {
  if (!flavourState[pid]) flavourState[pid] = {};
  const total = Object.values(flavourState[pid]).reduce((a,b)=>a+b, 0);
  if (max > 0 && total >= max) return;
  flavourState[pid][flavour] = (flavourState[pid][flavour] || 0) + 1;
  updateFlavourUI(pid, type, max);
}
function decFlavour(pid, flavour, type, max) {
  if (!flavourState[pid] || !flavourState[pid][flavour]) return;
  flavourState[pid][flavour]--;
  if (flavourState[pid][flavour] === 0) delete flavourState[pid][flavour];
  updateFlavourUI(pid, type, max);
}
function updateFlavourUI(pid, type, max) {
  const state = flavourState[pid] || {};
  const total = Object.values(state).reduce((a,b)=>a+b, 0);
  flavours.forEach(f => {
    const key = f.replace(/ /g,'_');
    const el  = document.getElementById('qty-'+pid+'-'+key);
    if (el) el.textContent = state[f] || 0;
    const row = document.getElementById('frow-'+pid+'-'+key);
    if (row) row.classList.toggle('selected', !!(state[f] && state[f]>0));
  });
  const pfill = document.getElementById('pfill-'+pid);
  const ptext = document.getElementById('ptext-'+pid);
  if (pfill && ptext && max > 0) {
    pfill.style.width = (total/max*100)+'%';
    const rem = max - total;
    ptext.textContent = total+'/'+max+' selected'+(rem>0?' — pick '+rem+' more':' ✓ Ready!');
  }
  const pcounter = document.getElementById('pcounter-'+pid);
  const pcount   = document.getElementById('pcount-'+pid);
  if (pcounter && pcount) {
    pcounter.style.display = total > 0 ? 'block' : 'none';
    pcount.textContent = total;
  }
  const mpriceEl    = document.getElementById('mprice-'+pid);
  const mpriceSubEl = document.getElementById('mpricesub-'+pid);
  if (type === 'plain' && mpriceEl) {
    const base = parseInt(mpriceEl.dataset.base || '80');
    mpriceEl.textContent = '₹'+(base * total);
    if (mpriceSubEl) mpriceSubEl.textContent = total > 0 ? '₹'+base+' × '+total : '';
  }
  const btn = document.getElementById('mbtn-'+pid);
  if (btn) {
    if (type === 'box6' || type === 'box9') {
      const ready = total === max;
      btn.disabled = !ready;
      btn.textContent = ready ? '+ Add to Cart' : 'Pick '+(max-total)+' more';
    } else if (type === 'plain') {
      btn.disabled = total < 1;
      btn.textContent = total < 1 ? 'Select at least 1' : '+ Add to Cart';
    }
  }
}

// ── KUNAFA ────────────────────────────────────────────────────────────
const sizeState = {};
function selectSize(pid, label, price) {
  sizeState[pid] = {label, price};
  ['Small','Big'].forEach(s => {
    const row   = document.getElementById('size-'+pid+'-'+s);
    const check = document.getElementById('check-'+pid+'-'+s);
    if (row)   row.classList.toggle('selected', s===label);
    if (check) { check.classList.toggle('active', s===label); check.textContent = s===label?'✓':''; }
  });
  document.getElementById('mprice-'+pid).textContent = '₹'+price;
  const btn = document.getElementById('mbtn-'+pid);
  btn.disabled = false; btn.textContent = '+ Add to Cart';
}

// ── CONFIRM CUSTOM ────────────────────────────────────────────────────
function confirmCustom(pid, name, basePrice, type, image) {
  if (type === 'kunafa') {
    if (!sizeState[pid]) return;
    const {label, price} = sizeState[pid];
    addToCart(pid, name+' ('+label+')', price, image, label, 1);
    closeModal(pid); return;
  }
  const state  = flavourState[pid] || {};
  const total  = Object.values(state).reduce((a,b)=>a+b, 0);
  if (total < 1) return;
  const customization = Object.entries(state).map(([f,q]) => f+' x'+q).join(', ');
  const finalName  = name+' ('+customization+')';
  const finalPrice = type==='plain' ? basePrice * total : basePrice;
  const qty        = type==='plain' ? total : 1;
  addToCart(pid, finalName, finalPrice, image, customization, qty);
  closeModal(pid);
}
</script>

</body>
</html>