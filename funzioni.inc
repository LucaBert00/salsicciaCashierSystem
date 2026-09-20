<?php
//define("DEBUG", 1);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Token anti-CSRF in sessione: mutazioni solo via POST con hidden tok.
function csrf_token()
{
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    if (empty($_SESSION['tok']))
        $_SESSION['tok'] = bin2hex(random_bytes(32));
    return $_SESSION['tok'];
}

function csrf_ok()
{
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    return isset($_POST['tok'], $_SESSION['tok']) && hash_equals((string)$_SESSION['tok'], (string)$_POST['tok']);
}

function csrf_field()
{
    echo '<input type="hidden" name="tok" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}


/**
 * Esegue SQL grezzo senza alcun escaping/placeholder: NON sanifica, interpola
 * cio' che riceve. T09: usare solo per query statiche senza variabili;
 * ogni variabile va in db_select()/db_exec() con placeholder "?".
 */
function mysql_query_safe($mysqli, $query)
{
    if(defined('DEBUG') && DEBUG)
    {
        $risultato = mysqli_query($mysqli, $query)
        or die(print "<br><center class=errore>Errore di MySql con la query <br><b>" . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . "</b><br>" . htmlspecialchars(mysqli_error($mysqli), ENT_QUOTES, 'UTF-8') . "<br><br><INPUT type=\"BUTTON\" value='INDIETRO' onClick=\"javascript:history.back(1);\"></center>");
    }
    else
    {
        $risultato = mysqli_query($mysqli, $query);
    }

    return $risultato;
}

// T09: SELECT parametrizzata (pattern backoffice visualizza.php / fiscale.inc:221).
// Ritorna mysqli_result o false come mysql_query_safe; solo "?" mai interpolazioni.
function db_select($mysqli, $sql, $tipi = '', $params = array())
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false)
        return false;
    if ($tipi !== '')
        $stmt->bind_param($tipi, ...$params);
    if (!$stmt->execute())
    {
        $stmt->close();
        return false;
    }
    $res = $stmt->get_result();
    $stmt->close();
    return $res ? $res : false;
}

// T09: INSERT/UPDATE/DELETE parametrizzata. Ritorna bool.
function db_exec($mysqli, $sql, $tipi = '', $params = array())
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false)
        return false;
    if ($tipi !== '')
        $stmt->bind_param($tipi, ...$params);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function printA($str)
{
    // Contesto JS: json_encode neutralizza quote/backslash/</script> (default escapa "/).
    print "\n<script type=\"text/javascript\">alert(" . json_encode($str) . ");</script>\n";
}

function calcolaTotali($id_prodotto, $id_ordine, $mysqli, $in_txn = false)
{
    // cast difensivo, i chiamanti cassa passano gia (int) ma la firma resta weakly-typed per BC.
    $id_prodotto = (int)$id_prodotto;
    $id_ordine = (int)$id_ordine;
    // T08: $in_txn=true quando il chiamante (mq/mr) possiede gia' la transazione:
    // niente begin/commit/rollback interni (un begin annidato farebbe implicit commit
    // su InnoDB), l'esito torna al chiamante. Ritorna bool per la catena esterna.
    $ok = false;
    if(isSet($id_prodotto))
    {
        // T07: catena SELECT->UPDATE->SELECT->UPDATE in un'unica transazione; la riga
        // ordini padre e' bloccata via SELECT ... FOR UPDATE dove il motore lo permette
        // (effettivo su InnoDB da T21, no-op su MyISAM baseline). Ordine di lock costante
        // padre->figlio anti-deadlock; niente retry interno, il prossimo tap ricalcola.
        if (!$in_txn)
            $mysqli->begin_transaction();
        $ok = true;
        try
        {
            #Lock riga padre: serializza i tap concorrenti sullo stesso ordine.
            $ris = db_select($mysqli, "SELECT id_ordine FROM ordini WHERE id_ordine = ? FOR UPDATE", 'i', array($id_ordine));
            if (!$ris)
                $ris = db_select($mysqli, "SELECT id_ordine FROM ordini WHERE id_ordine = ?", 'i', array($id_ordine));
            $ok = (bool)$ris;
            if ($ok)
            {
                #DOPO AVER INSERITO O AGGIUNTO UN PRODOTTO DEVO AGGIORNARE QUANTITA E TOTALE IN righe_ordini
                $ris = db_select($mysqli, "SELECT quantita, prezzo FROM prodotti,righe_ordini WHERE prodotti.id_prodotto = righe_ordini.id_prodotto AND id_ordine = ? AND prodotti.id_prodotto = ?", 'ii', array($id_ordine, $id_prodotto));
                //print "query: calcolaTotali select righe";
                $ok = (bool)$ris;
            }
            if($ok && mysqli_num_rows($ris) > 0)
            {
                $riga = mysqli_fetch_array($ris);
                $quantita = (int)$riga['quantita'];
                $prezzo = (float)$riga['prezzo'];
                $totale = $quantita * $prezzo;
                //print "\n<script>alert('n_pezzi: $quantita - totale:$totale');</script>";
                //print "query: calcolaTotali update righe";
                $ok = db_exec($mysqli, "UPDATE righe_ordini set quantita = ?, totale = ? WHERE id_ordine = ? AND id_prodotto = ?", 'idii', array($quantita, $totale, $id_ordine, $id_prodotto));
            }
            if ($ok)
            {
                #DOPO AVER INSERITO O AGGIUNTO UN PRODOTTO DEVO AGGIORNARE n_pezzi e totale IN ordini
                $ris = db_select($mysqli, "SELECT sum(quantita) as n_pezzi, sum(totale) as totale FROM righe_ordini WHERE id_ordine = ? GROUP BY id_ordine", 'i', array($id_ordine));
                $ok = (bool)$ris;
            }
            if ($ok)
            {
                $riga = mysqli_fetch_array($ris);
                $n_pezzi = (int)$riga['n_pezzi'];
                $totale = (float)$riga['totale'];

                if($totale == 0)
                    //print "query: calcolaTotali azzera ordini";
                    $ok = db_exec($mysqli, "UPDATE ordini set n_pezzi = 0, totale = 0 WHERE id_ordine = ?", 'i', array($id_ordine));
                //print "query: calcolaTotali update ordini";
                else
                    $ok = db_exec($mysqli, "UPDATE ordini set n_pezzi = ?, totale = ? WHERE id_ordine = ?", 'idi', array($n_pezzi, $totale, $id_ordine));
                //print "query: calcolaTotali update ordini";
            }
        }
        catch (Throwable)
        {
            // PHP8: query fallita lancia invece di tornare false; annulla come sopra.
            $ok = false;
        }
        if ($in_txn)
            return $ok; // commit/rollback al chiamante (mq/mr, T08).
        if ($ok)
            $mysqli->commit();
        else
        {
            $mysqli->rollback();
            error_log("calcolaTotali: rollback id_ordine=$id_ordine [T07]");
        }
    }

    return $ok;

}

