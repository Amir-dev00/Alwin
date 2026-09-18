/**
 * ALWIN public site — load editable CMS content with HTML fallback.
 */
(function () {
  "use strict";

  var config = window.ALWIN_CONFIG || {};
  var API = config.apiUrl || "/api/v1";

  function get(obj, path) {
    return String(path).split(".").reduce(function (acc, key) {
      return acc && acc[key] !== undefined ? acc[key] : undefined;
    }, obj);
  }

  function setText(el, value) {
    if (value === undefined || value === null || value === "") return;
    var ones = el.querySelectorAll(".text-one, .text-two");
    if (ones.length) {
      ones.forEach(function (n) { n.textContent = value; });
      return;
    }
    if (el.getAttribute("data-cms-html") === "true") {
      el.innerHTML = value;
    } else {
      el.textContent = value;
    }
  }

  function applyNav(list, items) {
    if (!list || !items || !items.length) return;
    var html = items.map(function (item) {
      return "<li><a href=\"" + String(item.url).replace(/"/g, "&quot;") + "\">" + String(item.label) + "</a></li>";
    }).join("");
    list.innerHTML = html;
  }

  function applySeo(page) {
    if (!page || !page.seo) return;
    if (page.seo.title) document.title = page.seo.title;
    var meta = document.querySelector('meta[name="description"]');
    if (meta && page.seo.description) meta.setAttribute("content", page.seo.description);
  }

  function applyPartners(partners) {
    if (!partners || !partners.length) return;
    var imgs = document.querySelectorAll(".client-box img");
    partners.forEach(function (p, i) {
      if (!imgs[i] || !p.image) return;
      imgs[i].src = p.image;
      imgs[i].alt = p.alt || p.name || "";
      var source = imgs[i].closest("picture") && imgs[i].closest("picture").querySelector("source");
      if (source) source.setAttribute("srcset", p.image);
    });

    document.querySelectorAll(".brand-logo-marquee img").forEach(function (img, i) {
      var p = partners[i % partners.length];
      if (!p || !p.image) return;
      img.src = p.image;
      if (img.getAttribute("aria-hidden") !== "true") {
        img.alt = p.alt || p.name || "";
      }
    });
  }

  function applyHomeProducts(products) {
    if (!products || !products.length) return;
    var boxes = document.querySelectorAll(".service-area .service-box");
    products.filter(function (p) { return p.show_on_home; }).forEach(function (p, i) {
      var box = boxes[i];
      if (!box) return;
      var title = box.querySelector(".title a, .title");
      if (title && p.title) title.textContent = p.title;
      var close = box.querySelector(".home-product-media__img--close");
      var open = box.querySelector(".home-product-media__img--open");
      var srcClose = p.images && p.images.close;
      var srcOpen = p.images && p.images.open;
      if (close && srcClose) {
        close.src = srcClose;
        close.alt = (p.images && p.images.alt) || p.title;
        var pic = close.previousElementSibling;
        if (pic && pic.tagName === "SOURCE") pic.srcset = srcClose;
        close.closest("picture")?.querySelector("source")?.setAttribute("srcset", srcClose);
      }
      if (open && srcOpen) {
        open.src = srcOpen;
        open.closest("picture")?.querySelector("source")?.setAttribute("srcset", srcOpen);
      }
    });
  }

  function applySettings(settings) {
    if (!settings) return;
    document.querySelectorAll(".alwin-footer__tagline").forEach(function (el) {
      if (settings.tagline) el.textContent = settings.tagline;
    });
    document.querySelectorAll('.alwin-footer__contact a[href^="mailto:"]').forEach(function (el) {
      if (!settings.email) return;
      el.href = "mailto:" + settings.email;
      el.textContent = settings.email;
    });
    document.querySelectorAll('.alwin-footer__contact a[href^="tel:"]').forEach(function (el) {
      if (!settings.phone) return;
      el.href = "tel:" + settings.phone;
      el.textContent = settings.phone_display || settings.phone;
    });
    document.querySelectorAll(".alwin-footer__contact li span").forEach(function (el) {
      if (settings.address && el.closest("li")?.querySelector(".fa-location-dot")) {
        el.textContent = settings.address;
      }
    });
    document.querySelectorAll(".header__button .text-one, .header__button .text-two").forEach(function (el) {
      if (settings.cta_label) el.textContent = settings.cta_label;
    });
    document.querySelectorAll(".alwin-footer__copy").forEach(function (el) {
      if (settings.copyright) el.textContent = settings.copyright;
    });
    document.querySelectorAll(".alwin-footer__credit").forEach(function (el) {
      if (settings.credit) el.textContent = settings.credit;
    });
    var social = {
      "اینستاگرام": settings.instagram,
      "تلگرام": settings.telegram,
      "واتساپ": settings.whatsapp
    };
    document.querySelectorAll(".alwin-footer__social-link").forEach(function (el) {
      var label = el.getAttribute("aria-label");
      if (label && social[label]) el.href = social[label];
    });
    if (settings.intro_src) {
      document.querySelectorAll(".video-area source, video.video-area source").forEach(function (el) {
        el.setAttribute("src", settings.intro_src);
      });
    }
  }

  function applyBlocks(pageKey, pages) {
    var page = pages && pages[pageKey];
    if (!page) return;
    applySeo(page);
    var blocks = page.blocks || {};
    document.querySelectorAll("[data-cms]").forEach(function (el) {
      var path = el.getAttribute("data-cms");
      var value = get({ pages: pages, blocks: blocks }, path);
      if (value === undefined && path.indexOf(".") === -1) value = blocks[path];
      setText(el, value);
    });
  }

  function pageKey() {
    return document.documentElement.getAttribute("data-cms-page") || "";
  }

  async function boot() {
    try {
      var siteRes = await fetch(API + "/site", { headers: { Accept: "application/json" } });
      if (!siteRes.ok) return;
      var site = await siteRes.json();
      applySettings(site.settings);
      applyNav(document.querySelector(".main-menu ul"), site.navigation && site.navigation.header);
      applyNav(document.querySelector(".alwin-footer__links"), site.navigation && site.navigation.footer);
      applyPartners(site.partners);
      applyBlocks(pageKey(), site.pages);
      window.ALWIN_CMS = site;
    } catch (err) {
      // Keep hardcoded HTML fallback.
    }

    try {
      var prodRes = await fetch(API + "/products", { headers: { Accept: "application/json" } });
      if (!prodRes.ok) return;
      var prod = await prodRes.json();
      applyHomeProducts(prod.products);
      window.ALWIN_CMS_PRODUCTS = prod;
    } catch (err) {}
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
