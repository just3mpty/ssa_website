<section class="users">
    <h1>Gestion des utilisateurs</h1>

    <div class="buttons">
        <button id="createUserBtn">Créer</button>
    </div>

    <div class="wrapper">
        <form id="usersTableForm" method="POST" action="/dashboard/users">
            <input type="hidden" name="action" value="delete">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Ajouté(e) le</th>
                        <th>Gérer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <!-- On transmet un tableau user_ids[] pour pouvoir supprimer plusieurs utilisateurs -->
                                <input class="user-checkbox" type="checkbox" name="user_ids[]" value="<?php echo htmlspecialchars($user->id); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($user->username); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td class="<?= htmlspecialchars($user->role) ?>">
                                <p><?php echo htmlspecialchars($user->role); ?></p>
                            </td>
                            <td><?php echo htmlspecialchars((new DateTime($user->created_at))->format('d/m/Y')) ?></td>
                            <td><button type="button">Gérer</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Bouton de suppression lié au tableau -->
            <button class="deleteUser" type="submit" disabled>Supprimer la sélection</button>
        </form>
    </div>

    <div class="popup hidden">
        <form method="POST" action="/dashboard/users">
            <h2>Créer un utilisateur</h2>
            <input type="hidden" name="action" value="create">

            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="email" name="email" placeholder="Email" required>

            <select name="role">
                <option value="employee">Employé</option>
                <option value="admin">Admin</option>
            </select>

            <button type="submit">Créer l'utilisateur</button>
        </form>
    </div>
</section>