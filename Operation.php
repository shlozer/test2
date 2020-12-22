<?php

class Operation
{

    private $_lien = DBPATH;
    private $_user = DBUSER;
    private $_pass = DBPASS;
    private $dbh;
    private $journal;

    public function __construct()
    {
        try {
            $this->dbh = new PDO($this->_lien, $this->_user, $this->_pass);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
        $this->journal = fopen("Journal.txt", 'a');
    }


    public function displaySearchIntervention($annee ='', $argument = '', $month = '', $sub_antite_choisi = '', $client = '', $type = '', $statut = '', $trie = '', $trieTaille = '', $antite = '', $last_modified = '')
    {

        if (!empty($annee)) {
            $txtannee = ' AND YEAR(o.dateIntervention) = :annee ';
        } else {
            $txtannee = '';
        }
        if (!empty($month)) {
            $txtmonth = ' AND MONTH(o.dateIntervention) = :month ';
        } else {
            $txtmonth = '';
        }

        if (!empty($antite)) {
            $txtAntite = ' AND o.antite = :antite ';
        } else {
            $txtAntite = '';
        }

        if (!empty($sub_antite_choisi)) {
            $txtmaskAssoc = ' AND o.sub_antite = :sub_antite_choisi ';
        } else {
            $txtmaskAssoc = '';
        }

        if (!empty($client)) {
            $txtclient = ' AND o.idClient = :idClient ';
        } else {
            $txtclient = '';
        }

        if (!empty($last_modified)) {
            $txtlast_modified = ' AND o.last_modified = :last_modified ';
        } else {
            $txtlast_modified = '';
        }

        if (!empty($type)) {
            $txttype = ' AND o.type_intervention = :type_intervention ';
        } else {
            $txttype = '';
        }

        if ($statut == 2) {
            $txtstatut = ' AND o.statut = 1 ';
        } elseif ($statut == 3) {
            $txtstatut = ' AND o.statut = 0 ';
        } else {
            $txtstatut = ' AND o.statut >= 0 ';
        }

        if (!empty($trie)) {
            $txtOrder = ' ORDER BY ' . $trie . ' ' . $trieTaille;
            if($trie == "c.Nom")
               {
                $txtOrder = ' ORDER BY c.societe ' .$trieTaille.', c.Nom '.$trieTaille;
                    $argument=" AND o.antite = 4 AND ( o.sub_antite = 1 ) ";
                }
        } else {
            $txtOrder = ' ORDER BY o.dateIntervention DESC';
        }


        $sql = 'SELECT * FROM operations AS o';
        if($trie =="c.Nom")
                    $sql = 'SELECT o.cerfa_no, o.clientCerfa,o.statut_cerfa,o.total,o.maskAssoc,o.pre_edit,o.edit,o.date_signature,o.No, o.last_user_modified, o.last_modified, o.dateIntervention, o.idClient, o.type_intervention, o.montant, o.op_maskassoc, o.statut, o.pe, o.e, o.ds, o.id_cerfa, o.sub_sub_antite, o.sub_antite, o.antite, c.Nom FROM operations AS o RIGHT JOIN client AS c ON o.idClient = c.No';
        $sql .= ' WHERE 1 = 1 ' . $txtannee . $txtmonth . $argument . $txtmaskAssoc . $txtclient . $txttype . $txtstatut . $txtAntite . $txtlast_modified . $txtOrder;

        $stmt = $this->dbh->prepare($sql);
        if (!empty($annee)) {
            $stmt->bindParam(':annee', $annee);
        }
        if (!empty($month)) {
            $stmt->bindParam(':month', $month);
        }
        if (!empty($client)) {
            $stmt->bindParam(':idClient', $client);
        }
        if (!empty($type)) {
            $stmt->bindParam(':type_intervention', $type);
        }
        if (!empty($sub_antite_choisi)) {
            $stmt->bindParam(':sub_antite_choisi', $sub_antite_choisi);
        }
        if (!empty($antite)) {
            $stmt->bindParam(':antite', $antite);
        }

        if (!empty($last_modified)) {
            $stmt->bindParam(':last_modified', $last_modified);
        }

        $stmt->execute();

        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }

    }


