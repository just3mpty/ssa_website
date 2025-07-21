const header = document.getElementById("header");
const hamburger = document.getElementById("hamburger");
const navbar = document.querySelector(".navbar");

// FILTRE DES ACTUALITES
const filterButtons = document.querySelectorAll(".filter-btn");
const articles = document.querySelectorAll(".news-item");

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

filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
        const filter = button.dataset.filter;
        filterButtons.forEach((btn) => btn.classList.remove("active-filter"));
        button.classList.add("active-filter");

        articles.forEach((article) => {
            const category = article.dataset.category;

            if (filter === "all" || category === filter) {
                article.style.display = "flex";
            } else {
                article.style.display = "none";
            }
        });
    });
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
