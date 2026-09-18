(function () {
  if (typeof gsap === "undefined") return;

  var svg = document.getElementById("svg");
  var wrap = document.querySelector(".loader-wrap");
  if (!svg || !wrap) return;

  var curve = "M0 502S175 272 500 272s500 230 500 230V0H0Z";
  var flat = "M0 2S175 1 500 1s500 1 500 1V0H0Z";

  var tl = gsap.timeline();

  tl.to(".loader-wrap-heading .load-text", {
    delay: 1.1,
    y: -60,
    opacity: 0,
    duration: 0.35,
  });

  tl.to(svg, { duration: 0.5, attr: { d: curve }, ease: "power2.in" }, "-=0.05").to(
    svg,
    { duration: 0.5, attr: { d: flat }, ease: "power2.out" }
  );

  tl.to(wrap, { y: "-100%", duration: 0.75, ease: "power3.inOut" });
  tl.set(wrap, { display: "none" });
  tl.from("main", { y: 30, opacity: 0, duration: 0.5, ease: "power2.out" }, "-=0.35");
})();
