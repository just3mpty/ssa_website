<section class="account">
    <h1>Mon compte</h1>

    <!-- display username, email, role -->

    <p><?= $user['username'] ?></p>
    <p><?= $user['role'] ?></p>
    <p><?= $user['email'] ?></p>

    <!-- fonction updatePassword -->
    <div id="update-password-form" >
        <form method="post" action="">
            <p>Changer de mot de passe :</p>
            <input type="password" name="old_password" placeholder="Ancien mot de passe" required>
            <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
            <input type="password" name="confirm_new_password" placeholder="Confirmer le nouveau mot de passe" required>
            <button type="submit" id="submit-update-password">Mettre à jour</button>
        </form>
    </div>
<<<<<<< HEAD
    
    <?php
        // Supposons que $user['id'] est défini et identifié
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les valeurs des champs
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmNewPassword = $_POST['confirm_new_password'] ?? '';
            
            if ($newPassword === $confirmNewPassword) {
                // Appel de ta fonction updatePassword
                $result = updatePassword($user['id'], $oldPassword, $newPassword);

                if ($result === true) {
                    echo "Mot de passe modifié avec succès !";
                } else {
                    echo "Erreur lors de la mise à jour : $result";
                }
            } else {
                echo "Les nouveaux mots de passe ne correspondent pas";
            }
        }
    ?>
=======
>>>>>>> 7231300f0e1958b5b50c839c8878c4ef8b54d289


</section>