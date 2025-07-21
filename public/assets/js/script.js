const header = document.getElementById("header");
const hamburger = document.getElementById("hamburger");
const navbar = document.querySelector(".navbar");

let lastScrollTop = 0;
window.addEventListener("scroll", () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    if (scrollTop > lastScrollTop && scrollTop > 100) {
        header.classList.add("hidden");
    } else {
        header.classList.remove("hidden");
    }
    lastScrollTop = scrollTop;
});

hamburger.addEventListener("click", () => {
    navbar.classList.toggle("visible");
});

// RESPONSIVE
const showMobileNav = () => {
    if (window.innerWidth <= 950) {
        navbar.classList.add("mobileNav");
        hamburger.style.display = "flex";
    } else {
        hamburger.style.display = "none";
        navbar.classList.remove("mobileNav");
    }
};

window.addEventListener("resize", showMobileNav);
window.addEventListener("load", showMobileNav);
