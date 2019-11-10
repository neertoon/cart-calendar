<?php


namespace App\Services;


class GoogleCalendarData implements CalendarDataIfc
{

    /**
     * @var int
     */
    public $iloscZmian;

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

        return $this->prepareDataTab($events);
    }

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

    public function prepareDataTab($eventsObj) {
        $events = [];
        $shifts = [];
        /** @var \Google_Service_Calendar_Event $event */
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
}
