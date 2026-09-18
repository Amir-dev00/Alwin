/**
 * ALWIN — Products page grid (services.html)
 * Renders catalog from products-data.json (single source of truth).
 */
(function () {
  "use strict";

  const DATA_URL = "/api/v1/products";
  const PAGE_SIZE = 9;
  const copy = (function () {
    var el = document.getElementById("alwin-page-copy");
    if (el) {
      try { return JSON.parse(el.textContent || "{}") || {}; } catch (e) {}
    }
    return (window.ALWIN_CONFIG && window.ALWIN_CONFIG.pageCopy) || {};
  })();
  const MORE_LABEL = copy.load_more || "نمایش محصولات بیشتر";
  const LESS_LABEL = copy.load_less || "نمایش محصولات کمتر";
  const FALLBACK_IMG = "assets/img/brand/window-fallback.png";

  const els = {};
  const state = {
    tab: "all",
    q: "",
    visible: PAGE_SIZE,
  };

  let CATALOG = [];
  let CATEGORIES = [];

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function toFaDigits(input) {
    const fa = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
    return String(input).replace(/[0-9]/g, (d) => fa[d]);
  }

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

  function productImages(p) {
    const img = p.images || {};
    const close = img.close || img.main || FALLBACK_IMG;
    const open = img.open || close;
    return {
      close: close && !String(close).startsWith("data:") ? close : FALLBACK_IMG,
      open: open && !String(open).startsWith("data:") ? open : FALLBACK_IMG,
    };
  }

  function buildCatalog(products) {
    return products.map((p, index) => {
      const images = productImages(p);
      const folderNumber = (p.images && p.images.folder_number) || index + 1;
      return {
        id: folderNumber,
        title: p.title,
        category: p.category,
        catId: p.category,
        imageClose: images.close,
        imageOpen: images.open,
        imageAlt: (p.images && p.images.alt) || p.title,
      };
    });
  }

  function getFiltered() {
    const q = state.q.trim();
    return CATALOG.filter((item) => {
      if (state.tab !== "all" && item.catId !== state.tab) return false;
      if (q && !item.title.includes(q)) return false;
      return true;
    });
  }

  function renderTabs() {
    if (!els.tabs) return;
    const parts = [
      `<button type="button" class="products-tab${state.tab === "all" ? " is-active" : ""}" data-tab="all">${escapeHtml(copy.tab_all || "همه محصولات")} <span class="count">(${toFaDigits(CATALOG.length)})</span></button>`,
    ];
    CATEGORIES.forEach((cat) => {
      const count = CATALOG.filter((i) => i.catId === cat).length;
      parts.push(
        `<button type="button" class="products-tab${state.tab === cat ? " is-active" : ""}" data-tab="${escapeAttr(cat)}"><span class="products-tab__name">${escapeHtml(cat)}</span> <span class="count">(${toFaDigits(count)})</span></button>`
      );
    });
    els.tabs.innerHTML = parts.join("");
  }

  function rasterPicture(src, extraAttrs) {
    const safe = escapeAttr(src);
    return `<img src="${safe}" ${extraAttrs}>`;
  }

  function cardTemplate(item) {
    const alt = escapeAttr(item.imageAlt);
    const closePic = rasterPicture(
      item.imageClose,
      `class="product-card__img product-card__img--close" alt="${alt}" width="800" height="600" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='${FALLBACK_IMG}';"`
    );
    const openPic = rasterPicture(
      item.imageOpen,
      `class="product-card__img product-card__img--open" alt="" aria-hidden="true" width="800" height="600" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='${FALLBACK_IMG}';"`
    );

    return `
      <article class="product-card" data-id="${item.id}">
        <div class="product-card__thumb">
          <div class="product-card__media">
            ${closePic}
            ${openPic}
          </div>
        </div>
        <div class="product-card__body">
          <h3 class="product-card__title">${escapeHtml(item.title)}</h3>
          <div class="product-card__spacer" aria-hidden="true"></div>
          <a href="contact.html" class="product-card__cta">
            <span>استعلام و ثبت سفارش</span>
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          </a>
        </div>
      </article>`;
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

  function updateGridMeta(filtered, shownCount) {
    if (els.empty) els.empty.hidden = filtered.length !== 0;
    if (els.grid) els.grid.hidden = filtered.length === 0;

    if (els.count) {
      els.count.innerHTML = filtered.length
        ? (copy.count_template || "نمایش {shown} از {total} محصول")
            .replace("{shown}", `<strong>${toFaDigits(shownCount)}</strong>`)
            .replace("{total}", `<strong>${toFaDigits(filtered.length)}</strong>`)
        : "";
    }

    if (els.loadMoreWrap) {
      els.loadMoreWrap.hidden = filtered.length <= PAGE_SIZE;
    }
    setToggleLabel(state.visible > PAGE_SIZE);
  }

  function renderGrid() {
    if (!els.grid) return;
    const filtered = getFiltered();
    const shownCount = Math.min(state.visible, filtered.length);

    els.grid.innerHTML = filtered.slice(0, shownCount).map(cardTemplate).join("");

    updateGridMeta(filtered, shownCount);
    observeCards();
  }

  let observer;
  function observeCards() {
    if (!("IntersectionObserver" in window)) {
      document.querySelectorAll(".product-card").forEach((c) => c.classList.add("is-visible"));
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
        { threshold: 0.08, rootMargin: "0px 0px -60px 0px" }
      );
    }
    document.querySelectorAll(".product-card:not(.is-visible)").forEach((card) => {
      observer.observe(card);
    });
  }

  function refresh() {
    renderTabs();
    renderGrid();
  }

  function onTabsClick(e) {
    const btn = e.target.closest("[data-tab]");
    if (!btn) return;
    state.tab = btn.dataset.tab;
    state.visible = PAGE_SIZE;
    refresh();
  }

  let searchTimer;
  function onSearchInput(e) {
    clearTimeout(searchTimer);
    const value = e.target.value;
    searchTimer = setTimeout(() => {
      state.q = value;
      state.visible = PAGE_SIZE;
      renderGrid();
    }, 200);
  }

  function captureEls() {
    els.tabs = $("#productsTabs");
    els.search = $("#productsSearch");
    els.grid = $("#productsGrid");
    els.empty = $("#productsEmpty");
    els.count = $("#productsCount");
    els.loadMoreWrap = $("#productsLoadMoreWrap");
    els.loadMore = $("#productsLoadMore");
    els.chips = $("#productsChips");
  }

  function onLoadMore() {
    captureEls();
    if (state.visible > PAGE_SIZE) {
      state.visible = PAGE_SIZE;
      renderGrid();
      document.querySelector(".products-grid-area")?.scrollIntoView({ behavior: "smooth", block: "start" });
      return;
    }
    state.visible = getFiltered().length;
    renderGrid();
  }

  function bindUi() {
    if (els.tabs && !els.tabs.dataset.bound) {
      els.tabs.dataset.bound = "1";
      els.tabs.addEventListener("click", onTabsClick);
    }
    if (els.search && !els.search.dataset.bound) {
      els.search.dataset.bound = "1";
      els.search.addEventListener("input", onSearchInput);
    }
    if (els.loadMore) {
      els.loadMore.onclick = onLoadMore;
    }
  }

  async function init() {
    captureEls();

    if (els.chips) els.chips.hidden = true;

    if (!els.grid) return;

    bindUi();

    try {
      const api = (window.ALWIN_CONFIG && window.ALWIN_CONFIG.apiUrl) || "/api/v1";
      const urls = [api + "/products", DATA_URL];
      let data = null;
      for (const url of urls) {
        try {
          const res = await fetch(url, { headers: { Accept: "application/json" } });
          if (res.ok) {
            data = await res.json();
            if (data && data.products) break;
          }
        } catch (e) {}
      }
      if (!data) throw new Error("no catalog");
      CATEGORIES = (data.summary && data.summary.categories) || [];
      CATALOG = buildCatalog(data.products || []);
      refresh();
    } catch (err) {
      console.error("[products.js] Failed to load products catalog", err);
      if (els.count) els.count.textContent = copy.load_error || "بارگذاری محصولات با خطا مواجه شد.";
    }
  }

  window.ALWIN_PAGE = window.ALWIN_PAGE || {};
  window.ALWIN_PAGE.products = init;
  window.ALWIN_PAGE.toggleProducts = onLoadMore;

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
