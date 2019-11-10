<?php


namespace App\Services;


class MockCalendarData implements CalendarDataIfc
{

    public function getEvents()
    {
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

    public function getShiftsNumber()
    {
        return 6;
    }
}
