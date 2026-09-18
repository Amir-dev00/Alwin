(function () {
  "use strict";

  const root = document.querySelector("[data-price-studio]");
  if (!root) return;

  const catalogEl = document.getElementById("pricing-catalog");
  let catalog = {};
  try {
    catalog = JSON.parse(catalogEl.textContent);
  } catch {
    return;
  }

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const fields = {
    model: root.querySelector('[data-ps="model"]'),
    profile: root.querySelector('[data-ps="profile"]'),
    glass: root.querySelector('[data-ps="glass"]'),
    origin: root.querySelector('[data-ps="origin"]'),
    type: root.querySelector('[data-ps="type"]'),
    width: root.querySelector('[data-ps="width"]'),
    height: root.querySelector('[data-ps="height"]'),
    qty: root.querySelector('[data-ps="qty"]'),
  };
  const typeWrap = root.querySelector("[data-ps-type-wrap]");
  const totalEl = root.querySelector("[data-ps-total]");
  const nameEl = root.querySelector("[data-ps-model-name]");
  const barsEl = root.querySelector("[data-ps-bars]");
  const linesEl = root.querySelector("[data-ps-lines]");
  const sums = {
    profile: root.querySelector('[data-ps-sum="profile"]'),
    glass: root.querySelector('[data-ps-sum="glass"]'),
    hardware: root.querySelector('[data-ps-sum="hardware"]'),
    screen: root.querySelector('[data-ps-sum="screen"]'),
  };

  function money(n) {
    if (!n) return "۰ تومان";
    return Math.round(n).toLocaleString("fa-IR") + " تومان";
  }

  function modelOf() {
    return catalog.models?.[fields.model.value] || catalog.models?.[Number(fields.model.value)];
  }

  function syncType() {
    const m = modelOf();
    if (!m || !typeWrap) return;
    const mode = m.hardwareMode;
    typeWrap.hidden = mode !== "casement";
    if (mode === "casement") {
      fields.type.value = fields.type.value === "tilt_turn" ? "tilt_turn" : "tilt";
    } else if (m.hardwareType) {
      fields.type.value = m.hardwareType;
    }
  }

  let timer;
  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(run, 180);
  }

  async function run() {
    syncType();
    const body = {
      model_id: Number(fields.model.value),
      width_cm: Number(fields.width.value),
      height_cm: Number(fields.height.value),
      quantity: Number(fields.qty.value || 1),
      profile_id: fields.profile.value,
      glass_id: fields.glass.value,
      hardware_id: fields.origin.value,
      hardware_type: fields.type.value,
    };
    try {
      const res = await fetch("/admin/pricing/preview", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": csrf,
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(body),
      });
      const json = await res.json();
      render(json);
    } catch {
      if (totalEl) totalEl.textContent = "خطا در محاسبه";
    }
  }

  function render(q) {
    if (!q || !q.ok) {
      if (totalEl) totalEl.textContent = q?.error || "—";
      return;
    }
    if (totalEl) totalEl.textContent = money(q.total);
    if (nameEl) {
      nameEl.textContent =
        q.model_name +
        " · " +
        q.width_cm +
        "×" +
        q.height_cm +
        " سانتی‌متر" +
        (q.quantity > 1 ? " × " + q.quantity : "");
    }
    sums.profile && (sums.profile.textContent = money(q.profile_total));
    sums.glass && (sums.glass.textContent = money(q.glass_total));
    sums.hardware && (sums.hardware.textContent = money(q.hardware_total));
    sums.screen && (sums.screen.textContent = money(q.screen_total));
    const sum = Math.max(1, q.profile_total + q.glass_total + q.hardware_total + (q.screen_total || 0));
    if (barsEl) {
      barsEl.innerHTML =
        '<span class="is-profile" style="width:' +
        ((q.profile_total / sum) * 100).toFixed(1) +
        '%"></span>' +
        '<span class="is-glass" style="width:' +
        ((q.glass_total / sum) * 100).toFixed(1) +
        '%"></span>' +
        '<span class="is-hardware" style="width:' +
        ((q.hardware_total / sum) * 100).toFixed(1) +
        '%"></span>' +
        '<span class="is-screen" style="width:' +
        (((q.screen_total || 0) / sum) * 100).toFixed(1) +
        '%"></span>';
    }
    if (linesEl) {
      const rows = (q.lines?.profile || [])
        .map(function (line) {
          const unit = line.unit === "sqm" ? "م۲" : "م";
          return (
            '<div class="price-quote__line"><span>' +
            line.name +
            "</span><span>" +
            line.qty +
            " " +
            unit +
            "</span><b>" +
            money(line.total) +
            "</b></div>"
          );
        })
        .join("");
      linesEl.innerHTML = rows;
    }
  }

  Object.values(fields).forEach(function (el) {
    el?.addEventListener("input", schedule);
    el?.addEventListener("change", schedule);
  });
  run();
})();
