/**
 * Alwin pricing wizard
 */
(function () {
  "use strict";

  const DATA = window.ALWIN_PRICING;
  const API = window.ALWIN_CONFIG?.apiUrl || "/api/v1";
  const TOTAL_STEPS = 5;

  const els = {};
  const state = {
    step: 1,
    tab: null,
    categoryId: null,
    modelId: null,
    profileId: null,
    glassId: null,
    hardwareId: null,
    hardwareType: "tilt",
    estimateTimer: null,
    lastPrice: null,
    lastQuote: null,
    modelFilter: "",
    subFilter: "",
  };

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function $$(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }

  function t(key, fallback) {
    const copy = window.ALWIN_CONFIG && window.ALWIN_CONFIG.copy;
    const value = copy && copy[key];
    return value != null && String(value) !== "" ? String(value) : fallback;
  }

  function formatPrice(n) {
    if (!n || n < 0) return "—";
    return Math.round(n).toLocaleString("fa-IR") + " " + t("calc_currency", "تومان");
  }

  function modelImg(id) {
    return DATA.modelImage(id);
  }

  function modelPicture(id, extraAttrs) {
    const src = modelImg(id);
    return `<img src="${src}" ${extraAttrs}>`;
  }

  function getModel() {
    if (!state.modelId) return null;
    return DATA.models[state.modelId] || DATA.models[String(state.modelId)] || null;
  }

  function getTabLabel() {
    return DATA.tabs.find((t) => t.id === state.tab)?.label || "";
  }

  function getCategoryLabel() {
    if (!state.tab || !state.categoryId) return "";
    const cat = DATA.categories[state.tab]?.find((c) => c.id === state.categoryId);
    return cat?.label || "";
  }

  function calcPrice() {
    const model = getModel();
    if (!model || !window.AlwinPrice) return null;
    const w = parseFloat(els.width?.value);
    const h = parseFloat(els.height?.value);
    if (!w || !h) return null;
    const quote = window.AlwinPrice.quote(
      {
        model_id: state.modelId,
        width_cm: w,
        height_cm: h,
        quantity: parseInt(els.quantity?.value || "1", 10) || 1,
        profile_id: state.profileId,
        glass_id: state.glassId,
        hardware_id: state.hardwareId,
        hardware_type: state.hardwareType,
      },
      DATA
    );
    state.lastQuote = quote;
    return quote.ok ? quote.total : null;
  }

  async function loadCatalog() {
    try {
      const res = await fetch(`${API}/pricing`, { headers: { Accept: "application/json" } });
      if (!res.ok) return;
      const json = await res.json();
      Object.assign(DATA, json);
      DATA.modelImage = function (id) {
        const m = DATA.models && (DATA.models[id] || DATA.models[String(id)]);
        if (m && m.image) return m.image;
        return "assets/img/pricing/" + id + ".webp";
      };
    } catch {
      /* static catalog */
    }
  }

  async function boot() {
    els.root = document.getElementById("alwin-calculator");
    if (!els.root || !DATA) return;
    await loadCatalog();
    init();
  }

  function init() {

    els.backdrop = $(".alwin-calc__backdrop", els.root);
    els.closeBtn = $(".alwin-calc__close", els.root);
    els.successClose = $(".alwin-calc__close-btn", els.root);
    els.crumb = $(".alwin-calc__crumb", els.root);
    els.steps = $$(".alwin-calc__step[data-step-indicator]", els.root);
    els.panels = $$(".alwin-calc__step-panel", els.root);
    els.tabs = $(".alwin-calc__tabs", els.root);
    els.categories = $(".alwin-calc__categories", els.root);
    els.models = $(".alwin-calc__models", els.root);
    els.search = $(".alwin-calc__search", els.root);
    els.selectedModel = $(".alwin-calc__selected-model", els.root);
    els.summary = $(".alwin-calc__summary", els.root);
    els.profile = $("#calc-profile", els.root);
    els.glass = $("#calc-glass", els.root);
    els.hardware = $("#calc-hardware", els.root);
    els.hardwareType = $("#calc-hardware-type", els.root);
    els.hardwareTypeWrap = $("#calc-hw-type-wrap", els.root);
    els.glassWrap = $("#calc-glass-wrap", els.root);
    els.hardwareWrap = $("#calc-hardware-wrap", els.root);
    els.breakdown = $(".alwin-calc__breakdown", els.root);
    els.width = $("#calc-width", els.root);
    els.height = $("#calc-height", els.root);
    els.quantity = $("#calc-quantity", els.root);
    els.estimate = $(".alwin-calc__estimate-value", els.root);
    els.name = $("#calc-name", els.root);
    els.phone = $("#calc-phone", els.root);
    els.error = $(".alwin-calc__error", els.root);
    els.inlineError = $(".alwin-calc__inline-error", els.root);
    els.btnBack = $(".alwin-calc__btn-back", els.root);
    els.btnNext = $(".alwin-calc__btn-next", els.root);
    els.btnSubmit = $(".alwin-calc__btn-submit", els.root);
    els.foot = $(".alwin-calc__foot", els.root);
    els.body = $(".alwin-calc__body", els.root);

    populateSelects();
    renderTabs();

    document.querySelectorAll("[data-calculator-open]").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const modelId = btn.dataset.calcModel ? Number(btn.dataset.calcModel) : null;
        const materialId = btn.dataset.calcMaterial || null;
        if (modelId && (DATA.models[modelId] || DATA.models[String(modelId)])) {
          openWithModel(modelId, materialId);
        } else {
          open();
        }
      });
    });

    els.backdrop?.addEventListener("click", close);
    els.closeBtn?.addEventListener("click", close);
    els.successClose?.addEventListener("click", close);
    els.btnBack?.addEventListener("click", onBack);
    els.btnNext?.addEventListener("click", onNext);
    els.btnSubmit?.addEventListener("click", onSubmit);

    els.search?.addEventListener("input", () => {
      state.modelFilter = els.search.value.trim();
      renderModels();
    });

    [els.width, els.height, els.quantity, els.profile, els.glass, els.hardware, els.hardwareType].forEach((el) => {
      el?.addEventListener("input", scheduleEstimate);
      el?.addEventListener("change", scheduleEstimate);
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && els.root.classList.contains("is-open")) close();
    });
  }

  function profilesForMaterial(material) {
    const all = DATA.profiles || [];
    const mat = material === "aluminum" ? "aluminum" : "upvc";
    const filtered = all.filter((p) => (p.material || "upvc") === mat);
    return filtered.length ? filtered : all;
  }

  function defaultProfileId(material) {
    const list = profilesForMaterial(material);
    if (material === "aluminum") {
      return list.find((p) => p.id === "al_thermal")?.id || list[0]?.id;
    }
    return list.find((p) => p.id === "wintech")?.id || list[0]?.id;
  }

  function fillProfileSelect() {
    if (!els.profile) return;
    const list = profilesForMaterial(state.tab);
    els.profile.innerHTML = list
      .map((p) => `<option value="${p.id}">${p.name}</option>`)
      .join("");
    const keep = list.some((p) => p.id === state.profileId);
    state.profileId = keep ? state.profileId : defaultProfileId(state.tab);
    els.profile.value = state.profileId;
  }

  function populateSelects() {
    fillProfileSelect();
    if (els.glass) {
      els.glass.innerHTML = DATA.glass
        .map((g) => `<option value="${g.id}">${g.name}</option>`)
        .join("");
      state.glassId = DATA.glass[0]?.id;
    }
    if (els.hardware) {
      els.hardware.innerHTML = (DATA.hardware || [])
        .map((h) => `<option value="${h.id}">${h.name}</option>`)
        .join("");
      state.hardwareId = DATA.hardware?.[0]?.id;
    }
    if (els.hardwareType) {
      const types = (DATA.hardwareTypes || []).filter((h) => h.id === "tilt" || h.id === "tilt_turn");
      els.hardwareType.innerHTML = types
        .map((h) => `<option value="${h.id}">${h.name}</option>`)
        .join("");
      state.hardwareType = "tilt";
    }

    els.profile?.addEventListener("change", () => {
      state.profileId = els.profile.value;
    });
    els.glass?.addEventListener("change", () => {
      state.glassId = els.glass.value;
    });
    els.hardware?.addEventListener("change", () => {
      state.hardwareId = els.hardware.value;
    });
    els.hardwareType?.addEventListener("change", () => {
      state.hardwareType = els.hardwareType.value;
    });
  }

  function syncOptionVisibility() {
    const model = getModel();
    if (!model) return;
    const noGlass = model.glassDeduction === null || model.glassDeduction === undefined || (model.panelRatio || 0) >= 0.999;
    const noHw = model.hardwareMode === "none" || !model.hardwareQty;
    if (els.glassWrap) els.glassWrap.hidden = !!noGlass;
    if (els.hardwareWrap) els.hardwareWrap.hidden = !!noHw;
    if (els.hardwareTypeWrap) els.hardwareTypeWrap.hidden = model.hardwareMode !== "casement";
    if (model.hardwareMode === "casement") {
      state.hardwareType = els.hardwareType?.value || "tilt";
    } else {
      state.hardwareType = model.hardwareType || "tilt";
    }
  }

  function tabBadge(id) {
    if (id === "aluminum") {
      return '<span class="alwin-calc__tab-badge" aria-hidden="true">Al</span>';
    }
    return '<span class="alwin-calc__tab-badge alwin-calc__tab-badge--upvc" aria-hidden="true">UPVC</span>';
  }

  function catEmoji(id) {
    if (id === "doors") return "🚪";
    if (id === "screens") return "🕸️";
    return "🪟";
  }

  function renderTabs() {
    if (!els.tabs) return;
    els.tabs.innerHTML = DATA.tabs
      .map(
        (t) => `
      <button type="button" class="alwin-calc__tab-card" data-tab="${t.id}">
        ${tabBadge(t.id)}
        <span class="alwin-calc__tab-label">${t.label}</span>
        <span class="alwin-calc__tab-hint">${t.hint || ""}</span>
      </button>`
      )
      .join("");

    $$(".alwin-calc__tab-card", els.tabs).forEach((card) => {
      card.addEventListener("click", () => {
        state.tab = card.dataset.tab;
        state.categoryId = null;
        state.modelId = null;
        state.subFilter = "";
        fillProfileSelect();
        renderCategories();
        goStep(2);
      });
    });
  }

  function renderCategories() {
    if (!els.categories || !state.tab) return;
    const cats = DATA.categories[state.tab] || [];
    els.categories.innerHTML = cats
      .map(
        (c) => `
      <button type="button" class="alwin-calc__cat-card${state.categoryId === c.id ? " is-selected" : ""}" data-cat="${c.id}">
        <span class="alwin-calc__cat-icon" aria-hidden="true">${catEmoji(c.id)}</span>
        <span class="alwin-calc__cat-label">${c.label}</span>
        <span class="alwin-calc__cat-hint">${c.hint || ""}</span>
        <span class="alwin-calc__cat-count">${t("calc_model_count", "{n} مدل").replace("{n}", c.modelIds.length)}</span>
      </button>`
      )
      .join("");

    $$(".alwin-calc__cat-card", els.categories).forEach((card) => {
      card.addEventListener("click", () => {
        state.categoryId = card.dataset.cat;
        state.modelId = null;
        state.subFilter = "";
        if (els.search) els.search.value = "";
        state.modelFilter = "";
        renderModels();
        goStep(3);
      });
    });
  }

  function renderModels() {
    if (!els.models || !state.tab || !state.categoryId) return;
    const cat = DATA.categories[state.tab].find((c) => c.id === state.categoryId);
    if (!cat) return;

    const q = state.modelFilter.toLowerCase();
    let ids = cat.modelIds.filter((id) => {
      const m = DATA.models[id] || DATA.models[String(id)];
      if (state.subFilter && (m?.categoryId || "") !== state.subFilter) return false;
      if (!q) return true;
      return (m?.name || "").includes(state.modelFilter);
    });

    const chips = cat.filters || [];
    const chipsHtml =
      chips.length > 1
        ? `<div class="alwin-calc__chips">${[{ id: "", label: t("calc_all_filters", "همه") }, ...chips]
            .map(
              (f) =>
                `<button type="button" class="alwin-calc__chip${state.subFilter === f.id ? " is-active" : ""}" data-filter="${f.id}">${f.label}</button>`
            )
            .join("")}</div>`
        : "";

    if (!ids.length) {
      els.models.innerHTML = `${chipsHtml}<p class="alwin-calc__empty">${t("calc_empty_models", "مدلی با این نام پیدا نشد.")}</p>`;
      bindModelChips();
      return;
    }

    els.models.innerHTML =
      chipsHtml +
      ids
        .map((id) => {
          const m = DATA.models[id] || DATA.models[String(id)];
          const selected = Number(state.modelId) === Number(id) ? " is-selected" : "";
          return `
        <button type="button" class="alwin-calc__model-card${selected}" data-model="${id}">
          <div class="alwin-calc__model-img-wrap">
            ${modelPicture(id, `alt="${m.name}" width="240" height="180" loading="lazy"`)}
          </div>
          <span class="alwin-calc__model-name">${m.name}</span>
        </button>`;
        })
        .join("");

    bindModelChips();
    $$(".alwin-calc__model-card", els.models).forEach((card) => {
      card.addEventListener("click", () => {
        state.modelId = Number(card.dataset.model);
        $$(".alwin-calc__model-card", els.models).forEach((c) =>
          c.classList.toggle("is-selected", Number(c.dataset.model) === state.modelId)
        );
        renderSelectedModel();
        syncOptionVisibility();
        setTimeout(() => {
          goStep(4);
          scheduleEstimate();
        }, 180);
      });
    });
  }

  function bindModelChips() {
    $$(".alwin-calc__chip", els.models).forEach((chip) => {
      chip.addEventListener("click", () => {
        state.subFilter = chip.dataset.filter || "";
        renderModels();
      });
    });
  }

  function renderSelectedModel() {
    const m = getModel();
    if (!m || !els.selectedModel) return;
    els.selectedModel.innerHTML = `
      <div class="alwin-calc__selected-inner">
        ${modelPicture(m.id, 'alt="" width="80" height="60"')}
        <div>
          <span class="alwin-calc__selected-label">${t("calc_selected_model", "مدل انتخاب‌شده")}</span>
          <strong>${m.name}</strong>
        </div>
      </div>`;
  }

  function renderCrumb() {
    if (!els.crumb) return;
    const parts = [];
    if (state.tab) parts.push(getTabLabel());
    if (state.categoryId) parts.push(getCategoryLabel());
    if (state.modelId) parts.push(getModel()?.name);

    if (parts.length && state.step > 1 && state.step !== "success") {
      els.crumb.hidden = false;
      els.crumb.innerHTML = parts.map((p) => `<span>${p}</span>`).join('<i aria-hidden="true">›</i>');
    } else {
      els.crumb.hidden = true;
      els.crumb.innerHTML = "";
    }
  }

  function renderSummary() {
    const m = getModel();
    if (!m || !els.summary) return;
    const price = state.lastPrice || calcPrice();
    const profile = DATA.profiles.find((p) => p.id === state.profileId)?.name || "";
    const glass = DATA.glass.find((g) => g.id === state.glassId)?.name || "";
    const isScreen = m.family === "screen" || m.tab === "screens";
    const specRow = isScreen
      ? `<div class="alwin-calc__summary-row"><span>${t("calc_summary_profile_only", "جنس / پروفیل")}</span><strong>${getTabLabel()} — ${profile}</strong></div>`
      : `<div class="alwin-calc__summary-row"><span>${t("calc_summary_profile_glass", "پروفیل / شیشه")}</span><strong>${profile} — ${glass}</strong></div>`;
    els.summary.innerHTML = `
      <div class="alwin-calc__summary-row"><span>${t("calc_summary_model", "مدل")}</span><strong>${m.name}</strong></div>
      ${specRow}
      <div class="alwin-calc__summary-row"><span>${t("calc_summary_size", "ابعاد")}</span><strong>${els.width?.value || "—"} × ${els.height?.value || "—"} ${t("calc_summary_cm", "سانتی‌متر")} × ${els.quantity?.value || 1}</strong></div>
      <div class="alwin-calc__summary-row alwin-calc__summary-row--price"><span>${t("calc_summary_estimate", "برآورد")}</span><strong>${formatPrice(price)}</strong></div>`;
  }

  function scrollBodyTop() {
    if (els.body) els.body.scrollTop = 0;
  }

  function open() {
    els.root.classList.add("is-open");
    els.root.setAttribute("aria-hidden", "false");
    document.body.classList.add("alwin-calc-open");
    reset();
    goStep(1);
  }

  function close() {
    els.root.classList.remove("is-open");
    els.root.setAttribute("aria-hidden", "true");
    document.body.classList.remove("alwin-calc-open");
  }

  /**
   * Opens the wizard pre-loaded with a specific model (used by the
   * products page cards), jumping straight to the options/dimensions step.
   */
  function findModelLocation(modelId, materialId) {
    const order = [];
    if (materialId) order.push(materialId);
    order.push("upvc", "aluminum");
    const seen = new Set();
    for (const tabId of order) {
      if (seen.has(tabId)) continue;
      seen.add(tabId);
      const cats = DATA.categories[tabId] || [];
      for (const cat of cats) {
        if ((cat.modelIds || []).some((id) => Number(id) === Number(modelId))) {
          return { tabId, catId: cat.id };
        }
      }
    }
    return null;
  }

  function openWithModel(modelId, materialId) {
    const location = findModelLocation(modelId, materialId);
    if (!location) {
      open();
      return;
    }

    els.root.classList.add("is-open");
    els.root.setAttribute("aria-hidden", "false");
    document.body.classList.add("alwin-calc-open");
    reset();

    state.tab = location.tabId;
    state.categoryId = location.catId;
    state.modelId = Number(modelId);
    fillProfileSelect();

    renderCategories();
    renderModels();
    renderSelectedModel();
    syncOptionVisibility();
    goStep(4);
    scheduleEstimate();
  }

  function reset() {
    state.step = 1;
    state.tab = null;
    state.categoryId = null;
    state.modelId = null;
    state.lastPrice = null;
    state.modelFilter = "";
    state.subFilter = "";
    if (els.search) els.search.value = "";
    if (els.width) els.width.value = "";
    if (els.height) els.height.value = "";
    if (els.quantity) els.quantity.value = "1";
    if (els.name) els.name.value = "";
    if (els.phone) els.phone.value = "";
    if (els.estimate) els.estimate.textContent = "—";
    if (els.error) els.error.textContent = "";
    state.profileId = defaultProfileId(state.tab);
    state.glassId = DATA.glass[0]?.id;
    state.hardwareId = DATA.hardware?.[0]?.id;
    state.hardwareType = "tilt";
    state.lastQuote = null;
    fillProfileSelect();
    if (els.glass) els.glass.value = state.glassId;
    if (els.hardware) els.hardware.value = state.hardwareId;
    if (els.hardwareType) els.hardwareType.value = "tilt";
  }

  function goStep(step) {
    if (step === "success") {
      state.step = "success";
    } else {
      state.step = Math.max(1, Math.min(TOTAL_STEPS, step));
    }
    updateUI();
    scrollBodyTop();
  }

  function updateUI() {
    const s = state.step;

    els.steps.forEach((el) => {
      const n = Number(el.dataset.stepIndicator);
      el.classList.toggle("is-active", s !== "success" && n === s);
      el.classList.toggle("is-done", s === "success" || (typeof s === "number" && n < s));
    });

    els.panels.forEach((panel) => {
      const ps = panel.dataset.step;
      if (ps === "success") {
        panel.hidden = s !== "success";
        return;
      }
      panel.hidden = s === "success" || Number(ps) !== s;
    });

    renderCrumb();

    if (els.foot) els.foot.hidden = s === "success";
    if (els.btnBack) els.btnBack.hidden = s === 1 || s === "success";
    if (els.btnNext) els.btnNext.hidden = s !== 4;
    if (els.btnSubmit) els.btnSubmit.hidden = s !== 5;

    if (s === 4) syncOptionVisibility();
    if (s === 5) renderSummary();
  }

  function onBack() {
    if (state.step > 1) goStep(state.step - 1);
  }

  function onNext() {
    if (state.step !== 4) return;
    if (!els.width?.value || !els.height?.value) {
      if (els.inlineError) {
        els.inlineError.textContent = t("calc_error_size", "لطفاً عرض و ارتفاع را وارد کنید.");
        els.inlineError.hidden = false;
      }
      return;
    }
    if (els.inlineError) els.inlineError.hidden = true;
    state.lastPrice = calcPrice();
    goStep(5);
  }

  function scheduleEstimate() {
    clearTimeout(state.estimateTimer);
    state.estimateTimer = setTimeout(runEstimate, 200);
  }

  async function runEstimate() {
    const price = calcPrice();
    state.lastPrice = price;
    if (els.estimate) els.estimate.textContent = formatPrice(price);
    renderBreakdown();

    if (!price || !getModel()) return;

    try {
      const res = await fetch(`${API}/calculator/estimate`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          product_type_id: state.modelId,
          model_id: state.modelId,
          width_cm: parseFloat(els.width.value),
          height_cm: parseFloat(els.height.value),
          quantity: parseInt(els.quantity?.value || "1", 10) || 1,
          profile_id: state.profileId,
          glass_id: state.glassId,
          hardware_id: state.hardwareId,
          hardware_type: state.hardwareType,
        }),
      });
      if (res.ok) {
        const json = await res.json();
        if (json.ok && json.total) {
          state.lastPrice = json.total;
          state.lastQuote = json;
          if (els.estimate) els.estimate.textContent = formatPrice(json.total);
          renderBreakdown();
        }
      }
    } catch {
      /* client estimate */
    }
  }

  function renderBreakdown() {
    if (!els.breakdown) return;
    const q = state.lastQuote;
    if (!q || !q.ok) {
      els.breakdown.hidden = true;
      els.breakdown.innerHTML = "";
      return;
    }
    els.breakdown.hidden = false;
    const bits = [];
    if (q.profile_total) bits.push(`<span>${t("calc_breakdown_profile", "پروفیل")} ${formatPrice(q.profile_total)}</span>`);
    if (q.glass_total) bits.push(`<span>${t("calc_breakdown_glass", "شیشه")} ${formatPrice(q.glass_total)}</span>`);
    if (q.hardware_total) bits.push(`<span>${t("calc_breakdown_hardware", "یراق")} ${formatPrice(q.hardware_total)}</span>`);
    if (q.screen_total) bits.push(`<span>${t("calc_breakdown_screen", "توری")} ${formatPrice(q.screen_total)}</span>`);
    els.breakdown.innerHTML = bits.join("");
  }

  async function onSubmit() {
    if (!getModel() || !els.name?.value?.trim() || !els.phone?.value?.trim()) {
      if (els.error) els.error.textContent = t("calc_error_contact", "لطفاً نام و شماره تماس را وارد کنید.");
      return;
    }

    if (els.error) els.error.textContent = "";
    els.btnSubmit.disabled = true;
    els.btnSubmit.textContent = t("calc_btn_submitting", "در حال ثبت...");

    try {
      const res = await fetch(`${API}/leads`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          product_type_id: state.modelId,
          model_id: state.modelId,
          width_cm: parseFloat(els.width.value),
          height_cm: parseFloat(els.height.value),
          quantity: parseInt(els.quantity?.value || "1", 10) || 1,
          name: els.name.value.trim(),
          phone: els.phone.value.trim(),
          notes: JSON.stringify({
            material: state.tab,
            model: getModel().name,
            profile: state.profileId,
            glass: state.glassId,
            hardware: state.hardwareId,
            hardware_type: state.hardwareType,
            estimate: state.lastPrice,
          }),
        }),
      });
      if (!res.ok) throw new Error("fail");
      goStep("success");
    } catch {
      if (els.error) els.error.textContent = t("calc_error_submit", "خطا در ثبت. لطفاً دوباره تلاش کنید.");
    } finally {
      els.btnSubmit.disabled = false;
      els.btnSubmit.textContent = t("calc_btn_submit", "ثبت درخواست");
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
