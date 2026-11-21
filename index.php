
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="apple-touch-icon" sizes="180x180" href="Images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="Images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="Images/favicon-16x16.png">
  <link rel="manifest" href="Images/site.webmanifest">

  <link rel="stylesheet" href="assets/css/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- jQuery + DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <title>Marketplace | Makethub</title>
</head>

<body>
  <div class="container">


<!-- HEADER -->
<header class="header-M">

    <div class="header-top">

        <div class="logo">
            <img src="Images/Makethub Logo.png" alt="Makethub Logo" width="40" height="40">
            <p>Makethub <br><span>One Hub, every market!</span></p>
        </div>

        <div class="search-box">
            <span class="search-icon">⌕</span>
            <input id="searchInput" type="search"
                   placeholder="Search products, services, properties...">
            <button class="search-clear" id="clearSearch">✕</button>
        </div>

        <div class="header-actions">
            <a href="/login" class="header-action" onclick="showToast('Your account')">
                <i class="fa-solid fa-user-lock"></i>
                Signup / Login
            </a>

            <button class="header-action" onclick="showToast('Your cart')">
                <i class="fa-brands fa-opencart"></i>
                Cart
                <span class="cart-count" id="cartCount">0</span>
            </button>
        </div>

    </div>

    <!-- MAJOR MARKETPLACE TABS -->
    <div class="main-tabs">

        <button class="main-tab active" data-tab="products">Products
        </button>

        <button class="main-tab" data-tab="services">Services
        </button>

        <button class="main-tab" data-tab="realestate">Real Estate
        </button>

    </div>

</header>

<!-- CATEGORY BAR -->
<div class="category-bar">
    <div class="categories" id="categories"></div>
</div>

<!-- MAIN CONTENT -->
<main class="container-M page">

    <!-- PROMO -->
    <section class="section">
        <div class="promo" id="promo">

            <img id="promoImage"
                 src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1400&q=85"
                 alt="Marketplace promotion">

            <div class="promo-content">
                <span class="promo-label" id="promoLabel">MAKETHUB DEALS</span>
                <h1 id="promoTitle">Everything you need.<br>All in one place.</h1>
                <p id="promoText">Discover great deals from businesses around you.</p>
                <button class="promo-btn" onclick="scrollToListings()">
                    Explore Now →
                </button>
            </div>

            <div class="promo-dots" id="promoDots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>

        </div>
    </section>

    <!-- QUICK LINKS -->
    <section class="section">
        <div class="quick-links" id="quickLinks"></div>
    </section>

    <!-- LISTINGS -->
    <section class="section" id="listingSection">

        <div class="section-heading">
            <h2 id="listingTitle">Discover Products</h2>
            <button class="see-all" onclick="resetCategory()">See all →</button>
        </div>

        <div class="product-grid" id="listingGrid"></div>

    </section>

    <!-- PROMO BANNERS -->
    <section class="section" id="bannerSection">
        <div class="banner-grid" id="bannerGrid"></div>
    </section>

</main>

<!-- MOBILE BOTTOM NAVIGATION -->
<nav class="bottom-nav">
    <div class="bottom-nav-inner">

        <button class="nav-item active" onclick="bottomNav('home',this)">
            <i class="fa-solid fa-house"></i>Home
        </button>

        <button class="nav-item" onclick="bottomNav('categories',this)">
            <i class="fa-solid fa-list"></i>Categories
        </button>

        <button class="nav-item" onclick="bottomNav('messages',this)">
            <i class="fa-solid fa-comments"></i>Messages
        </button>

        <button class="nav-item" onclick="bottomNav('cart',this)">
            <i class="fa-brands fa-opencart"></i>Cart
        </button>

        <a href="/login" class="nav-item" onclick="bottomNav('account',this)">
            <i class="fa-solid fa-user-lock"></i>Signup / Login
        </a>

    </div>
</nav>

  </div>

<div class="toast" id="toast"></div>

<script>

/* =========================================================
   MAKETHUB FRONTEND DATA
   Replace these sample listings with database/API data later.
========================================================= */