function testo_biglietti($stringa)
{
    #Converto i caratteri accentati in lattare + carattere '
    $stringa = str_replace("è","e'",$stringa);

    #Divido la stringa del testo da stampare sul bigliestto nelle parole che la
    #compongono e le tengo in un array dove metto ogni singola parola è un elemento
    $array = explode(" ", $stringa);
    $n = count($array);
    #Variabili che conterrano il testo del diglietto, una variabile per riga
    $str1 = "";
    $str2 = "";
    #Se il testo è composto da ...
    switch($n)
    {
        #...una parola lo assegno alla stringa della prima riga
        case 1:
            $str1 = $array[0];
            break;
        #...due parole e la prima è minore di 12 caratteri, assegno la prima parola alla rpima riga e la seconda alla seconda riga
        case 2:
            if(strlen($array[0]) < 12)
            {
                $str1 = $array[0];
                $str2 = $array[1];
            }
            break;
        case 3:
            if(strlen($array[0]) + strlen($array[1]) < 12)
            {
                $str1 = $array[0] ." ". $array[1];
                $str2 = $array[2];
            }
            else
            {
                $str1 = $array[0];
                $str2 = $array[1] ." ". $array[2];
            }
            break;
        case 4:
            if(strlen($array[0]) + strlen($array[1]) + strlen($array[2]) < 12)
            {
                $str1 = $array[0] ." ". $array[1] ." ". $array[2];
                $str2 = $array[3];
            }
            else
            {
                if(strlen($array[0]) + strlen($array[1]) < 12)
                {
                    $str1 = $array[0] ." ". $array[1];
                    $str2 = $array[2] ." ". $array[3];
                }
                else
                {
                    $str1 = $array[0];
                    $str2 = $array[1] ." ". $array[2] ." ". $array[3];
                }
            }
            break;
        case 5:
            if(strlen($array[0]) + strlen($array[1]) + strlen($array[2]) + strlen($array[3]) < 12)
            {
                $str1 = $array[0] ." ". $array[1] ." ". $array[2] ." ". $array[3];
                $str2 = $array[4];
            }
            else
            {
                if(strlen($array[0]) + strlen($array[1]) + strlen($array[2]) < 12)
                {
                    $str1 = $array[0] ." ". $array[1] ." ". $array[2];
                    @$str2 = $array[3] ." ". $array[4];
                }
                else
                {
                    if(strlen($array[0]) + strlen($array[1]) < 12)
                    {
                        $str1 = $array[0] ." ". $array[1];
                        @$str2 = $array[2] ." ". $array[3] ." ". $array[4];
                    }
                    else
                    {
                        $str1 = $array[0];
                        @$str2 = $array[1] ." ". $array[2] ." ". $array[3] ." ". $array[4];
                    }
                }
            }

            break;
    }
    #print "Str 1: $str1<br>\n";
    #print "Str 2: $str2<br>\n";

    #Converto il testo in maisucolo perchè il font 5 della stampante stampa solo lettere maiuscole
    $str1 = strtoupper($str1);
    $str2 = strtoupper($str2);

    $str[0] = $str1;
    $str[1] = $str2;

    //print "Biglietto:". $str1.$str2;

    return $str;
}

