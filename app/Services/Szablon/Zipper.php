<?php


namespace App\Services\Szablon;


class Zipper
{

    private $file;
    private $outputFileName;

    public function __construct() {
        $this->outputFileName = md5('szablonwydruku'.time());
    }

    public function ustawNazweWyjsciowegoPliku($nazwa) {
        $this->outputFileName = $nazwa;
    }

    private function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    $zipFile->addEmptyDir($localPath);
                    $this->folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    public function zwrocContent($address) {
        // OTWIERANIE ODT
        $content = '';
        $this->file = $address;
        $archive = new \ZipArchive();
        $res = $archive->open($address);
        if ($res === TRUE) {
            // PRZECHWYTYWANIE PLIKU CONTENT
            $fp = $archive->getStream('content.xml');
            if(!$fp) exit("Nie otworzylo pliku content. Jakis dziwny plik ODT podales...\n");

            while (!feof($fp)) {
                $content .= fread($fp, 2);
            }

            fclose($fp);
            file_put_contents('t',$content);
            return $content;
        } else {
            echo 'Cos zes podal zlego w adresie. Adres to: <br />' . $address;
        }
    }

    public function stworzOdt($content, $sciezkaFolderow) {
        $archive = new \ZipArchive();
        // NAJPIERW POBIERA ZAWARTOSC POPRZEDNIEGO
        $res = $archive->open($this->file);
        if ($res === TRUE) {
            $archive->extractTo('Temp/extracted/');
            $archive->close();
            // USUWAMY POPRZEDNI PLIK CONTENT.XML
            unlink('Temp/extracted/content.xml');
            // NO I TWORZYMY NOWY PLIK ODT!! ;-)
            $nowy = new \ZipArchive();
            // NOWY ADRES
            $nazwaPliku = $this->outputFileName.'.odt';
            $filename = $sciezkaFolderow."/".$nazwaPliku;
            if ($nowy->open($filename, \ZipArchive::CREATE)!==TRUE) {
                exit("Nie moge utworzyc pliku $filename!");
            }
            // DODAJEMY ZAWARTOSC WSZYSTKICH PLIKOW Z FOLDERU EXTRACTED OPROCZ CONTENT.XML
            $this->folderToZip("Temp/extracted", $nowy, strlen("Temp/extracted/"));
            // DODAJEMY PLIK CONTENT.XML
            $nowy->addFromString("content.xml", $content);
            return $filename;
        } else {
            echo 'Yyy... Wiesz co... Nie mozna stworzycOdt bez wczesniejszego zwrocContenta...';
        }
    }



}
