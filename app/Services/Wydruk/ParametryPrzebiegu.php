<?php
namespace App\Services\Wydruk;
class ParametryPrzebiegu {
	const ELEMENT_ROWS = 'table:table-row';
	const ELEMENT_LIST_ITEM = 'text:list-item';

	private $elementy;

	private $stanElementu;
	private $poziom;
	public $nrWierszaDanych;

	const STAN_ELEM_BEZ_POL_DANYCH = -1;
	const STAN_ELEM_PUSTY = 0;
	const STAN_ELEM_NIEPUSTY = 1; //OR
	const STAN_ELEM_POMIN = 2;

	//TODO : mozna tez prowadzic tabele najwazniejszych elementow wg ich wystepowania i kasowac w miare cofania sie poizomiami
	//wtedy np komorka znadzie sie na poziomie 4 to sprawdza, to sprawdza na ktorym poziomie byl wiersz
	// i na takim poizomie generuje warunek dla wiersza
	//dzieki temu jak beda tabele zagniezdzone to jesli komorka bedzie na poziomie 11 a wiersz na 9
	// to warunek zalozy na poziomie 9 i usunie ten wiersz a nie ten pierwszy
	// natomiast przy wychodzeniu z wiersza bedzie sprawdzenie na jego poziomie warunku na poprawnosc wiersza

	//Nie do końca. Wiersz jest na innym poziomie niż komórka!
	public function __construct() {
		$this->stanElementu = array(self::ELEMENT_ROWS=>array(), self::ELEMENT_LIST_ITEM=>array());
		$this->poziom = 1;

		$this->nrWierszaDanych = 0;

		$this->elementy = array (
			self::ELEMENT_LIST_ITEM,
			self::ELEMENT_ROWS,
		);
	}

	//TODO : ta klasa będzie spełniała rolę mini przewodnika po poziomach oraz po informacjach z nim związanych
	//np czy można dodać wiersz na tym poziomie itd. A wiadomo, że nie może być dwóch wierszy na 1 poziomie

	public function poziomPlus() {
		$this->poziom++;
	}

	public function poziomMinus() {
		foreach ($this->elementy as $element) {
			$this->poziomMinusElementu($element);
		}

		$this->poziom--;
	}

	private function poziomMinusElementu($element) {
		$poziomyWarunkuRows = array_keys($this->stanElementu[$element]);
		if (empty($poziomWarunkuRows)) {
		    return;
        }

		asort($poziomyWarunkuRows);
		$poziomWarunkuRows = $poziomyWarunkuRows[count($poziomyWarunkuRows)-1];
		if ($poziomWarunkuRows == $this->poziom) {
			unset($this->stanElementu[$element][$this->poziom]);
		}
	}

	public function poziom() {
		return $this->poziom;
	}

	public function przygotujDaneDlaElementu($nazwaElementu) {
		$this->stanElementu[$nazwaElementu][$this->poziom] = self::STAN_ELEM_BEZ_POL_DANYCH;
	}

	public function ustawCzyPominacWiersz($czyPominac) {
		if (empty($this->stanElementu[self::ELEMENT_ROWS])) {
			return false;
		}

		$klucze = array_keys($this->stanElementu[self::ELEMENT_ROWS]);
		asort($klucze);
		$ostatniIndeks = $klucze[count($klucze)-1];


		$this->stanElementu[self::ELEMENT_ROWS][$ostatniIndeks] = self::STAN_ELEM_POMIN;
	}


	public function ustawCzyPolePuste($czyPolePuste) {
		foreach ($this->elementy as $element) {
			$this->ustawCzyPolePusteDlaElementu($czyPolePuste, $element);
		}
	}

	private function ustawCzyPolePusteDlaElementu($czyPolePuste, $nazwaElementu) {
		if (empty($this->stanElementu[$nazwaElementu])) {
			return false;
		}

		$klucze = array_keys($this->stanElementu[$nazwaElementu]);
		asort($klucze);
		$ostatniIndeks = $klucze[count($klucze)-1];

		$stanPola = (int)!$czyPolePuste;
		$stanElementu = $this->stanElementu[$nazwaElementu][$ostatniIndeks];
		if ($stanElementu == self::STAN_ELEM_POMIN) {
			return;
		} else if($stanElementu == self::STAN_ELEM_BEZ_POL_DANYCH) {
			$stanElementu = self::STAN_ELEM_PUSTY;
		}
		$this->stanElementu[$nazwaElementu][$ostatniIndeks] = $stanElementu || $stanPola;
	}

	public function pominElement($nazwaElementu) {
		if (!in_array($nazwaElementu, $this->elementy)) {
			return false;
		}

		//TODO : funkcja będzie obsługiwała więcej elementów czyli tif, rif, pif itd
		switch ($nazwaElementu) {
			case self::ELEMENT_ROWS :
			case self::ELEMENT_LIST_ITEM :
				$stanElementu = $this->stanElementu[$nazwaElementu][$this->poziom];
				break;
			default:
				throw new Exception('Coś tu poszło nie tak! Nie podano własciwego elementu');
				break;
		}

		return $stanElementu == self::STAN_ELEM_PUSTY
				|| $stanElementu === self::STAN_ELEM_POMIN
				|| ($stanElementu === self::STAN_ELEM_BEZ_POL_DANYCH && $this->nrWierszaDanych != 0);

	}
}
