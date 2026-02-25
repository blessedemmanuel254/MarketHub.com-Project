<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="apple-touch-icon" sizes="180x180" href="Images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="Images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="Images/favicon-16x16.png">
  <link rel="manifest" href="Images/site.webmanifest">

  <link rel="stylesheet" href="styles/general.css">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Chewy&display=swap" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,70090000000;1,800;1,900&display=swap" rel="stylesheet">

  <title>Help Center | Market Hub</title>
</head>

<body>
  <div class="container faq-wrapper">
    <div class="overlay" onclick="toggleWhatsAppChat()" id="overlay"></div>
    <div id="whatsapp-button" onclick="toggleWhatsAppChat()">
      <img src="Images/Market Hub WhatsApp Icon.avif" width="45" alt="Chat with us on WhatsApp">
    </div>

    <div id="whatsapp-chat-box">
      <div class="chat-header">
        <div class="top">
          <img src="Images/Market Hub Logo.avif" alt="Market Hub Logo" width="35">
          <p><strong>Market Hub</strong><br>
          <small>online</small></p>
        </div>
        <i class="fa-solid fa-xmark" onclick="toggleWhatsAppChat()"></i>
      </div>
      <div class="chat-body">
        <div class="chat-container">
          <div class="chat-bubble">
            <div class="sender">Market Hub</div>
            <div class="message">
              Hello there! 😊<br>
              How can we help?
            </div>
            <div class="time">
              11:31 PM
            </div>
          </div>
        </div>
        <div class="containerWhp">
          <textarea id="userMessage" placeholder="Type a message.."></textarea>
          <img src="Images/Send-35.png" alt="Send Icon" width="45" onclick="sendWhatsAppMessage()">
        </div>
      </div>
    </div>
    <main class="help-centre-main">
      <button class="support-btn" onclick="toggleWhatsAppChat()">Contact Support<i class="fa-solid fa-arrow-trend-up"></i></button>

      <div class="faq-header">
        <h1>Help Centre</h1>
        <p>
          Answers to common questions about using Market Hub, from account management to buying and selling.
        </p>
      </div>

      <div class="faq-list" role="region" aria-label="Frequently Asked Questions">

        <!-- 1 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>What is Market Hub?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Market Hub is an online marketplace where you can buy products, book services,
              and rent items from local, national, and global sellers.
            </p>
          </div>
        </div>

        <!-- 2 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>What is the difference between Local, National, and Global markets?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Local markets show sellers near you, National markets include sellers countrywide,
              and Global markets allow international transactions.
            </p>
          </div>
        </div>

        <!-- 3 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do I create an account?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Click on Sign Up, provide your required details, verify your contact information,
              and you’ll be ready to use Market Hub.
            </p>
          </div>
        </div>

        <!-- 4 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do I buy products on Market Hub?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Browse products, select your preferred market, add items to cart, and complete
              checkout using supported payment methods.
            </p>
          </div>
        </div>

        <!-- 5 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Can I track my orders?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Yes. All orders can be tracked from your Orders page, including payment,
              processing, and delivery status.
            </p>
          </div>
        </div>

        <!-- 6 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do service bookings work?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              You choose a service provider, review pricing and availability, and place a booking
              request which the provider confirms.
            </p>
          </div>
        </div>

        <!-- 7 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do rentals work?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Rentals are charged based on duration. You select start and end dates,
              make payment, and return the item on time.
            </p>
          </div>
        </div>

        <!-- 8 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>What payment methods are supported?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Market Hub supports M-Pesa, card payments, and other secure options depending
              on your selected market.
            </p>
          </div>
        </div>

        <!-- 9 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>What happens if my payment fails?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              If payment fails, the order will not be processed. You can retry or choose
              a different payment method.
            </p>
          </div>
        </div>

        <!-- 10 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Can I cancel an order?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Orders can be cancelled before processing, depending on the seller’s policy.
            </p>
          </div>
        </div>

        <!-- 13 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Do I need an account to browse products?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              You can browse without an account, but placing orders or bookings
              requires signing in.
            </p>
          </div>
        </div>

        <!-- 14 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do I become a seller on Market Hub?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Apply through the seller registration process and provide the required
              verification details.
            </p>
          </div>
        </div>

        <!-- 15 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Are sellers verified?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Yes. Sellers go through a verification process to ensure trust
              and platform safety.
            </p>
          </div>
        </div>

        <!-- 16 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Is Market Hub safe to use?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Yes. We use secure payment systems, data protection measures,
              and continuous monitoring.
            </p>
          </div>
        </div>

        <!-- 17 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do I change my account details?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              You can update your profile details from the Account Profile page.
            </p>
          </div>
        </div>

        <!-- 18 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>Does Market Hub support international users?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              Yes. Global markets allow international buyers and sellers
              to transact on the platform.
            </p>
          </div>
        </div>

        <!-- 19 -->
        <div class="faq-item">
          <div class="faq-question" role="button" tabindex="0" aria-expanded="false"
            onclick="toggleFAQ(this)" onkeydown="handleKey(event,this)">
            <h3>How do I contact Market Hub support?</h3>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2">
              <path d="M6 9l6 6 6-6"/>
            </svg>
          </div>
          <div class="faq-answer">
            <p>
              You can reach support via the Contact Support button or through
              the Help section in your account.
            </p>
          </div>
        </div>

      </div>

    </main>
    <footer>
      <p>&copy; 2025/2026, Market Hub.com, All Rights reserved.</p>
    </footer>
  </div>
  
  <script src="Scripts/general.js" type="text/javascript" defer></script>

  <script>
    function toggleFAQ(element) {
      const currentItem = element.parentElement;
      const isOpen = currentItem.classList.contains("active");

      document.querySelectorAll(".faq-item").forEach(item => {
        item.classList.remove("active");
        item.querySelector(".faq-question").setAttribute("aria-expanded", "false");
      });

      if (!isOpen) {
        currentItem.classList.add("active");
        element.setAttribute("aria-expanded", "true");
      }
    }

    function handleKey(event, element) {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        toggleFAQ(element);
      }
    }
  </script>

</body>
</html>