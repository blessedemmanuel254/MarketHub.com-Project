

// ADMIN DASHBOARD JS
function toggleNavigationBar() {
  const navOverlay = document.getElementById("navOverlay");
  navOverlay.classList.toggle("active");
  document.querySelector('.navigation-bar')?.classList.toggle('show');
}

const popup = document.getElementById("confirmation-popup");
const popupOverlay = document.getElementById("confirmation-popup-overlay");
const popupTitle = document.getElementById("popupTitle");
const popupMessage = document.getElementById("popupMessage");
const confirmBtn = document.getElementById("confirmAction");
const cancelBtn = document.getElementById("cancelAction");

// ✅ Safe event binding
if (popup) {
  popup.onclick = (e) => e.stopPropagation();
}

function showPopup(title, message, onConfirm) {

  if (!popup || !popupOverlay || !popupTitle || !popupMessage || !confirmBtn || !cancelBtn) return;

  popupTitle.textContent = title;
  popupMessage.textContent = message;

  popup.classList.add("active");
  popupOverlay.classList.add("active");

  confirmBtn.onclick = null;
  cancelBtn.onclick = null;
  popupOverlay.onclick = null;

  confirmBtn.onclick = () => {
    closePopup();
    onConfirm();
  };

  cancelBtn.onclick = closePopup;
  popupOverlay.onclick = closePopup;
}

function closePopup() {
  if (!popup || !popupOverlay) return;

  popup.classList.remove("active");
  popupOverlay.classList.remove("active");
}

document.addEventListener("DOMContentLoaded", () => {

  // Only run if admin dashboard exists
  if (!document.querySelector(".admin-tab-panel")) return;

  const tabs = document.querySelectorAll(".nav-link");
  const navBtnTabs = document.querySelectorAll(".btn-edit");
  const panels = document.querySelectorAll(".admin-tab-panel");

  // ---------- RESTORE LAST TAB ----------
  const savedTab = localStorage.getItem("adminActiveTab");

  if (savedTab) {
    tabs.forEach(t => t.classList.remove("active"));
    panels.forEach(p => p.classList.remove("active"));

    const savedTabBtn = document.querySelector(`.nav-link[data-tab="${savedTab}"]`);
    const savedPanel = document.querySelector(`.admin-tab-panel[data-tab="${savedTab}"]`);

    savedTabBtn?.classList.add("active");
    savedPanel?.classList.add("active");
  }

  // ---------- TAB CLICK ----------
  tabs.forEach(tab => {
    tab.addEventListener("click", e => {
      e.preventDefault();

      const target = tab.dataset.tab;
      if (!target) return;

      tabs.forEach(t => t.classList.remove("active"));
      panels.forEach(p => p.classList.remove("active"));

      tab.classList.add("active");

      document
        .querySelector(`.admin-tab-panel[data-tab="${target}"]`)
        ?.classList.add("active");
      window.scrollTo({ top: 0, behavior: "smooth" });

      // SAVE LAST TAB
      localStorage.setItem("adminActiveTab", target);

      toggleNavigationBar?.();
    });
  });
  navBtnTabs.forEach(tab => {
    tab.addEventListener("click", e => {
      e.preventDefault();

      const target = tab.dataset.tab;
      if (!target) return;
      panels.forEach(p => p.classList.remove("active"));

      document
        .querySelector(`.admin-tab-panel[data-tab="${target}"]`)
        ?.classList.add("active");
      window.scrollTo({ top: 0, behavior: "smooth" });  

      // SAVE LAST TAB
      localStorage.setItem("adminActiveTab", target);
    });
  });

});