const marketplace = {

    products: {

        title: "Discover Products",

        searchPlaceholder: "Search products, shops, brands...",

        categories: [
            "All", "Electronics", "Phones", "Fashion",
            "Home & Kitchen", "Beauty", "Shoes",
            "Furniture", "Groceries", "Accessories"
        ],

        quickLinks: [
            ["📱", "Phones"],
            ["👗", "Fashion"],
            ["🏠", "Home"],
            ["🎧", "Electronics"],
            ["👟", "Shoes"]
        ],

        promo: {
            label: "MEGA SAVINGS",
            title: "Shop smarter.<br>Save more.",
            text: "Discover amazing offers from businesses across Kenya.",
            image: "https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1400&q=85"
        },

        banners: [
            {
                title: "Fashion Finds",
                text: "Fresh styles. Great prices.",
                image: "https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=800&q=80"
            },
            {
                title: "Upgrade Your Home",
                text: "Beautiful essentials for every home.",
                image: "https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80"
            }
        ],

        listings: [
            {
                title: "Premium Wireless Bluetooth Headphones",
                price: 2499,
                old: 3999,
                category: "Electronics",
                rating: 4.8,
                reviews: 36,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Modern Women's Handbag",
                price: 1499,
                old: 2500,
                category: "Fashion",
                rating: 4.6,
                reviews: 21,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Smart Watch Series",
                price: 2199,
                old: 3500,
                category: "Electronics",
                rating: 4.7,
                reviews: 18,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Casual Men's Sneakers",
                price: 2800,
                old: 4200,
                category: "Shoes",
                rating: 4.5,
                reviews: 42,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Elegant Home Decor",
                price: 1800,
                old: 2400,
                category: "Home & Kitchen",
                rating: 4.6,
                reviews: 12,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Travel Backpack",
                price: 1250,
                old: 2000,
                category: "Accessories",
                rating: 4.4,
                reviews: 30,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Skincare Essentials",
                price: 999,
                old: 1500,
                category: "Beauty",
                rating: 4.8,
                reviews: 51,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Wireless Earbuds",
                price: 1599,
                old: 2500,
                category: "Electronics",
                rating: 4.5,
                reviews: 24,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Kitchen Storage Set",
                price: 1400,
                old: 2100,
                category: "Home & Kitchen",
                rating: 4.3,
                reviews: 14,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Classic Wrist Watch",
                price: 2999,
                old: 4500,
                category: "Accessories",
                rating: 4.7,
                reviews: 28,
                delivery: "Local Dispatch",
                image: "https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=600&q=80"
            }
        ]
    },

    services: {

        title: "Discover Services",

        searchPlaceholder: "Find professionals, services...",

        categories: [
            "All", "Beauty", "Repairs", "Cleaning",
            "Photography", "Design", "Education",
            "Transport", "Events", "Technology"
        ],

        quickLinks: [
            ["💇", "Beauty"],
            ["🔧", "Repairs"],
            ["📸", "Photography"],
            ["🧹", "Cleaning"],
            ["💻", "Technology"]
        ],

        promo: {
            label: "FIND THE RIGHT PROFESSIONAL",
            title: "Services made<br>simple.",
            text: "Connect with skilled professionals near you.",
            image: "https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1400&q=85"
        },

        banners: [
            {
                title: "Hire a Professional",
                text: "Reliable services near you.",
                image: "https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=800&q=80"
            },
            {
                title: "Grow Your Business",
                text: "Showcase your skills on Makethub.",
                image: "https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=800&q=80"
            }
        ],

        listings: [
            {
                title: "Professional Photography",
                price: 3500,
                category: "Photography",
                rating: 4.9,
                reviews: 22,
                description: "Events, portraits and product photography.",
                image: "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Graphic Design Services",
                price: 500,
                category: "Design",
                rating: 4.8,
                reviews: 45,
                description: "Posters, branding and social media designs.",
                image: "https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Home Cleaning",
                price: 1500,
                category: "Cleaning",
                rating: 4.6,
                reviews: 17,
                description: "Professional home and office cleaning.",
                image: "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Salon & Beauty Services",
                price: 800,
                category: "Beauty",
                rating: 4.7,
                reviews: 33,
                description: "Beauty, hair styling and grooming.",
                image: "https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Computer Repair",
                price: 1000,
                category: "Technology",
                rating: 4.5,
                reviews: 14,
                description: "Computer maintenance and troubleshooting.",
                image: "https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Plumbing Services",
                price: 1200,
                category: "Repairs",
                rating: 4.6,
                reviews: 19,
                description: "Installation and plumbing repairs.",
                image: "https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Private Tutoring",
                price: 500,
                category: "Education",
                rating: 4.9,
                reviews: 28,
                description: "Professional tutoring for students.",
                image: "https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Event Decoration",
                price: 5000,
                category: "Events",
                rating: 4.8,
                reviews: 11,
                description: "Decor for celebrations and events.",
                image: "https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=600&q=80"
            }
        ]
    },

    realestate: {

        title: "Discover Properties",

        searchPlaceholder: "Search houses, land, apartments...",

        categories: [
            "All", "Rentals", "Houses", "Apartments",
            "Land", "Commercial", "Bedsitters", "Short Stay"
        ],

        quickLinks: [
            ["🏠", "Houses"],
            ["🏢", "Apartments"],
            ["🌍", "Land"],
            ["🔑", "Rentals"],
            ["🏬", "Commercial"]
        ],

        promo: {
            label: "FIND YOUR PLACE",
            title: "Your next home<br>starts here.",
            text: "Explore property listings from across Kenya.",
            image: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=85"
        },

        banners: [
            {
                title: "Find Your Dream Home",
                text: "Explore properties for sale.",
                image: "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80"
            },
            {
                title: "Property for Rent",
                text: "Discover available spaces.",
                image: "https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=800&q=80"
            }
        ],

        listings: [
            {
                title: "Modern 2 Bedroom Apartment",
                price: 35000,
                category: "Apartments",
                location: "Kilifi",
                features: "2 Bedrooms · 2 Baths · Parking",
                image: "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Beautiful Family Home",
                price: 65000,
                category: "Houses",
                location: "Mombasa",
                features: "3 Bedrooms · 2 Baths · Garden",
                image: "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Prime Residential Plot",
                price: 1200000,
                category: "Land",
                location: "Kilifi County",
                features: "50 × 100 · Ready Title · Road Access",
                image: "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Furnished Airbnb Apartment",
                price: 4500,
                category: "Short Stay",
                location: "Nyali, Mombasa",
                features: "1 Bedroom · WiFi · Pool",
                image: "https://images.unsplash.com/photo-1613490493576-7tmfmy1b212?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Affordable Bedsitter",
                price: 8000,
                category: "Bedsitters",
                location: "Mtwapa",
                features: "Single Room · Water · Security",
                image: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=600&q=80"
            },
            {
                title: "Commercial Shop Space",
                price: 20000,
                category: "Commercial",
                location: "Kilifi Town",
                features: "Roadside · High Foot Traffic",
                image: "https://images.unsplash.com/photo-1604719312566-8912e9c8a213?auto=format&fit=crop&w=600&q=80"
            }
        ]
    }
};

