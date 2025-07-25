<?php
$pictures = [];
for ($i = 1; $i <= 191; $i++) {
    $pictures[] = "/assets/img/gallery/image_" . $i . ".jpg";
}
?>

<section class="gallery" id="gallery">
    <h2>Galerie</h2>
    <div class="gallery-grid">
        <?php foreach ($pictures as $img): ?>
            <picture>
                <img
                    src="<?php echo htmlspecialchars($img); ?>"
                    alt="Image de la galerie"
                    loading="lazy"
                    decoding="async"
                    width="200" height="200">
            </picture>
        <?php endforeach; ?>
    </div>
</section>