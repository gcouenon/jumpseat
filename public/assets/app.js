(() => {
  const globalSpinner = document.getElementById("globalSpinner");
  const manualBtn = document.getElementById("manualBtn");
  const manualModal = document.getElementById("manualModal");
  const manualCloseBtn = document.getElementById("manualCloseBtn");
  const disclaimerBtn = document.getElementById("disclaimerBtn");
  const disclaimerModal = document.getElementById("disclaimerModal");
  const disclaimerCloseBtn = document.getElementById("disclaimerCloseBtn");
  const infoModal = document.getElementById("flightInfoModal");
  const interestModal = document.getElementById("interestModal");
  const modalBody = document.getElementById("modalBody");
  const modalTitle = document.getElementById("modalTitle");
  const modalUserPhoto = document.getElementById("modalUserPhoto");
  const interestFlightId = document.getElementById("interestFlightId");
  const contactFlightBtn = document.getElementById("contactFlightBtn");
  const infoModalContent = infoModal ? infoModal.querySelector(".modal-content") : null;
  let hoveredFlight = null;
  const labels = {
    title: document.body?.dataset.lblTitle || "Title",
    route: document.body?.dataset.lblRoute || "Route",
    aircraft: document.body?.dataset.lblAircraft || "Aircraft",
    departure: document.body?.dataset.lblDeparture || "Departure",
    return: document.body?.dataset.lblReturn || "Return",
    duration: document.body?.dataset.lblDuration || "Duration",
    seats: document.body?.dataset.lblSeats || "Seats",
    by: document.body?.dataset.lblBy || "By",
    info: document.body?.dataset.lblInfo || "Info",
    flightDetails: document.body?.dataset.lblFlightDetails || "Flight details",
    noSeat: document.body?.dataset.lblNoSeat || "No seat available",
  };

  const closeModal = (modal) => { if (modal) modal.classList.add("hidden"); };
  const openModal = (modal) => { if (modal) modal.classList.remove("hidden"); };
  const showGlobalSpinner = () => {
    if (globalSpinner) globalSpinner.classList.remove("hidden");
  };
  const escapeHtml = (value) => String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll("\"", "&quot;")
    .replaceAll("'", "&#39;");
  const eventPalette = [
    { bg: "#d7ecff", border: "#2f7fc5" },
    { bg: "#dff5e7", border: "#2f9f61" },
    { bg: "#ffe8d6", border: "#d67732" },
    { bg: "#f0e4ff", border: "#7d56c2" },
    { bg: "#ffe2eb", border: "#c2537d" },
    { bg: "#e1f3f8", border: "#3d8ea3" },
  ];
  const pickEventColor = (userId) => {
    const n = Number(userId || "0");
    return eventPalette[Math.abs(n) % eventPalette.length];
  };

  document.querySelectorAll("[data-close]").forEach((button) => {
    button.addEventListener("click", () => {
      closeModal(infoModal);
      closeModal(interestModal);
    });
  });

  if (infoModalContent) {
    infoModalContent.addEventListener("mouseleave", () => closeModal(infoModal));
  }

  if (contactFlightBtn) {
    contactFlightBtn.addEventListener("click", () => {
      if (!hoveredFlight) return;
      const seats = Number(hoveredFlight.dataset.seats || "0");
      if (seats <= 0 || !interestModal) return;
      if (interestFlightId) interestFlightId.value = hoveredFlight.dataset.flightId || "";
      openModal(interestModal);
    });
  }

  if (disclaimerBtn && disclaimerModal) {
    disclaimerBtn.addEventListener("click", () => openModal(disclaimerModal));
  }
  if (disclaimerCloseBtn && disclaimerModal) {
    disclaimerCloseBtn.addEventListener("click", () => closeModal(disclaimerModal));
  }
  if (manualBtn && manualModal) {
    manualBtn.addEventListener("click", () => openModal(manualModal));
  }
  if (manualCloseBtn && manualModal) {
    manualCloseBtn.addEventListener("click", () => closeModal(manualModal));
  }

  document.querySelectorAll(".flight-event").forEach((eventEl) => {
    if (!eventEl.classList.contains("disabled")) {
      const colors = pickEventColor(eventEl.dataset.userId);
      eventEl.style.backgroundColor = colors.bg;
      eventEl.style.borderLeftColor = colors.border;
    }

    eventEl.addEventListener("click", () => {
      hoveredFlight = eventEl;
      const details = [
        `<div><strong>${escapeHtml(labels.title)}:</strong> ${escapeHtml(eventEl.dataset.title || "")}</div>`,
        `<div><strong>${escapeHtml(labels.route)}:</strong> ${escapeHtml(eventEl.dataset.origin || "")} -> ${escapeHtml(eventEl.dataset.destination || "")}</div>`,
        `<div><strong>${escapeHtml(labels.aircraft)}:</strong> ${escapeHtml(eventEl.dataset.model || "")}</div>`,
        `<div><strong>${escapeHtml(labels.departure)}:</strong> ${escapeHtml(eventEl.dataset.departure || "")}</div>`,
        `<div><strong>${escapeHtml(labels.return)}:</strong> ${escapeHtml(eventEl.dataset.return || "")}</div>`,
        `<div><strong>${escapeHtml(labels.duration)}:</strong> ${escapeHtml(eventEl.dataset.duration || "")} minutes</div>`,
        `<div><strong>${escapeHtml(labels.seats)}:</strong> ${escapeHtml(eventEl.dataset.seats || "")}</div>`,
        `<div><strong>${escapeHtml(labels.by)}:</strong> ${escapeHtml(eventEl.dataset.owner || "")}</div>`,
        `<div><strong>${escapeHtml(labels.info)}:</strong> ${escapeHtml(eventEl.dataset.info || "-")}</div>`,
      ];
      if (modalTitle) modalTitle.textContent = labels.flightDetails;
      if (modalBody) modalBody.innerHTML = details.join("");
      if (modalUserPhoto) {
        const photoPath = eventEl.dataset.photo || "";
        if (photoPath) {
          modalUserPhoto.src = photoPath;
          modalUserPhoto.classList.remove("hidden");
        } else {
          modalUserPhoto.src = "";
          modalUserPhoto.classList.add("hidden");
        }
      }
      if (contactFlightBtn) {
        const canContact = Number(eventEl.dataset.seats || "0") > 0;
        contactFlightBtn.disabled = !canContact;
        contactFlightBtn.title = canContact ? "" : labels.noSeat;
      }
      openModal(infoModal);
    });
  });

  document.querySelectorAll(".event-actions").forEach((actionsEl) => {
    actionsEl.addEventListener("click", (event) => {
      event.stopPropagation();
    });
  });

  document.querySelectorAll(".day").forEach((dayEl) => {
    dayEl.addEventListener("click", (event) => {
      if (event.target.closest(".flight-event")) return;
      const date = dayEl.dataset.date;
      if (!date) return;
      showGlobalSpinner();
      window.location.href = `?action=flight_edit&prefill_date=${encodeURIComponent(date)}`;
    });
  });

  document.querySelectorAll("form").forEach((formEl) => {
    formEl.addEventListener("submit", () => {
      showGlobalSpinner();
      formEl.querySelectorAll("button[type='submit'], input[type='submit']").forEach((btn) => {
        btn.disabled = true;
      });
    });
  });

  window.addEventListener("pageshow", () => {
    if (globalSpinner) globalSpinner.classList.add("hidden");
  });
})();
