<?php
namespace App\Services\Wydruk;
/**
 * Taki mini manual:
 * {tu moze byc tekst, lub pole;rif:warunek}
 * rif - tu moze byc dowolne zapytanie
 * @author neertoon
 *
 */
class Warunek {
    const WARUNEK_CZY_WIERSZ = 'rif';

    public $nazwa;
    public $warunek;

    public function __construct($pole) {
        $this->nazwa = '';
        $this->warunek = '';

        $pole = html_entity_decode($pole, ENT_QUOTES|ENT_XML1);
        $miejsceSrednika = strpos($pole, ';');
        $miejsceDwukropka = strpos($pole, ':');
        if ($miejsceSrednika === false || $miejsceDwukropka === false) {
            return $this->warunek;
        }

        $this->nazwa = substr($pole, $miejsceSrednika+1, $miejsceDwukropka - $miejsceSrednika - 1);
		$this->warunek = substr($pole, $miejsceDwukropka+1);
    }

    public function czySpelniony($wierszDanych, $poprzedniWierszDanych = array()) {
        $kod = '';
        $warunek = $this->warunek;

        foreach ($poprzedniWierszDanych as $klucz => $dane) {
        	$kod .= '$old_'.$klucz.' = "'.$dane.'"; '."\r\n";
        	$warunek = preg_replace('/(old)\.('.$klucz.')([^a-zA-Z0-9_]{1})/', '$$1_$2$3', $warunek);
        }

        foreach ($wierszDanych as $klucz => $dane) {
            $kod .= '$'.$klucz.' = "'.$dane.'"; '."\r\n";
            $warunek = preg_replace('/([^\.]*)('.$klucz.')([^a-zA-Z0-9_]*)/', '$1$$2$3', $warunek);
        }

        $kod .= 'return ('.$warunek.');';

        return eval($kod);
    }

    public function oznacz($element) {
    	//$element->setAttribute('warunek', $this->warunek);
    }
}
