<?php
namespace App\Http\Controllers;

use App\Services\CalendarClient;
use App\Services\GoogleCalendarData;
use App\Services\JwToken;
use App\Services\Wydruk\GeneratorDom;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;

class Calendar extends Controller
{
    protected $application_id;
    /**
     * @var int
     */
    protected $iloscZmian;
    private $application_redirect_url;
    public function __construct() {
        session_start();
        $this->application_redirect_url = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/cart';
        $this->application_id = $_ENV['CALENDAR_APP_ID'] ?? '';
        //TEST? test test test test test test test test test jeszcze jeden leci
        /**
         * Posprzątaj
         * Refactor
         * Polskie nazwy miesięcy oraz dni
         * Laduj szablon w zależności od ilości zmian
         * Szablon w zależności od nazwy maila - parametr email w rozkodowanym tokenie
         * Upload nowego szablonu
         * Pobranie już istniejącego szablonu
         */
    }

    public function login() {
        if (empty($_SESSION['token'])) {
            $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar').' '.urlencode('https://www.googleapis.com/auth/userinfo.profile') . ' email&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . $this->application_id . '&access_type=online';

            echo view('login', ['link' => $linkToSignIn, 'dateModified' => date("Y-m-d H:i:s", filemtime(__FILE__))]);
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

        $monthYearFilter = $this->getStartDateTimeToGenerate();
        setcookie('month_year', $monthYearFilter, time()+3600, '/');


        $userData = JwToken::getData($_SESSION['token']['id_token']);


//        $data = $this->prepareDataTab($events);

//        $this->generateOdt($data);

//        $calendars = $service->calendarList->listCalendarList();

        echo view('welcome', [
            'monthYearNow' => $this->getStartDateTimeToGenerate(),
            'userName' => $userData['name'],
            'dateModified' => date("Y-m-d H:i:s", filemtime(__FILE__)),
        ]);
    }
/*
    public function getEvents() {
        $googleClient = new CalendarClient();
        $client = $googleClient->get();

        $monthYearFilter = $_COOKIE['month_year'];

        list($dateStart, $dateEnd) = $this->getDateToFilter($monthYearFilter);

        $service = new \Google_Service_Calendar($client);
        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 100,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $dateStart,
            'timeMax' => $dateEnd,
        );
        //timeMax date('c') tu będzie zabawa, bo bęzdie trzeba obliczyć początek tygodnia, w którym jest pierwszy dzień miesiąca i koniec miesiąca
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        file_put_contents('events_data.txt', var_export($events, true));

        return $events;
    }
*/
    public function generateAndDownload() {
        $calendarEventObj = new GoogleCalendarData();
        $data = $calendarEventObj->getEvents();

//        $data = $this->prepareDataTab($events);

        $plik = $this->generateOdt($data);

        if (!file_exists($plik)) {
            throw new \Exception('Plik '.$plik.' nie istnieje');
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($plik));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($plik));
        if (ob_get_length()) ob_clean();
        flush();
        readfile($plik);
        exit;
    }
/*
    public function prepareDataTab($eventsObj) {
        $events = [];
        $shifts = [];
        /** @var \Google_Service_Calendar_Event $event
        foreach ($eventsObj as $event) {
            $start = $event->getStart()->getDateTime();
            $end = $event->getEnd()->getDateTime();
            $title = $event->summary;

            $timezone = [
                'timeZone' => $event->getEnd()->timeZone,
                'getTimeZone' => $event->getEnd()->getTimeZone(),
            ];

            file_put_contents('event_timezone.txt', var_export($timezone, true));

            $events[] = [
                'title' => $title,
                'start' => $start,
                'end' => $end,
            ];

            $startTime = strtotime($start);
            $shift = date('H-i', $startTime);
            $shifts[$shift] = $shift;

            $endTime = strtotime($end);
            $shift = date('H-i', $endTime);
            $shifts[$shift] = $shift;
        }

        $ilZmian = count($shifts) - 1;
        $this->iloscZmian = $ilZmian;

        $zmianyKeys = array_keys($shifts);
        $rowHeader = [];
        for ($i = 1; $i <= $ilZmian; $i++) {
            list($godzina, $minuta) = explode('-', $shifts[$zmianyKeys[$i - 1]]);
            $rowHeader['zmiana'.$i.'_g_s'] = $godzina + 1;
            $rowHeader['zmiana'.$i.'_m_s'] = $minuta;

            list($godzina, $minuta) = explode('-', $shifts[$zmianyKeys[$i]]);
            $rowHeader['zmiana'.$i.'_g_k'] = $godzina + 1;
            $rowHeader['zmiana'.$i.'_m_k'] = $minuta;
        }
        $firstEvent = $events[0];
        $startTime = strtotime($firstEvent['start']);
        $rowHeader['miesiac'] = strtolower($this->getMonthNameInPolishFirstCase(date("n", $startTime)));
        $rowHeader['rok'] = date("Y", $startTime);
        $rowHeaderKey = array_keys($rowHeader);
        $emptyRowHeader = array_fill_keys ($rowHeaderKey, '');

        $data = [];
        foreach ($events as $event) {

            $startTime = strtotime($event['start']);
            $shift = date('H-i', $startTime);
            $numerZmiany = array_search($shift, $zmianyKeys);
            $numerZmiany++;

            $shiftDate = date("Y-m-d", $startTime);

            if (empty($data[$shiftDate])) {
                $data[$shiftDate]['zmiana_data'] = $this->getDayNameInPoland(date("N", $startTime)).' '.date("d", $startTime)."\r\n".$this->getMonthNameInPolish(date("n", $startTime)).' '.date("Y", $startTime);
            }

            $data[$shiftDate]['zmiana'.$numerZmiany.'_osoby'] = $event['title'];

        }

        $data = array_values($data);

        foreach ($data as $key => $values) {
            if ($key == 0) {
                $data[$key] = array_merge($values, $rowHeader);
            } else {
                $data[$key] = array_merge($values, $emptyRowHeader);
            }
        }

        return $data;
    }
*/
/*
    protected function getDayNameInPoland($dayNumber) {
        $numberToDay = [
            1 => 'Poniedziałek',
            2 => 'Wtorek',
            3 => 'Środa',
            4 => 'Czwartek',
            5 => 'Piątek',
            6 => 'Sobota',
            7 => 'Niedziela',
        ];

        return $numberToDay[$dayNumber];
    }

    protected function getMonthNameInPolish($monthNumber) {
        $numberToMonth = [
            1 => 'Stycznia',
            2 => 'Lutego',
            3 => 'Marca',
            4 => 'Kwietnia',
            5 => 'Maja',
            6 => 'Czerwca',
            7 => 'Lipca',
            8 => 'Sierpnia',
            9 => 'Września',
            10 => 'Października',
            11 => 'Listopada',
            12 => 'Grudnia',
        ];

        return $numberToMonth[$monthNumber];

    }

    protected function getMonthNameInPolishFirstCase($monthNumber) {
        $numberToMonth = [
            1 => 'Styczeń',
            2 => 'Luty',
            3 => 'Marzec',
            4 => 'Kwiecień',
            5 => 'Maj',
            6 => 'Czerwiec',
            7 => 'Lipiec',
            8 => 'Sierpień',
            9 => 'Wrzesień',
            10 => 'Październik',
            11 => 'Listopad',
            12 => 'Grudzień',
        ];

        return $numberToMonth[$monthNumber];

    }
*/

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

