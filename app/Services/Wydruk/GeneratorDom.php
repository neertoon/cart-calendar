<?php
namespace App\Services\Wydruk;
use App\Services\Szablon\Zipper;
//TODO : jest coś takiego jak table:table-row-group. Może się zagniezdzac
//TODO : zrobić eliminatora elementów - osobna klasa
//TODO : przygotuj testyu jednostkowe - Stwórz osobny katalog w modułach! oraz kontroler w którym będzie można wybrać test
//TODO : w tym katalogu będą pliki ze szablonami oraz tabelami danych


class GeneratorDom
{
	const ELEMENT_ODT_TABELA = 'table:table';
	const ELEMENT_ODT_LISTA = 'text:list';

	const ELEMENT_ODT_WIERSZ_TABELI = 'table:table-row';
	const ELEMENT_ODT_EL_LISTY = 'text:list-item';

    private $szablon;
    private $wydruk;
    private $tabelaDanych;
	private $zipper;

    private $elementyWielowierszowe;
    private $pozycjeWielowierszowe;

    private $paramPrzebiegu;
    private $daneZagniezdzone;

	public function __construct() {
		$this->zipper = new Zipper();
		$this->paramPrzebiegu = new ParametryPrzebiegu();
		$this->daneZagniezdzone = new GeneratorDaneZagniezdzone();

        $this->elementyWielowierszowe = array(
        	self::ELEMENT_ODT_TABELA,
        	self::ELEMENT_ODT_LISTA,
        );

        $this->pozycjeWielowierszowe = array(
        	self::ELEMENT_ODT_WIERSZ_TABELI,
        	self::ELEMENT_ODT_EL_LISTY,
        );
	}

	public function ustawNazweWyjsciowegoPliku($nazwaPliku) {
		$this->zipper->ustawNazweWyjsciowegoPliku($nazwaPliku);
	}

	public function nieTraktujListJakoElementowWielowierszowych() {
		$klucz = array_search(self::ELEMENT_ODT_LISTA, $this->elementyWielowierszowe);
		if ($klucz !== false) {
			unset($this->elementyWielowierszowe[$klucz]);
		}
		$klucz = array_search(self::ELEMENT_ODT_EL_LISTY, $this->pozycjeWielowierszowe);
		if ($klucz !== false) {
			unset($this->pozycjeWielowierszowe[$klucz]);
		}
	}

    public function generuj($tabela, $sciezkaPlikuSzablonu)
    {
        $this->tabelaDanych = $tabela;
        try {
            if (!file_exists($sciezkaPlikuSzablonu)) {
                throw new \Exception('Nie ma takiego pliku '.$sciezkaPlikuSzablonu);
            }
            $this->szablon = $this->zipper->zwrocContent($sciezkaPlikuSzablonu);

            $this->wydruk = $this->analizujDOM();

            echo "PO GEN\n";

            echo $this->wydruk;

            return $this->zipper->stworzOdt($this->wydruk, 'Upload');
        } catch (\Exception $blad){
            echo $blad->getMessage();
        }
    }

    private function analizujDOM() {
    	$htmlParse = new \DOMDocument();
    	if (@!$htmlParse->loadXML($this->szablon)) {
    		throw new \Exception('Nie udalo sie dokonac analizy szablonu');
    	}

    	$zbiorTitle = $htmlParse->getElementsByTagName("body");
    	var_export($zbiorTitle);
    	$office = $zbiorTitle->item(0);
    	$this->analizujWezlyPotomne($office);

    	return $htmlParse->saveXML();
    }

