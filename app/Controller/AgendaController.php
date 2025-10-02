<?php

declare(strict_types=1);

namespace App\Controller;

use Capsule\Contracts\ResponseFactoryInterface;
use Capsule\Contracts\ViewRendererInterface;
use Capsule\Http\Message\Request;
use Capsule\Http\Message\Response;
use Capsule\Routing\Attribute\Route;
use Capsule\Routing\Attribute\RoutePrefix;
use Capsule\Security\CsrfTokenManager;
use Capsule\View\BaseController;
use DateTime;

#[RoutePrefix('/dashboard/agenda')]
final class AgendaController extends BaseController
{
    private const PX_PER_HOUR = 64;

    public function __construct(
        ResponseFactoryInterface $res,
        ViewRendererInterface $view,
    ) {
        parent::__construct($res, $view);
        if (session_status() !== \PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['agenda_events'] ??= []; // stockage très simple (à remplacer par service/DB)
    }

    /** GET /dashboard/agenda */
    #[Route(path: '', methods: ['GET'])]
    public function index(Request $req): Response
    {
        // 1) Récup semaine demandée (dd-mm-YYYY) → lundi de la semaine
        $weekParam = $this->strFromQuery($req, 'week');
        [$monday, $days] = $this->computeWeek($weekParam);

        $hours = $this->hoursRange(8, 18); // 08:00 → 18:00
        $weekLabel = $monday->format('d-m-Y');
        $prevMonday = (clone $monday)->modify('-7 days')->format('d-m-Y');
        $nextMonday = (clone $monday)->modify('+7 days')->format('d-m-Y');

        // 2) Events → filtrés sur cette semaine et mappés par case (jour, heure)
        /** @var array<int,array{date:string,time:string,title:string,location:string,duration:float}> $all */
        $all = $_SESSION['agenda_events'];

        $grid = $this->buildGrid($days, $hours, $this->filterWeekEvents($days, $all));

        // 3) Render dans le shell dashboard
        $content = $this->view->render('components/dash_agenda.tpl.php', [
            'create_url' => '/dashboard/agenda/create',
            'csrf_input' => $this->csrfInput(),
            'week_label' => $weekLabel,
            'prev_week_url' => '/dashboard/agenda?week=' . rawurlencode($prevMonday),
            'next_week_url' => '/dashboard/agenda?week=' . rawurlencode($nextMonday),
            'days' => $grid['days'],   // enrichi avec events/has_events par heure
            'hours' => $grid['hours'],
            'modal_open' => false,
        ]);

        return $this->html('dashboard/home.tpl.php', [
            'title' => 'Mon agenda',
            'isDashboard' => true,
            'showHeader' => false,
            'showFooter' => false,
            'links' => $this->linksForSidebar(), // si tu as un provider, sinon enlève
            'dashboardContent' => $content,
        ]);
    }

    /** POST /dashboard/agenda/create */
    #[Route(path: '/create', methods: ['POST'])]
    public function create(Request $req): Response
    {
        CsrfTokenManager::requireValidToken();

        // HTML5 <input type="date"> → YYYY-MM-DD ; <input type="time"> → HH:MM
        $title = trim((string)($_POST['titre'] ?? ''));
        $date = trim((string)($_POST['date'] ?? ''));   // YYYY-MM-DD
        $time = trim((string)($_POST['heure'] ?? ''));  // HH:MM
        $loc = trim((string)($_POST['lieu'] ?? ''));

        if ($title === '' || $date === '' || $time === '') {
            return $this->res->redirect('/dashboard/agenda', 303);
        }

        // Normalisation date => dd-mm-YYYY (comme le rendu)
        $dt = DateTime::createFromFormat('Y-m-d', $date) ?: new DateTime($date);
        $dateDisplay = $dt->format('d-m-Y');

        // durée par défaut : 1h (tu peux ajouter un champ “duree” dans le form)
        $duration = 1.0;

        $_SESSION['agenda_events'][] = [
            'date' => $dateDisplay,
            'time' => $time,
            'title' => $title,
            'location' => $loc,
            'duration' => $duration,
        ];

        // Revenir sur la semaine du nouvel event
        $monday = $this->mondayOf($dt)->format('d-m-Y');

        return $this->res->redirect('/dashboard/agenda?week=' . rawurlencode($monday), 302);
    }

    /* ------------------------- Helpers ------------------------- */

    private function csrfInput(): string
    {
        return CsrfTokenManager::insertInput();
    }

    private function strFromQuery(Request $req, string $key): ?string
    {
        $q = $req->queryParams[$key] ?? null;

        return is_string($q) ? $q : null;
    }

    /**
     * @return array{0:DateTime,1:list<array{name:string,date:string}>}
     */
    private function computeWeek(?string $weekParam): array
    {
        $base = $weekParam
            ? DateTime::createFromFormat('d-m-Y', $weekParam) ?: new DateTime()
            : new DateTime();

        $monday = $this->mondayOf($base);
        $days = [];
        $labels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
        foreach ($labels as $i => $name) {
            $d = (clone $monday)->modify("+{$i} days");
            $days[] = ['name' => $name, 'date' => $d->format('d-m-Y')];
        }

        return [$monday, $days];
    }

    private function mondayOf(DateTime $dt): DateTime
    {
        $m = clone $dt;
        $weekday = (int)$m->format('N'); // 1 = Lundi
        if ($weekday !== 1) {
            $m->modify('-' . ($weekday - 1) . ' days');
        }

        return $m;
    }

    /** @return list<array{display:string,hour:int}> */
    private function hoursRange(int $start, int $end): array
    {
        $out = [];
        foreach (range($start, $end) as $h) {
            $out[] = ['display' => sprintf('%02d:00', $h), 'hour' => $h];
        }

        return $out;
    }

    /**
     * Filtre les events de la semaine courante.
     * @param list<array{name:string,date:string}> $days
     * @param list<array{date:string,time:string,title:string,location:string,duration:float}> $all
     * @return list<array{date:string,time:string,title:string,location:string,duration:float}>
     */
    private function filterWeekEvents(array $days, array $all): array
    {
        $allowedDates = array_column($days, 'date');

        return array_values(array_filter($all, fn ($e) => in_array($e['date'], $allowedDates, true)));
    }

    /**
     * Construit la grille exploitable directement par le template.
     *
     * @param list<array{name:string,date:string}> $days
     * @param list<array{display:string,hour:int}> $hours
     * @param list<array{date:string,time:string,title:string,location:string,duration:float}> $events
     * @return array{days:list<array{name:string,date:string}>,hours:list<array{display:string,hour:int,rows:list<mixed>}>>}
     */
    private function buildGrid(array $days, array $hours, array $events): array
    {
        // index => date -> hour -> events
        $index = [];
        foreach ($events as $e) {
            $hour = (int)substr($e['time'], 0, 2);
            $index[$e['date']][$hour][] = $e;
        }

        $pxHour = self::PX_PER_HOUR;
        $pxHalf = $pxHour / 2;

        // enrichit chaque day cell par heure
        $enrichedDays = [];
        foreach ($days as $d) {
            $enrichedDays[] = $d + ['_events' => $index[$d['date']] ?? []];
        }

        // Pour le template : on ne duplique pas toutes les cases ici,
        // on laisse MiniMustache itérer sur hours X days et piocher les events.
        // On marque juste “has_events / events” à la volée dans le rendu :
        // => astuce : on remplit les days avec closures résolues avant rendu.
        // Ici, on prépare une structure d’accès simple pour les callbacks.

        // On post-transforme les days en injectant une “getter simple”
        $daysForTpl = array_map(function ($d) use ($pxHour, $pxHalf) {
            $byHour = $d['_events'];
            unset($d['_events']);

            // on ajoute une clé interne qui sera lue par le renderer parent
            $d['__byHour'] = $byHour;
            $d['has_events'] = false; // valeur par défaut

            return $d;
        }, $enrichedDays);

        // On “emballe” hours pour que chaque croisement (day,hour) puisse récupérer
        // les events et en déduire style/top/height
        $hoursForTpl = array_map(function ($h) use ($daysForTpl, $pxHour, $pxHalf) {
            $h['_days'] = array_map(function ($d) use ($h, $pxHalf, $pxHour) {
                $hour = (int)$h['hour'];
                $byHour = $d['__byHour'] ?? [];
                $rawEvents = $byHour[$d['date']] ?? $byHour; // sécurité

                $cellEvents = [];
                if (isset($byHour[$hour])) {
                    foreach ($byHour[$hour] as $ev) {
                        $isHalf = (substr($ev['time'], 3, 2) === '30');
                        $cellEvents[] = [
                            'title' => $ev['title'],
                            'date' => $ev['date'],
                            'time' => $ev['time'],
                            'location' => $ev['location'],
                            'duration' => $ev['duration'],
                            'css_class' => $isHalf ? 'event-half-hour' : 'event-whole-hour',
                            'height_px' => (int)round($ev['duration'] * $pxHour),
                            'top_px' => $isHalf ? (int)$pxHalf : 0,
                        ];
                    }
                }

                return [
                    'name' => $d['name'],
                    'date' => $d['date'],
                    'has_events' => !empty($cellEvents),
                    'events' => $cellEvents,
                ];
            }, $daysForTpl);

            return [
                'display' => $h['display'],
                'hour' => $h['hour'],
                '_days' => $h['_days'],
            ];
        }, $hours);

        // Le template itère {{#each hours}} puis {{#each ../days}}
        // Pour ça, on renomme _days → days :
        $hoursForTpl = array_map(fn ($h) => ['display' => $h['display'], 'hour' => $h['hour'], 'days' => $h['_days']], $hoursForTpl);

        // Et pour entête, on repasse le tableau “jours” simple (sans __byHour)
        $daysHeader = array_map(fn ($d) => ['name' => $d['name'], 'date' => $d['date']], $daysForTpl);

        return [
            'days' => $daysHeader,
            'hours' => $hoursForTpl,
        ];
    }

    /** Sidebar links si tu en as besoin dans le shell */
    private function linksForSidebar(): array
    {
        // branche sur ton SidebarLinksProvider si dispo.
        return [
            ['title' => 'Mon compte', 'url' => '/dashboard/account', 'icon' => 'user'],
            ['title' => 'Articles', 'url' => '/dashboard/articles', 'icon' => 'file-text'],
            ['title' => 'Agenda', 'url' => '/dashboard/agenda', 'icon' => 'calendar'],
            // …
        ];
    }
}