function stampaMenu($testo,$quantita,$tipo_stampa,$parametri, $id=0)
{
    /*
            tipo stampa, se è 1 allora sono 3 menù separati
            se è 0 allora stampa i menù assieme
                        0										                1
          polenta, salsiccia, acqua				polenta, polenta, polenta
            polenta, salsiccia, acqua				salsiccia, salsiccia, salsiccia
            polenta, salsiccia, acqua				acqua, acqua, acqua
    */

//print_r($testo);

    if(in_array("ACQUA", $testo))
        $bevanda = "ACQUA";
    else if(in_array("BUONO BIRRA", $testo))
        $bevanda = "BIRRA";
    else if(in_array("VINO", $testo))
        $bevanda = "VINO";
    else
        $bevanda = "BIBITA";

    $label = "";
    $num_pezzi =0;
    if($tipo_stampa==0)
    {
        $menu[0]="TARAGNA E";
        $menu[1]="SALSICCIA";
        $menu[2]="MIX FORMAGGI";
        $menu[3]="PATATINE";
        $menu[4]=$bevanda;
        $menu[5]="DOLCE";
        $menu[6]="CAFFE";
        $numero_prodotti_menu = 3;
        //print("	<script language=\"javascript\">alert(\"1 num_pezzi = $num_pezzi\")</script>");
        for($i=0;$i<$quantita;$i++)
        {
            #Prodotto 1
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 1 riga
            $label .= "A440,195,2,5,1,1,N,\"$menu[6]\"\nA440,135,2,5,1,1,N,\"\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;

            #Prodotto 2
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 1 riga
            $label .= "A440,195,2,5,1,1,N,\"$menu[5]\"\nA440,135,2,5,1,1,N,\"\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;

            #Prodotto 3
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 1 riga
            $label .= "A440,195,2,5,1,1,N,\"$menu[4]\"\nA440,135,2,5,1,1,N,\"\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;

            #Prodotto 4
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 1 riga
            $label .= "A440,195,2,5,1,1,N,\"$menu[3]\"\nA440,135,2,5,1,1,N,\"\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;

            #Prodotto 5
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 1 riga
            $label .= "A440,195,2,5,1,1,N,\"$menu[2]\"\nA440,135,2,5,1,1,N,\"\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;

            #Prodotto 6
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 2 righe
            $label .= "A440,195,2,5,1,1,N,\"$menu[0]\"\nA440,135,2,5,1,1,N,\"$menu[1]\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";
            $num_pezzi ++;


        }
        /*#Stampa NOME DEL MENU del Menu
        #Stampa di un'etichetta per ogni singola quantità di un prodotto
            #Nome prodotto su 2 righe
            $label .= "N\nA438,230,2,2,1,1,R,\"$parametri[evento]\"\n";
            $label .= "A440,195,2,5,1,1,N,\"$menu[0]\"\nA440,135,2,5,1,1,N,\"$menu[1]\"\n";
            #Numero cassa data e ora biglietto
            $label .= "A380,65,2,2,1,1,N,\"Cassa$parametri[cassa] - $parametri[now] $parametri[t]\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$parametri[credits]\"\n";
            #Quantità
            $label .= "P1\n";*/
    }
    $vect["num_pezzi"] = $num_pezzi;
    $vect["label"] = $label;
    return $vect;
}

function printOre()
{
    print "<table border=\"0\" width=\"100%\">\n
          <tr>\n
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('00:00');\" type=\"button\">00:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('01:00');\" type=\"button\">01:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('02:00');\" type=\"button\">02:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('03:00');\" type=\"button\">03:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('04:00');\" type=\"button\">04:00</button></td></tr>
          <tr>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('05:00');\" type=\"button\">05:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('06:00');\" type=\"button\">06:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('07:00');\" type=\"button\">07:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('08:00');\" type=\"button\">08:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('09:00');\" type=\"button\">09:00</button></td></tr>
          <tr>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('10:00');\" type=\"button\">10:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('11:00');\" type=\"button\">11:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('12:00');\" type=\"button\">12:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('13:00');\" type=\"button\">13:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('14:00');\" type=\"button\">14:00</button></td></tr>
          <tr>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('15:00');\" type=\"button\">15:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('16:00');\" type=\"button\">16:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('17:00');\" type=\"button\">17:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('18:00');\" type=\"button\">18:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('19:00');\" type=\"button\">19:00</button></td></tr>
          <tr>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('20:00');\" type=\"button\">20:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('21:00');\" type=\"button\">21:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('22:00');\" type=\"button\">22:00</button></td>
          <td class=\"td_bottone\"><button class=\"bottone\" style=\"width: 80px; height: 60px;\" onclick=\"setOra('23:00');\" type=\"button\">23:00</button></td>
          <td class=\"td_bottone\">&nbsp;</td>
         </tr></table>";
}

function stampa_contatori($pwd, $mysqli)
{
    $query = "SELECT * FROM contatori WHERE attivo = 'T'";
    $ris = mysql_query_safe($mysqli, $query);

    while($riga = mysqli_fetch_array($ris))
    {
        print "<button class=\"bottone_conf\" onclick=\"location.href='index.php?action=contatori'\">" . htmlspecialchars($riga['nome'], ENT_QUOTES, 'UTF-8') . "</button>";
    }
}

