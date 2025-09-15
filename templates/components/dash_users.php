<?php
$e = static fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');


?>

<section class="users">
    <h1>Gestion des utilisateurs</h1>

    <div class="buttons">
        <button id="createUserBtn">Créer</button>
    </div>

    <?php if (!empty($flash)): ?>
        <p class="notice notice--success" style="color: #43c466;"><?= $e($flash) ?></p>
    <?php endif; ?>

    <div class="wrapper" id="wrapper">
            <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
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
                                <input class="user-checkbox" type="checkbox" name="user_ids[]" value="<?php echo htmlspecialchars($user->id); ?>">
                            </td>
                            <td class="idValue" name='idValue' hidden><?= htmlspecialchars($user->id); ?></td>

                            <td class="usernameValue"><?php echo htmlspecialchars($user->username); ?></td>
                            <td class="emailValue"><?php echo htmlspecialchars($user->email); ?></td>
                            <td class="<?= htmlspecialchars($user->role) ?> role">
                                <p><?php echo htmlspecialchars($user->role); ?></p>
                            </td>
                            <td><?php echo htmlspecialchars((new DateTime($user->created_at))->format('d/m/Y')) ?></td>
                            <td><button class="editBtn" type="button" onclick='editLeUser(event)'>Gérer</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="deleteUser" name="deleteBtn" onclick='suppUsers()'>Supprimer la sélection</button>
        <div id="placeHolderFormEnd"></div>
    </div>

    <div class="popup hidden">
        <form method="POST" action="/dashboard/users/create">
            <?= \CapsuleLib\Security\CsrfTokenManager::insertInput(); ?>
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
