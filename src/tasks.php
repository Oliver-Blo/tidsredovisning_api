<?php

declare (strict_types=1);
require_once __DIR__ . '/activities.php';

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter 
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum(new DateTimeImmutable($route->getParams()[0]), new DateTimeImmutable($route->getParams()[1]));
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return sparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift((int) $route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param int $sida
 * @return Response
 */
function hamtaSida(int $sida): Response {
    $posterPerSida=3;
    //Kolla att id är ok
    $kollatsidnr=filter_var($sida, FILTER_VALIDATE_INT);
    if(!$kollatsidnr || $kollatsidnr<1){
        $out=new stdClass();
        $out->error=["Felaktigt sidnummer ($sida) angivet", "Läsning misslyckades"];
        return new Response($out, 400);
    }

    //Koppla mot databasen
    $db=connectDb();

    ///Hämta antal poster
    $result=$db->query("SELECT COUNT(*) FROM uppgifter");
    if($row=$result->fetch()) {
        $antalPoster=$row[0];
    }
    $antalSidor=ceil($antalPoster/$posterPerSida);

    //Hämta aktuella poster
    $first=($kollatsidnr-1)*$posterPerSida;
    $result=$db->query("SELECT t.ID, KategoriID, Datum, Tid, Beskrivning, kategori "
        . " FROM uppgifter t "
        . " INNER JOIN kategori a ON KategoriID=a.id "
        . " ORDER BY Datum asc "
        . " LIMIT $first, $posterPerSida");

    //Loopa resultattest och skapa utdata
    $record=[];
    while($row=$result->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["KategoriID"];
        $rec->activity=$row["kategori"];
        $rec->date=$row["Datum"];
        $rec->time=substr($row["Tid"], 0, 5);
        $rec->description=$row["Beskrivning"];
        $records[]=$rec;
    }

    //Returnera utdata
    $out=new stdClass();
    $out->pages=$antalSidor;
    $out->tasks=$record;

    return new Response($out);

    return new Response("Hämta alla tasks sida $sida", 200);
}

/**
 * Hämtar alla poster mellan angivna datum
 * @param DateTimeInterface $from
 * @param DateTimeInterface $tom
 * @return Response
 */
function hamtaDatum(DateTimeInterface $from, DateTimeInterface $tom): Response {
    //Kolla indata
    if($from->format('Y-m-d')>$tom->format('Y-m-d')){
        $out=new stdClass();
        $out->error=["Felaktig indata", "Från datum ska vara mindre än till-datum"];
        return new Response($out, 400);
    }

    //Koppla databas
    $db=connectDb();

    //Hämta poster
    $stmt=$db->prepare("SELECT t.ID, KategoriID, Datum, Tid, Beskrivning, kategori "
    . " FROM uppgifter t "
    . " INNER JOIN kategori a ON KategoriID=a.id "
    . " WHERE Datum between :from AND :to"
    . " ORDER BY Datum asc ");
    $stmt->execute(["from"=>$from->format('Y-m-d'), "to"=>$tom->format('Y-m-d')]);

    //Loopa resultatsettet och skapa utdata
    $records=[];
    while($row=$stmt->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["KategoriID"];
        $rec->activity=$row["kategori"];
        $rec->date=$row["Datum"];
        $rec->time=substr($row["Tid"], 0, 5);
        $rec->description=$row["Beskrivning"];
        $records[]=$rec;
    }

    //Returnera utdata
    $out=new stdClass();
    $out->tasks=$records;

    return new Response($out);
}

/**
 * Hämtar en enskild uppgiftspost
 * @param int $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(int $id): Response {
    //Kolla indata
    $kollatID=filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID < 1){
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out, 400);
    }

    //Koppla mot databas
    $db=connectDb();

    //Förbered och exekvera SQL
    $stmt=$db->prepare("SELECT t.ID, KategoriID, Datum, Tid, Beskrivning, kategori "
    . " FROM uppgifter t "
    . " INNER JOIN kategori a ON KategoriID=a.id "
    . " WHERE t.id=:id");
    $stmt->execute(["id"=>$kollatID]);

    //Returnera svaret
    if($row=$stmt->fetch()) {
        $out =new stdClass();
        $out ->id=$row["ID"];
        $out->activityId=$row["KategoriID"];
        $out-> date=$row["Datum"];
        $out-> time=$row["Tid"];
        $out-> description=$row["Beskrivning"];
        $out-> activity=$row["kategori"];

        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["Fel vid hämtning", "Inga poster returnerades"];
        return new Response($out, 400);
    }

}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postData): Response {
    //Kolla indata
    
    $check=kontrolleraIndata($postData);
    if($check!=="") {
        $out=new stdClass();
        $out->error=["Felaktig indata", $check];
        return new Response($out, 400);
    }

    //Koppla mot databas
    $db=connectDb();

    //Förbered och exekvera SQL
    $stmt=$db->prepare("INSERT INTO uppgifter "
        . "(Datum, Tid, KategoriID, Beskrivning)"
        . " VALUES (:date, :time, :activityId, :description)");
    
    $stmt->execute(["date"=>$postData["date"],
    "time"=>$postData["time"],
    "activityId"=>$postData["activityId"],
    "description"=>$postData["description"] ?? ""]);

    //Kontrollera svar
    $antalPoster=$stmt->rowCount();
    if($antalPoster>0) {
        $out=new stdClass();
        $out->id=$db->lastinsertId();
        $out->message=["Spara ny uppgift lyckades"];
        return new Response($out);
    }else {
        $out=new stdClass();
        $out->error=["Spara ny uppgift misslyckades"];
        return new Response($out, 400);
    }
    //Skapa utdata

    return new Response("Sparar ny task", 200);
}

/**
 * Uppdaterar en angiven uppgiftspost med ny information 
 * @param int $id id för posten som ska uppdateras
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(int $id, array $postData): Response {
    //Kolla indata
    $check=kontrolleraIndata($postData);
    if($check!=="") {
        $out=new stdClass();
        $out->error=["Felaktig indata", $check];
        return new Response($out, 400);
    }
    $kollatID=filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID < 1){
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out,400);
    }

    //Koppla databas
    $db=connectDb();

    //Förbered exekvera SQL
    $stmt=$db->prepare("UPDATE uppgifter "
        . "SET Tid=:time, "
        . "Datum=:date, "
        . "Beskrivning=:description, "
        . "KategoriID=:activityId "
        . "WHERE ID=:id");
    $stmt->execute(["time"=>$postData["time"],
        "date"=>$postData["date"],
        "description"=>$postData["description"],
        "activityId"=>$postData["activityId"],
        "id"=>$kollatID]);

    //Kontrollera svar och skicka svar
    $antalPoster=$stmt->rowCount();
    if($antalPoster===0) {
        $out=new stdClass();
        $out->result=false;
        $out->message=["Uppdatera misslyckades", "Inga poster uppdaterades"];
    }else {
        $out=new stdClass();
        $out->result=true;
        $out->message=["Uppdatera lyckades", "$antalPoster poster uppdaterades"];
    }
    return new Response($out);
}

/**
 * Raderar en uppgiftspost
 * @param int $id Id för posten som ska raderas
 * @return Response
 */
function raderaUppgift(int $id): Response {
    //Kolla indata
    $kollatID=filter_var($id, FILTER_VALIDATE_INT);
    if(!$kollatID || $kollatID < 1){
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out,400);
    }

    //Koppla mot databas
    $db=connectDb();

    //Förbered och exekvera SQL
    $stmt=$db->prepare("DELETE FROM uppgifter WHERE id=:id");
    $stmt->execute(["id"=>$kollatID]);

    //Skicka svar
    $antalPoster=$stmt->rowCount();
    if($antalPoster===0) {
        $out=new stdClass();
        $out->result=false;
        $out->message=["Radera post misslyckades", "Inga poster raderades"];
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->result=true;
        $out->message=["Radera post lyckades", "$antalPoster poster raderades"];
        return new Response($out);
    }

}



function kontrolleraIndata(array $postData):string {
    try {
        //Kontrollera giltigt datum
        if(!isset($postData["date"])) {
            return "Datum saknas (date)";
        }
        $datum=DateTimeImmutable::createFromFormat("Y-m-d", $postData["date"]);
        if(!$datum || $datum->format('Y-m-d')>date("Y-m-d")) {
            return "Ogiltigt datum (date)";
        }

        //Kontrollera giltig tid
        if(!isset($postData["time"])) {
            return "Tid saknas (time)";
        }
        $tid=DateTimeImmutable::createFromFormat("H:i", $postData["time"]);
        if(!$tid || $tid->format("H:i")>"08:00") {
            return "Ogitlig tid (time)";
        }
        //Kontrollera aktivitetsid
        $aktivitetsId=filter_var($postData["activityId"], FILTER_VALIDATE_INT);
        if(!$aktivitetsId || $aktivitetsId<1) {
            return "Ogiltigt aktivitetsid (activityId)";
        }
        $svar=hamtaEnskildAktivitet($aktivitetsId);
        if($svar->getStatus()!==200) {
            return "Angivet aktivitetsid saknas";
        }
        return "";
    }catch (Exception $exc) {
        return $exc->getMessage();
    }
}