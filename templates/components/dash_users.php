<section class="users">
    <h1>Liste des utilisateurs</h1>
    <div class="wrapper">
        <table class="table table-striped">
            <thead>
                <tr>
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
                        <td><?php echo htmlspecialchars($user->username); ?></td>
                        <td><?php echo htmlspecialchars($user->email); ?></td>
                        <td class="<?= $user->role ?>">
                            <p><?php echo htmlspecialchars($user->role); ?></p>
                        </td>
                        <td><?php echo htmlspecialchars($user->created_at) ?></td>
                        <td><button>Gérer</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>