    public function generateOdt($data) {


        $szablonDef = 'Szablony/szablon.skoczow.zawisle.odt';
        $szablonDed = 'Szablony/szablon.skoczow.zawisle_'.$this->iloscZmian.'.odt';
        if (file_exists($szablonDed)) {
            $szablonFileName = $szablonDed;
        } else {
            $szablonFileName = $szablonDef;
        }

        $filename = md5(time());
        $wydruk = new GeneratorDom();
        $wydruk->ustawNazweWyjsciowegoPliku($filename);
        $wydruk->generuj($data, $szablonFileName);

        return 'Upload/'.$filename.'.odt';
    }

    public function testgen() {
        $dane = $this->mockData();
        $this->generateOdt($dane);
    }

    public function mockData() {
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

//                'zmiana7_g_s' => '16',
//                'zmiana7_m_s' => '00',
//                'zmiana7_g_k' => '17',
//                'zmiana7_m_k' => '00',
//
//                'zmiana8_g_s' => '17',
//                'zmiana8_m_s' => '00',
//                'zmiana8_g_k' => '18',
//                'zmiana8_m_k' => '00',

                'zmiana_data' => 'sobota 01 września 2019',

                'zmiana1_osoby' => 'Mateusz',
                'zmiana2_osoby' => 'Brzozowski',
                'zmiana3_osoby' => 'Inny',
                'zmiana4_osoby' => 'Gostek',
                'zmiana5_osoby' => 'Taki',
                'zmiana6_osoby' => 'Gosc',
//                'zmiana7_osoby' => 'Jestem',
//                'zmiana8_osoby' => 'Tutaj',
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

//                'zmiana7_g_s' => '',
//                'zmiana7_m_s' => '',
//                'zmiana7_g_k' => '',
//                'zmiana7_m_k' => '',
//
//                'zmiana8_g_s' => '',
//                'zmiana8_m_s' => '',
//                'zmiana8_g_k' => '',
//                'zmiana8_m_k' => '',

                'zmiana_data' => 'niedziela 02 września 2019',

                'zmiana1_osoby' => 'Teraz',
                'zmiana2_osoby' => 'Jest',
                'zmiana3_osoby' => 'Kolejny',
                'zmiana4_osoby' => 'Wiersz',
                'zmiana5_osoby' => 'Z',
                'zmiana6_osoby' => 'Danymi',
//                'zmiana7_osoby' => 'Do',
//                'zmiana8_osoby' => 'Druku',
            ],
        ];

        return $dane;
    }
/*
    public function getDateToFilter($monthYear) {
        list($month, $year) = explode('-', $monthYear);
        $nextMont = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        $nextMont = str_pad($nextMont, 2, "0", STR_PAD_LEFT);

        $start = "01-".$month."-".$year;
        $timeStart = strtotime($start);
        $isoStart = date("c", $timeStart);

        $end = "01-".$nextMont."-".$nextYear;
        $timeEnd = strtotime($end);
        $isoEnd = date("c", $timeEnd);

        return [$isoStart, $isoEnd];
    }
*/

    public function test() {

        $dane = [
            ['zmienna' => 'sdfds', 'pole1' => 'taki tekst', 'pole2' => 'inny gosciu'],
            ['zmienna' => '', 'pole1' => 'koelny', 'pole2' => 'wiersz'],
        ];

//        $wydruk = new GeneratorDom();
//        $wydruk->ustawNazweWyjsciowegoPliku('szablon_out.odt');
//        return $wydruk->generuj($dane, 'szablon.odt');

        echo view('login', ['link' => 'http://www.google.pl', 'dateModified' => date("Y-m-d H:i:s", filemtime(__FILE__))]);
    }

    protected function showView($data) {
        $out = <<<OUT

<h1>Welcom on Cart Calendar V18-2032</h1>
<h2>Please <a href="{$data['sign_in']}">SIGN IN</a></h2>

OUT;

        echo $out;
    }
}
