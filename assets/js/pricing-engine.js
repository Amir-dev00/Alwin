/**
 * Shared UPVC quote engine — mirrors cms/app/Support/PricingCalculator.php
 */
(function (root) {
  "use strict";

  function usage(recipe, widthM, heightM) {
    const out = {};
    Object.keys(recipe || {}).forEach(function (key) {
      const row = recipe[key] || {};
      const qty =
        (Number(row.w) || 0) * widthM +
        (Number(row.h) || 0) * heightM +
        (Number(row.c) || 0) +
        (Number(row.area) || 0) * widthM * heightM;
      out[key] = Math.max(0, qty);
    });
    return out;
  }

  function find(list, id) {
    return (list || []).find(function (row) {
      return String(row.id) === String(id);
    });
  }

  function componentPrice(brand, key, catalog, fallback) {
    const direct = brand && brand.prices ? brand.prices[key] : null;
    if (direct !== null && direct !== undefined && direct !== "") return Number(direct) || 0;
    const fb = find(catalog.profiles, fallback);
    const v = fb && fb.prices ? fb.prices[key] : null;
    if (v !== null && v !== undefined && v !== "") return Number(v) || 0;
    for (let i = 0; i < (catalog.profiles || []).length; i++) {
      const p = catalog.profiles[i].prices && catalog.profiles[i].prices[key];
      if (p !== null && p !== undefined && p !== "") return Number(p) || 0;
    }
    return 0;
  }

  function hardwareType(model, requested) {
    const mode = model.hardwareMode || "fixed";
    if (mode === "none") return "none";
    if (mode === "casement") {
      return requested === "tilt_turn" ? "tilt_turn" : "tilt";
    }
    return model.hardwareType || "tilt";
  }

  function quote(input, catalog) {
    const modelId = input.model_id;
    const model = catalog.models[modelId] || catalog.models[String(modelId)];
    if (!model) return { ok: false, total: 0, formatted_price: "—" };

    const widthCm = Number(input.width_cm) || 0;
    const heightCm = Number(input.height_cm) || 0;
    const qty = Math.max(1, parseInt(input.quantity || "1", 10) || 1);
    if (widthCm <= 0 || heightCm <= 0) return { ok: false, total: 0, formatted_price: "—" };

    const widthM = widthCm / 100;
    const heightM = heightCm / 100;
    const opening = widthM * heightM;
    const settings = catalog.settings || {};
    const waste = (Number(settings.waste_percent) || 0) / 100;
    const roundTo = Math.max(1, Number(settings.round_to) || 1000);
    const fallback = settings.fallback_brand || "wintech";

    const brand =
      find(catalog.profiles, input.profile_id) ||
      find(catalog.profiles, fallback) ||
      (catalog.profiles || [])[0];
    const glass =
      find(catalog.glass, input.glass_id) || (catalog.glass || [])[0];
    const origin = input.hardware_id === "germany" ? "germany" : "turk";
    const hwKey = hardwareType(model, input.hardware_type);
    const hwType = find(catalog.hardwareTypes, hwKey);

    const usedMap = usage(model.recipe, widthM, heightM);
    let profileTotal = 0;
    const profileLines = [];
    Object.keys(usedMap).forEach(function (key) {
      const used = usedMap[key] * (1 + waste);
      if (used < 0.0001) return;
      const unitPrice = componentPrice(brand, key, catalog, fallback);
      const total = Math.round(used * unitPrice);
      profileTotal += total;
      profileLines.push({ key: key, qty: used, unit_price: unitPrice, total: total });
    });

    let glassArea = 0;
    let glassTotal = 0;
    if (model.glassDeduction !== null && model.glassDeduction !== undefined && (model.panelRatio || 0) < 0.999) {
      glassArea = Math.max(0, opening - Number(model.glassDeduction));
      glassTotal = Math.round(glassArea * (Number(glass && glass.pricePerSqm) || 0));
    }

    const hwQty = Number(model.hardwareQty) || 0;
    const hwUnit = hwQty > 0 && hwType ? Number(hwType.prices[origin]) || 0 : 0;
    const hardwareTotal = hwQty * hwUnit;
    const areaRate = Number(model.areaRate) || 0;
    const screenTotal = areaRate > 0 ? Math.round(areaRate * opening) : 0;
    const subtotal = profileTotal + glassTotal + hardwareTotal + screenTotal;
    const total = Math.round(subtotal / roundTo) * roundTo * qty;

    return {
      ok: true,
      total: total,
      profile_total: profileTotal,
      glass_total: glassTotal,
      hardware_total: hardwareTotal,
      screen_total: screenTotal,
      glass_area: glassArea,
      hardware_type: hwKey,
      formatted_price: total
        ? Math.round(total).toLocaleString("fa-IR") + " تومان"
        : "—",
      lines: { profile: profileLines },
    };
  }

  root.AlwinPrice = { quote: quote, hardwareType: hardwareType };
})(window);