document.addEventListener("click", function(e){

  const btn = e.target.closest(".action-btn");
  if(!btn) return;

  const actionsUserId = btn.dataset.userId;
  const action = btn.dataset.action;
  console.log(actionsUserId, action);

  if(!actionsUserId || !action) return;

  /* CONFIRMATION MESSAGE */

  let message = "";

  if(action === "suspend") message = "Suspend this user?";
  if(action === "restore") message = "Restore this user?";
  if(action === "activate") message = "Activate this agent?";
  if(action === "deactivate") message = "Deactivate this agent?";
  if(action === "delete") message = "Delete this user permanently?";

  let title = "Confirm Action";

  if(action === "delete") title = "Delete User";
  if(action === "suspend") title = "Suspend User";
  if(action === "restore") title = "Restore User";
  if(action === "activate") title = "Activate Agent";
  if(action === "deactivate") title = "Deactivate Agent";

showPopup(title, message, () => {

  /* AJAX REQUEST */

  fetch("user_functionalities.php",{
  method:"POST",
  headers:{
  "Content-Type":"application/x-www-form-urlencoded",
  "X-Requested-With":"XMLHttpRequest"
  },
  body: new URLSearchParams({
    user_id: actionsUserId,
    action: action
  })
  })
  .then(async res => {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("SERVER RESPONSE:", text);
      throw new Error("Invalid JSON");
    }
  })
  .then(data=>{

  if(!data.success){
  alert(data.error || "Action failed");
  return;
}

const row = btn.closest("tr");
const badge = row.querySelector(".badge");
const actionsCell = row.querySelector(".actions div");

/* ===== UPDATE ECONOMIC + SUB AGENTS ===== */

const subAgentsCell = row.querySelector(".sub-agents");
const economicCell = row.querySelector(".economic");

if (data.total_sub_agents !== undefined && subAgentsCell) {
  subAgentsCell.textContent = data.total_sub_agents;
}

if (data.economic_period_count !== undefined && economicCell) {
  economicCell.textContent = data.economic_period_count;
}

/* DELETE ROW */

if(action === "delete"){
row.remove();
return;
}


/* UPDATE STATUS BADGE */

if(action === "suspend"){
badge.textContent="Suspended";
badge.className="badge suspendedSpan";
}

if(action === "restore"){

  if(data.is_verified == 0){
    badge.textContent = "Unverified";
    badge.className = "badge unverified";
  } 
  else {
    if(data.is_expired){
      badge.textContent = "Expired";
      badge.className = "badge expired";
    } else {
      badge.textContent = "Verified";
      badge.className = "badge verified";
    }
  }
}

if(action === "activate"){
badge.textContent="Verified";
badge.className="badge verified";
}

if(action === "deactivate"){
badge.textContent="Unverified";
badge.className="badge unverified";
}


/* SWITCH BUTTONS */

if(action === "suspend"){
btn.outerHTML=`<button class="btn-restore action-btn"
data-action="restore"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-trash-can-arrow-up"></i></button>`;
}

if(action === "restore"){
btn.outerHTML=`<button class="btn-suspend action-btn"
data-action="suspend"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-ban"></i></button>`;
}

if(action === "activate"){
btn.outerHTML=`<button class="btn-deactivate action-btn"
data-action="deactivate"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-toggle-off"></i> Deactivate
</button>`;
}

if(action === "deactivate"){
btn.outerHTML=`<button class="btn-activate action-btn"
data-action="activate"
data-user-id="${actionsUserId}">
<i class="fa-solid fa-toggle-on"></i> Activate
</button>`;
}

})
.catch(()=>{
alert("Network error");
});

});

});
document.addEventListener("click", function(e){

const btn = e.target.closest(".copy-link-btn");
if(!btn) return;

const ref = btn.dataset.ref;

const link = `https://makethub.shop/agentregister.php?ref=${ref}`;

navigator.clipboard.writeText(link)
.then(()=>{

const original = btn.innerHTML;

btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';

setTimeout(()=>{
btn.innerHTML = original;
},2000);

})
.catch(()=>{
alert("Failed to copy link");
});

});

// EDIT FUNCTION - No page reload

const allowedTypes = ["agent","buyer","seller","owner","product"];

function editRecord(type, id) {
  if (!Number.isInteger(id) || id <= 0) return;
  if (!allowedTypes.includes(type)) return;

  const activePanel = document.querySelector(".admin-tab-panel.active");

  if (activePanel) {
    localStorage.setItem("previousAdminTab", activePanel.dataset.tab);
  }

  localStorage.setItem("activeForm", type);
  localStorage.setItem("editId", id);

  window.location.href = "adminPage.php?type=" + type + "&id=" + id;
}

function openAddProductForm(type) {
  if (!allowedTypes.includes(type)) return;

  const activePanel = document.querySelector(".admin-tab-panel.active");

  if (activePanel) {
    localStorage.setItem("previousAdminTab", activePanel.dataset.tab);
  }
  localStorage.setItem("activeForm", type);
  localStorage.removeItem("editId");

  window.location.href = "adminPage.php?type=product";
}

// Delete Makethub Products JS
document.addEventListener("click", function(e){

  const btn = e.target.closest(".delete-product-btn");
  if (!btn) return;

  const productId = btn.dataset.productId;
  if (!productId) return;

  showPopup("Delete Product", "Delete this product permanently?", () => {

    fetch("adminPage.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new URLSearchParams({
        ajax_delete_product: 1,
        product_id: productId
      })
    })
    .then(res => res.json())
    .then(data => {

      if (!data.success) {
        alert(data.error || "Delete failed");
        return;
      }

      try {
        const card = btn.closest(".product-card");

        if (card) {
          const grid = card.closest(".products-grid-admin");

          card.remove();

          if (grid && grid.querySelectorAll(".product-card").length === 0) {
            grid.innerHTML = '<p class="noproducts-admin-p">No products in this category.</p>';
          }
        }

        setTimeout(() => {
          location.reload();
        }, 1000);

      } catch (err) {
        console.error("DOM error:", err);
      }

    })
    .catch(err => {
      console.error("FETCH ERROR:", err);
      alert("Network error");
    });

  });

});

// RESTORE FORM AFTER PAGE RELOAD
document.addEventListener("DOMContentLoaded", function () {
  const activeForm = localStorage.getItem("activeForm");
  const editId = localStorage.getItem("editId");

  if (activeForm && allowedTypes.includes(activeForm)) {
    allowedTypes.forEach(t => {
      const f = document.getElementById(t + "-edit-form");
      if (f) f.style.display = "none";
    });

    const form = document.getElementById(activeForm + "-edit-form");
    if (form) form.style.display = "flex";

    // Restore URL if missing
    const params = new URLSearchParams(window.location.search);
    if (!params.get("id") && editId) {
      window.history.replaceState(null, "", "adminPage.php?type=" + activeForm + "&id=" + editId);
    }
  }
});