function conta_prodotto($id_contatore, $mysqli)
{
    // C4: firma (int); solo conteggio venduti per display, mai enforcement di limiti (non bloccante).
    $id_contatore = (int)$id_contatore;
    $ris_nome = db_select($mysqli, "SELECT nome FROM contatori WHERE id_contatore = ?", 'i', array($id_contatore));
    $riga_nome = $ris_nome ? mysqli_fetch_array($ris_nome) : false;
    $nome = $riga_nome ? $riga_nome['nome'] : '';
    $ris = db_select($mysqli, "SELECT * FROM contatori, prodotti_contatori WHERE contatori.id_contatore = prodotti_contatori.id_contatore AND contatori.id_contatore = ?", 'i', array($id_contatore));
    $totale = 0;
    while($ris && ($riga = mysqli_fetch_array($ris)))
    {
        $controllo_periodo = $riga['controllo_periodo'];
        $data_da = $riga['data_da'];
        $data_a = $riga['data_a'];
        $qta = (float)$riga['quantita'];
        $id_prodotto = (int)$riga['id_prodotto'];
        $sql_conta = "SELECT SUM(quantita) AS somma FROM righe_ordini, ordini WHERE ordini.id_ordine = righe_ordini.id_ordine AND righe_ordini.id_prodotto = ?";
        $tipi_conta = 'i';
        $par_conta = array($id_prodotto);

        if($controllo_periodo == "T")
        {
            // date da DB riusate in SQL, formato stretto o filtro saltato (mai input grezzo).
            $ok_da = is_string($data_da) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data_da);
            $ok_a = is_string($data_a) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data_a);
            if($ok_da && $ok_a)
            {
                $sql_conta .= " AND data_ora BETWEEN ? AND ?";
                $tipi_conta = 'iss';
                $par_conta = array($id_prodotto, $data_da, $data_a);
            }
        }

        $ris_conta = db_select($mysqli, $sql_conta, $tipi_conta, $par_conta);
        $riga_conta = $ris_conta ? mysqli_fetch_array($ris_conta) : false;
        $tot_par = ($riga_conta && $riga_conta['somma'] !== null) ? ((float)$riga_conta['somma']) * $qta : 0;

        $totale += $tot_par;
    }
    $ret['totale'] = $totale;
    $ret['nome'] = $nome;
    return $ret;
}

function generaCardDegustazione($numero, $qta)
{
    $top = 600;

    $label = "N\nA832,$top,2,3,2,3,R,\"     DEGUSTAZIONE $numero SAKE         \"\n";
    $top1 = $top-80;
    //$label .= "A832,$top1,2,3,2,2,N,\"    30 Ott. - 3 Nov. 2025\"\n";
    $h = $top1-120;
    if($numero == 1)
    {

        $label .= "X475,$top1,10,355,$h\n";
    }

    if($numero == 2)
    {
        $label .= "X600,$top1,10,480,$h\n";
        $label .= "X400,$top1,10,280,$h\n";
    }

    if($numero == 5)
    {
        $label .= "X770,$top1,10,650,$h\n";
        $label .= "X620,$top1,10,500,$h\n";
        $label .= "X470,$top1,10,350,$h\n";
        $label .= "X320,$top1,10,200,$h\n";
        $label .= "X170,$top1,10,50,$h\n";
    }
    $now = date("d/m/y H:i");
    //$label .= "A800,$top1,2,3,1,1,N,\"                Cassa$cassa - $now\"\n";
    /* $label .= "A800,$top1,2,4,2,2,N,\"     Totale: $totale"."Õ\"\n";
       $top1 = $top1-60;
       $label .= "A800,$top1,2,2,1,1,N,\"                   Non valido ai fini fiscali\"\n";*/
    $top1 = $top1-150;
    $label .= "A800,$top1,2,2,2,2,N,\"Il presente biglietto da diritto\"\n";
    $top1 = $top1-40;
    $label .= "A800,$top1,2,2,2,2,N,\"alla degustazione di $numero sake ed\"\n";
    $top1 = $top1-40;
    $label .= "A800,$top1,2,2,2,2,N,\"e' valido per tutta la durata \"\n";
    $top1 = $top1-40;
    $label .= "A800,$top1,2,2,2,2,N,\"dell'evento 16 Ottobbre 2025\"\n";
    $top1 = $top1-60;
    $label .= "A800,$top1,2,4,1,1,N,\"  La quantita' di ciascun assaggio e' di 30ml\"\n";
    $top1 = $top1-60;
    $label .= "A800,$top1,2,3,1,1,N,\"             Stampato il  $now\"\n";
    $top1 = $top1-40;
    $label .= "A800,$top1,2,2,1,1,N,\"(La vendita di bevande alcoliche e' vietata ai minori di 18 anni)\"\n";
    /*$top1 = $top1-60;
	  $label .= "A800,$top1,2,8,1,1,N,\"ppp 日本酒 ppp\"\n";*/
    $top1 = $top1-40;
    $label .= "A832,$top1,2,2,2,2,R,\"         www.sushitalia.com             \"\n";

    $top1 = $top1-60;
    //$label .= "A800,$top1,2,5,2,2,N,\"Ogni assaggio prevede una quantità di 30ml \"\n";
    #Numero biglietti
    $label .= "P1\n";
    //Taglio Carta
    $label .= "C\n";

    if($qta > 1)
    {
        for($i=1; $i<$qta; $i++)
        {
            $label .= $label;
        }
    }

    $pf = fopen(__DIR__ . "/storage/labelCards", "w")
    or die("Impossibile aprire il file di stampa");
    fwrite($pf, $label, strlen($label));
}