/* =========================================================
   STATE
========================================================= */

let currentTab = "products";
let currentCategory = "All";
let searchQuery = "";
let cart = 0;

const data = () => marketplace[currentTab];

/* =========================================================
   RENDER PAGE
========================================================= */

function renderPage() {

    const current = data();

    document.getElementById("searchInput").placeholder =
        current.searchPlaceholder;

    document.getElementById("listingTitle").textContent =
        current.title;

    renderCategories();
    renderPromo();
    renderQuickLinks();
    renderBanners();
    renderListings();
}

/* =========================================================
   CATEGORY NAVIGATION
========================================================= */

function renderCategories() {

    const categories = data().categories;

    document.getElementById("categories").innerHTML =
        categories.map(category => `
            <button class="category-chip
                ${currentCategory === category ? 'active' : ''}"
                onclick="selectCategory('${category}')">
                ${category}
            </button>
        `).join("");
}

function selectCategory(category) {

    currentCategory = category;

    renderCategories();
    renderListings();
}

function resetCategory() {

    currentCategory = "All";

    renderCategories();
    renderListings();
}

/* =========================================================
   PROMOTIONAL HERO
========================================================= */

function renderPromo() {

    const promo = data().promo;

    document.getElementById("promoLabel").textContent = promo.label;
    document.getElementById("promoTitle").innerHTML = promo.title;
    document.getElementById("promoText").textContent = promo.text;
    document.getElementById("promoImage").src = promo.image;
}

