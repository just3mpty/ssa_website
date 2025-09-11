<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lang\TranslationLoader;
use CapsuleLib\Core\RenderController;
use CapsuleLib\Http\RequestUtils;
use CapsuleLib\Http\FlashBag;
use CapsuleLib\Security\CsrfTokenManager;


final class CalendarController extends RenderController
{
    public function index(): void
    {
        echo $this->renderView('calendar/home.php', [
            'title'       => 'Calendrier',
            'isDashboard' => false,
            'isAdmin'     => isset($_SESSION['admin']),
            'user'        => $_SESSION['admin'] ?? [],
            'str'         => TranslationLoader::load(defaultLang: 'fr'),
            'articleGenerateIcsAction' => '/home/generate_ics',
        ]);
    }

    public function handlePost(): void
    {

        $this->generateICS();
        // Gérer la soumission du formulaire
        //
        //

        // if (isset($_POST['article_id']) && $_POST['article_id'] === 'generer_ics') {
        //     $this->generateICS();
        // } else {
        //     // Gérer le cas où 'article_id' n'est pas défini ou a une autre valeur
        //     echo "ID d'événement non spécifié ou invalide.";
        // }

        // if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        //     header('Location: /dashboard/articles/', true, 303);
        //     return;
        // }


    }

    public function generateICS(): void
    {
        RequestUtils::ensurePostOrRedirect('/home');
        //CsrfTokenManager::requireValidToken();

        // Récupération des données
        $date_debut = strtotime($_POST['eventDate'] ?? '');
        $date_fin   = $date_debut + 3600;
        $objet      = $_POST['eventTitle'] ?? '';
        $lieu       = $_POST['eventLocation'] ?? '';
        $details    = $_POST['eventDescription'] ?? '';

        // Génération du contenu ICS
        $ics  = "BEGIN:VCALENDAR\n";
        $ics .= "VERSION:2.0\n";
        $ics .= "PRODID:-//MonSite//FR\n";
        $ics .= "BEGIN:VEVENT\n";
        $ics .= "UID:" . uniqid() . "@monsite.fr\n";
        $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\n";
        $ics .= "DTSTART:" . gmdate('Ymd\THis\Z', $date_debut) . "\n";
        $ics .= "DTEND:"   . gmdate('Ymd\THis\Z', $date_fin) . "\n";
        $ics .= "SUMMARY:" . addcslashes($objet, ",;\\") . "\n";
        $ics .= "LOCATION:" . addcslashes($lieu, ",;\\") . "\n";
        $ics .= "DESCRIPTION:" . addcslashes($details, ",;\\") . "\n";
        $ics .= "END:VEVENT\n";
        $ics .= "END:VCALENDAR";

        // Envoi des en-têtes
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="event.ics"');
        echo $ics;

    }
}
