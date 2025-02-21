const yearEl = document.querySelector(".year"),
    currentYear = new Date().getFullYear();
yearEl.textContent = currentYear;
const btnNavEl = document.querySelector(".btn-mobile-nav"),
    headerEl = document.querySelector(".header");
btnNavEl.addEventListener("click", () => {
    headerEl.classList.toggle("nav-open");
});
const allLinks = document.querySelectorAll("a:link");

allLinks.forEach((e) => {
    e.addEventListener("click", (t) => {
        let l = e.getAttribute("href");

        // استثناء الروابط الخارجية أو التي لا تبدأ بـ #
        if (!l.startsWith("#") && !l.startsWith("javascript") && l !== "") {
            return;
        }

        // منع السلوك الافتراضي فقط للروابط الداخلية
        t.preventDefault();

        if (l === "#") {
            window.scrollTo({ top: 0, behavior: "smooth" });
        } else if (l.startsWith("#")) {
            let o = document.querySelector(l);
            if (o) o.scrollIntoView({ behavior: "smooth" });
        }

        if (e.classList.contains("main-nav-link")) {
            headerEl.classList.toggle("nav-open");
        }
    });
});

const sectionHeroEl = document.querySelector(".section-hero"),
    obs = new IntersectionObserver(
        function (e) {
            let t = e[0];
            !1 === t.isIntersecting && document.body.classList.add("sticky"),
                !0 === t.isIntersecting &&
                    document.body.classList.remove("sticky");
        },
        { root: null, threshold: 0, rootMargin: "-80px" }
    );
function checkFlexGap() {
    var e = document.createElement("div");
    (e.style.display = "flex"),
        (e.style.flexDirection = "column"),
        (e.style.rowGap = "1px"),
        e.appendChild(document.createElement("div")),
        e.appendChild(document.createElement("div")),
        document.body.appendChild(e);
    var t = 1 === e.scrollHeight;
    e.parentNode.removeChild(e),
        console.log(t),
        t || document.body.classList.add("no-flexbox-gap");
}
obs.observe(sectionHeroEl), checkFlexGap();