/* =========================================================
   QUICK LINKS
========================================================= */

function renderQuickLinks() {

    document.getElementById("quickLinks").innerHTML =
        data().quickLinks.map(item => `
            <button class="quick-link"
                onclick="selectCategory('${item[1]}')">
                <span class="emoji">${item[0]}</span>
                <span>${item[1]}</span>
            </button>
        `).join("");
}

/* =========================================================
   PROMO BANNERS
========================================================= */

function renderBanners() {

    document.getElementById("bannerGrid").innerHTML =
        data().banners.map(banner => `
            <div class="small-banner">
                <img src="${banner.image}" alt="${banner.title}">
                <div class="small-banner-content">
                    <h3>${banner.title}</h3>
                    <p>${banner.text}</p>
                </div>
            </div>
        `).join("");
}

/* =========================================================
   LISTINGS
========================================================= */

function renderListings() {

    const listings = data().listings;

    const filtered = listings.filter(item => {

        const categoryMatch =
            currentCategory === "All" ||
            item.category === currentCategory ||
            item.title.toLowerCase().includes(currentCategory.toLowerCase());

        const searchMatch =
            item.title.toLowerCase().includes(searchQuery) ||
            item.category.toLowerCase().includes(searchQuery) ||
            (item.location &&
             item.location.toLowerCase().includes(searchQuery)) ||
            (item.description &&
             item.description.toLowerCase().includes(searchQuery));

        return categoryMatch && searchMatch;
    });

    if (!filtered.length) {

        document.getElementById("listingGrid").innerHTML = `
            <div class="empty">
                <div>🔎</div>
                <h3>No listings found</h3>
                <p>Try another search or category.</p>
            </div>
        `;

        return;
    }

    if (currentTab === "products") {
        renderProducts(filtered);
    }

    if (currentTab === "services") {
        renderServices(filtered);
    }

    if (currentTab === "realestate") {
        renderProperties(filtered);
    }
}

/* =========================================================
   PRODUCT CARDS
========================================================= */

function renderProducts(items) {

    document.getElementById("listingGrid").innerHTML =
        items.map((item, index) => {

            const discount = item.old
                ? Math.round((1 - item.price / item.old) * 100)
                : 0;

            return `
                <div class="product-card">

                    <div class="product-image">
                        <img src="${item.image}" alt="${item.title}">

                        ${discount ? `
                            <span class="discount">
                                -${discount}%
                            </span>
                        ` : ""}

                        <button class="favorite"
                            onclick="toggleFavorite(this)">♡</button>
                    </div>

                    <div class="product-info">

                        <div class="product-title">
                            ${item.title}
                        </div>

                        <div class="product-price">
                            KSh ${Number(item.price).toLocaleString()}

                            ${item.old ? `
                                <span class="old-price">
                                    KSh ${Number(item.old).toLocaleString()}
                                </span>
                            ` : ""}
                        </div>

                        <div class="rating">
                            ★★★★★ <span>(${item.reviews})</span>
                        </div>

                        <div class="delivery">
                            ✓ ${item.delivery || "Available"}
                        </div>

                        <button class="add-cart"
                            onclick="addToCart('${item.title}')">
                            + Add to Cart
                        </button>

                    </div>

                </div>
            `;
        }).join("");
}

/* =========================================================
   SERVICE CARDS
========================================================= */

function renderServices(items) {

    document.getElementById("listingGrid").innerHTML =
        items.map(item => `
            <div class="service-card">

                <img src="${item.image}" alt="${item.title}">

                <div class="service-icon">🛠️</div>

                <h3>${item.title}</h3>

                <p>${item.description || "Professional service available for booking."}</p>

                <div class="service-price">
                    From KSh ${Number(item.price).toLocaleString()}
                </div>

                <div class="service-rating">
                    ★★★★★ (${item.reviews})
                </div>

                <button class="add-cart"
                    style="margin-top:12px"
                    onclick="showToast('Service selected: ${item.title}')">
                    View Service
                </button>

            </div>
        `).join("");
}

/* =========================================================
   PROPERTY CARDS
========================================================= */