// Scontrino unico (carta continua): builder puro su righe gia' lette, cosi'
// e' testabile senza DB ne' stampante (issue #54). $righe = lista assoc con
// id_ordine, tot_ord, tipo, testo, quantita, totale, id_prodotto.
// Ritorna array(label, num_pezzi). Solo path fiscale, niente cucina.
function etichetta_continua($righe, $numero_righe, $evento, $credits, $cassa, $now, $tipo_stampante)
{
    $riga0 = $righe[0];
    $totale = number_format($riga0['tot_ord'], 2, '.', '.');
    $top = 420 + ($numero_righe * 60);
    $label = "";

    // CONTROLLO LINGUAGGIO CONTINUO: ZPL
    if ($tipo_stampante == "ZPL")
    {
        $euro = "\xE2\x82\xAC";
        $zpl_y = 280;
        $zpl_step = 44;
        $ll = 280 + ($numero_righe * $zpl_step) + 230;
        // Titolo bilanciato su 2 righe: parole distribuite a meta', data in riga2
        $titolo = trim(str_replace(array('^', '~'), ' ', $evento));
        $evento_data = "";
        if (preg_match('/^(.*?)(\d{1,2}[\/\-.]\d{1,2}[\/\-.]\d{2,4})\s*$/', $titolo, $m)) {
            $titolo = trim($m[1], " \t-");
            $evento_data = $m[2];
        }
        if ($evento_data === "") {
            $evento_data = date("d/m/y");
        }
        $titolo = trim(str_replace(" - ", " ", $titolo . " " . $evento_data));
        $parole = preg_split('/\s+/', $titolo);
        $tot = strlen($titolo);
        $len = 0;
        $i = 0;
        $n = count($parole);
        while ($i < $n) {
            $nl = $len + ($len > 0 ? 1 : 0) + strlen($parole[$i]);
            if ($len > 0 && $nl > $tot / 2) break;
            $len = $nl;
            $i++;
        }
        if ($i == 0) $i = 1;
        $evento_nome = implode(" ", array_slice($parole, 0, $i));
        $evento_data = implode(" ", array_slice($parole, $i));
        $w_nome = (int)min(30, floor(800 / max(1, strlen($evento_nome))));
        $w_data = (int)min(30, floor(800 / max(1, strlen($evento_data))));
        $font_nome = "70,$w_nome";
        $font_data = "70,$w_data";
        $label .= "^XA\n^CI28\n^POI\n^PW832\n^LH0,0\n^LS0\n^LL$ll\n";
        $label .= "^FO0,10^GB832,30,30^FS\n";
        $label .= "^FO0,16^FB832,1,0,C^ADN,18^FR^FDCassa$cassa - $now^FS\n";
        $label .= "^FO0,84^FB832,1,0,C^ADN,$font_nome^FD$evento_nome^FS\n";
        $label .= "^FO2,84^FB832,1,0,C^ADN,$font_nome^FD$evento_nome^FS\n";
        $label .= "^FO0,166^FB832,1,0,C^ADN,$font_data^FD$evento_data^FS\n";
        $label .= "^FO2,166^FB832,1,0,C^ADN,$font_data^FD$evento_data^FS\n";

        $num_pezzi = 0;
        foreach($righe as $riga)
        {
            $testo = str_replace(array('^', '~'), ' ', strtoupper($riga['testo']));
            // Nome max 18 caratteri su parole intere: se il taglio cade in
            // mezzo a parola/numero, torna all'ultimo spazio
            if(function_exists('mb_substr'))
            {
                $taglio = mb_substr($testo, 0, 18, 'UTF-8');
                $oltre = mb_substr($testo, 18, 1, 'UTF-8');
                $ultimo = mb_substr($taglio, -1, 1, 'UTF-8');
            }
            else
            {
                $taglio = substr($testo, 0, 18);
                $oltre = substr($testo, 18, 1);
                $ultimo = substr($taglio, -1, 1);
            }
            if($oltre !== "" && $oltre !== " " && $ultimo !== " ")
            {
                $sp = strrpos($taglio, " ");
                if($sp !== false) $taglio = substr($taglio, 0, $sp);
            }
            $testo = rtrim($taglio);
            $quantita = $riga['quantita'];
            $importo = number_format($riga['totale'], 2, '.', '.') . " $euro";

            $label .= "^FO35,$zpl_y^FB560,1,0,L^ADN,28^FDN.$quantita $testo^FS\n";
            $label .= "^FO610,$zpl_y^FB190,1,0,R^ADN,28^FD$importo^FS\n";
            $num_pezzi++;
            $zpl_y += $zpl_step;
        }

        // Riga vuota bianca tra ultimo prodotto e totale
        $zpl_y += $zpl_step;
        // Totale e chiusura scontrino unico ZPL
        $label .= "^FO0,$zpl_y^FB832,1,0,C^ADN,30^FDTOTALE: $totale $euro^FS\n";
        $zpl_y += 42;
        $label .= "^FO0,$zpl_y^FB832,1,0,C^ADN,28^FDArrivederci e grazie!!^FS\n";
        $zpl_y += 60;
        $label .= "^FO0,$zpl_y^GB832,30,30^FS\n";
        $label .= "^FO0,".($zpl_y+6)."^FB832,1,0,C^ADN,18^FR^FD$credits^FS\n";
        $label .= "^PQ1\n^XZ\n";
    }
    // CONTROLLO LINGUAGGIO CONTINUO: EPL (stessi dati del ramo ZPL, niente hardcoded)
    elseif ($tipo_stampante == "EPL")
    {
        $titolo_epl = str_replace(array('"', "\r", "\n"), '', $evento);
        $credits_epl = str_replace(array('"', "\r", "\n"), '', $credits);
        $label = "N\nA832,$top,2,3,2,3,R,\" $titolo_epl\"\n";
        $top1 = $top-80;
        $label .= "A832,$top1,2,3,2,2,N,\"    $now\"\n";
        $top1 = $top1-60;
        $label .= "A800,$top1,2,3,1,1,N,\"                Cassa$cassa - $now\"\n";
        $top1 = $top1-60;

        $num_pezzi = 0;
        foreach($righe as $riga)
        {
            $testo = str_replace(array('"', "\r", "\n"), '', strtoupper($riga['testo']));
            $quantita = $riga['quantita'];
            $importo = number_format($riga['totale'], 2, '.', '.')."\xc3\x95";

            $label .= "A800,$top1,2,1,1,2,N,\"N.$quantita $testo\"\n";
            if(strlen($importo) == 6) $importo = " ".$importo;
            $label .= "A170,$top1,2,1,2,2,N,\"$importo\"\n";

            $num_pezzi++;
            $top1 = $top1-60;

            if(in_array($riga['id_prodotto'], [14, 15, 16])) {
                if($riga['id_prodotto'] == 14) generaCardDegustazione(1, $quantita);
                if($riga['id_prodotto'] == 15) generaCardDegustazione(2, $quantita);
                if($riga['id_prodotto'] == 16) generaCardDegustazione(5, $quantita);

                if (PRINTER_CONNECTION == "DIRETTA") {
                    // solo costanti set.inc, mai input utente; whitelist+escapeshellarg+log, niente @ silente.
                    if (preg_match('/^[A-Za-z0-9_-]+$/', PRINTER_NAME))
                    {
                        $cmdCards = "lpr -P " . escapeshellarg(PRINTER_NAME) . " " . escapeshellarg(__DIR__ . "/storage/labelCards");
                        system($cmdCards, $rcCards);
                        if ($rcCards !== 0)
                            error_log("lpr labelCards fallito rc=$rcCards printer=" . PRINTER_NAME);
                    }
                    else
                        error_log("lpr labelCards bloccato: PRINTER_NAME non whitelistato");
                }
            }
        }
        // Riga vuota bianca tra ultimo prodotto e totale
        $top1 = $top1-60;
        $label .= "A800,$top1,2,4,2,2,N,\"     TOTALE: $totale"."\xc3\x95\"\n";
        $top1 = $top1-120;
        $label .= "A800,$top1,2,2,2,2,N,\"     Arrivederci e grazie!!\"\n";
        $top1 = $top1-60;
        $label .= "A832,$top1,2,2,2,2,R,\"         $credits_epl             \"\n";
        $label .= "P1\nC\n";
    }

    $ret_c['label'] = $label;
    $ret_c['num_pezzi'] = isset($num_pezzi) ? $num_pezzi : 0;
    return $ret_c;
}

