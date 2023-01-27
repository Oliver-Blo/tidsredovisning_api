<?php

declare (strict_types=1);
require_once '../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testActivityFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen test_$funktion finns inte.</p>";
    }
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur="<h2>test_HamtaAllaAktiviteter</h2>";
try{
    $svar=hamtaAllaAktivitet();

    //Kontrollera statuskoden
    if(!$svar->getStatus()===200){
        $retur .="<p class='error'>Felaktig statuskod förväntade 200 fick {$svar->getStatus()}";
    }else{
        $retur .="<p class='ok'>Korrekt statuskod 200</p>";
    }
    
    //Kontrollerar egenskaperna
    foreach($svar->getContent()->activities as $kategori){
        if(!isset($kategori->id)) {
            $retur .="<p class='error'>Egenskapen id saknas</p>";
            break;
        }
        if(!isset($kategori->activity)) {
            $retur .="<p class='error'>Egenskapen activity saknas</p>";
            break;
        }
    }
}
catch(Exception $ex){
    $retur .="<p class='error'>Något gick fel, meddelandet säger:<br>{$ex->getMessage()}</p>";
}
    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try{
        //Testa negativt tal
        $svar=hamtaEnskildAktivitet(-1);
        if($svar->getStatus()===400){
            $retur .= "<p class='ok'>Hämta eskild med negativt tal ger förväntat svar 400</p>";
        }else{
            $retur .= "<p class='error'>Hämta eskild med negativt tal ger {$svar->getStatus()} " . "inte förväntat svar 400</p>";
        }
        //Testa för stort tal
        $svar=hamtaEnskildAktivitet(100);
        if($svar->getStatus()===400){
            $retur .= "<p class='ok'>Hämta eskild med stort tal ger förväntat svar 400</p>";
        }else{
            $retur .= "<p class='error'>Hämta eskild med stort (100) tal ger {$svar->getStatus()} " . "inte förväntat svar 400</p>";
        }
        //Testa bokstäver
        $svar=hamtaEnskildAktivitet((int)"sju");
        if($svar->getStatus()===400){
            $retur .= "<p class='ok'>Hämta eskild med bokstäver ger förväntat svar 400</p>";
        }else{
            $retur .= "<p class='error'>Hämta eskild med bokstäver (sju) ger {$svar->getStatus()} " . "inte förväntat svar 400</p>";
        }
        //Testa giltigt tal
        $svar=hamtaEnskildAktivitet(3);
        if($svar->getStatus()===200){
            $retur .= "<p class='ok'>Hämta eskild med 3 ger förväntat svar 400</p>";
        }else{
            $retur .= "<p class='error'>Hämta eskild med 3 ger {$svar->getStatus()} " . "inte förväntat svar 200</p>";
        }
    }catch(Exception $ex){
        $retur .="<p class='error'>Något gick fel, meddelandet säger:<br>{$ex->getMessage()}</p>";
    
    }
    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";
    $retur .= "<p class='ok'>Testar spara ny aktivitet</p>";
    $retur .= "<p class='ok'>Ett test till för spara som givk bra</p>";
    return $retur;
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";
    $retur .= "<p class='ok'>Testar uppdatera aktivitet</p>";
    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    try{
    //Testa felaktigt id(-1)
        $svar=raderaAktivitet(-1);
        if($svar->getStatus() === 400) {
            $retur .= "<p class='ok'>Radera post med negativt tal ger förväntat svar 400</p>";
        }else {
            $retur .= "<p class='error'>Radera post med negativt tal ger {$svar->getStatus()} "
                . "inte förväntat svar 400</p>";
        }
    //Testa felaktigt id(sju)
    $svar=raderaAktivitet((int)"sju");
    if($svar->getStatus() === 400) {
        $retur .= "<p class='ok'>Radera post med felaktigt id ('sju') ger förväntat svar 400</p>";
    }else {
        $retur .= "<p class='error'>Radera post med felaktigt id ('sju') ger {$svar->getStatus()} "
            . "inte förväntat svar 400</p>";
    }
    //Testa id som inte finns(100)
    $svar=raderaAktivitet(100);
    if($svar->getStatus() === 200 && $svar->getContent()->result===false) {
        $retur .= "<p class='ok'>Radera post med id som inte finns ('100') ger förväntat svar 200</p>";
    }else {
        $retur .= "<p class='error'>Radera post id som inte finns ('100') ger {$svar->getStatus()} "
            . "inte förväntat svar 200</p>";
    }
    //Testa radera nyskapat id
    $db=connectDb();
    $db->beginTransaction();
    $nyPost=sparaNyAktivitet("Nizze");
    if($nyPost->getStatus() !== 200){
        throw new Exception("Skapa ny post misslyckades", 10001);
    }
    $nyttId=(int) $nyPost->getContent()->id;
    $svar=raderaAktivitet($nyttId);

    if($svar->getStatus() === 200 && $svar->getContent()->result===true) {
        $retur .= "<p class='ok'>Radera post med nyskapat id ger förväntat svar 200</p>";
    }else {
        $retur .= "<p class='error'>Radera post med nyskapat id ger {$svar->getStatus()} "
            . "inte förväntat svar 200</p>";
    }

    }catch(Exception $ex) {
        $db->rollBack();
        if($ex->getCode() === 10001) {
        $retur .= "<p class='error'>Spara ny post misslyckades, uppdatera går inte att testa</p>";
    }else {
        $retur .= "<p class='error'>Fel inträffade:<br>{$ex->getMessage()}</p>";
    }
}
    return $retur;

}