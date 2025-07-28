const header = document.getElementById("header");
const hamburger = document.getElementById("hamburger");
const navbar = document.querySelector(".navbar");
const downloadLink = document.getElementById("download");

// FILTRE DES ACTUALITES
const filterButtons = document.querySelectorAll(".filter-btn");
const articles = document.querySelectorAll(".news-item");

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

// OUVERTURE DU MENU MOBILE
hamburger.addEventListener("click", () => {
    navbar.classList.toggle("visible");
});

// OUVERTURE DE L'OVERLAY IMAGES
const overlay = document.getElementById("image-overlay");
const overlayImg = document.getElementById("overlay-img");
const closeBtn = document.getElementById("close-overlay");
const prevBtn = document.getElementById("prev-img");
const nextBtn = document.getElementById("next-img");

const galleryImages = Array.from(
    document.querySelectorAll(".gallery-grid img")
);
let currentImgIndex = -1;

function showOverlay(index) {
    currentImgIndex = index;
    overlayImg.src = galleryImages[currentImgIndex].src;
    overlay.classList.add("active");
    overlay.focus();
}

function showPrev() {
    if (galleryImages.length === 0) return;
    currentImgIndex =
        (currentImgIndex - 1 + galleryImages.length) % galleryImages.length;
    overlayImg.src = galleryImages[currentImgIndex].src;
}

function showNext() {
    if (galleryImages.length === 0) return;
    currentImgIndex = (currentImgIndex + 1) % galleryImages.length;
    overlayImg.src = galleryImages[currentImgIndex].src;
}

prevBtn.addEventListener("click", showPrev);
nextBtn.addEventListener("click", showNext);

galleryImages.forEach((img, idx) => {
    img.addEventListener("click", () => {
        showOverlay(idx);
    });
});

overlay.addEventListener("click", (e) => {
    if (e.target === overlay || e.target === closeBtn) {
        overlay.classList.remove("active");
        overlayImg.src = "";
        currentImgIndex = -1;
    }
});

overlay.addEventListener("keydown", (e) => {
    if (!overlay.classList.contains("active")) return;
    if (e.key === "ArrowRight") {
        currentImgIndex = (currentImgIndex + 1) % galleryImages.length;
        overlayImg.src = galleryImages[currentImgIndex].src;
    } else if (e.key === "ArrowLeft") {
        currentImgIndex =
            (currentImgIndex - 1 + galleryImages.length) % galleryImages.length;
        overlayImg.src = galleryImages[currentImgIndex].src;
    } else if (e.key === "Escape") {
        overlay.classList.remove("active");
        overlayImg.src = "";
        currentImgIndex = -1;
    }
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