function genera_file_stampa($ris, $numero_righe)
{
    $print_order_id = PRINT_ORDER_ID;
    $evento = EVENT_NAME;
    $credits = CREDITS;
    $cassa = function_exists('cassaCorrente') ? cassaCorrente() : ID_CASSA;


    // Unica chiave linguaggio: "ZPL" o "EPL" da PRINTER_LANGUAGE.
    $tipo_stampante = PRINTER_LANGUAGE;

    // Gate unico in env.inc: stessa tabella di CONTINUOUS_LABEL in set.inc.
    $stampa_permessa = printer_gate_allowed(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE);

    if ($stampa_permessa==false)
    {
        $specifiche = "";
        if (PRINTER_NAME == "Zebra_Multi") {
            $specifiche = "supporta ZPL e EPL, ma lavora solo in modalità DIRETTA.";
        } elseif (PRINTER_NAME == "Zebra_Net") {
            $specifiche = "lavora in DIRETTA e RETE, ma supporta solo il linguaggio ZPL.";
        } elseif (PRINTER_NAME == "Zebra_EPL_1" || PRINTER_NAME == "Zebra_EPL_2") {
            $specifiche = "supporta solo il linguaggio EPL e solo in modalità DIRETTA.";
        }

        print "<div style='background-color: #d9534f; color: white; padding: 20px; font-family: \"Trebuchet MS\"; font-weight: bold; text-align: center; border-radius: 5px; margin: 20px 0;'>";
        print " ERRORE DI CONFIGURAZIONE <br>";

        // Ecco la tua riga modificata che concatena la variabile dinamica:
        print "<span style='font-size: 14px; font-weight: normal;'>La stampante <b>" . PRINTER_NAME . "</b> non è compatibile con la modalità <b>" . PRINTER_CONNECTION . "</b> in linguaggio <b>" . PRINTER_LANGUAGE . "</b>. Impostazioni bloccate.<br> Questa stampante " . $specifiche . "</span>";

        print "</div>";
        print "<center><button class=\"bottone_conf\" onclick=\"location.href='index.php'\"><b>TORNA AL MENU</b></button></center>";

        return false;
    }

    #apertura file
    $pf = fopen(LABELS_FILE, "w") or die("Impossibile aprire il file di stampa");

    if(CONTINUOUS_LABEL)
    {
        $righe = array();
        while($riga_tmp = mysqli_fetch_array($ris)) $righe[] = $riga_tmp;
        $id_ordine = $righe[0]['id_ordine'];
        $totale = number_format($righe[0]['tot_ord'], 2, '.', '.');
        $now = date("d/m/y H:i");
        $ret_c = etichetta_continua($righe, $numero_righe, $evento, $credits, $cassa, $now, $tipo_stampante);
        $label = $ret_c['label'];

        $num_pezzi = 1;
    }
    else
    {
        // BIGLIETTI SINGOLI (Passaggio parametri a get_product_label)

        $riga_biglietto = mysqli_fetch_array($ris);
        $id_ordine = $riga_biglietto['id_ordine'];
        $totale = number_format($riga_biglietto['tot_ord'], 2, '.', '.');
        $tipo = $riga_biglietto['tipo'];
        $t = "";

        if($print_order_id == 1) $id = "N.$id_ordine"; else $id = "";

        if($tipo == "pre") $t = "#P#";
        if($tipo == "mus") $t = "#M#";
        if($tipo == "stf") $t = "#S#";
        if($tipo == "asp") $t = "#A#";

        mysqli_data_seek($ris,0);
        $label = "";

        # Singola: mai biglietto totale ne' "Arrivederci e grazie" (solo ramo continuo).
        # Ogni etichetta ha solo il prodotto; TOTAL_LABEL resta disattivo qui.
        $label = "";
        $num_pezzi = 0;

        while($riga = mysqli_fetch_array($ris))
        {
            $now = date("d/m/y H:i");
            $testo = testo_biglietti($riga['testo']);
            $quantita = $riga['quantita'];
            $olpp_2 = $riga['olpp'];

            // Compilazione dei parametri da passare
            $parametri["now"] = $now;
            $parametri["id"] = $id;
            $parametri["cassa"] = $cassa;
            $parametri["credits"] = $credits;
            $parametri["evento"] = $evento;
            $parametri["t"] = $t;
            $parametri["quantita"] = $quantita;
            $parametri["testo"] = $testo;
            $parametri["olpp"] = $olpp_2;
            $parametri["num_pezzi"] = $num_pezzi;
            $parametri["print_order_id"] = $print_order_id;

            // PASSA LA COSTANTE DI LINGUAGGIO DINAMICA
            $parametri["tipo_stampante"] = $tipo_stampante;

            if(($riga["id_prodotto"] == 60 || $riga["id_prodotto"] == 61 || $riga["id_prodotto"] == 62 || $riga["id_prodotto"] == 63) && MENU_ENABLE)
            {
                $vect = stampaMenu($testo,$quantita,0,$parametri);
                $num_pezzi += $vect["num_pezzi"];
                $label .= $vect["label"];
            }
            else
            {
                // Richiama get_product_label passandogli il tipo stampante impostato dalla costante
                $ret = get_product_label($parametri);
                $num_pezzi = $ret['num_pezzi'];
                $label .= $ret['label'];
            }
        }
    }

    #Scrittura finale del file unico generato
    fwrite($pf, $label, strlen($label));
    fclose($pf);

    #L'invio in stampante avviene in entrambe le modalita (continua e biglietti singoli)
    $vect["inviato"] = invia_file_stampa();

    $vect["num_pezzi"] = $num_pezzi;
    $vect["id_ordine"] = $id_ordine;
    $vect["totale"] = $totale;
    return $vect;
}

