<?php
session_start();
$user  = $_SESSION['user_name'] ?? null;
$cart  = $_SESSION['cart']      ?? [];
$count = array_sum(array_column($cart, 'qty'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sugar Dips — Freshly Baked with Love</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">
    <img src="assets/Logo.png" alt="Sugar Dips" />
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="menu.php">Menu</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <button onclick="openCart()" class="cart-btn">
      🛒
      <?php if ($count > 0) echo "<span class='cart-badge'>$count</span>"; ?>
    </button>
    <?php if ($user): ?>
      <span class="user-name">👤 <?= htmlspecialchars(explode(' ',$user)[0]) ?></span>
      <a href="logout.php" class="btn-outline"
         style="font-size:0.78rem;padding:0.35rem 0.9rem;">Logout</a>
    <?php else: ?>
      <a href="login.php" class="btn-pink">Login</a>
    <?php endif; ?>
  </div>
</nav>

<?php include 'cart_sidebar.php'; ?>

<!-- HERO -->
<section class="hero page">
  <div class="blob1"></div>
  <div class="blob2"></div>
  <div class="hero-content">
    <span class="hero-tag">🍫 Homemade · Premium · Bangalore</span>
    <h1>
      Crafted with Love,<br>
      <span class="pink">Dipped in</span>
      <span class="teal">Sweetness</span>
    </h1>
    <p>Brownies, cheesecakes, premium dates & festive specials —<br>
       made fresh, delivered to your door.</p>
    <div class="hero-actions">
      <a href="menu.php" class="btn-pink">Explore Menu ✦</a>
      <a href="#about" class="btn-secondary">Our Story →</a>
    </div>
    <div class="hero-badges">
      <span class="hero-badge">🎁 Gift Boxes</span>
      <span class="hero-badge">🌙 Festive Specials</span>
      <span class="hero-badge">🚚 Home Delivery</span>
    </div>
  </div>
  <div class="hero-visual">
    <div class="logo-ring">
      <img src="assets/Logo.png" alt="Sugar Dips" />
    </div>
    <div class="float-card fc1">🍰 Biscoff Cheesecake<small>₹950</small></div>
    <div class="float-card fc2">🍫 Brownie Slab<small>₹999</small></div>
    <div class="float-card fc3">⭐ 100+ Happy Customers</div>
  </div>
</section>

<!-- ── DESSERT EMOTION ENGINE ── -->
<section class="emotion-section" id="emotion-engine">
  <div class="emotion-inner">

    <div class="emotion-header">
      <p class="section-label">✦ Dessert Emotion Engine</p>
      <h2 class="emotion-title">
        What kind of comfort do you<br>need today? ☁️
      </h2>
      <p class="emotion-sub">Pick your mood and we'll find your perfect treat</p>
    </div>

    <div class="mood-grid" id="moodGrid">
      <div class="mood-card" onclick="selectMood('stressed')"    data-mood="stressed">
        <div class="mood-glow"></div>
        <div class="mood-emoji">😭</div>
        <div class="mood-label">Stressed</div>
        <div class="mood-desc">Need chocolate therapy</div>
      </div>
      <div class="mood-card" onclick="selectMood('celebrating')" data-mood="celebrating">
        <div class="mood-glow"></div>
        <div class="mood-emoji">🎉</div>
        <div class="mood-label">Celebrating</div>
        <div class="mood-desc">Something festive!</div>
      </div>
      <div class="mood-card" onclick="selectMood('heartbroken')" data-mood="heartbroken">
        <div class="mood-glow"></div>
        <div class="mood-emoji">💔</div>
        <div class="mood-label">Heartbroken</div>
        <div class="mood-desc">I need a hug in food form</div>
      </div>
      <div class="mood-card" onclick="selectMood('sleepy')"      data-mood="sleepy">
        <div class="mood-glow"></div>
        <div class="mood-emoji">😴</div>
        <div class="mood-label">Sleepy</div>
        <div class="mood-desc">Late night cravings</div>
      </div>
      <div class="mood-card" onclick="selectMood('happy')"       data-mood="happy">
        <div class="mood-glow"></div>
        <div class="mood-emoji">😄</div>
        <div class="mood-label">Happy</div>
        <div class="mood-desc">Spread the sweetness</div>
      </div>
      <div class="mood-card" onclick="selectMood('anxious')"     data-mood="anxious">
        <div class="mood-glow"></div>
        <div class="mood-emoji">😨</div>
        <div class="mood-label">Anxious</div>
        <div class="mood-desc">Need something calming</div>
      </div>
      <div class="mood-card" onclick="selectMood('bored')"       data-mood="bored">
        <div class="mood-glow"></div>
        <div class="mood-emoji">🙄</div>
        <div class="mood-label">Bored</div>
        <div class="mood-desc">Surprise me!</div>
      </div>
      <div class="mood-card" onclick="selectMood('inlove')"      data-mood="inlove">
        <div class="mood-glow"></div>
        <div class="mood-emoji">🥰</div>
        <div class="mood-label">In Love</div>
        <div class="mood-desc">Share the sweetness</div>
      </div>
    </div>

    <!-- RESULT PANEL -->
    <div class="mood-result" id="moodResult" style="display:none">
      <div class="result-particles" id="resultParticles"></div>
      <div class="result-inner">
        <div class="result-left">
          <div class="result-quote"  id="resultQuote"></div>
          <h3 class="result-heading" id="resultHeading"></h3>
          <p  class="result-text"    id="resultText"></p>
          <div class="result-product-cards" id="resultProductCards"></div>
          <div class="result-ai-box" id="resultAiBox">
            <span class="ai-label">🤖 Why this recommendation?</span>
            <p class="ai-reason" id="aiReason"></p>
          </div>
          <div class="result-actions">
            <button class="result-reset" onclick="resetMood()">← Try another mood</button>
          </div>
        </div>
        <div class="result-right">
          <div class="result-big-emoji"   id="resultEmoji"></div>
          <div class="result-float-items" id="resultFloats"></div>
        </div>
      </div>
    </div>

    <div class="cart-toast" id="cartToast">🛒 Added to cart!</div>
  </div>
</section>

<!-- ABOUT -->
<section id="about" class="about">
  <div class="about-inner">
    <div class="about-img-side">
      <div class="about-ring">
        <img src="assets/Logo.png" alt="Sugar Dips" />
      </div>
      <div class="stats-grid">
        <div class="stat">
          <div class="stat-num">100+</div>
          <div class="stat-label">Happy Customers</div>
        </div>
        <div class="stat">
          <div class="stat-num">14+</div>
          <div class="stat-label">Menu Items</div>
        </div>
        <div class="stat">
          <div class="stat-num">100%</div>
          <div class="stat-label">Homemade</div>
        </div>
        <div class="stat">
          <div class="stat-num">5★</div>
          <div class="stat-label">Rated</div>
        </div>
      </div>
    </div>
    <div class="about-text">
      <p class="section-label">✦ Our Story</p>
      <h2 class="section-title">
        Made with <em>Passion</em>,<br>Served with <em>Love</em>
      </h2>
      <p class="about-body">Sugar Dips is a Bangalore-based homemade dessert brand
        crafted with heart. What started as a passion for baking turned into a
        full-fledged love affair with brownies, cheesecakes, premium dates,
        and festive treats.</p>
      <p class="about-body">Every product is made fresh using premium ingredients —
        no preservatives, no shortcuts. Just pure, handcrafted sweetness
        delivered right to your door.</p>
      <div class="perks">
        <div class="perk">🌿 Fresh ingredients daily</div>
        <div class="perk">🎁 Custom gift boxes</div>
        <div class="perk">🌙 Festive & seasonal specials</div>
        <div class="perk">🚚 Home delivery in Bangalore</div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section id="contact" class="cta-strip">
  <p class="section-label">✦ Ready to Order?</p>
  <h2 class="section-title">Start your <em>sweetest</em> order today.</h2>
  <p>DM us on Instagram or WhatsApp, or order directly from our website!</p>
  <div class="cta-btns">
    <a href="https://instagram.com/sugardipzz" target="_blank" class="btn-insta">📸 Instagram</a>
    <a href="https://wa.me/919739956250"        target="_blank" class="btn-wa">💬 WhatsApp</a>
    <a href="menu.php" class="btn-pink">🍫 Order Online</a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-top">
    <div class="footer-logo">
      <img src="assets/Logo.png" alt="Sugar Dips" />
      <p class="footer-tagline">Handcrafted sweetness,<br>delivered with love 🍫</p>
    </div>
    <div class="footer-links">
      <div class="footer-col">
        <div class="footer-col-title">Quick Links</div>
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="#about">About</a>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Contact</div>
        <a href="https://instagram.com/sugardipzz" target="_blank">📸 @sugardipzz</a>
        <a href="https://wa.me/919739956250"        target="_blank">💬 WhatsApp</a>
        <span>📍 Bangalore, India</span>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    © 2025 Sugar Dips. Made with ♥ in Bangalore.
  </div>
</footer>

<!-- ══ EMOTION ENGINE STYLES ══════════════════ -->
<style>
.emotion-section {
  padding: 6rem 4rem; background: var(--offwhite);
  position: relative; overflow: hidden;
  transition: background 1s cubic-bezier(0.4,0,0.2,1);
}
.emotion-section::before {
  content:''; position:absolute;
  width:500px; height:500px; border-radius:50%;
  top:-150px; right:-150px; pointer-events:none;
  background:radial-gradient(circle,rgba(232,66,122,0.07) 0%,transparent 70%);
  transition:background 1s ease;
}
.emotion-inner  { max-width:1100px; margin:0 auto; position:relative; z-index:1; }
.emotion-header { text-align:center; margin-bottom:3rem; }
.emotion-title  {
  font-family:var(--font-display);
  font-size:clamp(1.8rem,3vw,2.6rem);
  color:var(--text); line-height:1.2; margin-bottom:0.75rem;
  transition:color 0.8s ease;
}
.emotion-sub { font-size:0.95rem; color:var(--muted); font-weight:300; }

.mood-grid {
  display:grid; grid-template-columns:repeat(4,1fr);
  gap:1rem; margin-bottom:2.5rem;
}
.mood-card {
  background:rgba(255,255,255,0.65);
  backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px);
  border:1.5px solid rgba(232,66,122,0.1);
  border-radius:24px; padding:1.75rem 1rem;
  text-align:center; cursor:pointer;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  position:relative; overflow:hidden;
}
.mood-glow {
  position:absolute; inset:0; border-radius:24px; opacity:0;
  background:radial-gradient(circle at 50% 50%,rgba(232,66,122,0.15) 0%,transparent 70%);
  transition:opacity 0.4s ease;
}
.mood-card:hover .mood-glow,
.mood-card.active .mood-glow { opacity:1; }
.mood-card:hover {
  transform:translateY(-10px) scale(1.02);
  box-shadow:0 24px 50px rgba(0,0,0,0.12);
}
.mood-card.active {
  transform:translateY(-10px) scale(1.03);
  box-shadow:0 0 0 3px rgba(0,0,0,0.1),0 24px 60px rgba(0,0,0,0.15);
}
.mood-emoji {
  font-size:2.75rem; margin-bottom:0.65rem; display:block;
  animation:float-emoji 3.5s ease-in-out infinite;
}
.mood-card:nth-child(2) .mood-emoji{animation-delay:0.4s}
.mood-card:nth-child(3) .mood-emoji{animation-delay:0.8s}
.mood-card:nth-child(4) .mood-emoji{animation-delay:1.2s}
.mood-card:nth-child(5) .mood-emoji{animation-delay:0.2s}
.mood-card:nth-child(6) .mood-emoji{animation-delay:0.6s}
.mood-card:nth-child(7) .mood-emoji{animation-delay:1.0s}
.mood-card:nth-child(8) .mood-emoji{animation-delay:1.4s}
@keyframes float-emoji {
  0%,100%{transform:translateY(0) rotate(-2deg)}
  50%    {transform:translateY(-7px) rotate(2deg)}
}
.mood-label {
  font-family:var(--font-display); font-size:1rem;
  font-style:italic; color:var(--text);
  margin-bottom:0.3rem; font-weight:600; transition:color 0.6s ease;
}
.mood-desc { font-size:0.72rem; color:var(--muted); font-weight:300; }

.mood-result {
  position:relative; border-radius:32px; padding:2.5rem;
  overflow:hidden;
  animation:resultReveal 0.6s cubic-bezier(0.34,1.2,0.64,1);
  transition:background 0.8s ease,border-color 0.8s ease,box-shadow 0.8s ease;
}
@keyframes resultReveal {
  from{opacity:0;transform:translateY(32px) scale(0.97)}
  to  {opacity:1;transform:translateY(0) scale(1)}
}
.result-particles{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:0}
.rp{position:absolute;border-radius:50%;animation:rpFloat linear infinite}
@keyframes rpFloat{
  0%  {transform:translateY(100%) scale(0);opacity:0}
  10% {opacity:0.7}
  90% {opacity:0.3}
  100%{transform:translateY(-120%) scale(1.2);opacity:0}
}
.result-inner{
  display:grid;grid-template-columns:1fr 200px;
  gap:2rem;align-items:center;position:relative;z-index:1;
}
.result-quote{
  font-size:0.85rem;font-style:italic;
  margin-bottom:0.85rem;font-weight:500;line-height:1.5;
  animation:fadeSlideIn 0.5s ease 0.1s both;transition:color 0.6s ease;
}
.result-heading{
  font-family:var(--font-display);font-size:1.55rem;
  margin-bottom:0.6rem;font-style:italic;
  animation:fadeSlideIn 0.5s ease 0.2s both;transition:color 0.6s ease;
}
.result-text{
  font-size:0.88rem;line-height:1.75;
  margin-bottom:1.25rem;font-weight:300;
  animation:fadeSlideIn 0.5s ease 0.3s both;transition:color 0.6s ease;
}
@keyframes fadeSlideIn{
  from{opacity:0;transform:translateX(-16px)}
  to  {opacity:1;transform:translateX(0)}
}

.result-product-cards{
  display:flex;flex-direction:column;gap:0.75rem;
  margin-bottom:1.25rem;
  animation:fadeSlideIn 0.5s ease 0.4s both;
}
.result-product-card{
  display:flex;align-items:center;justify-content:space-between;
  padding:0.85rem 1rem;border-radius:16px;gap:0.75rem;
  transition:all 0.25s ease;border:1.5px solid transparent;
}
.result-product-card:hover{transform:translateX(4px)}
.rpc-left{display:flex;align-items:center;gap:0.75rem;flex:1}
.rpc-img{
  width:48px;height:48px;border-radius:10px;
  object-fit:cover;flex-shrink:0;
  box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
.rpc-name{font-size:0.85rem;font-weight:600;line-height:1.3;margin-bottom:2px;transition:color 0.6s ease}
.rpc-price{font-size:0.78rem;font-weight:700;transition:color 0.6s ease}
.rpc-add-btn{
  border:none;border-radius:50px;
  padding:0.45rem 1rem;font-size:0.78rem;
  font-weight:700;cursor:pointer;font-family:var(--font-body);
  transition:all 0.25s cubic-bezier(0.34,1.56,0.64,1);
  white-space:nowrap;flex-shrink:0;
}
.rpc-add-btn:hover{transform:scale(1.08)}
.rpc-add-btn.added{background:#22c55e !important;color:white !important}

.result-ai-box{
  border-radius:14px;padding:0.9rem 1.1rem;
  margin-bottom:1.25rem;
  animation:fadeSlideIn 0.5s ease 0.55s both;
  transition:background 0.6s ease,border-color 0.6s ease;
  border:1px solid transparent;
}
.ai-label{
  font-size:0.72rem;font-weight:700;text-transform:uppercase;
  letter-spacing:0.06em;display:block;margin-bottom:0.35rem;
  transition:color 0.6s ease;
}
.ai-reason{font-size:0.82rem;line-height:1.6;font-style:italic;font-weight:300;transition:color 0.6s ease}
.result-actions{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
.result-reset{
  background:none;border:none;font-size:0.8rem;cursor:pointer;
  font-family:var(--font-body);text-decoration:underline;
  transition:opacity 0.2s;padding:0;opacity:0.7;
}
.result-reset:hover{opacity:1}

.result-right{text-align:center;position:relative}
.result-big-emoji{
  font-size:5.5rem;display:block;
  animation:bigEmojiPulse 2.5s ease-in-out infinite;
}
@keyframes bigEmojiPulse{
  0%,100%{transform:scale(1) rotate(-4deg)}
  50%    {transform:scale(1.1) rotate(4deg)}
}
.result-float-items{position:absolute;inset:-20px;pointer-events:none}
.float-item{
  position:absolute;font-size:1.4rem;
  animation:floatAround linear infinite;opacity:0.65;
}
@keyframes floatAround{
  0%  {transform:rotate(0deg)   translateX(60px) rotate(0deg)    scale(0.8);opacity:0.4}
  50% {transform:rotate(180deg) translateX(60px) rotate(-180deg) scale(1.1);opacity:0.8}
  100%{transform:rotate(360deg) translateX(60px) rotate(-360deg) scale(0.8);opacity:0.4}
}

.cart-toast{
  position:fixed;bottom:2rem;left:50%;
  transform:translateX(-50%) translateY(80px);
  background:white;color:var(--text);
  padding:0.75rem 1.5rem;border-radius:50px;
  font-size:0.875rem;font-weight:600;
  box-shadow:0 8px 32px rgba(0,0,0,0.15);
  z-index:9999;opacity:0;
  transition:all 0.4s cubic-bezier(0.34,1.56,0.64,1);
  pointer-events:none;border:1.5px solid var(--border);
}
.cart-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* ── THEMES ───────────────────────────────────── */
/* STRESSED #CDB4F6 */
.emotion-section.theme-stressed{background:linear-gradient(135deg,#f3eeff 0%,#e8d8ff 50%,#f3eeff 100%)}
.emotion-section.theme-stressed .mood-card{background:rgba(205,180,246,0.25);border-color:rgba(160,120,240,0.25)}
.emotion-section.theme-stressed .mood-card.active,
.emotion-section.theme-stressed .mood-card:hover{border-color:#CDB4F6;box-shadow:0 16px 40px rgba(180,140,255,0.25)}
.emotion-section.theme-stressed .mood-glow{background:radial-gradient(circle,rgba(180,140,255,0.2) 0%,transparent 70%)}
.emotion-section.theme-stressed .mood-label,
.emotion-section.theme-stressed .emotion-title,
.emotion-section.theme-stressed .result-heading{color:#5b2d9e}
.emotion-section.theme-stressed .result-quote,
.emotion-section.theme-stressed .ai-label{color:#7c4dcc}
.emotion-section.theme-stressed .mood-result{background:rgba(243,238,255,0.85);border-color:rgba(205,180,246,0.4);box-shadow:0 20px 60px rgba(180,140,255,0.15)}
.emotion-section.theme-stressed .result-ai-box{background:rgba(205,180,246,0.15);border-color:rgba(205,180,246,0.4)}
.emotion-section.theme-stressed .result-product-card{background:rgba(205,180,246,0.18);border-color:rgba(205,180,246,0.35)}
.emotion-section.theme-stressed .rpc-name{color:#5b2d9e}
.emotion-section.theme-stressed .rpc-price{color:#7c4dcc}
.emotion-section.theme-stressed .rpc-add-btn{background:#CDB4F6;color:#3d1a7a}
.emotion-section.theme-stressed .rpc-add-btn:hover{background:#b090e8}
.emotion-section.theme-stressed .result-reset{color:#7c4dcc}

/* CELEBRATING #FFBE98 */
.emotion-section.theme-celebrating{background:linear-gradient(135deg,#fff5ee 0%,#ffe8d6 50%,#fff5ee 100%)}
.emotion-section.theme-celebrating .mood-card{background:rgba(255,190,152,0.25);border-color:rgba(255,160,100,0.25)}
.emotion-section.theme-celebrating .mood-card.active,
.emotion-section.theme-celebrating .mood-card:hover{border-color:#FFBE98;box-shadow:0 16px 40px rgba(255,160,100,0.2)}
.emotion-section.theme-celebrating .mood-glow{background:radial-gradient(circle,rgba(255,160,100,0.2) 0%,transparent 70%)}
.emotion-section.theme-celebrating .mood-label,
.emotion-section.theme-celebrating .emotion-title,
.emotion-section.theme-celebrating .result-heading{color:#8b3a00}
.emotion-section.theme-celebrating .result-quote,
.emotion-section.theme-celebrating .ai-label{color:#c05010}
.emotion-section.theme-celebrating .mood-result{background:rgba(255,248,240,0.88);border-color:rgba(255,190,152,0.4);box-shadow:0 20px 60px rgba(255,160,100,0.12)}
.emotion-section.theme-celebrating .result-ai-box{background:rgba(255,190,152,0.15);border-color:rgba(255,190,152,0.4)}
.emotion-section.theme-celebrating .result-product-card{background:rgba(255,190,152,0.18);border-color:rgba(255,190,152,0.35)}
.emotion-section.theme-celebrating .rpc-name{color:#8b3a00}
.emotion-section.theme-celebrating .rpc-price{color:#c05010}
.emotion-section.theme-celebrating .rpc-add-btn{background:#FFBE98;color:#6b2800}
.emotion-section.theme-celebrating .rpc-add-btn:hover{background:#f0a070}
.emotion-section.theme-celebrating .result-reset{color:#c05010}

/* HEARTBROKEN #E89AAE */
.emotion-section.theme-heartbroken{background:linear-gradient(135deg,#fff0f4 0%,#fde0e8 50%,#fff0f4 100%)}
.emotion-section.theme-heartbroken .mood-card{background:rgba(232,154,174,0.2);border-color:rgba(220,120,150,0.25)}
.emotion-section.theme-heartbroken .mood-card.active,
.emotion-section.theme-heartbroken .mood-card:hover{border-color:#E89AAE;box-shadow:0 16px 40px rgba(220,120,150,0.2)}
.emotion-section.theme-heartbroken .mood-glow{background:radial-gradient(circle,rgba(220,120,150,0.2) 0%,transparent 70%)}
.emotion-section.theme-heartbroken .mood-label,
.emotion-section.theme-heartbroken .emotion-title,
.emotion-section.theme-heartbroken .result-heading{color:#8b1a35}
.emotion-section.theme-heartbroken .result-quote,
.emotion-section.theme-heartbroken .ai-label{color:#b03050}
.emotion-section.theme-heartbroken .mood-result{background:rgba(255,242,246,0.88);border-color:rgba(232,154,174,0.4);box-shadow:0 20px 60px rgba(220,120,150,0.12)}
.emotion-section.theme-heartbroken .result-ai-box{background:rgba(232,154,174,0.15);border-color:rgba(232,154,174,0.4)}
.emotion-section.theme-heartbroken .result-product-card{background:rgba(232,154,174,0.18);border-color:rgba(232,154,174,0.35)}
.emotion-section.theme-heartbroken .rpc-name{color:#8b1a35}
.emotion-section.theme-heartbroken .rpc-price{color:#b03050}
.emotion-section.theme-heartbroken .rpc-add-btn{background:#E89AAE;color:#6b0820}
.emotion-section.theme-heartbroken .rpc-add-btn:hover{background:#d07090}
.emotion-section.theme-heartbroken .result-reset{color:#b03050}

/* SLEEPY #B7C9F2 */
.emotion-section.theme-sleepy{background:linear-gradient(135deg,#eef3ff 0%,#dce8ff 50%,#eef3ff 100%)}
.emotion-section.theme-sleepy .mood-card{background:rgba(183,201,242,0.25);border-color:rgba(140,170,230,0.25)}
.emotion-section.theme-sleepy .mood-card.active,
.emotion-section.theme-sleepy .mood-card:hover{border-color:#B7C9F2;box-shadow:0 16px 40px rgba(140,170,230,0.2)}
.emotion-section.theme-sleepy .mood-glow{background:radial-gradient(circle,rgba(140,170,230,0.2) 0%,transparent 70%)}
.emotion-section.theme-sleepy .mood-label,
.emotion-section.theme-sleepy .emotion-title,
.emotion-section.theme-sleepy .result-heading{color:#1a3060}
.emotion-section.theme-sleepy .result-quote,
.emotion-section.theme-sleepy .ai-label{color:#2a50a0}
.emotion-section.theme-sleepy .mood-result{background:rgba(238,243,255,0.88);border-color:rgba(183,201,242,0.4);box-shadow:0 20px 60px rgba(140,170,230,0.15)}
.emotion-section.theme-sleepy .result-ai-box{background:rgba(183,201,242,0.15);border-color:rgba(183,201,242,0.4)}
.emotion-section.theme-sleepy .result-product-card{background:rgba(183,201,242,0.18);border-color:rgba(183,201,242,0.35)}
.emotion-section.theme-sleepy .rpc-name{color:#1a3060}
.emotion-section.theme-sleepy .rpc-price{color:#2a50a0}
.emotion-section.theme-sleepy .rpc-add-btn{background:#B7C9F2;color:#0a1e50}
.emotion-section.theme-sleepy .rpc-add-btn:hover{background:#90b0e8}
.emotion-section.theme-sleepy .result-reset{color:#2a50a0}

/* HAPPY #FFE27A */
.emotion-section.theme-happy{background:linear-gradient(135deg,#fffde8 0%,#fff8c0 50%,#fffde8 100%)}
.emotion-section.theme-happy .mood-card{background:rgba(255,226,122,0.25);border-color:rgba(240,200,60,0.25)}
.emotion-section.theme-happy .mood-card.active,
.emotion-section.theme-happy .mood-card:hover{border-color:#FFE27A;box-shadow:0 16px 40px rgba(240,200,60,0.2)}
.emotion-section.theme-happy .mood-glow{background:radial-gradient(circle,rgba(240,200,60,0.2) 0%,transparent 70%)}
.emotion-section.theme-happy .mood-label,
.emotion-section.theme-happy .emotion-title,
.emotion-section.theme-happy .result-heading{color:#6b4a00}
.emotion-section.theme-happy .result-quote,
.emotion-section.theme-happy .ai-label{color:#a07000}
.emotion-section.theme-happy .mood-result{background:rgba(255,253,232,0.9);border-color:rgba(255,226,122,0.4);box-shadow:0 20px 60px rgba(240,200,60,0.12)}
.emotion-section.theme-happy .result-ai-box{background:rgba(255,226,122,0.15);border-color:rgba(255,226,122,0.4)}
.emotion-section.theme-happy .result-product-card{background:rgba(255,226,122,0.2);border-color:rgba(255,226,122,0.4)}
.emotion-section.theme-happy .rpc-name{color:#6b4a00}
.emotion-section.theme-happy .rpc-price{color:#a07000}
.emotion-section.theme-happy .rpc-add-btn{background:#FFE27A;color:#5a3a00}
.emotion-section.theme-happy .rpc-add-btn:hover{background:#f0c830}
.emotion-section.theme-happy .result-reset{color:#a07000}

/* ANXIOUS #AED9E0 */
.emotion-section.theme-anxious{background:linear-gradient(135deg,#f0fafc 0%,#daf3f7 50%,#f0fafc 100%)}
.emotion-section.theme-anxious .mood-card{background:rgba(174,217,224,0.25);border-color:rgba(120,190,200,0.25)}
.emotion-section.theme-anxious .mood-card.active,
.emotion-section.theme-anxious .mood-card:hover{border-color:#AED9E0;box-shadow:0 16px 40px rgba(120,190,200,0.2)}
.emotion-section.theme-anxious .mood-glow{background:radial-gradient(circle,rgba(120,190,200,0.2) 0%,transparent 70%)}
.emotion-section.theme-anxious .mood-label,
.emotion-section.theme-anxious .emotion-title,
.emotion-section.theme-anxious .result-heading{color:#0a4048}
.emotion-section.theme-anxious .result-quote,
.emotion-section.theme-anxious .ai-label{color:#1a6070}
.emotion-section.theme-anxious .mood-result{background:rgba(240,250,252,0.9);border-color:rgba(174,217,224,0.4);box-shadow:0 20px 60px rgba(120,190,200,0.12)}
.emotion-section.theme-anxious .result-ai-box{background:rgba(174,217,224,0.15);border-color:rgba(174,217,224,0.4)}
.emotion-section.theme-anxious .result-product-card{background:rgba(174,217,224,0.2);border-color:rgba(174,217,224,0.4)}
.emotion-section.theme-anxious .rpc-name{color:#0a4048}
.emotion-section.theme-anxious .rpc-price{color:#1a6070}
.emotion-section.theme-anxious .rpc-add-btn{background:#AED9E0;color:#073038}
.emotion-section.theme-anxious .rpc-add-btn:hover{background:#80c4ce}
.emotion-section.theme-anxious .result-reset{color:#1a6070}

/* BORED #D8D2C8 */
.emotion-section.theme-bored{background:linear-gradient(135deg,#faf9f7 0%,#f0ede8 50%,#faf9f7 100%)}
.emotion-section.theme-bored .mood-card{background:rgba(216,210,200,0.3);border-color:rgba(180,170,155,0.3)}
.emotion-section.theme-bored .mood-card.active,
.emotion-section.theme-bored .mood-card:hover{border-color:#D8D2C8;box-shadow:0 16px 40px rgba(180,170,155,0.2)}
.emotion-section.theme-bored .mood-glow{background:radial-gradient(circle,rgba(180,170,155,0.2) 0%,transparent 70%)}
.emotion-section.theme-bored .mood-label,
.emotion-section.theme-bored .emotion-title,
.emotion-section.theme-bored .result-heading{color:#3a3028}
.emotion-section.theme-bored .result-quote,
.emotion-section.theme-bored .ai-label{color:#6a5a48}
.emotion-section.theme-bored .mood-result{background:rgba(250,248,245,0.9);border-color:rgba(216,210,200,0.5);box-shadow:0 20px 60px rgba(180,170,155,0.12)}
.emotion-section.theme-bored .result-ai-box{background:rgba(216,210,200,0.2);border-color:rgba(216,210,200,0.5)}
.emotion-section.theme-bored .result-product-card{background:rgba(216,210,200,0.25);border-color:rgba(216,210,200,0.5)}
.emotion-section.theme-bored .rpc-name{color:#3a3028}
.emotion-section.theme-bored .rpc-price{color:#6a5a48}
.emotion-section.theme-bored .rpc-add-btn{background:#D8D2C8;color:#2a2018}
.emotion-section.theme-bored .rpc-add-btn:hover{background:#b8b0a0;color:white}
.emotion-section.theme-bored .result-reset{color:#6a5a48}

/* IN LOVE #FFB3C7 */
.emotion-section.theme-inlove{background:linear-gradient(135deg,#fff5f8 0%,#ffe4ec 50%,#fff5f8 100%)}
.emotion-section.theme-inlove .mood-card{background:rgba(255,179,199,0.22);border-color:rgba(240,140,170,0.25)}
.emotion-section.theme-inlove .mood-card.active,
.emotion-section.theme-inlove .mood-card:hover{border-color:#FFB3C7;box-shadow:0 16px 40px rgba(240,140,170,0.2)}
.emotion-section.theme-inlove .mood-glow{background:radial-gradient(circle,rgba(240,140,170,0.2) 0%,transparent 70%)}
.emotion-section.theme-inlove .mood-label,
.emotion-section.theme-inlove .emotion-title,
.emotion-section.theme-inlove .result-heading{color:#7a0a30}
.emotion-section.theme-inlove .result-quote,
.emotion-section.theme-inlove .ai-label{color:#b02050}
.emotion-section.theme-inlove .mood-result{background:rgba(255,245,248,0.9);border-color:rgba(255,179,199,0.4);box-shadow:0 20px 60px rgba(240,140,170,0.15)}
.emotion-section.theme-inlove .result-ai-box{background:rgba(255,179,199,0.15);border-color:rgba(255,179,199,0.4)}
.emotion-section.theme-inlove .result-product-card{background:rgba(255,179,199,0.18);border-color:rgba(255,179,199,0.35)}
.emotion-section.theme-inlove .rpc-name{color:#7a0a30}
.emotion-section.theme-inlove .rpc-price{color:#b02050}
.emotion-section.theme-inlove .rpc-add-btn{background:#FFB3C7;color:#6a0025}
.emotion-section.theme-inlove .rpc-add-btn:hover{background:#f090a8}
.emotion-section.theme-inlove .result-reset{color:#b02050}

/* RESPONSIVE */
@media(max-width:1000px){
  .emotion-section{padding:4rem 2rem}
  .mood-grid{grid-template-columns:repeat(4,1fr);gap:0.75rem}
}
@media(max-width:700px){
  .mood-grid{grid-template-columns:repeat(2,1fr)}
  .result-inner{grid-template-columns:1fr}
  .result-right{display:none}
}
@media(max-width:480px){
  .mood-grid{grid-template-columns:repeat(2,1fr);gap:0.6rem}
  .mood-card{padding:1.25rem 0.75rem}
  .mood-emoji{font-size:2.2rem}
}
</style>

<!-- ══ EMOTION ENGINE SCRIPT ══════════════════ -->
<script>
const productImages = {
  'Brownie Slab':              'assets/Brownie Slab.jpeg',
  'Assorted Brownie Box of 9': 'assets/Assorted Brownie box of 9.jpeg',
  'Plain Brownie':             'assets/Plain brownie.jpeg',
  'Biscoff Cheesecake':        'assets/Biscoff cheesecake.jpeg',
  'Blueberry Cheesecake':      'assets/Blueberry cheesecake.jpeg',
  'Box of 6 Assorted Brownies':'assets/Box of 6 assorted brownie.jpeg',
  'Strawberry Brownie Tub':    'assets/Strawberry brownie tub.jpeg',
  'Premium Date Rolls':        'assets/Premium date rolls.jpeg',
  'Assorted Dates':            'assets/Assorted Dates.jpeg',
  'Pistachio Kunafa Tub':      'assets/Pistachio Kunafa Tub.jpeg',
  'Mini Chocolate Chip Cookies':'assets/Mini chocolate chip cookes.jpeg',
  'Osmania Cups':              'assets/Osmalia Cups.jpeg',
  'Special Eid Moon Brownie':  'assets/Special eid moon brownie.jpeg',
};
const productPrices = {
  'Brownie Slab':999,'Assorted Brownie Box of 9':999,'Plain Brownie':80,
  'Biscoff Cheesecake':950,'Blueberry Cheesecake':999,'Box of 6 Assorted Brownies':650,
  'Strawberry Brownie Tub':500,'Premium Date Rolls':899,'Assorted Dates':750,
  'Pistachio Kunafa Tub':250,'Mini Chocolate Chip Cookies':450,
  'Osmania Cups':60,'Special Eid Moon Brownie':750,
};
const productIds = {
  'Brownie Slab':2,'Assorted Brownie Box of 9':6,'Plain Brownie':3,
  'Biscoff Cheesecake':8,'Blueberry Cheesecake':9,'Box of 6 Assorted Brownies':1,
  'Strawberry Brownie Tub':5,'Premium Date Rolls':13,'Assorted Dates':12,
  'Pistachio Kunafa Tub':10,'Mini Chocolate Chip Cookies':14,
  'Osmania Cups':11,'Special Eid Moon Brownie':7,
};

const moodData = {
  stressed:{
    theme:'theme-stressed',emoji:'😭',
    quote:'"Chocolate is cheaper than therapy — and you don\'t need an appointment." 🍫',
    heading:'You need chocolate. Right now.',
    text:'Stress is temporary, brownies are forever. Let the rich chocolate melt your worries away.',
    floats:['💜','✨','🍫','💝'],particleColor:'#CDB4F6',
    aiReason:'Dark chocolate triggers serotonin release — your brain\'s natural stress reliever. The warm cocoa depth creates a grounding, comforting sensation perfect for anxious moments. 🍫',
    products:['Brownie Slab','Assorted Brownie Box of 9','Plain Brownie']
  },
  celebrating:{
    theme:'theme-celebrating',emoji:'🎉',
    quote:'"Life is short — eat the cheesecake first!" 🎉',
    heading:'Time to celebrate with something divine!',
    text:'You\'re glowing and you deserve the absolute best. Treat yourself and everyone around you!',
    floats:['🎉','⭐','🌟','🎊'],particleColor:'#FFBE98',
    aiReason:'Sweet creamy textures amplify joy and create shareable moments. The festive richness matches your celebratory energy perfectly. 🎉',
    products:['Biscoff Cheesecake','Blueberry Cheesecake','Box of 6 Assorted Brownies']
  },
  heartbroken:{
    theme:'theme-heartbroken',emoji:'💔',
    quote:'"You can\'t buy happiness — but you can buy desserts, and that\'s close enough." 💕',
    heading:'We\'ve got you. Healing starts here.',
    text:'Some days just need extra sweetness. Wrap yourself in a blanket and let these treats hold you gently.',
    floats:['💕','🌸','✨','💫'],particleColor:'#E89AAE',
    aiReason:'Comfort foods with natural sweetness activate the brain\'s reward pathways, providing gentle emotional relief. 💕',
    products:['Plain Brownie','Strawberry Brownie Tub','Premium Date Rolls']
  },
  sleepy:{
    theme:'theme-sleepy',emoji:'🌙',
    quote:'"The night is yours. So is the kunafa." 🌙',
    heading:'Late night? We completely understand.',
    text:'The world is quiet and your cravings are loud. Something warm and gently sweet, like a cozy hug at midnight.',
    floats:['🌙','⭐','💫','✨'],particleColor:'#B7C9F2',
    aiReason:'Warm, mildly sweet flavors with natural sugars ease you into rest. These picks are gentle and create the perfect late-night comfort ritual. 🌙',
    products:['Pistachio Kunafa Tub','Mini Chocolate Chip Cookies','Assorted Dates']
  },
  happy:{
    theme:'theme-happy',emoji:'☀️',
    quote:'"Happiness is homemade — and so are these treats!" ☀️',
    heading:'You\'re glowing — let\'s keep it going!',
    text:'Good vibes deserve great food. Share the sweetness, double the joy!',
    floats:['☀️','🌸','⭐','🌟'],particleColor:'#FFE27A',
    aiReason:'Light, fruity and sweet flavors amplify positive emotions. Sharing food you love creates connection and extends the happiness even further. ☀️',
    products:['Blueberry Cheesecake','Strawberry Brownie Tub','Mini Chocolate Chip Cookies']
  },
  anxious:{
    theme:'theme-anxious',emoji:'🌿',
    quote:'"Breathe in. Breathe out. Take a bite." 🌿',
    heading:'Slow down. Something calming awaits.',
    text:'When the mind races, something gentle and grounding helps. These picks are soft and naturally calming.',
    floats:['🌿','🍃','🌸','✨'],particleColor:'#AED9E0',
    aiReason:'Natural dates and nuts contain magnesium which supports relaxation. Earthy, mildly sweet flavors help slow racing thoughts. 🌿',
    products:['Assorted Dates','Premium Date Rolls','Osmania Cups']
  },
  bored:{
    theme:'theme-bored',emoji:'✨',
    quote:'"Adventure is just a dessert you haven\'t tried yet." ✨',
    heading:'Let\'s shake things up a little!',
    text:'Boredom is just an invitation for something new and exciting. Try something you\'ve never had before!',
    floats:['✨','🎭','🌟','🔮'],particleColor:'#D8D2C8',
    aiReason:'Novel flavor combinations stimulate dopamine — the brain\'s curiosity chemical. Trying something new creates a mini adventure. ✨',
    products:['Pistachio Kunafa Tub','Osmania Cups','Special Eid Moon Brownie']
  },
  inlove:{
    theme:'theme-inlove',emoji:'🥰',
    quote:'"The way to someone\'s heart is through a Sugar Dips box." 🥰',
    heading:'Share the sweetness of love.',
    text:'Love deserves to be celebrated with something as beautiful as the feeling itself.',
    floats:['💕','🌹','✨','💝'],particleColor:'#FFB3C7',
    aiReason:'Sharing sweet food creates oxytocin — the bonding hormone. Gifting desserts deepens emotional connection and creates lasting memories. 💕',
    products:['Biscoff Cheesecake','Box of 6 Assorted Brownies','Premium Date Rolls']
  }
};

function selectMood(mood) {
  const data    = moodData[mood];
  const section = document.getElementById('emotion-engine');
  section.className = 'emotion-section';
  void section.offsetWidth;
  section.classList.add(data.theme);

  document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('active'));
  document.querySelector(`[data-mood="${mood}"]`).classList.add('active');

  document.getElementById('resultQuote').textContent   = data.quote;
  document.getElementById('resultHeading').textContent = data.heading;
  document.getElementById('resultText').textContent    = data.text;
  document.getElementById('resultEmoji').textContent   = data.emoji;
  document.getElementById('aiReason').textContent      = data.aiReason;

  // Product cards
  document.getElementById('resultProductCards').innerHTML =
    data.products.map((name, idx) => {
      const img   = productImages[name]  || 'assets/Logo.png';
      const price = productPrices[name]  || 0;
      const id    = productIds[name]     || idx+1;
      return `
        <div class="result-product-card">
          <div class="rpc-left">
            <img src="${img}" alt="${name}" class="rpc-img"
                 onerror="this.src='assets/Logo.png'"/>
            <div>
              <div class="rpc-name">${name}</div>
              <div class="rpc-price">&#8377;${price}</div>
            </div>
          </div>
          <button class="rpc-add-btn" id="rpc-btn-${mood}-${idx}"
            onclick="addMoodItem(${id},'${name.replace(/'/g,"\\'")}',${price},'${img}','${mood}',${idx})">
            + Add
          </button>
        </div>`;
    }).join('');

  // Floating items
  const floatsEl = document.getElementById('resultFloats');
  floatsEl.innerHTML = '';
  data.floats.forEach((emoji, i) => {
    const f = document.createElement('div');
    f.className   = 'float-item';
    f.textContent = emoji;
    f.style.cssText = `animation-duration:${3+i*0.7}s;animation-delay:${i*0.4}s;top:50%;left:50%;`;
    floatsEl.appendChild(f);
  });

  // Particles
  const pEl = document.getElementById('resultParticles');
  pEl.innerHTML = '';
  for (let i = 0; i < 16; i++) {
    const p = document.createElement('div');
    p.className = 'rp';
    const sz = 4 + Math.random()*7;
    p.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;background:${data.particleColor};opacity:${0.3+Math.random()*0.4};animation-duration:${3+Math.random()*4}s;animation-delay:${Math.random()*3}s;`;
    pEl.appendChild(p);
  }

  const resultEl = document.getElementById('moodResult');
  resultEl.style.display = 'block';
  setTimeout(() => resultEl.scrollIntoView({behavior:'smooth',block:'nearest'}), 120);
}

// ── UPDATED: reloads cart sidebar without page refresh ──
function addMoodItem(id, name, price, image, mood, idx) {
  const btn = document.getElementById(`rpc-btn-${mood}-${idx}`);
  btn.disabled = true;
  btn.textContent = '✓ Added!';
  btn.classList.add('added');

  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action:'add', id, name, price, image, customization:'', qty:1})
  })
  .then(r => r.json())
  .then(data => {
    showToast('🛒 ' + name + ' added!');

    // ── RELOAD CART SIDEBAR WITHOUT PAGE REFRESH ──
    fetch('get_cart_sidebar.php')
    .then(r => r.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;

      const newItems  = temp.querySelector('.cart-sidebar-items');
      const newFooter = temp.querySelector('.cart-sidebar-footer');
      const newEmpty  = temp.querySelector('.cart-sidebar-empty');
      const oldItems  = document.querySelector('.cart-sidebar-items');
      const oldFooter = document.querySelector('.cart-sidebar-footer');
      const oldEmpty  = document.querySelector('.cart-sidebar-empty');

      if (newItems && oldItems)      oldItems.outerHTML  = newItems.outerHTML;
      else if (newItems && oldEmpty) oldEmpty.outerHTML  = newItems.outerHTML;

      if (newFooter && oldFooter)    oldFooter.outerHTML = newFooter.outerHTML;
      else if (newFooter)            document.getElementById('cartSidebar').appendChild(newFooter);

      if (newEmpty && oldEmpty)      oldEmpty.outerHTML  = newEmpty.outerHTML;

      // Update nav badge
      const count = temp.querySelector('.cart-badge-count');
      if (count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
          badge.textContent = count.textContent;
        } else {
          const cartBtn = document.querySelector('.cart-btn');
          const s = document.createElement('span');
          s.className   = 'cart-badge';
          s.textContent = count.textContent;
          cartBtn.appendChild(s);
        }
      }
    });

    setTimeout(() => {
      btn.disabled = false;
      btn.textContent = '+ Add';
      btn.classList.remove('added');
    }, 2000);
  });
}

function showToast(msg) {
  const t = document.getElementById('cartToast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

function resetMood() {
  document.getElementById('emotion-engine').className = 'emotion-section';
  document.querySelectorAll('.mood-card').forEach(c => c.classList.remove('active'));
  document.getElementById('moodResult').style.display = 'none';
  document.getElementById('resultParticles').innerHTML = '';
  document.getElementById('resultFloats').innerHTML    = '';
}
</script>

</body>
</html>