function renderProperties(items) {

    document.getElementById("listingGrid").innerHTML =
        items.map(item => `
            <div class="property-card">

                <div class="property-image">
                    <img src="${item.image}" alt="${item.title}">

                    <span class="property-tag">
                        ${item.category}
                    </span>

                    <button class="favorite"
                        onclick="toggleFavorite(this)">♡</button>
                </div>

                <div class="property-info">

                    <h3>${item.title}</h3>

                    <div class="location">
                        📍 ${item.location}
                    </div>

                    <div class="property-price">
                        KSh ${Number(item.price).toLocaleString()}
                        ${item.category === "Short Stay" ? "/ night" :
                          item.category === "Land" ? "" : "/ month"}
                    </div>

                    <div class="property-features">
                        ${item.features}
                    </div>

                    <button class="add-cart"
                        style="margin-top:12px"
                        onclick="showToast('Property selected')">
                        View Property
                    </button>

                </div>

            </div>
        `).join("");
}

/* =========================================================
   TAB SWITCHING
========================================================= */

document.querySelectorAll(".main-tab").forEach(button => {

    button.addEventListener("click", () => {

        document.querySelectorAll(".main-tab")
            .forEach(tab => tab.classList.remove("active"));

        button.classList.add("active");

        currentTab = button.dataset.tab;
        currentCategory = "All";
        searchQuery = "";

        document.getElementById("searchInput").value = "";

        renderPage();
    });
});

/* =========================================================
   SEARCH
========================================================= */

document.getElementById("searchInput")
    .addEventListener("input", function() {

        searchQuery = this.value.toLowerCase().trim();

        document.getElementById("clearSearch").style.display =
            searchQuery ? "block" : "none";

        renderListings();
    });

document.getElementById("clearSearch")
    .addEventListener("click", function() {

        document.getElementById("searchInput").value = "";

        searchQuery = "";

        this.style.display = "none";

        renderListings();
    });

/* =========================================================
   CART
========================================================= */

function addToCart(name) {

    cart++;

    const count = document.getElementById("cartCount");

    count.textContent = cart;
    count.style.display = "block";

    showToast(name + " added to cart");
}

/* =========================================================
   FAVORITES
========================================================= */

function toggleFavorite(button) {

    button.classList.toggle("active");

    button.innerHTML =
        button.classList.contains("active") ? "♥" : "♡";

    showToast(
        button.classList.contains("active")
        ? "Added to favorites"
        : "Removed from favorites"
    );
}

/* =========================================================
   TOAST
========================================================= */

let toastTimeout;

function showToast(message) {

    const toast = document.getElementById("toast");

    toast.textContent = message;
    toast.classList.add("show");

    clearTimeout(toastTimeout);

    toastTimeout = setTimeout(() => {
        toast.classList.remove("show");
    }, 2200);
}

/* =========================================================
   BOTTOM NAV
========================================================= */

function bottomNav(name, button) {

    document.querySelectorAll(".nav-item")
        .forEach(item => item.classList.remove("active"));

    button.classList.add("active");

    if (name === "home") {
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    if (name === "categories") {
        document.querySelector(".category-bar")
            .scrollIntoView({ behavior: "smooth" });
    }

    if (name === "cart") {
        showToast("Cart: " + cart + " items");
    }

    if (name === "messages") {
        showToast("Messages");
    }

    if (name === "account") {
        showToast("Account");
    }
}

/* =========================================================
   SCROLL TO LISTINGS
========================================================= */

function scrollToListings() {

    document.getElementById("listingSection")
        .scrollIntoView({ behavior: "smooth" });
}

/* =========================================================
   COUNTDOWN
========================================================= */

let countdown = 8 * 3600 + 24 * 60 + 45;

function updateCountdown() {

    if (countdown <= 0) countdown = 8 * 3600;

    const h = Math.floor(countdown / 3600);
    const m = Math.floor((countdown % 3600) / 60);
    const s = countdown % 60;

    document.getElementById("hours").textContent =
        String(h).padStart(2, "0");

    document.getElementById("minutes").textContent =
        String(m).padStart(2, "0");

    document.getElementById("seconds").textContent =
        String(s).padStart(2, "0");

    countdown--;
}

setInterval(updateCountdown, 1000);

/* =========================================================
   INITIALIZE
========================================================= */

renderPage();
updateCountdown();

</script>

</body>
</html>