<?php
session_start();
include 'connection.php';

// Restore an existing remembered session.
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'], $_COOKIE['username'], $_COOKIE['account_type'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['username'] = $_COOKIE['username'];
    $_SESSION['account_type'] = $_COOKIE['account_type'];
}

// Send signed-in users to the correct workspace.
if (isset($_SESSION['user_id'], $_SESSION['account_type'])) {
    if (!empty($_SESSION['redirect_after_login'])) {
        $redirect = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit();
    }
    $routes = [
        'seller' => '/sellerPage',
        'buyer' => 'buyerPage.php',
        'sales_agent' => 'agentPage.php',
        'property_owner' => 'propertyOwnerPage.php',
        'administrator' => 'adminPage.php',
        'admin' => 'adminPage.php'
    ];
    $role = strtolower((string)$_SESSION['account_type']);
    if (isset($routes[$role])) {
        header('Location: ' . $routes[$role]);
        exit();
    }
}

$error = '';
$success = '';
$identifier = '';
$selectedRole = '';

function normalizePhone($phone) {
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    if (strpos($cleaned, '+') === 0) return $cleaned;
    if (strpos($cleaned, '0') === 0) return '+254' . substr($cleaned, 1);
    if (strlen($cleaned) >= 9) return '+' . $cleaned;
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $selectedRole = strtolower(trim($_POST['login_type'] ?? ''));
    $allowedRoles = ['buyer', 'seller', 'sales_agent', 'property_owner', 'administrator'];

    if ($identifier === '' || $password === '' || !in_array($selectedRole, $allowedRoles, true)) {
        $error = 'Please complete all fields and choose a valid account type.';
    } else {
        $encryptedEmail = base64_encode(strtolower($identifier));
        $normalizedPhone = normalizePhone($identifier);
        $encryptedPhone = base64_encode($normalizedPhone);
        $query = "SELECT * FROM users WHERE account_type = ? AND (BINARY username = ? OR email = ? OR phone = ?) LIMIT 1";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ssss', $selectedRole, $identifier, $encryptedEmail, $encryptedPhone);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['account_type'] = $user['account_type'];
                    if (!empty($_POST['remember'])) {
                        $cookieTime = time() + (30 * 24 * 60 * 60);
                        setcookie('user_id', $user['user_id'], ['expires'=>$cookieTime, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']), 'httponly'=>true, 'samesite'=>'Lax']);
                        setcookie('username', $user['username'], ['expires'=>$cookieTime, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']), 'httponly'=>true, 'samesite'=>'Lax']);
                        setcookie('account_type', $user['account_type'], ['expires'=>$cookieTime, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']), 'httponly'=>true, 'samesite'=>'Lax']);
                    }
                    $routes = [
                        'seller'=>'/sellerPage',
                        'buyer'=>'buyerPage.php',
                        'administrator'=>'adminPage.php',
                        'sales_agent'=>'agentPage.php',
                        'property_owner'=>'propertyOwnerPage.php'
                    ];
                    $redirectPage = $routes[strtolower($user['account_type'])] ?? 'index.php';
                    if (!empty($_SESSION['redirect_after_login'])) {
                        $redirectPage = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }
                    $success = 'Welcome back! You are being taken to your Makethub workspace.';
                    $redirectAfterLogin = $redirectPage;
                } else {
                    $error = 'One of the credentials is invalid. Please try again.';
                }
            } else {
                $error = 'No account was found with those details and account type.';
            }
            $stmt->close();
        } else {
            $error = 'We could not process your login right now. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Makethub brings buyers, sellers, shop attendants and property owners together through one connected marketplace and business platform. Buy Local. Order Global!">
<meta property="og:title" content="Makethub — Buy Local. Order Global!">
<meta property="og:description" content="Discover products, connect with businesses and manage your shop with Makethub.">
<meta property="og:image" content="https://makethub.shop/Images/Makethub%20Logo.png">
<meta property="og:url" content="https://makethub.shop/">
<meta property="og:type" content="website">
<link rel="icon" type="image/png" href="Images/favicon-32x32.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Makethub | Buy Local. Order Global!</title>
<style>
:root{--ink:#171a18;--muted:#707773;--green:#16833b;--green-dark:#08652b;--green-soft:#edf7ef;--line:#e8ece9;--paper:#fff;--wash:#f7faf7;--gold:#f5b900;--shadow:0 18px 55px rgba(24,48,31,.09)}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--paper);color:var(--ink);font-family:'DM Sans',sans-serif;-webkit-font-smoothing:antialiased}a{color:inherit;text-decoration:none}button,input,select{font:inherit}.site-shell{overflow:hidden}.wrap{width:min(1160px,calc(100% - 48px));margin:auto}.site-header{height:82px;display:flex;align-items:center;border-bottom:1px solid #f0f2f0;background:rgba(255,255,255,.94);position:sticky;top:0;z-index:20;backdrop-filter:blur(14px)}.nav{display:flex;align-items:center;justify-content:space-between;gap:28px}.brand{display:flex;align-items:center;gap:10px;font-family:Manrope,sans-serif;font-size:22px;font-weight:800;letter-spacing:-1px}.brand img{width:42px;height:42px;object-fit:contain}.brand span{color:var(--green)}.nav-links{display:flex;align-items:center;gap:30px;color:#535b56;font-size:14px;font-weight:600}.nav-links a:hover{color:var(--green)}.nav-actions{display:flex;align-items:center;gap:10px}.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;border:1px solid transparent;border-radius:9px;padding:12px 19px;font-weight:700;font-size:14px;cursor:pointer;transition:.2s ease}.btn:hover{transform:translateY(-2px)}.btn-outline{border-color:#dce4de;background:#fff;color:var(--ink)}.btn-outline:hover{border-color:var(--green);color:var(--green)}.btn-green{background:var(--green);color:#fff;box-shadow:0 8px 20px rgba(22,131,59,.16)}.btn-green:hover{background:var(--green-dark)}.hero{padding:78px 0 82px;background:radial-gradient(circle at 82% 28%,#f0f8f0 0,rgba(240,248,240,0) 36%),linear-gradient(180deg,#fff 0%,#fcfefc 100%)}.hero-grid{display:grid;grid-template-columns:1.02fr .98fr;align-items:center;gap:64px}.eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--green-soft);color:var(--green-dark);border-radius:30px;padding:8px 13px;font-size:12px;font-weight:800;letter-spacing:.5px;text-transform:uppercase}.eyebrow i{font-size:11px}.hero h1{font-family:Manrope,sans-serif;font-size:clamp(42px,5.4vw,68px);line-height:1.07;letter-spacing:-3.2px;margin:22px 0 19px;font-weight:800}.hero h1 .accent{color:var(--green)}.hero-copy{font-size:17px;line-height:1.8;color:#68716b;max-width:540px;margin:0}.hero-actions{display:flex;align-items:center;gap:13px;margin-top:30px;flex-wrap:wrap}.hero-actions .btn{padding:15px 22px}.trust-line{display:flex;align-items:center;gap:12px;margin-top:30px;color:#747d77;font-size:12px}.trust-dots{display:flex}.trust-dots span{width:27px;height:27px;border:2px solid white;border-radius:50%;margin-left:-7px;background:#d8eadc;display:grid;place-items:center;color:var(--green-dark);font-size:10px}.trust-dots span:first-child{margin-left:0}.hero-visual{position:relative;min-height:470px}.hero-photo{position:absolute;inset:12px 20px 28px 34px;border-radius:26px;overflow:hidden;background:#e8f0e9;box-shadow:var(--shadow)}.hero-photo img{width:100%;height:100%;object-fit:cover;display:block}.photo-shade{position:absolute;inset:45% 0 0;background:linear-gradient(transparent,rgba(10,25,14,.55))}.photo-caption{position:absolute;bottom:24px;left:25px;color:white}.photo-caption strong{display:block;font-family:Manrope,sans-serif;font-size:22px}.photo-caption small{opacity:.88;font-size:12px}.float-card{position:absolute;background:white;border:1px solid #edf0ed;border-radius:15px;padding:15px 17px;box-shadow:0 14px 38px rgba(20,40,24,.13)}.float-card.top{top:0;right:0;display:flex;align-items:center;gap:11px}.float-card.bottom{left:0;bottom:0;display:flex;align-items:center;gap:12px}.float-icon{width:38px;height:38px;border-radius:11px;background:var(--green-soft);display:grid;place-items:center;color:var(--green);font-size:16px}.float-card b{display:block;font-size:13px}.float-card small{color:#818a84;font-size:11px}.mini-avatars{display:flex}.mini-avatars img{width:31px;height:31px;border-radius:50%;object-fit:cover;border:2px solid white;margin-left:-8px}.mini-avatars img:first-child{margin-left:0}.section{padding:82px 0}.section-soft{background:var(--wash)}.section-head{text-align:center;max-width:660px;margin:0 auto 42px}.section-kicker{color:var(--green);font-size:12px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}.section-head h2{font-family:Manrope,sans-serif;font-size:clamp(29px,3.5vw,42px);line-height:1.17;letter-spacing:-1.6px;margin:12px 0}.section-head p{color:var(--muted);font-size:15px;line-height:1.75;margin:0}.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.feature-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:25px;transition:.22s ease}.feature-card:hover{transform:translateY(-5px);box-shadow:var(--shadow);border-color:#d5e7d9}.feature-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:13px;background:var(--green-soft);color:var(--green);font-size:19px}.feature-card h3{font-family:Manrope,sans-serif;font-size:18px;margin:19px 0 8px}.feature-card p{font-size:13px;line-height:1.75;color:var(--muted);margin:0}.feature-link{display:inline-flex;gap:8px;align-items:center;margin-top:18px;color:var(--green);font-size:12px;font-weight:800}.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:32px;counter-reset:step}.step{position:relative;padding:8px 18px;text-align:center}.step-number{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;margin:0 auto 17px;background:var(--green);color:#fff;font-weight:800}.step h3{font-family:Manrope,sans-serif;font-size:17px;margin:0 0 8px}.step p{font-size:13px;line-height:1.7;color:var(--muted);margin:0}.audience-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.audience{padding:23px 19px;border:1px solid var(--line);border-radius:14px;background:#fff}.audience i{font-size:20px;color:var(--green)}.audience h3{font-size:15px;margin:14px 0 6px}.audience p{font-size:12px;line-height:1.65;color:var(--muted);margin:0}.cta-band{background:#102c19;border-radius:22px;padding:42px 48px;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:30px}.cta-band h2{font-family:Manrope,sans-serif;font-size:clamp(25px,3vw,35px);letter-spacing:-1px;margin:0 0 9px}.cta-band p{color:#c4d5c8;font-size:14px;margin:0}.cta-band .btn{background:#fff;color:#135c2b;white-space:nowrap}.footer{padding:38px 0 28px;border-top:1px solid var(--line)}.footer-top{display:flex;align-items:center;justify-content:space-between;gap:24px}.footer-copy{font-size:12px;color:#818a84}.footer-links{display:flex;gap:22px;flex-wrap:wrap;font-size:12px;color:#68716b}.footer-links a:hover{color:var(--green)}.footer-bottom{border-top:1px solid #edf0ed;margin-top:25px;padding-top:18px;display:flex;justify-content:space-between;color:#929a95;font-size:11px}.modal-backdrop{position:fixed;inset:0;background:rgba(13,24,16,.56);z-index:50;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(5px)}.modal-backdrop.open{display:flex}.login-modal{width:min(460px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:18px;padding:30px;box-shadow:0 24px 80px rgba(0,0,0,.2);position:relative}.modal-close{position:absolute;right:17px;top:14px;border:0;background:#f3f5f3;border-radius:50%;width:34px;height:34px;cursor:pointer;color:#5d665f}.modal-brand{display:flex;align-items:center;gap:9px;font-family:Manrope,sans-serif;font-size:18px;font-weight:800}.modal-brand img{width:34px;height:34px;object-fit:contain}.login-modal h2{font-family:Manrope,sans-serif;font-size:25px;letter-spacing:-.7px;margin:20px 0 5px}.modal-intro{color:var(--muted);font-size:13px;margin:0 0 20px}.form-alert{border-radius:9px;padding:11px 13px;font-size:12px;line-height:1.5;margin:14px 0}.form-alert.error{background:#fff0ef;color:#b42318}.form-alert.success{background:#edf8ef;color:#12612c}.login-field{margin:13px 0}.login-field label{display:block;font-size:12px;font-weight:700;margin-bottom:7px}.login-field input,.login-field select{width:100%;padding:13px 14px;border:1px solid #dfe6e0;border-radius:9px;outline:none;background:#fff;color:var(--ink);font-size:13px}.login-field input:focus,.login-field select:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(22,131,59,.09)}.password-wrap{position:relative}.password-wrap input{padding-right:46px}.password-toggle{position:absolute;right:13px;top:50%;transform:translateY(-50%);border:0;background:none;color:#7a847d;cursor:pointer}.remember-row{display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#66716a;margin:14px 0 18px}.remember-row label{display:flex;align-items:center;gap:7px}.remember-row a{color:var(--green);font-weight:700}.login-submit{width:100%;border:0}.register-note{text-align:center;color:#717a74;font-size:12px;margin:17px 0 0}.register-note a{color:var(--green);font-weight:800}.mobile-menu{display:none}
@media(max-width:900px){.hero-grid{gap:30px}.hero-visual{min-height:400px}.audience-grid{grid-template-columns:repeat(2,1fr)}.nav-links{gap:17px}.feature-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:680px){.wrap{width:min(100% - 34px,560px)}.site-header{height:70px}.brand{font-size:19px}.brand img{width:36px;height:36px}.nav-links{display:none}.nav-actions .btn-outline{display:none}.nav-actions .btn{padding:10px 13px;font-size:12px}.hero{padding:54px 0 60px}.hero-grid{grid-template-columns:1fr;gap:35px}.hero h1{font-size:45px;letter-spacing:-2.4px}.hero-copy{font-size:15px}.hero-visual{min-height:350px}.hero-photo{inset:10px 8px 20px 20px}.float-card{padding:11px 12px}.float-card.top{right:0}.section{padding:62px 0}.feature-grid{grid-template-columns:1fr}.steps{grid-template-columns:1fr;gap:24px}.audience-grid{grid-template-columns:1fr 1fr;gap:10px}.audience{padding:17px 14px}.cta-band{padding:30px 23px;align-items:flex-start;flex-direction:column}.footer-top,.footer-bottom{align-items:flex-start;flex-direction:column}.footer-links{gap:14px}.login-modal{padding:25px 21px}}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*::before,*::after{transition:none!important}}
</style>
</head>
<body>
<div class="site-shell">
<header class="site-header">
  <div class="wrap nav">
    <a class="brand" href="/" aria-label="Makethub home"><img src="Images/Makethub Logo.png" alt=""><span>make<span>thub</span></span></a>
    <nav class="nav-links" aria-label="Main navigation"><a href="#about">About</a><a href="#how-it-works">How it works</a><a href="#who-its-for">Who it's for</a></nav>
    <div class="nav-actions"><a class="btn btn-outline" href="accountTypeSelection.php">Create account</a><button class="btn btn-green" type="button" data-open-login>Log in <i class="fa-solid fa-arrow-right"></i></button></div>
  </div>
</header>
<main>
<section class="hero" id="about">
  <div class="wrap hero-grid">
    <div>
      <div class="eyebrow"><i class="fa-solid fa-globe"></i> One connected marketplace</div>
      <h1>Buy local.<br><span class="accent">Order global.</span></h1>
      <p class="hero-copy">Makethub brings people, products and businesses together. Discover what you need, connect with sellers and explore a marketplace built to help commerce move forward.</p>
      <div class="hero-actions"><a class="btn btn-green" href="accountTypeSelection.php">Get started <i class="fa-solid fa-arrow-right"></i></a><button class="btn btn-outline" type="button" data-open-login><i class="fa-regular fa-user"></i> Log in to your account</button></div>
      <div class="trust-line"><span class="trust-dots"><span><i class="fa-solid fa-bag-shopping"></i></span><span><i class="fa-solid fa-store"></i></span><span><i class="fa-solid fa-house"></i></span></span><span>A shared space for shoppers, businesses and property owners.</span></div>
    </div>
    <div class="hero-visual" aria-label="Marketplace shopping and business imagery">
      <div class="hero-photo"><img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1100&q=85" alt="Bright modern retail store with products on display"><div class="photo-shade"></div><div class="photo-caption"><strong>More ways to make things happen.</strong><small>Discover. Connect. Grow.</small></div></div>
      <div class="float-card top"><span class="float-icon"><i class="fa-solid fa-store"></i></span><span><b>Your business, connected</b><small>Tools for everyday selling</small></span></div>
      <div class="float-card bottom"><div class="mini-avatars"><img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt=""><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=100&q=80" alt=""><img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=100&q=80" alt=""></div><span><b>Many roles. One platform.</b><small>Find your place on Makethub</small></span></div>
    </div>
  </div>
</section>
<section class="section" aria-labelledby="platform-title">
  <div class="wrap">
    <div class="section-head"><span class="section-kicker">What is Makethub?</span><h2 id="platform-title">A marketplace that works for more than one side of business.</h2><p>Makethub is designed to connect customers with businesses while giving sellers practical tools to run their day-to-day operations.</p></div>
    <div class="feature-grid">
      <article class="feature-card"><div class="feature-icon"><i class="fa-solid fa-bag-shopping"></i></div><h3>Discover and shop</h3><p>Explore products and services, find businesses and place orders through a connected marketplace experience.</p><a class="feature-link" href="accountTypeSelection.php">Join as a buyer <i class="fa-solid fa-arrow-right"></i></a></article>
      <article class="feature-card"><div class="feature-icon"><i class="fa-solid fa-shop"></i></div><h3>Run your store</h3><p>Manage products, keep track of stock, process point-of-sale transactions and handle online orders from your seller workspace.</p><a class="feature-link" href="accountTypeSelection.php">Join as a seller <i class="fa-solid fa-arrow-right"></i></a></article>
      <article class="feature-card"><div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div><h3>Keep business in view</h3><p>Use sales records, order tracking and sales analytics to understand activity and stay on top of business operations.</p><a class="feature-link" href="#how-it-works">See how it works <i class="fa-solid fa-arrow-right"></i></a></article>
    </div>
  </div>
</section>
<section class="section section-soft" id="how-it-works">
  <div class="wrap">
    <div class="section-head"><span class="section-kicker">Getting started</span><h2>Find your place. Then get moving.</h2><p>Whether you're here to shop or to do business, getting started is straightforward.</p></div>
    <div class="steps"><article class="step"><div class="step-number">1</div><h3>Create your account</h3><p>Choose the account type that matches how you want to use Makethub.</p></article><article class="step"><div class="step-number">2</div><h3>Set up your space</h3><p>Complete your account details and access the tools available in your workspace.</p></article><article class="step"><div class="step-number">3</div><h3>Connect and take action</h3><p>Shop, manage a store, support sales activity or manage your property account.</p></article></div>
  </div>
</section>
<section class="section" id="who-its-for">
  <div class="wrap">
    <div class="section-head"><span class="section-kicker">One platform, different journeys</span><h2>Makethub brings different participants together.</h2><p>Choose the account that reflects the role you play. Your workspace is tailored to that role.</p></div>
    <div class="audience-grid">
      <article class="audience"><i class="fa-solid fa-cart-shopping"></i><h3>Buyers</h3><p>Explore the marketplace, discover products and services, and manage your purchases.</p></article>
      <article class="audience"><i class="fa-solid fa-store"></i><h3>Sellers</h3><p>Showcase products, manage inventory, handle sales and follow customer orders.</p></article>
      <article class="audience"><i class="fa-solid fa-people-arrows-left-right"></i><h3>Sales agents</h3><p>Use an agent workspace to support sales activity and follow your earnings.</p></article>
      <article class="audience"><i class="fa-solid fa-house"></i><h3>Property owners</h3><p>Join Makethub through a dedicated property-owner account.</p></article>
    </div>
  </div>
</section>
<section class="section section-soft"><div class="wrap"><div class="cta-band"><div><h2>Ready to make your next move?</h2><p>Create your Makethub account and become part of the marketplace.</p></div><a class="btn" href="accountTypeSelection.php">Create your account <i class="fa-solid fa-arrow-right"></i></a></div></div></section>
</main>
<footer class="footer"><div class="wrap"><div class="footer-top"><a class="brand" href="/"><img src="Images/Makethub Logo.png" alt=""><span>make<span>thub</span></span></a><div class="footer-links"><a href="privacy.php">Privacy Policy</a><a href="terms.php">Terms &amp; Conditions</a><a href="contact.php">Contact Us</a></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Makethub.shop. All rights reserved.</span><span>Buy Local. Order Global!</span></div></div></footer>
</div>

<div class="modal-backdrop<?= ($error || $success) ? ' open' : '' ?>" id="loginModal" aria-hidden="<?= ($error || $success) ? 'false' : 'true' ?>">
  <section class="login-modal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <button class="modal-close" type="button" aria-label="Close login" data-close-login><i class="fa-solid fa-xmark"></i></button>
    <a class="modal-brand" href="/"><img src="Images/Makethub Logo.png" alt=""> Makethub</a>
    <h2 id="loginTitle">Welcome back</h2><p class="modal-intro">Log in to continue to your Makethub workspace.</p>
    <?php if ($error): ?><div class="form-alert error" role="alert"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($success): ?><div class="form-alert success" role="status"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="POST" action="index.php#login">
      <div class="login-field"><label for="identifier">Username, email or phone</label><input id="identifier" type="text" name="identifier" value="<?= htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required></div>
      <div class="login-field"><label for="password">Password</label><div class="password-wrap"><input id="password" type="password" name="password" autocomplete="current-password" required><button class="password-toggle" type="button" id="togglePassword" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div></div>
      <div class="login-field"><label for="login_type">Log in as</label><select id="login_type" name="login_type" required><option value="">Choose account type</option><option value="buyer" <?= $selectedRole==='buyer'?'selected':'' ?>>Buyer</option><option value="seller" <?= $selectedRole==='seller'?'selected':'' ?>>Seller</option><option value="sales_agent" <?= $selectedRole==='sales_agent'?'selected':'' ?>>Sales agent</option><option value="property_owner" <?= $selectedRole==='property_owner'?'selected':'' ?>>Property owner</option><option value="administrator" <?= $selectedRole==='administrator'?'selected':'' ?>>Administrator</option></select></div>
      <div class="remember-row"><label><input type="checkbox" name="remember" value="1"> Remember me</label><a href="contact.php">Need help?</a></div>
      <button class="btn btn-green login-submit" type="submit">Log in <i class="fa-solid fa-arrow-right"></i></button>
    </form>
    <p class="register-note">New to Makethub? <a href="accountTypeSelection.php">Create an account</a></p>
  </section>
</div>
<script>
const loginModal=document.getElementById('loginModal');
function openLogin(){loginModal.classList.add('open');loginModal.setAttribute('aria-hidden','false');setTimeout(()=>document.getElementById('identifier').focus(),80)}
function closeLogin(){loginModal.classList.remove('open');loginModal.setAttribute('aria-hidden','true')}
document.querySelectorAll('[data-open-login]').forEach(button=>button.addEventListener('click',openLogin));
document.querySelectorAll('[data-close-login]').forEach(button=>button.addEventListener('click',closeLogin));
loginModal.addEventListener('click',event=>{if(event.target===loginModal)closeLogin()});
document.addEventListener('keydown',event=>{if(event.key==='Escape')closeLogin()});
document.getElementById('togglePassword').addEventListener('click',function(){const field=document.getElementById('password');const showing=field.type==='text';field.type=showing?'password':'text';this.innerHTML=showing?'<i class="fa-regular fa-eye"></i>':'<i class="fa-regular fa-eye-slash"></i>';this.setAttribute('aria-label',showing?'Show password':'Hide password')});
<?php if (!empty($redirectAfterLogin)): ?>
setTimeout(()=>{window.location.href=<?= json_encode($redirectAfterLogin, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>},1800);
<?php endif; ?>
</script>
</body>
</html>