    private function analizujWezlyPotomne(\DOMElement &$parentNode) {
    	$this->paramPrzebiegu->poziomPlus();

    	/* @var $node DOMNode */
    	$element = $parentNode->firstChild;

    	do {
    		if ($this->czyElementWielowierszowy($element)) {
    			$this->wypelnijElementWielowierszowy($element);
    			$this->paramPrzebiegu->nrWierszaDanych = 0;
    		} else if ($element->nodeType == XML_TEXT_NODE) {
    			$this->wypelnijElementTekstowy($element);
    		} else {
	    		if ($element->hasChildNodes()) {
	    			$this->analizujWezlyPotomne($element);
	    		}
    		}
    		//TODO : przeanalizuj, czy może wystąpić null, gdy nie zostanie spełmnoiny jakiś warunek?!
    		$element = $element->nextSibling;
    	} while(is_null($element) == false);

    	$this->paramPrzebiegu->poziomMinus();
    }

    private function wypelnijElementTekstowy(\DOMText $element) {
    	if (!preg_match_all('/({[^}]+})/', $element->nodeValue, $polaWeWierszu)) {
    		return;
    	}
    	//TODO : można by napisać klasę usuwator. Zamiast realizacji warunków w tej funkcji
    	//działo by się co innego. Spełniony warunek oznaczałby jakoś element do usunięcia atrybutem
    	// np <p usun="wiersz"
    	//Następnie cały kod XML byłby przekazywany do klasy usuwającej.
    	// badałaby ona atrybuty i usuwała elementy wg przepisu
    	$tabelaZastapien = array();

    	foreach ($polaWeWierszu[1] as $pole) {
    		$wierszDanych = $this->tabelaDanych[$this->paramPrzebiegu->nrWierszaDanych];
    		$poprzedniWierszDanych = $this->paramPrzebiegu->nrWierszaDanych == 0 ? array() : $this->tabelaDanych[$this->paramPrzebiegu->nrWierszaDanych - 1];

    		$poleDoWarunku = str_replace(array('{', '}'), '', $pole);
    		$warunek = new Warunek($poleDoWarunku);
    		//TODO ponizej zamiast sprawdzania typu warunku by bylo od razu czy spelniony, a w sekcji nie spelnienia
    		//byloby oznaczElementDoUsuniecia($element). Mogła by to być metoda eliminatora
    		//jeśli chodzi o puste, to można by dodać do elminatora metodę "ustawInfoCzyPuste"
    		if ($warunek->nazwa == Warunek::WARUNEK_CZY_WIERSZ) {
    			if ($warunek->czySpelniony($wierszDanych, $poprzedniWierszDanych)) {
    				$this->paramPrzebiegu->ustawCzyPolePuste(false);
    			} else {
    				//$warunek->oznaczDoUsuniecia($element); //TODO : to zamiast poniżeszego elementu
    				$this->paramPrzebiegu->ustawCzyPominacWiersz(true);
    				break;
    			}
    		}

    		$zwrotZKomorki = $this->dajWartoscPola($pole, $wierszDanych);

    		$tabelaZastapien[$pole] = html_entity_decode($zwrotZKomorki, ENT_QUOTES|ENT_XML1);
    		$this->paramPrzebiegu->ustawCzyPolePuste(empty($zwrotZKomorki));
    	}

    	foreach ($tabelaZastapien as $pole => $zastapienie) {
    		$element->nodeValue = str_replace($pole, $zastapienie, $element->nodeValue);
    	}
    }

    private function dajWartoscPola($pole, $wierszDanych) {
    	$poleSzukane = html_entity_decode($pole, ENT_QUOTES|ENT_XML1);
    	$poleSzukane = preg_replace('/({)([^;}]*).*/', '$2', $poleSzukane);

    	$kod = '';
    	$daneSort = array();
    	foreach ($wierszDanych as $klucz => $pole) {
    		$daneSort[] = array('klucz' => $klucz,  'pole' => $pole);
    	}
    	uasort($daneSort, function ($a, $b)
        {
            if (strlen($a['klucz']) >= strlen($b['klucz']))
                return -1;
            else
                return 1;
        });


    	$wierszDanych = array();
    	foreach ($daneSort as $pole) {
    		$wierszDanych[$pole['klucz']] = $pole['pole'];
    	}

    	foreach ($wierszDanych as $klucz => $dane) {
    		$kod .= '$'.$klucz.' = "'.$dane.'"; '."\r\n";
    		$poleSzukane = preg_replace('/(^|[^\$\.]+)('.$klucz.')([^a-zA-Z0-9_]*)/', '$1$$2$3', $poleSzukane);
    	}
    	$poleSzukane = empty($poleSzukane) ? '""' : $poleSzukane;

        if (!empty($poleSzukane) && !strstr($poleSzukane, '$')) {
            $kod .= '$'.$poleSzukane.' = ""; '."\r\n";
            $poleSzukane = '$'.$poleSzukane;
        }

    	$kod .= ' return ('.$poleSzukane.');';

    	return eval($kod);
    }

