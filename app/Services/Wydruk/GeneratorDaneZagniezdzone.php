<?php
namespace App\Services\Wydruk;

class GeneratorDaneZagniezdzone {
	private $poziom;
	private $polaTab;
	private $pozStart;

	public function __construct() {
		$this->poziom = 0;
		$this->polaTab[0] = array();
		$this->pozStart[0] = 0;
	}

	public function poziomPlus() {
		$this->poziom++;
	}

	public function poziomMinus() {
		$this->polaTab[$this->poziom] = array();
		$this->poziom--;
	}

	public function dajIndeksStartowy() {
		return $this->pozStart[$this->poziom - 1];
	}

	public function ustawWarunkiDlaKolejnegoPoziomu($wierszDanych, $nrWierszaDanych) {
		$this->pozStart[$this->poziom] = $nrWierszaDanych + 1;
		if (empty($this->polaTab[$this->poziom])) {
			foreach ($wierszDanych as $pole => $wartosc) {
				if (!empty($wartosc)) {
					$this->polaTab[$this->poziom][] = $pole;
				}
			}
		}
	}

	public function czyKoniecAktualnegoZagniezdzenia($wierszDanych) {
		if (!empty($this->polaTab[$this->poziom-1])) {
			$wartosciPol = '';
			foreach ($this->polaTab[$this->poziom-1] as $pole) {
				$wartosciPol .= $wierszDanych[$pole];
			}

			return !empty($wartosciPol);
		} else {
			return false;
		}
	}
}
