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
        if (empty($_SESSION['code'])) {
            $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . self::APPLICATION_ID . '&access_type=online';

            echo view('login', ['link' => $linkToSignIn]);
        } else {
            $this->index();
        }
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

        if(!empty($_GET['code'])) {
            $_SESSION['code'] = $_GET['code'];
        }

        $googleClient = new CalendarClient();
        $client = $googleClient->get();

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

        $calendars = $service->calendarList->listCalendarList();
        echo '<pre>';
//            var_export($calendars);
        var_export($events);
        echo '</pre>';

        $linkToSignIn  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->application_redirect_url) . '&response_type=code&client_id=' . self::APPLICATION_ID . '&access_type=online';

        $data = [
            'sign_in' => $linkToSignIn,
        ];

        $this->showView($data);
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