    private function wypelnijElementWielowierszowy(\DOMElement &$elWielowierszowy) {
    	$nowyElWielowierszowy = $elWielowierszowy->cloneNode(false);

    	$this->paramPrzebiegu->poziomPlus();

    	$this->daneZagniezdzone->poziomPlus();
    	$indexStart = $this->daneZagniezdzone->dajIndeksStartowy();

    	for ($klucz = $indexStart; $klucz<count($this->tabelaDanych); $klucz++) {
    		$wiersz = $this->tabelaDanych[$klucz];
    		$this->paramPrzebiegu->nrWierszaDanych = $klucz;

    		$this->daneZagniezdzone->ustawWarunkiDlaKolejnegoPoziomu($wiersz, $klucz);

    		/* @var $row DOMElement */
	    	$potomek = $elWielowierszowy->firstChild;
    		do {
    			$powielonyPotomek = $potomek->cloneNode(true);
    			if ($this->czyElementToPozycjaWielowierszowa($powielonyPotomek)){
    				$this->paramPrzebiegu->przygotujDaneDlaElementu($powielonyPotomek->nodeName);
    				$this->analizujWezlyPotomne($powielonyPotomek);
    			}

    			//TODO : wtedy tutaj byłoby zawsze append child. Elementy byłby zawsze podpinany
    			if (($klucz == 0 || $this->czyElementToPozycjaWielowierszowa($powielonyPotomek))
    					&& ! $this->paramPrzebiegu->pominElement($powielonyPotomek->nodeName)) {
    				$nowyElWielowierszowy->appendChild($powielonyPotomek);
    			}

    			$potomek = $potomek->nextSibling;
    		} while(! is_null($potomek));

    		if ($this->daneZagniezdzone->czyKoniecAktualnegoZagniezdzenia($wiersz)) {
				break;
    		}
    	}
    	$this->paramPrzebiegu->poziomMinus();
    	$this->paramPrzebiegu->nrWierszaDanych = 0;

    	$this->aktualizujElementWielowierszowy($nowyElWielowierszowy, $elWielowierszowy);

    	$this->daneZagniezdzone->poziomMinus();
    }

    private function aktualizujElementWielowierszowy(&$nowyElWielowierszowy, &$elWielowierszowy) {
    	if ($nowyElWielowierszowy->hasChildNodes()) {
    		$elWielowierszowy->parentNode->replaceChild($nowyElWielowierszowy, $elWielowierszowy);
    		$elWielowierszowy = $nowyElWielowierszowy;
    	} else {
    		$elPoprzedni = $elWielowierszowy->previousSibling;
    		$elWielowierszowy->parentNode->removeChild($elWielowierszowy);
    		$elWielowierszowy = $elPoprzedni;
    	}
    }

    private function czyElementWielowierszowy($element) {
    	return in_array($element->nodeName, $this->elementyWielowierszowe);
    }

    private function czyElementToPozycjaWielowierszowa($element) {
    	return in_array($element->nodeName, $this->pozycjeWielowierszowe);
    }
}
function sortujWgDlugosciKluczaMalejaco($a, $b)
{
	if (strlen($a['klucz']) >= strlen($b['klucz']))
		return -1;
	else
		return 1;
}