function get_product_label($parametri)
{
    $label = "";
    $now = $parametri["now"] ;
    $id = $parametri["id"];
    $cassa = $parametri["cassa"];
    $credits = $parametri["credits"];
    $evento = $parametri["evento"];
    $t = $parametri["t"];
    $quantita = $parametri["quantita"];
    $testo = $parametri["testo"];
    $olpp = $parametri["olpp"];
    $num_pezzi = $parametri["num_pezzi"];
    $print_order_id = $parametri["print_order_id"];
    // Unica chiave linguaggio (issue #54): EPL o ZPL da genera_file_stampa.
    $tipo_stampante = isset($parametri["tipo_stampante"]) ? $parametri["tipo_stampante"] : "ZPL";

    if ($tipo_stampante == "EPL")
    {
        // EPL: i campi viaggiano tra doppi apici, via le virgolette spurie.
        $evento_epl = str_replace('"', '', $evento);
        $credits_epl = str_replace('"', '', $credits);
        $testo_epl0 = str_replace('"', '', $testo[0]);
        $testo_epl1 = str_replace('"', '', $testo[1]);
        if(($olpp=="T") || ($quantita==1))
        {
            #Un'etichetta per ogni singola quantita, nome su 2 righe
            $label .= "N\nA438,230,2,2,1,1,R,\"$evento_epl\"\n";
            $label .= "A440,195,2,5,1,1,N,\"$testo_epl0\"\nA440,135,2,5,1,1,N,\"$testo_epl1\"\n";
            #Numero cassa data e ora biglietto
            if($print_order_id == 1)
                $label .= "A430,65,2,2,1,1,N,\"$id Cassa$cassa - $now $t\"\n";
            else
                $label .= "A380,65,2,2,1,1,N,\"Cassa$cassa - $now $t\"\n";
            #Credits
            $label .= "A390,14,2,1,1,1,N,\"$credits_epl\"\n";
            #Quantita
            $label .= "P".$quantita."\n";
            $num_pezzi += $quantita;
        }
        else
        {
            $label .= "N\nA438,230,2,2,1,1,R,\"$evento_epl\"\n";
            #Un'etichetta con la quantita; se il testo e' lungo riduce il font
            if(strlen($testo_epl0)<=8)
            {
                #Nome prodotto su 2 righe
                $label .= "A440,195,2,5,1,1,N,\"$quantita"."X $testo_epl0\"\nA440,135,2,5,1,1,N,\"$testo_epl1\"\n";
                #Numero cassa data e ora biglietto
                if($print_order_id == 1)
                    $label .= "A430,65,2,2,1,1,N,\"$id Cassa$cassa - $now $t\"\n";
                else
                    $label .= "A380,65,2,2,1,1,N,\"Cassa$cassa - $now $t\"\n";
                #Credits
                $label .= "A390,14,2,1,1,1,N,\"$credits_epl\"\n";
                #Quantita
                $label .= "P1\n";
                $num_pezzi++;
            }
            else
            {
                #Nome prodotto su 2 righe, font ridotto
                $label .= "A440,195,2,1,3,3,N,\"$quantita"."x $testo_epl0\"\nA440,135,2,1,3,3,N,\"$testo_epl1\"\n";
                #Numero cassa data e ora biglietto
                if($print_order_id == 1)
                    $label .= "A430,65,2,2,1,1,N,\"$id Cassa$cassa - $now $t\"\n";
                else
                    $label .= "A380,65,2,2,1,1,N,\"Cassa$cassa - $now $t\"\n";
                #Credits
                $label .= "A390,14,2,1,1,1,N,\"$credits_epl\"\n";
                #Quantita
                $label .= "P1\n";
                $num_pezzi++;
            }
        }
    }
    else
    {
        if(($olpp=="T") || ($quantita==1))
        {
            #TEST ZPL - biglietti singoli: font item ingranditi (solo ramo non continuo)
            $label .= "^XA\n^POI\n^FO0,9^GB470,20,20^FS\n^FO35,12^ADN,16,9^FR^FD$evento^FS\n";
            $label .= "^FO35,60^ADN,55^FD$testo[0]^FS\n^FO35,135^ADN,55^FD$testo[1]^FS\n";
            #Numero cassa data e ora biglietto
            if($print_order_id == 1)
                $label .= "^FO90,195^ADN^FD$id Cassa$cassa - $now $t^FS\n";
            else
                $label .= "^FO90,195^ADN^FDCassa$cassa - $now $t^FS\n";
            #Credits
            $label .= "^FO15,225^AAN,14^FD$credits^FS\n";
            #Quantita
            $label .= "^PQ".$quantita."\n^XZ\n";
            $num_pezzi += $quantita;
        }
        else
        {
            #TEST ZPL
            $label .= "^XA\n^POI\n^FO0,9^GB470,20,20^FS\n^FO35,12^ADN,16,9^FR^FD$evento^FS\n";
            #Un'etichetta con la quantita; se il testo e' lungo riduce il font
            if(strlen($testo[0])<=8)
            {
                #Nome prodotto su 2 righe - biglietti singoli: font ingranditi (ramo continuo non toccato)
                $label .= "^FO35,60^ADN,55^FD$quantita"."X $testo[0]^FS\n^FO35,135^ADN,55^FD$testo[1]^FS\n";
                #Numero cassa data e ora biglietto
                if($print_order_id == 1)
                    $label .= "^FO90,195^ADN^FD$id Cassa$cassa - $now $t^FS\n";
                else
                    $label .= "^FO90,195^ADN^FDCassa$cassa - $now $t^FS\n";
                #Credits
                $label .= "^FO15,225^AAN,14^FD$credits^FS\n";
                #Quantita
                $label .= "^PQ1\n^XZ\n";
                $num_pezzi++;
            }
            else
            {
                #Nome prodotto su 2 righe - nomi lunghi biglietti singoli: font ingrandito (ramo continuo non toccato)
                $label .= "^FO35,60^ABN,32^FD$quantita"."X $testo[0]^FS\n^FO35,135^ABN,32^FD$testo[1]^FS\n";
                #Numero cassa data e ora biglietto
                if($print_order_id == 1)
                    $label .= "^FO90,195^ADN^FD$id Cassa$cassa - $now $t^FS\n";
                else
                    $label .= "^FO90,195^ADN^FDCassa$cassa - $now $t^FS\n";
                #Credits
                $label .= "^FO15,225^AAN,14^FD$credits^FS\n";
                #Quantita
                $label .= "^PQ1\n^XZ\n";
                $num_pezzi++;
            }
        }
    }

    $ret['num_pezzi'] = $num_pezzi;
    $ret['label'] = $label;
    return $ret;

}

