function toggleWhatsAppChat() {
  var box = document.getElementById("whatsapp-chat-box");
  var overlay = document.getElementById("overlay");
  box.style.display = box.style.display === "block" ? "none" : "block";
  overlay.classList.toggle("active");
}

function sendWhatsAppMessage() {
  var message = document.getElementById("userMessage").value.trim();
  if (message !== "") {
    var phoneNumber = "254781449115";
    var url = "https://wa.me/" + phoneNumber + "?text=" + encodeURIComponent(message);
    window.open(url, "_blank");
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const cards = document.querySelectorAll('.account-card');
  const radios = document.querySelectorAll('input[name="accountType"]');
  const form = document.querySelector('form');
  const errorMessage = document.querySelector('.errorMessage');

  radios.forEach((radio) => {
    radio.addEventListener('change', () => {
      cards.forEach(card => card.classList.remove('selected'));
      radio.closest('.account-card').classList.add('selected');
    });
  });

  if (errorMessage) {
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

/* Successful registration and redirecting */
document.addEventListener("DOMContentLoaded", () => {
  const successParagraph = document.querySelector(".successMessage");
  if (!successParagraph) return;

  const redirectSpan = document.getElementById("redirect-msg");
  if (!redirectSpan) return;

  const baseText = "Redirecting";
  let dots = "";
  let dotCount = 0;
  let typingDone = false;

  let i = 0;
  const typing = setInterval(() => {
    if (i < baseText.length) {
      redirectSpan.textContent += baseText.charAt(i);
      i++;
    } else {
      clearInterval(typing);
      typingDone = true;
      animateDots();
      setTimeout(() => {
        window.location.href = "index.php";
      }, 3500);
    }
  }, 100);

  function animateDots() {
    setInterval(() => {
      if (!typingDone) return;
      dotCount = (dotCount + 1) % 4;
      dots = ".".repeat(dotCount);
      redirectSpan.textContent = baseText + dots;
    }, 500);
  }
});

/* Toggle password visibility */
document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".toggle-password");

  toggles.forEach(icon => {
    icon.addEventListener("click", function () {
      const input = this.closest(".inpBox").querySelector(".password-field");

      if (input.type === "password") {
        input.type = "text";
        this.classList.replace("fa-eye", "fa-eye-slash");
        this.setAttribute("title", "Hide Password");

        // Auto-hide after 3 seconds
        setTimeout(() => {
          input.type = "password";
          this.classList.replace("fa-eye-slash", "fa-eye");
          this.setAttribute("title", "Show Password");
        }, 3000);
      } else {
        input.type = "password";
        this.classList.replace("fa-eye-slash", "fa-eye");
        this.setAttribute("title", "Show Password");
      }
    });
  });
});

/* Main Page Tabs */

const tabs = document.querySelectorAll('.tab-btn');
const panels = document.querySelectorAll('.tab-panel');

tabs.forEach(tab => {
tab.addEventListener('click', () => {
  const target = tab.dataset.tab;

  tabs.forEach(t => t.classList.remove('active'));
  panels.forEach(p => p.classList.remove('active'));

  tab.classList.add('active');
  document.getElementById(target).classList.add('active');
});
});

/* Profile Option Js */
function toggleProfileOption() {
  const profileOption = document.getElementById("profileOption");
  const overlay = document.getElementById("overlay1");
  profileOption.style.display = profileOption.style.display === "flex" ? "none" : "flex";
  overlay.classList.toggle("active");
}

/* Recent Order Js */

// Show only the 5 most recent orders
const tableBody = document.querySelector("#ordersTable tbody");
const allRows = Array.from(tableBody.querySelectorAll("tr"));
allRows.forEach((row, index) => {
  if(index >= 5) row.style.display = "none";
});

// Filter by status
const filter = document.getElementById("statusFilter");
filter.addEventListener("change", () => {
  const value = filter.value;
  let visibleCount = 0;

  document.querySelectorAll("#ordersTable tbody tr").forEach(el => {
    if (value === "all" || el.dataset.status === value) {
      el.style.display = visibleCount < 5 ? "" : "none"; // only 5
      visibleCount++;
    } else {
      el.style.display = "none";
    }
  });
});

/* Limit Mobile Order Cards Js */
document.addEventListener("DOMContentLoaded", () => {
  limitMobileOrderCards();
});

function limitMobileOrderCards(max = 5) {
  const cards = document.querySelectorAll("#orderCards .order-card");

  cards.forEach((card, index) => {
    card.style.display = index < max ? "block" : "none";
  });
}

/* Order Details Toggle Js */
document.querySelectorAll(".toggleOrd").forEach(btn => {
  btn.addEventListener("click", () => {
    const target = document.getElementById(btn.dataset.target);
    target.classList.toggle("active");
    btn.textContent = target.classList.contains("active")
      ? "Hide details"
      : "View details";
  });
});

function toggleOrderMain() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");
  orderMain.style.display = "block";
  marketMain.style.display = "none";
}

function toggleMarketMain() {
  const orderMain = document.getElementById("orderMain");
  const marketMain = document.getElementById("marketMain");
  orderMain.style.display = "none";
  marketMain.style.display = "block";
}