    public function displayInterventionMonth($year = '', $client = '', $type = '', $argument = '')
    {


        $txtmonth = ' AND YEAR(dateIntervention) = :year ';
        if ((empty($year))) {
            $year = date('Y');
        }
        if (!empty($client)) {
            $txtclient = ' AND idClient = :idClient ';
        } else {
            $txtclient = '';
        }
        if (!empty($type)) {
            $txttype = ' AND type_intervention = :type_intervention ';
        } else {
            $txttype = '';
        }


        $sql = 'SELECT No, idClient, dateIntervention, type_intervention, montant FROM operations WHERE statut = 1 ' . $txtmonth . $txtclient . $txttype . $argument . ' ORDER BY dateIntervention ASC';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':year', $year);


        if (!empty($client)) {
            $stmt->bindParam(':idClient', $client);
        }
        if (!empty($type)) {
            $stmt->bindParam(':type_intervention', $type);
        }
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }

    }


    public function displayInterventionByData(array $donnees)
    {
        $sql = 'SELECT * FROM operations WHERE idClient = ? AND dateIntervention = ? AND type_intervention = ? AND montant = ? AND antite = ? AND sub_antite = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($donnees);
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }


    }


    public function displayIntervention($No = '')
    {


        if (empty($No)) {
            $sql = 'SELECT * FROM operations ORDER BY dateIntervention DESC';
            $stmt = $this->dbh->prepare($sql);
        } else {
            $sql = 'SELECT * FROM operations WHERE No = :No';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':No', $No);
        }
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }


    }

//modif shlomo
    public function displayTotalInterventionsByIdByYear($Antite = '', $SubAntite = '')
    {
        if ((!isset($Antite)) || (!isset($SubAntite)) || (empty($Antite)) || (empty($SubAntite))) {
            $sql = 'SELECT dons.idClient AS idClient,
                    client.Nom AS nom,
                    client.Prenom AS prenom,
                    client.societe AS societe,
                    year(dons.dateIntervention) AS annee,
                    sum(dons.montant) AS total,
                    dons.entite AS entite2,
                    dons.sous_entite AS sentite2,
                    infosgenerales.displayTitre AS displayTitre,
                    client.adresse AS adresse,
                    client.cp AS cp,
                    client.ville AS ville,
                    client.email AS email,
                    client.tel AS tel,
                    client.`tel 2` AS tel2 
                    from (dons left join client on(dons.idClient = client.No)
                                left join infosgenerales on (dons.entite = infosgenerales.Antite 
                                                                AND dons.sous_entite = infosgenerales.sub_antite)
                        ) 
                    where (1 = 1) 
                    group by dons.idClient,
                        year(dons.dateIntervention) 
                    order by client.Nom,
                            client.Prenom,
                            client.societe,
                            dons.idClient,
                            displayTitre,
                            year(dons.dateIntervention)
                    ';
            $stmt = $this->dbh->prepare($sql);
        } 
        else {
            $sql = 'SELECT dons.idClient AS idClient,
            client.Nom AS nom,
            client.Prenom AS prenom,
            client.societe AS societe,
            year(dons.dateIntervention) AS annee,
            sum(dons.montant) AS total,
            dons.entite AS entite2,
            dons.sous_entite AS sentite2,
            infosgenerales.displayTitre AS displayTitre,
            client.adresse AS adresse,
            client.cp AS cp,
            client.ville AS ville,
            client.email AS email,
            client.tel AS tel,
            client.`tel 2` AS tel2 
            from (dons left join client on (dons.idClient = client.No)
                        left join infosgenerales on (dons.entite = infosgenerales.Antite 
                                        AND dons.sous_entite = infosgenerales.sub_antite)
            ) 
            where (dons.entite = :Antite AND dons.sous_entite = :SubAntite ) 
            group by dons.idClient,
                year(dons.dateIntervention) 
            order by client.Nom,
                    client.Prenom,
                    client.societe,
                    dons.idClient,
                    displayTitre,
                    year(dons.dateIntervention)
            ';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':Antite', $Antite);
            $stmt->bindParam(':SubAntite', $SubAntite);
        }
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }


    }

    public function displayYears($Antite = '', $SubAntite = '')
    {
        if ((!isset($Antite)) || (!isset($SubAntite)) || (empty($Antite)) || (empty($SubAntite))) {
            $sql = 'SELECT distinct year(dateIntervention) as annee FROM dons 
                    WHERE 1 order by annee';
            $stmt = $this->dbh->prepare($sql);
        }else{
            $sql = 'SELECT distinct year(dateIntervention) as annee FROM dons 
                    WHERE entite = :Antite AND sous_entite = :SubAntite  order by annee';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':Antite', $Antite);
            $stmt->bindParam(':SubAntite', $SubAntite);
        }
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') { 
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row['annee'];
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }

    }    
