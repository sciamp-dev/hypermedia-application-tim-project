<?php

    if(is_ajax()) {
        if (isset($_POST["action"]) && !empty($_POST["action"])) {
            $action = $_POST["action"];
            switch($action) {
                case "get_device":
                    get_device();
                    break;
                case "get_img":
                    get_img();
                    break;
                case "get_devlist":
                    get_devlist();
                    break;
            }
        }
    }

    function is_ajax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    function get_device() {
        
        require "connessione.php";
        
        $id = $_POST["id"];
            
          $query = "
          SELECT Devices.idDevices, Devices.nome, Devices.prezzo_intero, Devices.prezzo_rate, Devices.prezzo_scontato,Devices.n_rate, Devices.promo, Devices.novita, Devices.disponibile, Devices.caratteristiche, Devices.descrizione, Devices.inclusi, Devices.specifiche, Categoria.nome AS  'nome_categoria', Marca.nome AS  'nome_marca', Tipologia.nome AS  'nome_tipologia', SisOp.nome AS  'nome_sisop', Schermo.dimensione AS  'dimensione_schermo', Connessione.tipo AS  'tipo_connessione'
            FROM Devices, Categoria, Marca, Tipologia, SisOp, Schermo, Connessione
            WHERE Devices.idDevices = ".$id."
                AND Devices.categoriaID = Categoria.idCategoria
                AND Devices.marcaID = Marca.idMarca
                AND Devices.tipologiaID = Tipologia.idTipologia
                AND Devices.sisopID = SisOp.idSisOp
                AND Devices.schermoID = Schermo.idSchermo
                AND Devices.connessioneID = Connessione.idConnessione

        ";
        
        $dati = mysqli_query($con, $query);
        
         if(mysqli_num_rows($dati) > 0) {
            $riga = mysqli_fetch_assoc($dati);
        }
        
        $riga["caratteristiche"] = str_replace("#", "<br>", $riga["caratteristiche"]);
        
        $return = $riga;
        
        $queryImg = '
        SELECT DISTINCT Colori.idColori AS "id_colore", Colori.codice AS "codice_colore", Colori.nome AS "nome_colore"
            FROM Colori, Devices JOIN Img_Dev ON Devices.idDevices = Img_Dev.devicesID
                JOIN Immagini ON Img_Dev.immaginiID = Immagini.idImmagini
            WHERE Devices.idDevices = '.$id.'
                AND Colori.idColori = Immagini.coloriID
        ';
        
        $dati = mysqli_query($con, $queryImg);
        
        $e_p = "colore_0";
        $i=0;
        
         if(mysqli_num_rows($dati) > 0) {
            while($riga = mysqli_fetch_assoc($dati)){
                $return[$e_p] = $riga;
                $i++;
                $e_p="colore_".$i;
            }
        }
        
        $return["n_colori"] = $i;
        
        $queryImg = '
        SELECT Memoria.idMemoria, Memoria.dimensione
            FROM Devices JOIN Mem_Dev ON Devices.idDevices = Mem_Dev.devicesID
			JOIN Memoria ON Memoria.idMemoria = Mem_Dev.memoriaID
            WHERE Devices.idDevices = '.$id.'
        ';
        
        $dati = mysqli_query($con, $queryImg);
        
        $e_p = "memoria_0";
        $i=0;
        
         if(mysqli_num_rows($dati) > 0) {
            while($riga = mysqli_fetch_assoc($dati)){
                $return[$e_p] = $riga;
                $i++;
                $e_p="memoria_".$i;
            }
        }
        
        $return["n_memoria"] = $i;
        $return["json"] = json_encode($return);
        echo json_encode($return);
        
    } 

    
    function get_img() {
        
        require "connessione.php";
        
        $id = $_POST["id"];
        $id_colore = $_POST["id_colore"];
        
        $queryImg = '
        SELECT Immagini.percorso, Colori.idColori AS "id_colore", Colori.codice AS "codice_colore", Colori.nome AS "nome_colore"
            FROM Colori, Devices JOIN Img_Dev ON Devices.idDevices = Img_Dev.devicesID
                JOIN Immagini ON Img_Dev.immaginiID = Immagini.idImmagini
            WHERE Devices.idDevices = '.$id.'
                AND Colori.idColori = '.$id_colore.'
                AND Colori.idColori = Immagini.coloriID
        ';
        
        $dati = mysqli_query($con, $queryImg);
        
        $e_p = "percorso_0";
        $i=0;
        
         if(mysqli_num_rows($dati) > 0) {
            while($riga = mysqli_fetch_assoc($dati)){
                $return[$e_p] = $riga["percorso"];
                $i++;
                $e_p="percorso_".$i;
            }
        }
        
        $return["n_immagini"] = $i;
        $return["json"] = json_encode($return);
        echo json_encode($return);
        
    }

    function get_devlist() {
        
        require "connessione.php";
        
        $cat = $_POST["categoria"];
        
        $query = '
        SELECT Devices.idDevices, Devices.nome, Devices.prezzo_intero, Devices.prezzo_rate, Devices.prezzo_scontato, Devices.n_rate, Devices.promo, Devices.novita
            FROM Devices, Categoria
            WHERE Devices.categoriaID = Categoria.idCategoria
            AND Devices.categoriaID = '.$cat.'
        ';
        
        $dati = mysqli_query($con, $query);
        
        $dev = "device_0";
        $i=0;
        
         if(mysqli_num_rows($dati) > 0) {
            while($riga = mysqli_fetch_assoc($dati)){
                $return[$dev] = $riga;
                $i++;
                $dev="device_".$i;
            }
         }
        
        $return["n_devices"] = $i;
        
        $queryImg = '
        SELECT DISTINCT imgdevlist.* FROM (
            SELECT DISTINCT Devices.idDevices, Immagini.percorso
                FROM Categoria, Devices JOIN Img_Dev ON Devices.idDevices = Img_Dev.devicesID
                    JOIN Immagini ON Img_Dev.immaginiID = Immagini.idImmagini
                WHERE Devices.categoriaID = Categoria.idCategoria
                AND Devices.categoriaID = '.$cat.'
                ORDER BY Devices.idDevices
            ) AS imgdevlist
            GROUP BY idDevices
        ';
        
        $dati = mysqli_query($con, $queryImg);
        
        $img_dev = "device_0";
        $i=0;
        
         if(mysqli_num_rows($dati) > 0) {
            while($riga = mysqli_fetch_assoc($dati)){
                $return[$img_dev] += $riga;
                $i++;
                $img_dev="device_".$i;
            }
        }
        
        $return["json"] = json_encode($return);
        echo json_encode($return);
        
    }

?>