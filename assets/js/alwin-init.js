/**
 * Run before main.js — prevent SplitText from breaking Persian/Arabic script.
 */
(function () {
  document.querySelectorAll(".char-anim, .word-anim").forEach(function (el) {
    el.classList.remove("char-anim", "word-anim");
    el.removeAttribute("data-delay");
    el.removeAttribute("data-direction");
    el.removeAttribute("data-ease");
  });

  var scripts = document.getElementsByTagName("script");
  var cmsSrc = "/assets/js/cms-client.js?v=1.0";
  for (var i = 0; i < scripts.length; i++) {
    var src = scripts[i].src || "";
    if (src.indexOf("alwin-init.js") !== -1) {
      cmsSrc = src.replace(/alwin-init\.js[^/]*$/, "cms-client.js?v=1.0");
      break;
    }
  }
  var s = document.createElement("script");
  s.src = cmsSrc;
  s.defer = true;
  document.head.appendChild(s);
})();
