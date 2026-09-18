/**
 * ALWIN — Project detail page (project.html)
 * Routes by ?slug= or ?id=; content from projects-detail-data.json (facts only).
 */
(function () {
  "use strict";

  const DETAIL_DATA_URL = "projects-detail-data.json";
  const CATALOG_DATA_URL = "projects-data.json";

  let CATALOG_BY_ID = {};

  const PROJECT_REGISTRY = [
    { id: 1, slug: "پروژه-آریو-در-شهرک-اندیشه", title: "پروژه آریو در شهرک اندیشه" },
    { id: 2, slug: "مهندس-رضایی-در-جلال-آل-احمد", title: "مهندس رضایی در جلال آل احمد" },
    { id: 3, slug: "پروژه-شهرک-ژاندارمری", title: "پروژه شهرک ژاندارمری" },
    { id: 4, slug: "آقای-محسنی-در-شهریار", title: "آقای محسنی در شهریار" },
    { id: 5, slug: "دکتر-ابراهیمی-شهریار", title: "دکتر ابراهیمی در شهریار" },
    { id: 6, slug: "آقای-رهگذر-شهریار", title: "آقای رهگذر در شهریار" },
    { id: 7, slug: "آقای-نیک-آور-در-شهرک-اندیشه", title: "آقای نیک آور در شهرک اندیشه" },
    { id: 8, slug: "شرکت-آوند-در-خیابان-جلال-آل-احمد", title: "شرکت آوند در خیابان جلال آل احمد" },
    { id: 9, slug: "آقاي-منافي-در-جنت-آباد-شمالي", title: "آقاي منافي در جنت آباد شمالي" },
    { id: 10, slug: "ویلای-آقای-نجاتی-محمود-آباد", title: "ویلای آقای نجاتی محمود آباد" },
    { id: 11, slug: "آقاي-بهرامي-زاده-در-مرزداران", title: "آقاي بهرامي زاده در مرزداران" },
    { id: 12, slug: "پروژه-آقای-تجلی-شهر-قدس", title: "پروژه آقای تجلی شهر قدس" },
    { id: 13, slug: "پروژه-آقای-زارع-صفاییه-یزد", title: "پروژه آقای زارع صفاییه یزد" },
  ];

  let DETAIL_BY_KEY = {};

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  /** Normalize Persian/Arabic variants for title matching */
  function normalizeTitle(str) {
    return String(str || "")
      .replace(/\u200c/g, "")
      .replace(/ي/g, "ی")
      .replace(/ك/g, "ک")
      .replace(/آ/g, "ا")
      .replace(/\s+/g, " ")
      .trim();
  }

  function titleKey(str) {
    return normalizeTitle(str);
  }

  function projectUrl(entry) {
    return `project.html?slug=${encodeURIComponent(entry.slug)}`;
  }

  function findRegistry(params) {
    const slug = params.get("slug");
    const id = params.get("id");
    if (slug) return PROJECT_REGISTRY.find((p) => p.slug === slug);
    if (id) return PROJECT_REGISTRY.find((p) => String(p.id) === String(id));
    return null;
  }

  function findDetail(registryEntry) {
    if (!registryEntry) return null;
    return DETAIL_BY_KEY[titleKey(registryEntry.title)] || null;
  }

  function setProjectImage(imgEl, src, alt) {
    if (!imgEl || !src) return;
    const picture = imgEl.closest("picture");
    const source = picture && picture.querySelector("source[type='image/webp']");
    if (source) source.srcset = src;
    imgEl.src = src;
    imgEl.alt = alt || "";
    imgEl.hidden = false;
    if (picture) picture.hidden = false;
  }

  function materialsOf(detail) {
    return (detail && detail.materials) || {};
  }

  function hasSlidingProfile(profile) {
    return /کشویی/.test(profile || "");
  }

  function hasLaminate(profile) {
    return /لمینت/.test(profile || "");
  }

  function profileKindLabel(profile) {
    if (!profile) return "";
    if (hasSlidingProfile(profile)) return "ترکیب لولایی و کشویی";
    if (/سفید/.test(profile)) return "پروفیل سفید";
    if (hasLaminate(profile)) return "پروفیل لمینت";
    return "پروفیل UPVC";
  }

  function buildOverviewTitle(detail) {
    if (detail.project_type) {
      const t = escapeHtml(detail.project_type.replace(/^پروژه\s*/, ""));
      // Prefer a line break before unit count if present
      return t.replace(/(\d+\s*واحدی\s+)/, "$1<br>");
    }
    if (detail.description) {
      return "ساخت، اجرا و نصب<br>پنجره دوجداره UPVC";
    }
    const m = materialsOf(detail);
    if (m.glass && hasLaminate(m.profile)) {
      return "پروفیل لمینت<br>و شیشه رفلکس";
    }
    if (m.glass) {
      return "پنجره UPVC<br>با شیشه رفلکس";
    }
    if (hasLaminate(m.profile)) {
      return "اجرای پنجره<br>با پروفیل لمینت";
    }
    if (hasSlidingProfile(m.profile)) {
      return "پنجره‌های لولایی<br>و کشویی UPVC";
    }
    if (detail.address) {
      const short = escapeHtml(detail.address.split("،")[0].trim());
      return `پروژه اجرایی<br>در ${short}`;
    }
    return "جزئیات<br>پروژه اجرایی";
  }

  function buildOverviewParagraphs(detail) {
    const m = materialsOf(detail);
    const paras = [];

    // Opening: who / where / what
    let open = "";
    if (detail.project_type) {
      open = `این نمونه‌کار مربوط به ${detail.project_type} است`;
      if (detail.client) open += ` که به سفارش ${detail.client} انجام شده`;
      if (detail.address) open += ` و در ${detail.address} واقع شده است`;
      open += ".";
    } else if (detail.description) {
      open = detail.description;
      if (!/[.۔؟!]$/.test(open.trim())) open += ".";
      if (detail.client) open += ` کارفرمای این پروژه ${detail.client} بوده است.`;
      if (detail.address) open += ` محل اجرا در ${detail.address} قرار دارد.`;
    } else {
      open = "این پروژه از نمونه‌کارهای اجرایی پنجره دوجداره UPVC است";
      if (detail.client) open += ` که برای ${detail.client} انجام شده`;
      if (detail.address) open += ` و آدرس آن ${detail.address} می‌باشد`;
      open += ".";
    }
    paras.push(open);

    // Materials elaboration
    if (m.profile || m.hardware || m.glass) {
      let mat = "در این پروژه";
      const bits = [];
      if (m.profile) {
        let p = `از پروفیل ${m.profile} استفاده شده است`;
        if (hasSlidingProfile(m.profile)) {
          p += "؛ ترکیب سری لولایی و کشویی امکان انتخاب بازشوی مناسب برای فضاهای مختلف ساختمان را فراهم می‌کند";
        } else if (hasLaminate(m.profile) && /بلوطی|قهوه‌ای|دورو/.test(m.profile)) {
          p += " که ظاهر چوبی/رنگی لمینت را با ساختار UPVC همراه می‌کند";
        } else if (hasLaminate(m.profile)) {
          p += "؛ سطح لمینت‌شده جلوه‌ای متمایز نسبت به پروفیل سفید ساده دارد";
        } else if (/سفید/.test(m.profile)) {
          p += " با نمای روشن و کلاسیک سفید";
        } else if (/پلاس\s*پن|پلاس‌پن/i.test(m.profile)) {
          p += " به‌عنوان برند پروفیل این اجرا";
        }
        bits.push(p);
      }
      if (m.hardware) {
        bits.push(`یراق‌آلات ${m.hardware} برای مکانیزم بازشوی پنجره‌ها به‌کار رفته است`);
      }
      if (m.glass) {
        bits.push(`شیشه ${m.glass} در ترکیب جداره‌ها استفاده شده و ظاهری رفلکسی به سطح شیشه می‌دهد`);
      }
      if (bits.length === 1) {
        mat += " " + bits[0] + ".";
      } else if (bits.length === 2) {
        mat += " " + bits[0] + " و " + bits[1] + ".";
      } else {
        mat += " " + bits.slice(0, -1).join("؛ ") + "؛ همچنین " + bits[bits.length - 1] + ".";
      }
      paras.push(mat);
    }

    // Timing
    if (detail.execution_time) {
      paras.push(`زمان اجرای این پروژه ${detail.execution_time} بوده است.`);
    }

    return paras;
  }

  function buildFeatureItems(detail) {
    const m = materialsOf(detail);
    const items = [];
    if (detail.project_type) items.push(detail.project_type);
    if (m.profile) items.push(`پروفیل: ${m.profile}`);
    if (m.hardware) items.push(`یراق: ${m.hardware}`);
    if (m.glass) items.push(`شیشه: ${m.glass}`);
    if (detail.execution_time) items.push(`زمان اجرا: ${detail.execution_time}`);
    if (detail.address) items.push(`آدرس: ${detail.address}`);
    if (detail.client && !items.some((i) => i.includes(detail.client))) {
      items.push(`کارفرما: ${detail.client}`);
    }
    return items;
  }

  function buildMetaItems(detail) {
    const items = [];
    if (detail.client) items.push({ label: "کارفرما", value: detail.client });
    if (detail.address) items.push({ label: "موقعیت", value: detail.address });
    if (detail.execution_time) items.push({ label: "زمان اجرا", value: detail.execution_time });
    if (detail.project_type) {
      items.push({ label: "نوع پروژه", value: detail.project_type });
    } else if (detail.description) {
      items.push({ label: "موضوع", value: "پنجره دوجداره UPVC" });
    } else if (materialsOf(detail).profile) {
      items.push({ label: "موضوع", value: "پنجره UPVC" });
    }
    return items;
  }

  function buildSpecCards(detail) {
    const m = materialsOf(detail);
    const cards = [];
    if (m.profile) {
      cards.push({
        icon: "fa-window-maximize",
        label: "پروفیل",
        value: m.profile,
      });
    }
    if (m.hardware) {
      cards.push({
        icon: "fa-gears",
        label: "یراق‌آلات",
        value: m.hardware,
      });
    }
    if (m.glass) {
      cards.push({
        icon: "fa-square",
        label: "شیشه",
        value: m.glass,
      });
    }
    if (detail.execution_time && cards.length < 3) {
      cards.push({
        icon: "fa-clock",
        label: "زمان اجرا",
        value: detail.execution_time,
      });
    }
    if (detail.address && cards.length < 3) {
      cards.push({
        icon: "fa-location-dot",
        label: "محل اجرا",
        value: detail.address,
      });
    }
    return cards;
  }

  function buildDetailBlocks(detail) {
    const m = materialsOf(detail);
    const blocks = [];

    if (m.profile || m.hardware || m.glass) {
      let text = "مشخصات مصالح این پروژه بر اساس داده‌های ثبت‌شده به شرح زیر است. ";
      if (m.profile) {
        text += `پروفیل انتخاب‌شده «${m.profile}» است`;
        const kind = profileKindLabel(m.profile);
        if (kind && kind !== "پروفیل UPVC") text += ` (${kind})`;
        text += ". ";
      }
      if (m.hardware) {
        text += `سیستم یراق ${m.hardware} برای مکانیزم بازشو به‌کار رفته است. `;
      }
      if (m.glass) {
        text += `نوع شیشه مصرفی ${m.glass} بوده است. `;
      }
      blocks.push({ title: "مصالح و متریال", text: text.trim() });
    }

    if (detail.address || detail.client || detail.execution_time || detail.project_type) {
      let text = "";
      if (detail.project_type) {
        text += `${detail.project_type} `;
      }
      if (detail.client) {
        text += `با کارفرمایی ${detail.client} `;
      }
      if (detail.address) {
        text += `در موقعیت ${detail.address} `;
      }
      if (detail.execution_time) {
        text += `در بازه زمانی ${detail.execution_time} `;
      }
      text = text.trim();
      if (text) {
        if (!/[.۔؟!]$/.test(text)) text += ".";
        blocks.push({ title: "موقعیت و زمان‌بندی", text });
      }
    }

    if (detail.description && blocks.length < 2) {
      let text = detail.description;
      if (!/[.۔؟!]$/.test(text.trim())) text += ".";
      blocks.push({ title: "شرح پروژه", text });
    }

    return blocks;
  }

  function renderMeta(detail) {
    const el = document.getElementById("projectMeta");
    if (!el) return;
    const items = buildMetaItems(detail);
    if (!items.length) {
      el.hidden = true;
      el.innerHTML = "";
      return;
    }
    el.innerHTML = items
      .map(
        (item) => `<div class="meta-item">
        <p class="title">${escapeHtml(item.label)}</p>
        <p class="text">${escapeHtml(item.value)}</p>
      </div>`
      )
      .join("");
    el.hidden = false;
  }

  function renderOverview(detail) {
    const titleEl = document.getElementById("projectOverviewTitle");
    const textEl = document.getElementById("projectOverviewText");
    const listWrap = document.getElementById("projectFeatureList");
    const listEl = document.getElementById("projectFeatureItems");

    if (titleEl) titleEl.innerHTML = buildOverviewTitle(detail);

    if (textEl) {
      textEl.innerHTML = buildOverviewParagraphs(detail)
        .map((p) => `<p class="text">${escapeHtml(p)}</p>`)
        .join("");
    }

    const features = buildFeatureItems(detail);
    if (listWrap && listEl) {
      if (features.length) {
        listEl.innerHTML = features.map((f) => `<li>${escapeHtml(f)}</li>`).join("");
        listWrap.hidden = false;
      } else {
        listEl.innerHTML = "";
        listWrap.hidden = true;
      }
    }
  }

  function renderSpecs(detail) {
    const el = document.getElementById("projectSpecs");
    if (!el) return;
    const cards = buildSpecCards(detail);
    if (!cards.length) {
      el.hidden = true;
      el.innerHTML = "";
      return;
    }
    el.innerHTML = cards
      .map(
        (c) => `<div class="project-spec-card">
        <div class="icon"><i class="fa-solid ${c.icon}" aria-hidden="true"></i></div>
        <p class="label">${escapeHtml(c.label)}</p>
        <p class="value">${escapeHtml(c.value)}</p>
      </div>`
      )
      .join("");
    el.hidden = false;
    el.classList.toggle("project-specs-grid--2", cards.length === 2);
    el.classList.toggle("project-specs-grid--1", cards.length === 1);
  }

  function renderDetails(detail) {
    const el = document.getElementById("projectDetails");
    if (!el) return;
    const blocks = buildDetailBlocks(detail);
    if (!blocks.length) {
      el.hidden = true;
      el.innerHTML = "";
      return;
    }
    el.innerHTML = blocks
      .map(
        (b) => `<div class="details-info">
        <h3 class="title">${escapeHtml(b.title)}</h3>
        <p class="text">${escapeHtml(b.text)}</p>
      </div>`
      )
      .join("");
    el.hidden = false;
  }

  function renderNav(registryEntry) {
    const idx = PROJECT_REGISTRY.findIndex((p) => p.id === registryEntry.id);
    const prev = idx > 0 ? PROJECT_REGISTRY[idx - 1] : null;
    const next = idx < PROJECT_REGISTRY.length - 1 ? PROJECT_REGISTRY[idx + 1] : null;

    const prevLink = document.getElementById("projectPrev");
    const nextLink = document.getElementById("projectNext");
    if (prevLink) {
      if (prev) {
        prevLink.href = projectUrl(prev);
        prevLink.classList.remove("is-disabled");
        prevLink.querySelector(".label").textContent = prev.title;
      } else {
        prevLink.href = "#";
        prevLink.classList.add("is-disabled");
        prevLink.querySelector(".label").textContent = "—";
      }
    }
    if (nextLink) {
      if (next) {
        nextLink.href = projectUrl(next);
        nextLink.classList.remove("is-disabled");
        nextLink.querySelector(".label").textContent = next.title;
      } else {
        nextLink.href = "#";
        nextLink.classList.add("is-disabled");
        nextLink.querySelector(".label").textContent = "—";
      }
    }
  }

  function renderNotFound(root) {
    root.innerHTML = `
      <section class="project-not-found fade-anim">
        <h2>پروژه یافت نشد</h2>
        <p>پروژه‌ای با این شناسه وجود ندارد. لطفاً از صفحه پروژه‌ها یک نمونه‌کار دیگر را انتخاب کنید.</p>
        <a href="portfolio.html" class="rr-btn hover-bg-theme">
          <span class="btn-wrap">
            <span class="text-one">بازگشت به پروژه‌ها</span>
            <span class="text-two">بازگشت به پروژه‌ها</span>
          </span>
        </a>
      </section>`;
  }

  function renderTypeTag(categoryLabel) {
    const el = document.getElementById("projectTypeTag");
    if (!el) return;
    if (categoryLabel) {
      el.textContent = categoryLabel;
      el.hidden = false;
    } else {
      el.textContent = "";
      el.hidden = true;
    }
  }

  function renderProject(registryEntry, detail, catalogEntry) {
    const imageSrc = (catalogEntry && catalogEntry.image) || "";
    const categoryLabel = (catalogEntry && catalogEntry.categoryLabel) || "";
    const displayTitle = (detail && detail.title) || registryEntry.title;

    document.title = `${displayTitle} | ALWIN — جزئیات پروژه`;

    const metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc) {
      const bits = [displayTitle];
      if (detail && detail.address) bits.push(detail.address);
      if (detail && detail.materials && detail.materials.profile) {
        bits.push(`پروفیل ${detail.materials.profile}`);
      }
      metaDesc.setAttribute("content", `${bits.join(" — ")} | نمونه‌کار اجرایی ALWIN`);
    }

    const titleEl = document.getElementById("projectTitle");
    const breadcrumbEl = document.getElementById("projectBreadcrumb");
    if (titleEl) titleEl.textContent = displayTitle;
    if (breadcrumbEl) breadcrumbEl.textContent = displayTitle;

    renderTypeTag(categoryLabel);

    setProjectImage(document.getElementById("projectThumb"), imageSrc, displayTitle);
    setProjectImage(document.getElementById("projectHeroImage"), imageSrc, displayTitle);

    if (detail) {
      renderMeta(detail);
      renderOverview(detail);
      renderSpecs(detail);
      renderDetails(detail);
    }

    renderNav(registryEntry);
  }

  async function init() {
    const root = document.getElementById("projectDetailRoot");
    if (!root) return;

    const params = new URLSearchParams(window.location.search);
    const registryEntry = findRegistry(params);

    if (!registryEntry) {
      renderNotFound(root);
      return;
    }

    try {
      const [detailRes, catalogRes] = await Promise.all([
        fetch(DETAIL_DATA_URL),
        fetch(((window.ALWIN_CONFIG && window.ALWIN_CONFIG.apiUrl) || "/api/v1") + "/projects").catch(() => null),
      ]);
      let catalogOk = catalogRes && catalogRes.ok;
      let catalogJson = catalogOk ? await catalogRes.json() : null;
      if (!catalogJson || !catalogJson.projects) {
        const fallback = await fetch(CATALOG_DATA_URL);
        if (fallback.ok) catalogJson = await fallback.json();
      }
      if (detailRes.ok) {
        const data = await detailRes.json();
        DETAIL_BY_KEY = {};
        (data.projects || []).forEach((p) => {
          if (p && p.title) DETAIL_BY_KEY[titleKey(p.title)] = p;
        });
      }
      if (catalogJson && catalogJson.projects) {
        CATALOG_BY_ID = {};
        catalogJson.projects.forEach((p) => {
          if (p && p.id != null) CATALOG_BY_ID[p.id] = p;
        });
      }
    } catch (err) {
      console.error("[project-detail.js] Failed to load project data", err);
    }

    const detail = findDetail(registryEntry);
    const catalogEntry = CATALOG_BY_ID[registryEntry.id] || null;
    if (!detail) {
      console.warn("[project-detail.js] No detail record for", registryEntry.title);
    }
    renderProject(registryEntry, detail, catalogEntry);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