//modif shlomo fin
public function displayDon($No)
    {

            $sql = 'SELECT * FROM dons WHERE No = :No';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':No', $No);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            if (isset($data)) {
                return $data;
            } else {
                return false;
            }

        }


    }


    public function displayCountInterventionByIntervenant()
    {

        $sql = 'SELECT * FROM operations WHERE statut=0';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $count = $stmt->rowCount();
        if ($error[0] != '00000') {
            return false;
        } else {
            return $count;
        }
    }


    public function addOperation($last_user_modified, $last_modified, $idClient, $dateIntervention, $type_intervention, $montant, $statut, $date_signature, $antite, $sub_antite, $edit = 0, $pre_edit = 0)
    {

        $verif = 'SELECT idClient, dateIntervention, montant, antite, sub_antite FROM operations WHERE idClient = :idClient AND montant = :montant AND dateIntervention = :dateIntervention AND antite = :antite AND sub_antite = :sub_antite';

        $stmtVerif = $this->dbh->prepare($verif);
        $stmtVerif->bindParam(':idClient', $idClient);
        $stmtVerif->bindParam(':montant', $montant);
        $stmtVerif->bindParam(':antite', $antite);
        $stmtVerif->bindParam(':sub_antite', $sub_antite);
        $stmtVerif->bindParam(':dateIntervention', $dateIntervention);
        $stmtVerif->execute();
        $count = $stmtVerif->rowCount();
        if ($count != 0) {
            return 'Ce don existe déjà';

        } else {
            if(!($pre_edit and $edit))
                $pre_edit = substr($dateIntervention, 2, 2);
            $sql = 'INSERT INTO dons (last_user_modified, last_modified, idClient, dateIntervention, type_intervention, montant, statut, pre_edit,  edit, date_signature, entite, sous_entite) VALUES (:last_user_modified, :last_modified, :idClient, :dateIntervention, :type_intervention, :montant, :statut, :pre_edit, :edit,  :date_signature, :antite, :sub_antite)';

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':last_user_modified', $last_user_modified);
            $stmt->bindParam(':last_modified', $last_modified);
            $stmt->bindParam(':idClient', $idClient);
            $stmt->bindParam(':dateIntervention', $dateIntervention);
            $stmt->bindParam(':type_intervention', $type_intervention);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':statut', $statut);
            $stmt->bindParam(':edit', $edit);
            $stmt->bindParam(':pre_edit', $pre_edit);
            $stmt->bindParam(':date_signature', $date_signature);
            $stmt->bindParam(':antite', $antite);
            $stmt->bindParam(':sub_antite', $sub_antite);
            $stmt->execute();
            $error = $stmt->errorInfo();

            if ($error[0] != '00000') {
                var_dump($error);
                //return 'Un problème est survenue lors de l\'enregistrement du don';
            } else {
                $string = "------- Création d'un don --------".
                "\nDate = " . $dateIntervention . 
                "\nId Client : ".$idClient.
                "\nEntité : " . $antite.
                "\nSous-entité : " . $sub_antite.
                "\nType d'intevention : " .$type_intervention.
                "\nMontant : " . $montant.
                "\nDate signature : " . $date_signature . "\n\n";
                fwrite($this->journal, $string);
                $sql = 'SELECT LAST_INSERT_ID()';
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }

    public function SimCerfa($No)
    {
        $sql = 'SELECT cerfa_global FROM dons WHERE No = :No';

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->execute();
        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } 

        $cerfa_global = $stmt->fetch(PDO::FETCH_ASSOC)['cerfa_global'];
        $sql = 'SELECT * FROM dons WHERE cerfa_global = ' . $cerfa_global;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $error = $stmt->errorInfo();         
        if ($error[0] != '00000')
            return false;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $data[] = $row;
        return $data;

    }

    public function lastCerfa($antite, $sub_antite)
    {
        $sql = 'SELECT pre_edit, edit FROM cerfas WHERE entite = :antite AND sous_entite = :sub_antite ORDER BY pre_edit DESC, edit DESC;';

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':antite', $antite);
        $stmt->bindParam(':sub_antite', $sub_antite);
        $stmt->execute();
        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } 
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCerfaGlobalNG($No, $idNewCerfa, $edit = 0)
    {
        $sql = 'UPDATE dons SET id_cerfa = :idNewCerfa, edit = :edit WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->bindParam(':idNewCerfa', $idNewCerfa);
        $stmt->bindParam(':edit', $edit);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return 'Un problème est survenue lors de la mise à jour du don. Niveau 1';
        } else {
                $string = "------- Modifiation d'un cerfa global --------".
                "\nDate = " . date("Y-m-d") . 
                "\nId Cerfa : ".$idNewCerfa.
                "\nAnnée : " . $annee.
                "\nEdit : " . $edit. "\n\n";
                fwrite($this->journal, $string);
                return true;
            }
    }


    public function updateCerfaGlobalNGG($No, $edit = 0)
    {
        $sql = 'UPDATE cerfas SET edit = :edit WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->bindParam(':edit', $edit);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return 'Un problème est survenue lors de la mise à jour du don. Niveau 1';
        } else {
                $string = "------- Modifiation d'un cerfa global --------".
                "\nDate = " . date("Y-m-d") . 
                "\nId Cerfa : ".$idNewCerfa.
                "\nAnnée : " . $annee.
                "\nEdit : " . $edit. "\n\n";
                fwrite($this->journal, $string);
                return true;
            }
    }


    public function updateCerfaGlobal($No, $idNewCerfa, $annee, $edit, $id_intervention = 0)
    {
        $sql = 'UPDATE dons SET id_cerfa = '. $idNewCerfa .', cerfa_global = :idNewCerfa, pre_edit = :annee, edit = :edit WHERE No = :No';
        if($id_intervention)
            $sql = 'UPDATE dons SET id_cerfa = '. $idNewCerfa .', cerfa_global = :idNewCerfa, pre_edit = :annee, edit = :edit, id_intervention = :id_intervention WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->bindParam(':idNewCerfa', $idNewCerfa);
        $stmt->bindParam(':annee', $annee);
        $stmt->bindParam(':edit', $edit);
        if($id_intervention)
            $stmt->bindParam(':id_intervention', $id_intervention);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return 'Un problème est survenue lors de la mise à jour du don. Niveau 1';
        } else {
                $string = "------- Modifiation d'un cerfa global --------".
                "\nDate = " . date("Y-m-d") . 
                "\nId Cerfa : ".$idNewCerfa.
                "\nAnnée : " . $annee.
                "\nEdit : " . $edit. "\n\n";
                fwrite($this->journal, $string);
                return true;
            }
    }


    public function updateIntervention($No, $last_user_modified, $last_modified, $client, $dateIntervention, $type_intervention, $montant, $date_signature, $user_session_antite, $sub_antite_operation, $Ncd)
    {
        $pre_edit = substr($dateIntervention, 2, 2);
        $sql = 'UPDATE dons SET last_user_modified = :last_user_modified, last_modified = :last_modified, idClient = :idClient, dateIntervention = :dateIntervention, ';
		$sql .= 'type_intervention = :type_intervention, montant = :montant, pre_edit = :pre_edit, date_signature = :date_signature, sous_entite = :sous_entite ' ;
		$sql .= 'WHERE No = :No AND entite = :entite';
		//$date_signature='2018-10-15';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->bindParam(':last_user_modified', $last_user_modified);
        $stmt->bindParam(':last_modified', $last_modified);
        $stmt->bindParam(':idClient', $client);
        $stmt->bindParam(':dateIntervention', $dateIntervention);
        $stmt->bindParam(':type_intervention', $type_intervention);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':pre_edit', $pre_edit);
        $stmt->bindParam(':date_signature', $date_signature);
        $stmt->bindParam(':entite', $user_session_antite);
        $stmt->bindParam(':sous_entite', $sub_antite_operation);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return 'Un problème est survenue lors de la mise à jour du don. Niveau 1';
        } else {
                $string = "------- Modifiation d'un don --------".
                "\nDate = " . $dateIntervention . 
                "\nId Client : ".$client.  
                "\nEntité : " . $user_session_antite.
                "\nSous-entité : " . $sub_antite_operation.
                "\nType d'intevention : " .$type_intervention.
                "\nMontant : " . $montant.
                "\nDate signature : " . $date_signature . "\n\n";
                fwrite($this->journal, $string);

            $sql = "UPDATE cerfas SET  date_signature ='".$date_signature."' ,  idClient = :idClient, 			montant = :montant, pre_edit = :pre_edit, 			 sous_entite = :sous_entite 
			WHERE No=$Ncd AND entite = :entite " ;
			
		//	echo  $date_signature." bye ".$Ncd." Bye ------- ".$sql."-------------";
//$Ncd=2085;

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':idClient', $client);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':pre_edit', $pre_edit);
            //$stmt->bindParam(':date_signature', $date_signature);  ///////
            $stmt->bindParam(':sous_entite', $sub_antite_operation);
            //$stmt->bindParam(':No', $Ncd);
            $stmt->bindParam(':entite', $user_session_antite);
			
			//var_dump($stmt);
            $stmt->execute();
            $error = $stmt->errorInfo();
            if ($error[0] != '00000') {
					//echo  ' hello3 '.$date_signature." bye ";

                return 'Un problème est survenue lors de la mise à jour du don. Niveau 2';
            } else {
                return true;
            }
        }
    }

    public function getSimilarDons($maskAssoc, $idClient, $annee)
    {

        $dons = array();

        //No != :No
        //TROUVER TOUS LES NUMEROS DE DONS QUI ONT LA MEME ANNEE, MASKASSOC, ET IDCLIENT SANS NUMERO DE CERFA ET QUI EST VALIDE
        $sql = 'SELECT No FROM dons WHERE maskAssoc = :maskAssoc AND idClient = :idClient AND YEAR(dateIntervention) = :annee AND id_cerfa IS NULL';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':maskAssoc', $maskAssoc);
        $stmt->bindParam(':idClient', $idClient);
        $stmt->bindParam(':annee', $annee);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($dons, $row['No']);
            }
            if (isset($dons) && is_array($dons)) {
                return $dons;
            } else {
                return false;
            }
        }
    }

    public function getDonsWithSameCerfa($id_cerfa)
    {

        $dons = array();

        //No != :No
        //TROUVER TOUS LES NUMEROS DE DONS QUI ONT LA MEME ANNEE, MASKASSOC, ET IDCLIENT SANS NUMERO DE CERFA ET QUI EST VALIDE
        $sql = 'SELECT * FROM dons WHERE id_cerfa = :id_cerfa';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id_cerfa', $id_cerfa);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($dons, $row['No']);
            }
            if (isset($dons) && is_array($dons)) {
                return $dons;
            } else {
                return false;
            }
        }
    }

    private function lastIdCerfaInserted($maskAssoc, $idClient, $annee, $montant, $leNumero)
    {
        $sql = 'SELECT TOP 1 No FROM cerfas WHERE maskAssoc = :maskAssoc, idClient = :idClient, annee = :annee, montant = :montant, edit > :leNumero ORDER BY No DESC';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':maskAssoc', $maskAssoc);
        $stmt->bindParam(':idClient', $idClient);
        $stmt->bindParam(':annee', $annee);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':leNumero', $leNumero);
        $stmt->execute();
        $error = $stmt->errorInfo();
        $count = $stmt->rowCount();
        if ($error[0] != '00000') {
            return false;
        } else {
            if ($count > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['No'];
            } else {
                return false;
            }
        }
    }

    public function insertCerfa($idClient, $date_signature, $annee, $montant, $entite, $sous_entite, $sous_sous_entite, $n='', $leNumero=0)
    {
        if(!$leNumero)
            $leNumero = (intval($this->findMaxCerfaNumber($entite, $sous_entite, $annee)) + 1);
        $sql = 'INSERT INTO cerfas (idClient, montant, statut, pre_edit, edit, date_signature, entite, sous_entite, sous_sous_entite) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array($idClient, $montant, 1, $annee, $leNumero, $date_signature, $entite, $sous_entite, $sous_sous_entite));
        $idCerfa = $this->dbh->lastInsertId();
        //$idCerfa = $this->lastIdCerfaInserted($maskAssoc, $idClient, $annee, $montant);
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
                $string = "------- Création d'un cerfa --------".
                "\nDate = " . date("Y-m-d") . 
                "\nId Client : ".$idClient.
                "\nEntité : " . $entite.
                "\nSous-entité : " . $sous_entite.
                "\nMontant : " . $montant.
                "\nDate signature : " . $date_signature . "\n\n";
                fwrite($this->journal, $string);

            if($n) return [$idCerfa, $annee, $leNumero];
            if (isset($idCerfa)) {
                return $idCerfa;
            } else {
                return false;
            }
        }
    }

    public function deleteCerfa($No)
    {
        $sql = 'DELETE FROM cerfas WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->execute();

        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } else {
                $string = "------- Suppression du cerfa --------".
                "\nDate = " . date("Y-m-d") . 
                "\nNo cerfa : ". $No . "\n\n";
                fwrite($this->journal, $string);
            return true;
        }
    }

    public function deleteNumCerfa($No)
    { 
        $sql = 'UPDATE dons SET id_cerfa = NULL WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->execute();

        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } else {
                $string = "------- Suppression du numéro de cerfa --------".
                "\nDate = " . date("Y-m-d") . 
                "\nNo cerfa : ". $No . "\n\n";
                fwrite($this->journal, $string);
            return true;
        }
    }


    public function idCerfaForSimilarDons($Nos)
    {

        if (!is_array($Nos)) {
            return false;
        } else {
            $montantTotal = 0;
            foreach ($Nos as $No) {
                $sql = 'SELECT * FROM operations WHERE No = :No';
                $stmt = $this->dbh->prepare($sql);
                $stmt->bindParam(':No', $No);
                $stmt->execute();
                $error = $stmt->errorInfo();

                if ($error[0] != '00000') {
                    return false;
                } else {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $montantTotal += $row['montant'];
                }
            }//end foreach

        }
    }

    public function confirmIntervention($No)
    {

        $sql = 'UPDATE operations SET statut = 1 WHERE No = :No';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':No', $No);
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return 'Un problème est survenue lors de la mise à jour du don';
        } else {
            return true;
        }
    }


    public function deleteIntervention($No)
    {

        if (!is_numeric($No)) {
            return false;
        } else {

            $sql = 'DELETE FROM dons, cerfas USING dons 
                    INNER JOIN cerfas ON dons.id_cerfa = cerfas.No 
                    WHERE dons.No = :No';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':No', $No);
            $stmt->execute();
            $error = $stmt->errorInfo();

            if ($error[0] != '00000') {
                return false;
            } else {
                $string = "------- Suppression d'un don --------".
                "\nDate = " . date("Y-m-d") . 
                "\nNuméro du don : ". $No ."\n\n";
                fwrite($this->journal, $string);
                return true;
            }
        }

    }

    public function deleteDon($No)
    {

        if (!is_numeric($No)) {
            return false;
        } else {

            $sql = 'DELETE FROM dons WHERE dons.No = :No';

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':No', $No);
            $stmt->execute();
            $error = $stmt->errorInfo();

            if ($error[0] != '00000') {
                return false;
            } else {
                $string = "------- Suppression d'un don --------".
                "\nDate = " . date("Y-m-d") . 
                "\nNuméro du don : ". $No ."\n\n";
                fwrite($this->journal, $string);
                return true;
            }
        }

    }


    public function deleteInterventionsByEntite($antite, $sub_antite = '')
    {
        if (empty($sub_antite)) {
            $sql = 'DELETE FROM operations WHERE antite = :antite';

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':antite', $antite);
        } else {
            $sql = 'DELETE FROM operations WHERE antite = :antite AND sub_antite = :sub_antite';

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':antite', $antite);
            $stmt->bindParam(':sub_antite', $sub_antite);
        }
        $stmt->execute();
        $error = $stmt->errorInfo();

        if ($error[0] != '00000') {
            return false;
        } else {
                $string = "------- Suppression de tous les dons de l'association --------".
                "\nDate = " . date("Y-m-d") . 
                "\nAssociation numéro : ". $antite .
                "\nSous-entité numéro : ". $sub_antite . "\n\n";
                fwrite($this->journal, $string);
            return true;
        }
    }


    public function numeroRecu($No)
    {

        if (!is_numeric($No)) {
            return false;
        } else {

            $sql = 'SELECT dateIntervention, pre_edit, edit, antite, sub_antite FROM operations WHERE No = :No';
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(':No', $No);
            $stmt->execute();
            $error = $stmt->errorInfo();

            if ($error[0] != '00000') {
                return false;
            } else {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row['edit'] == 0) {
                    $newSql = 'SELECT MAX(edit) AS maxi FROM operations WHERE antite = ? AND sub_antite = ? AND pre_edit = ?';
                    $newStmt = $this->dbh->prepare($newSql);
                    $newStmt->execute(array($row['antite'], $row['sub_antite'], substr($row['dateIntervention'], 2, 2)));
                    $error = $newStmt->errorInfo();

                    if ($error[0] != '00000') {
                        return false;
                    } else {
                        $rowMax = $newStmt->fetch(PDO::FETCH_ASSOC);
                        if ($rowMax['maxi'] > 0) {
                            $numeroRecu = $rowMax['maxi'] + 1;
                        } else {
                            $numeroRecu = 1;
                        }
                        $upSql = 'UPDATE operations SET edit = :edit WHERE No = :No';
                        $upStmt = $this->dbh->prepare($upSql);
                        $upStmt->bindParam(':edit', $numeroRecu);
                        $upStmt->bindParam(':No', $No);
                        $upStmt->execute();
                        $error = $upStmt->errorInfo();

                        if ($error[0] != '00000') {
                            return false;
                        } else {
                            return true;
                        }

                    }
                }
                /*else{
                    return true;
                }*/
            }
        }

    }

    public function findMaxCerfaNumber($antite, $sub_antite, $pre_edit)
    {
        $sql = 'SELECT MAX(edit) AS maxi FROM operations WHERE antite = :antite AND sub_antite = :sub_antite AND pre_edit = :pre_edit';
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':pre_edit', $pre_edit);
        $stmt->bindParam(':sub_antite', $sub_antite);
        $stmt->bindParam(':antite', $antite);

        $stmt->execute();
        $error = $stmt->errorInfo();
        if ($error[0] != '00000') {
            return false;
        } else {
            $rowMax = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rowMax['maxi'];
        }
    }

    public function genererNumeroRecu($nb, $antite, $sub_antite)
    {

        if (!is_numeric($nb)) {
            var_dump($nb);
        } else {
            $pre_edit = substr(date('Y'), 2, 2);
            $newSql = 'SELECT MAX(edit) FROM operations WHERE antite = :antite AND sub_antite = :sub_antite AND pre_edit = :pre_edit';
            $newStmt = $this->dbh->prepare($newSql);
            $newStmt->bindParam(':antite', $antite);
            $newStmt->bindParam(':sub_antite', $sub_antite);
            $newStmt->bindParam(':pre_edit', $pre_edit);
            $newStmt->execute();
            $error = $newStmt->errorInfo();

            if ($error[0] != '00000') {
                var_dump($error);
            } else {
                $rowMax = $newStmt->fetch(PDO::FETCH_ASSOC);
                if ($rowMax['MAX(edit)'] > 0) {
                    $numeroRecu = $rowMax['MAX(edit)'];
                } else {
                    $numeroRecu = 0;
                }
                $upSql = 'INSERT INTO operations (last_modified, dateIntervention, statut, pre_edit, edit, antite, sub_antite) VALUES (?,?,?,?,?,?,?)';
                $upStmt = $this->dbh->prepare($upSql);
                for ($i = 0; $i < $nb; $i++) {
                    $numeroRecu++;
                    $upStmt->execute(array(date('Y-m-d'), date('Y-m-d'), intval(1), $pre_edit, $numeroRecu, $antite, $sub_antite));
                    $numerosGenere[] = $numeroRecu;
                }
                $error = $upStmt->errorInfo();
                if ($error[0] != '00000') {
                    var_dump($error);
                } else {

                    return $numerosGenere;

                }

            }


        }

    }


    /* --------------------------- END CLASS ------------------------------- */

}

?>
