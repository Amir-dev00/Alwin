/**
 * ALWIN — Portfolio page filters & reveal (portfolio.html)
 * Renders catalog from projects-data.json (single source of truth).
 */
(function () {
  "use strict";

  const DATA_URL = "/api/v1/projects";
  const PAGE_SIZE = 9;
  const copy = (function () {
    var el = document.getElementById("alwin-page-copy");
    if (el) {
      try { return JSON.parse(el.textContent || "{}") || {}; } catch (e) {}
    }
    return (window.ALWIN_CONFIG && window.ALWIN_CONFIG.pageCopy) || {};
  })();
  const MORE_LABEL = copy.load_more || "نمایش پروژه‌های بیشتر";
  const LESS_LABEL = copy.load_less || "نمایش پروژه‌های کمتر";

  const CATEGORIES = [
    { id: "all", label: copy.filter_all || "همه" },
    { id: "upvc", label: copy.filter_upvc || "پنجره UPVC" },
    { id: "curtain", label: copy.filter_curtain || "نمای کرتین وال" },
    { id: "balcony", label: copy.filter_balcony || "جام بالکن" },
    { id: "glass", label: copy.filter_glass || "شیشه دوجداره" },
  ];

  let PROJECTS = [];

  const els = {};
  const state = {
    category: "all",
    visible: PAGE_SIZE,
  };

  let observer;

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function escapeAttr(str) {
    return escapeHtml(str).replace(/'/g, "&#39;");
  }

  function toFaDigits(input) {
    const fa = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
    return String(input).replace(/[0-9]/g, (d) => fa[d]);
  }

  function projectDetailUrl(p) {
    if (p.slug) return `/projects/${encodeURIComponent(p.slug)}`;
    return `/projects`;
  }

  function buildCatalog(raw) {
    return raw
      .slice()
      .sort((a, b) => (a.order || 0) - (b.order || 0))
      .map((p) => ({
        id: p.id,
        slug: p.slug || "",
        title: p.title,
        location: p.location || "",
        image: p.image || "",
        alt: p.imageAlt || p.title,
        href: projectDetailUrl(p),
        category: p.category || "",
        categoryLabel: p.categoryLabel || "",
        year: p.year || "",
      }));
  }

  function getFiltered() {
    if (state.category === "all") return PROJECTS.slice();
    return PROJECTS.filter((p) => p.category === state.category);
  }

  function countByCategory(id) {
    if (id === "all") return PROJECTS.length;
    return PROJECTS.filter((p) => p.category === id).length;
  }

  function cardTemplate(p) {
    const chip = p.categoryLabel
      ? `<span class="project-card__chip">${escapeHtml(p.categoryLabel)}</span>`
      : "";
    const year = p.year ? `<span class="year">${escapeHtml(p.year)}</span>` : "";
    const loc = p.location ? `<span class="loc">${escapeHtml(p.location)}</span>` : "";
    const meta = year || loc ? `<div class="project-card__meta">${year}${loc}</div>` : "";

    return `
      <article class="project-card" data-category="${escapeAttr(p.category)}" data-id="${p.id}">
        <a class="project-card__link"
           href="${escapeAttr(p.href)}">
          <div class="project-card__media">
            <img src="${escapeAttr(p.image)}" alt="${escapeAttr(p.alt)}" loading="lazy" width="800" height="600">
            <div class="project-card__overlay" aria-hidden="true"></div>
            ${chip}
            <div class="project-card__content">
              <h3 class="project-card__title">${escapeHtml(p.title)}</h3>
              ${meta}
              <span class="project-card__cta">
                مشاهده جزئیات
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
              </span>
            </div>
          </div>
        </a>
      </article>`;
  }

  function renderFilters() {
    if (!els.filters) return;
    els.filters.innerHTML = CATEGORIES.map((cat) => {
      const active = state.category === cat.id ? " is-active" : "";
      const count = toFaDigits(countByCategory(cat.id));
      return `<button type="button" class="portfolio-filter-btn${active}" data-filter="${cat.id}" aria-pressed="${state.category === cat.id}">
        <span class="portfolio-filter-btn__name">${escapeHtml(cat.label)}</span> <span class="count">(${count})</span>
      </button>`;
    }).join("");
  }

  function observeCards() {
    if (!("IntersectionObserver" in window)) {
      document.querySelectorAll(".project-card").forEach((c) => c.classList.add("is-visible"));
      return;
    }
    if (!observer) {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add("is-visible");
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.08, rootMargin: "0px 0px -40px 0px" }
      );
    }
    document.querySelectorAll(".project-card:not(.is-visible)").forEach((card) => {
      observer.observe(card);
    });
  }

  function renderGrid() {
    if (!els.grid) return;
    const filtered = getFiltered();
    const shown = filtered.slice(0, state.visible);

    els.grid.innerHTML = shown.map(cardTemplate).join("");

    if (els.empty) els.empty.hidden = filtered.length !== 0;
    if (els.grid) els.grid.hidden = filtered.length === 0;

    if (els.count) {
      els.count.innerHTML = filtered.length
        ? (copy.count_template || "نمایش {shown} از {total} پروژه")
            .replace("{shown}", `<strong>${toFaDigits(shown.length)}</strong>`)
            .replace("{total}", `<strong>${toFaDigits(filtered.length)}</strong>`)
        : "";
    }

    if (els.loadMoreWrap) {
      els.loadMoreWrap.hidden = filtered.length <= PAGE_SIZE;
    }
    setToggleLabel(state.visible > PAGE_SIZE);

    observeCards();
  }

  function setToggleLabel(expanded) {
    captureEls();
    if (!els.loadMore) return;
    const label = expanded ? LESS_LABEL : MORE_LABEL;
    els.loadMore.querySelectorAll(".text-one, .text-two").forEach((node) => {
      node.textContent = label;
    });
    els.loadMore.onclick = onLoadMore;
  }

  function onFilterClick(e) {
    const btn = e.target.closest("[data-filter]");
    if (!btn) return;
    state.category = btn.dataset.filter;
    state.visible = PAGE_SIZE;
    renderFilters();
    renderGrid();
  }

  function captureEls() {
    els.filters = document.getElementById("portfolioFilters");
    els.grid = document.getElementById("portfolioGrid");
    els.empty = document.getElementById("portfolioEmpty");
    els.count = document.getElementById("portfolioCount");
    els.loadMoreWrap = document.getElementById("portfolioLoadMoreWrap");
    els.loadMore = document.getElementById("portfolioLoadMore");
  }

  function onLoadMore() {
    captureEls();
    if (state.visible > PAGE_SIZE) {
      state.visible = PAGE_SIZE;
      renderGrid();
      document.querySelector(".portfolio-grid-area")?.scrollIntoView({ behavior: "smooth", block: "start" });
      return;
    }
    state.visible = getFiltered().length;
    renderGrid();
  }

  function bindUi() {
    if (els.filters && !els.filters.dataset.bound) {
      els.filters.dataset.bound = "1";
      els.filters.addEventListener("click", onFilterClick);
    }
    if (els.loadMore) {
      els.loadMore.onclick = onLoadMore;
    }
  }

  async function init() {
    captureEls();

    if (!els.grid) return;

    bindUi();

    try {
      const api = (window.ALWIN_CONFIG && window.ALWIN_CONFIG.apiUrl) || "/api/v1";
      const urls = [api + "/projects", DATA_URL];
      let data = null;
      for (const url of urls) {
        try {
          const res = await fetch(url, { headers: { Accept: "application/json" } });
          if (res.ok) {
            data = await res.json();
            if (data && data.projects) break;
          }
        } catch (e) {}
      }
      if (!data) throw new Error("no catalog");
      PROJECTS = buildCatalog(data.projects || []);
      renderFilters();
      renderGrid();
    } catch (err) {
      console.error("[portfolio.js] Failed to load projects catalog", err);
      if (els.count) els.count.textContent = copy.load_error || "بارگذاری پروژه‌ها با خطا مواجه شد.";
    }
  }

  window.ALWIN_PAGE = window.ALWIN_PAGE || {};
  window.ALWIN_PAGE.projects = init;
  window.ALWIN_PAGE.toggleProjects = onLoadMore;

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
