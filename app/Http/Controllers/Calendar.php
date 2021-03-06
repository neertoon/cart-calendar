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

        $this->authByCode();

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

    public function generateAndDownload() {
        $calendarEventObj = new GoogleCalendarData();
        $data = $calendarEventObj->getEvents();
        $shiftsNumber = $calendarEventObj->getShiftsNumber();

        file_put_contents('calendar_data_plus.txt', var_export($data, true));

        $plik = $this->generateOdt($data, $shiftsNumber);

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

    public function generateOdt($data, $shiftsNumber) {

        file_put_contents('ilosc_zmian.txt', var_export($shiftsNumber, true));

        $szablonDef = 'Szablony/szablon.skoczow.zawisle.odt';
        $szablonDed = 'Szablony/szablon.skoczow.zawisle_'.$shiftsNumber.'.odt';
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

    private function authByCode() {
        $googleClient = new CalendarClient();
        if(!empty($_GET['code'])) {
            $client = $googleClient->get($_GET['code']);
        } else {
            $client = $googleClient->get();
        }
    }
}