function invia_file_stampa()
{
    // nessun input GET/POST qui, solo costanti set.inc; whitelist+escapeshellarg+error_log.
    // T16: LABELS_FILE e' assoluto in storage/, whitelist sul basename.
    if (!preg_match('/^[A-Za-z0-9_-]+$/', PRINTER_NAME) || !preg_match('/^[A-Za-z0-9_.-]+$/', basename(LABELS_FILE)))
    {
        error_log("invia_file_stampa bloccato: costanti stampa non whitelistate");
        return false;
    }
    if (PRINTER_CONNECTION == "DIRETTA")
    {
        $cmd = "lpr -P " . escapeshellarg(PRINTER_NAME) . " " . escapeshellarg(LABELS_FILE);
        system($cmd, $rc);
        if ($rc !== 0)
            error_log("lpr fallito rc=$rc printer=" . PRINTER_NAME . " file=" . LABELS_FILE);
        return $rc === 0;
    }
    return ftpPut();
}

function ftpPut()
{
    $file = LABELS_FILE;

    // set up basic connection
    // Il print server Zebra accetta solo FTP anonimo, senza credenziali;
    // timeout corto cosi' in fiera la cassa fallisce in fretta invece di bloccarsi.
    $ftp = ftp_connect(PRINTER_IP, 21, 5);
    if ($ftp === false)
    {
        error_log("ftp_connect fallito verso " . PRINTER_IP);
        return false;
    }

    // login with username and password
    if (!ftp_login($ftp, "", ""))
    {
        error_log("ftp_login anonimo fallito verso " . PRINTER_IP);
        ftp_close($ftp);
        return false;
    }

    // upload a file (spool locale assoluto in storage/ da T16, nome remoto solo basename)
    $ok = ftp_put($ftp, basename($file), $file, FTP_BINARY);
    if (!$ok)
        error_log("ftp_put fallito file=$file verso " . PRINTER_IP);

    // close the connection
    ftp_close($ftp);
    return $ok;
}




?>
