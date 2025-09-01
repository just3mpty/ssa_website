<section class="articles">
    <h1>Gestion des articles</h1>
    <a href="/events/create">Créer un article</a>
    <?php if (empty($articles)): ?>
        <p>Aucun article trouvé.</p>
    <?php else: ?>
        <div class="wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Résumé</th>
                        <th>Date</th>
                        <th>Auteur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article->id) ?></td>
                            <td><?= htmlspecialchars($article->titre) ?></td>
                            <td><?= htmlspecialchars($article->resume) ?></td>
                            <td><?= htmlspecialchars((new DateTime($article->date_event))->format('d/m/Y')) ?></td>
                            <td><?= htmlspecialchars($article->author) ?></td>
                            <td class="buttons">
                                <a href="/events/edit/<?= $article->id ?>">Modifier</a>
                                <form action="/events/delete/<?= $article->id ?>" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                                    <button style="background-color: #ED7F7F;" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>