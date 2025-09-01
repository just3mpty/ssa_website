<section class="users">
    <h1>Gestions des utilisateurs</h1>
    <div class="buttons">
        <button id=" createUserBtn">Créer</button>
        <button id="deleteUserBtn">Supprimer</button>
    </div>
    <div class="wrapper">
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
                            <input type="checkbox" class="user-checkbox" value="<?php echo htmlspecialchars($user->id); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($user->username); ?></td>
                        <td><?php echo htmlspecialchars($user->email); ?></td>
                        <td class="<?= $user->role ?>">
                            <p><?php echo htmlspecialchars($user->role); ?></p>
                        </td>
                        <td><?php echo htmlspecialchars((new DateTime($user->created_at))->format('d/m/Y')) ?></td>
                        <td><button>Gérer</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="popup">
        <form method="POST" action="/dashboard/users">
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