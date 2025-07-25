const header = document.getElementById("header");
const hamburger = document.getElementById("hamburger");
const navbar = document.querySelector(".navbar");
const downloadLink = document.getElementById("download");

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

// TELECHARGEMENT DE FICHIER
const handleDownload = () => {
    const fileUrl = "/assets/files/dossier_de_candidature.pdf";
    const link = document.createElement("a");
    link.href = fileUrl;
    link.download = "dossier_de_candidature.pdf";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

if (downloadLink) {
    downloadLink.addEventListener("click", handleDownload);
}

// RESPONSIVE
const showMobileNav = () => {
    if (window.innerWidth <= 950) {
        navbar.classList.add("mobileNav");
        hamburger.style.display = "flex";

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("open");
        });
    } else {
        hamburger.style.display = "none";
        navbar.classList.remove("mobileNav");
    }
};

window.addEventListener("resize", showMobileNav);
window.addEventListener("load", showMobileNav);
