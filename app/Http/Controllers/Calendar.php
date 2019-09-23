<?php
namespace App\Http\Controllers;

use App\Services\CalendarClient;
use App\Services\Wydruk\GeneratorDom;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;

class Calendar extends Controller
{
    const APPLICATION_ID = '973852827368-4981aoak1r7car10lskp4jau4bm61khv.apps.googleusercontent.com';
    private $application_redirect_url;
    public function __construct() {
        session_start();
        $this->application_redirect_url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/cart';
        //TEST?
    }

    public function login() {
        if (empty($_SESSION['token'])) {
            $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . self::APPLICATION_ID . '&access_type=online';

            echo view('login', ['link' => $linkToSignIn]);
        } else {
            $this->index();
        }
    }

    public function logout() {
        $_SESSION['token'] = null;
        $this->login();
    }

    /**
     * Show the profile for the given user.
     *
     * @param int $id
     * @return View
     */
    public function index()
    {
        $data = array_merge($_GET, $_POST);
        file_put_contents('server_data.txt', var_export($data, true));

        $googleClient = new CalendarClient();
        if(!empty($_GET['code'])) {
            $client = $googleClient->get($_GET['code']);
        } else {
            $client = $googleClient->get();
        }

        $service = new \Google_Service_Calendar($client);
        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        //timeMax date('c') tu będzie zabawa, bo bęzdie trzeba obliczyć początek tygodnia, w którym jest pierwszy dzień miesiąca i koniec miesiąca
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        file_put_contents('events_data.txt', var_export($events, true));

        $this->prepareDataTab($events);

        $calendars = $service->calendarList->listCalendarList();
//        echo '<pre>';
//            var_export($calendars);
//        var_export($events);
//        echo '</pre>';

        echo view('welcome', ['monthYearNow' => $this->getStartDateTimeToGenerate()]);
    }

    public function prepareDataTab($events) {
        /** @var \Google_Service_Calendar_Event $event */
        foreach ($events as $event) {
            $start = $event->getStart();
            $end = $event->getEnd();
            $title = $event->summary;

            $startDateTime = $start->getDateTime();
            $endDateTime = $end->getDateTime();

            echo $title.' '.$startDateTime.' '.$endDateTime."</br>";
        }
    }

    public function getStartDateTimeToGenerate() {
        $month = date("m");
        $day = date("d");
        $year = date("Y");

        if ($day >= 15) {
            $newMonth = $month + 2;
        } else {
            $newMonth = $month + 1;
        }

        if ($newMonth > 12) {
            $newMonth = $newMonth - 12;
            $year++;
        }

        return $newMonth.'-'.$year;
    }

    public function generateOdt() {
        $dane = [
            [
                'miesiac' => 'listopad',
                'rok' => '2019',
                'zmiana1_g_s' => '10',
                'zmiana1_m_s' => '00',
                'zmiana1_g_k' => '11',
                'zmiana1_m_k' => '00',

                'zmiana2_g_s' => '11',
                'zmiana2_m_s' => '00',
                'zmiana2_g_k' => '12',
                'zmiana2_m_k' => '00',

                'zmiana3_g_s' => '12',
                'zmiana3_m_s' => '00',
                'zmiana3_g_k' => '13',
                'zmiana3_m_k' => '00',

                'zmiana4_g_s' => '13',
                'zmiana4_m_s' => '00',
                'zmiana4_g_k' => '14',
                'zmiana4_m_k' => '00',

                'zmiana5_g_s' => '14',
                'zmiana5_m_s' => '00',
                'zmiana5_g_k' => '15',
                'zmiana5_m_k' => '00',

                'zmiana6_g_s' => '15',
                'zmiana6_m_s' => '00',
                'zmiana6_g_k' => '16',
                'zmiana6_m_k' => '00',

                'zmiana7_g_s' => '16',
                'zmiana7_m_s' => '00',
                'zmiana7_g_k' => '17',
                'zmiana7_m_k' => '00',

                'zmiana8_g_s' => '17',
                'zmiana8_m_s' => '00',
                'zmiana8_g_k' => '18',
                'zmiana8_m_k' => '00',

                'zmiana_data' => 'sobota 01 września 2019',

                'zmiana1_osoby' => 'Mateusz',
                'zmiana2_osoby' => 'Brzozowski',
                'zmiana3_osoby' => 'Inny',
                'zmiana4_osoby' => 'Gostek',
                'zmiana5_osoby' => 'Taki',
                'zmiana6_osoby' => 'Gosc',
                'zmiana7_osoby' => 'Jestem',
                'zmiana8_osoby' => 'Tutaj',
            ],

            [
                'miesiac' => '',
                'rok' => '',
                'zmiana1_g_s' => '',
                'zmiana1_m_s' => '',
                'zmiana1_g_k' => '',
                'zmiana1_m_k' => '',

                'zmiana2_g_s' => '',
                'zmiana2_m_s' => '',
                'zmiana2_g_k' => '',
                'zmiana2_m_k' => '',

                'zmiana3_g_s' => '',
                'zmiana3_m_s' => '',
                'zmiana3_g_k' => '',
                'zmiana3_m_k' => '',

                'zmiana4_g_s' => '',
                'zmiana4_m_s' => '',
                'zmiana4_g_k' => '',
                'zmiana4_m_k' => '',

                'zmiana5_g_s' => '',
                'zmiana5_m_s' => '',
                'zmiana5_g_k' => '',
                'zmiana5_m_k' => '',

                'zmiana6_g_s' => '',
                'zmiana6_m_s' => '',
                'zmiana6_g_k' => '',
                'zmiana6_m_k' => '',

                'zmiana7_g_s' => '',
                'zmiana7_m_s' => '',
                'zmiana7_g_k' => '',
                'zmiana7_m_k' => '',

                'zmiana8_g_s' => '',
                'zmiana8_m_s' => '',
                'zmiana8_g_k' => '',
                'zmiana8_m_k' => '',

                'zmiana_data' => 'niedziela 02 września 2019',

                'zmiana1_osoby' => 'Teraz',
                'zmiana2_osoby' => 'Jest',
                'zmiana3_osoby' => 'Kolejny',
                'zmiana4_osoby' => 'Wiersz',
                'zmiana5_osoby' => 'Z',
                'zmiana6_osoby' => 'Danymi',
                'zmiana7_osoby' => 'Do',
                'zmiana8_osoby' => 'Druku',
            ],
        ];
        $filename = md5(time());
        $wydruk = new GeneratorDom();
        $wydruk->ustawNazweWyjsciowegoPliku($filename);
        return $wydruk->generuj($dane, 'Szablony/szablon.skoczow.zawisle.odt');
    }

    public function test() {

        $dane = [
            ['zmienna' => 'sdfds', 'pole1' => 'taki tekst', 'pole2' => 'inny gosciu'],
            ['zmienna' => '', 'pole1' => 'koelny', 'pole2' => 'wiersz'],
        ];

//        $wydruk = new GeneratorDom();
//        $wydruk->ustawNazweWyjsciowegoPliku('szablon_out.odt');
//        return $wydruk->generuj($dane, 'szablon.odt');

        echo view('login', ['name' => 'James']);
    }

    protected function showView($data) {
        $out = <<<OUT

<h1>Welcom on Cart Calendar V18-2032</h1>
<h2>Please <a href="{$data['sign_in']}">SIGN IN</a></h2>

OUT;

        echo $out;
    }
}
