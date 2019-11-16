<?php

if(!isset($_POST['submit']))
{
	//This page should not be accessed directly. Need to submit the form.
	echo "error; you need to submit the form!";
}
/*********************SENSORES**************************/
$Qsensor;
/*FACIL*/
$Sensor_Mote=$_POST['Sensor1E'];
$Divisao_Mote=$_POST['Room1E'];
$Timestamp_Mote=$_POST['TempoInicial1E'];
$Timestamp_Mote=str_replace("T"," ", $Timestamp_Mote);
$Timestampf_Mote=$_POST['TempoFinal1E'];
$Timestampf_Mote=str_replace("T"," ", $Timestampf_Mote);
$QsensorE= "SET search_path TO estufa; SELECT tempo AS timestamp, valor AS measurement FROM medi_sensor JOIN sensor ON medi_sensor.sensor_nome= sensor.nome JOIN mote ON num_mot=mot_id JOIN divisao ON divisao_id=id_div WHERE (tempo>'".$Timestamp_Mote."' AND tempo<'".$Timestampf_Mote."'AND sensor_nome='".$Sensor_Mote."' AND divisao.nome='".$Divisao_Mote."')ORDER BY timestamp ;";
/*MEDIO*/
$TipoSensor_Sensor=$_POST['Sensor1M'];
$Timestamp_Sensor=$_POST['TempoInicial1M'];
$Timestamp_Sensor=str_replace("T"," ", $Timestamp_Sensor);
$Timestampf_Sensor=$_POST['TempoFinal1M'];
$Timestampf_Sensor=str_replace("T"," ", $Timestampf_Sensor);
$QsensorM = "SET search_path TO estufa; SELECT AVG(medi_sensor.valor) AS average, divisao.nome AS room FROM medi_sensor JOIN sensor ON medi_sensor.sensor_nome = sensor.nome JOIN estufa.mote ON num_mot=mot_id JOIN estufa.divisao ON divisao_id=id_div WHERE medi_sensor.sensor_nome LIKE '".$TipoSensor_Sensor."%' AND tempo>'".$Timestamp_Sensor."' AND tempo<'".$Timestampf_Sensor."' GROUP BY divisao.nome";

/*********************ATUADORES**************************/
$Qactuator;
/*FACIL*/
$QactuatorE="SET search_path TO estufa; SELECT medi_atuador.atuador_nome AS \"Actuador\", divisao.nome AS \"Room\", medi_atuador.on_off AS \"State\" FROM medi_atuador JOIN atuador ON atuador.nome= medi_atuador.atuador_nome JOIN divisao ON divisao.id_div = atuador.divisao_id WHERE id_ma IN (SELECT MAX(id_ma) FROM estufa.medi_atuador GROUP BY atuador_nome) ORDER BY divisao.id_div ASC;";
/*MEDIO*/
$TipoAtuador_Atuador=$_POST['Actuador2M'];
$Timestamp_Atuador=$_POST['TempoInicial2M'];
$Timestamp_Atuador=str_replace("T"," ", $Timestamp_Atuador);
$Timestampf_Atuador=$_POST['TempoFinal2M'];
$Timestampf_Atuador=str_replace("T"," ", $Timestampf_Atuador);
$QactuatorM="SELECT COUNT(divisao.nome) AS change, divisao.nome AS room FROM estufa.medi_atuador AS a JOIN estufa.atuador ON atuador.nome= a.atuador_nome  JOIN estufa.divisao ON divisao.id_div = atuador.divisao_id WHERE atuador_nome LIKE '".$TipoAtuador_Atuador."%' AND tempo>'".$Timestamp_Atuador."' AND tempo>'".$Timestampf_Atuador."' AND a.on_off<> ( SELECT b.on_off FROM estufa.medi_atuador AS b WHERE a.atuador_nome= b.atuador_nome AND a.tempo> b.tempo ORDER BY b.tempo DESC LIMIT 1) GROUP BY divisao.nome";

/*********************CONFIGURAÇAO**************************/
$Qconfig;
/*FACIL*/
$TipoSensor_Config=$_POST['Sensor3E'];
$Mote1_Config=$_POST['Mote13E'];
$Mote2_Config=$_POST['Mote23E'];
$QconfigE="SET search_path TO estufa; UPDATE mote SET    divisao_id = mote_old.divisao_id FROM   mote mote_old JOIN sensor ON sensor.mot_id = mote_old.num_mot WHERE (mote.divisao_id, mote_old.divisao_id) IN ((".$Mote1_Config.",".$Mote2_Config."), (".$Mote2_Config.",".$Mote1_Config.")) RETURNING  (SELECT nome FROM sensor WHERE mot_id=mote_old.num_mot AND nome LIKE '".$TipoSensor_Config."%') AS \"Sensor Antigo\", mote_old.num_mot AS \"Num. Mote Antigo\", (SELECT nome FROM divisao WHERE id_div=mote_old.divisao_id) AS \"Room Antigo\",(SELECT nome FROM sensor WHERE mot_id=mote.num_mot AND nome LIKE '".$TipoSensor_Config."%') AS \"Sensor\", mote.num_mot AS \"Num. Mote\",(SELECT nome FROM divisao WHERE id_div=mote.divisao_id) AS \"Room\" ";
/*MEDIO*/
$QconfigM="SET search_path TO estufa; SELECT divisao.nome AS \"Room\", COUNT(DISTINCT sensor.mot_id) AS \"Mote Count\", COUNT(sensor.nome) AS \"Sensor Count\" FROM estufa.sensor JOIN estufa.mote ON num_mot=mot_id JOIN estufa.divisao ON divisao_id=id_div GROUP BY divisao.nome";

/*********************RULES**************************/
$Qrule;
/*FACIL*/
$Valor_rule=$_POST['Valor4E'];
$Sensor_rule=$_POST['Sensor4E'];
$QruleE="UPDATE estufa.regra_geral SET valor=".$Valor_rule."WHERE sensor_nome='".$Sensor_rule."' RETURNING  regra_id AS \"Rule\", (SELECT nome FROM estufa.divisao WHERE id_div=(SELECT divisao_id FROM estufa.atuador WHERE nome=atuador_nome ))AS \"Room\", valor AS \"Reference Value\" ,sensor_nome AS \"Sensor\"";
/*MEDIO*/
$QruleM="SELECT COUNT(divisao.nome) AS rules, divisao.nome AS room FROM estufa.regra_geral JOIN estufa.sensor ON estufa.regra_geral.sensor_nome= estufa.sensor.nome JOIN estufa.mote ON num_mot=mot_id JOIN estufa.divisao ON divisao_id=id_div GROUP BY divisao.nome";

/*********************ENERGIA**************************/
$Qenergy; // KWS
/*FACIL*/
$Room_Energy=$_POST['Room5E'];
$TimestampInicialE_Energy=$_POST['TempoInicial5E'];
$TimestampInicialE_Energy=str_replace("T"," ", $TimestampInicialE_Energy);
$TimestampFinalE_Energy=$_POST['TempoFinal5E'];
$TimestampFinalE_Energy=str_replace("T"," ", $TimestampFinalE_Energy);
$QenergyE = "SET search_path TO estufa; SELECT (medi_sensor.valor/3600) *220*1 AS energy, tempo AS timestamp FROM medi_sensor JOIN sensor ON medi_sensor.sensor_nome = sensor.nome JOIN mote ON num_mot=mot_id JOIN divisao ON divisao_id=id_div WHERE medi_sensor.sensor_nome LIKE 'corrente%' AND divisao.nome='".$Room_Energy."' AND tempo>'".$TimestampInicialE_Energy."' AND tempo<'".$TimestampFinalE_Energy."' ORDER BY tempo ASC";
/*MEDIO*/
$TimestampInicialM_Energy=$_POST['TempoInicial5M'];
$TimestampInicialM_Energy=str_replace("T"," ", $TimestampInicialM_Energy);
$TimestampFinalM_Energy=$_POST['TempoFinal5M'];
$TimestampFinalM_Energy=str_replace("T"," ", $TimestampFinalM_Energy);
$QenergyM = "SET search_path TO estufa; SELECT SUM((medi_sensor.valor/3600) *220*1*0.1544) AS cost, divisao.nome AS room FROM medi_sensor JOIN sensor ON medi_sensor.sensor_nome = sensor.nome JOIN mote ON num_mot=mot_id JOIN divisao ON divisao_id=id_div WHERE medi_sensor.sensor_nome LIKE 'corrente%' AND tempo>'".$TimestampInicialM_Energy."' AND tempo<'".$TimestampFinalM_Energy."' GROUP BY divisao.nome";



/*********************TROCA MOTS PARA ORIGINAL**************************/
$Qother = "UPDATE mote SET divisao_id = mote_old.divisao_id FROM mote mote_old JOIN sensor ON sensor.mot_id = mote_old.num_mot WHERE (mote.divisao_id, mote_old.divisao_id) IN ((".$Mote1_Config.",".$Mote2_Config."), (".$Mote2_Config.",".$Mote1_Config."))";

$link = $print_connect  = "";
$print_tableA = $print_tableR = $print_tableC = $print_tableO = $print_tableS = $print_tableE = "<br>";
$print_queryA = $print_queryR = $print_queryC = $print_queryS = $print_queryO = $print_queryE = "";
$numrowsA = $numrowsR = $numrowsC = $numrowsS = $numrowsO = $numrowsE = "";
$numfieldsA = $numfieldsR = $numfieldsC = $numfieldsS = $numfieldsO = $numfieldsE = "";
$SdataPoints = array();
$AdataPoints = array();
$C1dataPoints = array();
$C2dataPoints = array();
$RdataPoints = array();
$R1dataPoints = array();
$R2dataPoints = array();
$EdataPoints = array();
$E1dataPoints = array();
$E2dataPoints = array();
$E3dataPoints = array();

//radio data
$QComplexity = $_POST['Complexity'];
$Debug = $_POST['Debug'];

//grafical variables Config
$CfieldDivision = "room";
$CfieldQuantS = "sensor";
$CfieldQuantA = "actuator";

//grafical variables Sensor
$SfieldTime = "timestamp";
$SfieldReading = "measurement";

$SfieldDivision = "room";
$SfieldAverage = "average";

//grafical variables Actuator
$AfieldDivision = "room";
$AfieldChange = "change";

//grafical variables Rules
$RfieldQuant = "rules";
$RfieldDivision = "room";

$RfieldRead = "measurement";
$RfieldRef = "reference";
$RfieldTime = "timestamp";

//grafical variables energy
$EfieldTime = "timestamp";
$EfieldReading = "energy";

$EfieldQuant = "cost";
$EfieldDivision = "room";

$EfieldQuantP = "peak";
$EfieldQuantOFF = "offpeak";

//db connection **************************************************************************************

   $connect_sting = "host=db.fe.up.pt dbname=sinf19a38 user=sinf19a38 password=UbwJSLsu";

   $link = pg_Connect($connect_sting);
   if(!$link) {
         $print_connect = "Error : Unable to open database";
      } else {
         $print_connect = "Opened database successfully";

		}

//Query for sensor **************************************************************************************
if($QComplexity == "Easy"){
	$Qsensor= $QsensorE;
}elseif ($QComplexity == "Medium"){
	$Qsensor= $QsensorM;
}

if(!empty($Qsensor)){

	$resultS = pg_exec($link, $Qsensor);
	if(!$resultS) {

	$print_queryS = "Query «" . $Qsensor . "» error: " . pg_last_error($link);
		} else {
			$print_queryS = "Query «" . $Qsensor . "» executed successfully";

			$numrowsS = pg_numrows($resultS);
			$numfieldsS = pg_num_fields($resultS);
			if($QComplexity == "Easy"){

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsS; $ri++) {
				 $rowS = pg_fetch_array($resultS, $ri);
				 $time = (string)$rowS[$SfieldTime];
				 $print_tableS .= $SfieldTime . "= " . $rowS[$SfieldTime] . " " . $SfieldReading . "= " . $rowS[$SfieldReading];
				 array_push($SdataPoints, array("label" => $time, "y" => $rowS[$SfieldReading]));
				}

			}elseif ($QComplexity == "Medium") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsS; $ri++) {
				 $rowS = pg_fetch_array($resultS, $ri);
				 $division = (string)$rowS[$SfieldDivision];
				 $print_tableS .= $SfieldDivision . "= " . $rowS[$SfieldDivision] . " " . $SfieldAverage . "= " . $rowS[$SfieldAverage];
				 array_push($SdataPoints, array("label" => $division, "y" => $rowS[$SfieldAverage]));

				}

			}elseif ($QComplexity == "Hard") {
				$print_tableS = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsS; $j++) {
						$fieldnameS = pg_field_name($resultS, $j);
						$print_tableS .= "<th> $fieldnameS </th>";
				}
				$print_tableS .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsS; $ri++) {
				 $print_tableS .= "<tr>";
				 $rowS = pg_fetch_array($resultS, $ri);

				 for ($j = 0; $j < $numfieldsS; $j++) {
							$fieldnameS = pg_field_name($resultS, $j);
							$print_tableS .= "<td>" . $rowS[$fieldnameS] . "</td>";
					}
					$print_tableS .=  "</tr>";
				}
			}else {
				// code...
			}

		}
}

//Query for actuator **************************************************************************************
if($QComplexity == "Easy"){
	$Qactuator= $QactuatorE;
}elseif ($QComplexity == "Medium"){
	$Qactuator= $QactuatorM;
}
if(!empty($Qactuator)){

	$resultA = pg_exec($link, $Qactuator);
	if(!$resultA) {

	$print_queryA = "Query «" . $Qactuator . "» error: " . pg_last_error($link);
		} else {
			$print_queryA = "Query «" . $Qactuator . "» executed successfully";

			$numrowsA = pg_numrows($resultA);
			$numfieldsA = pg_num_fields($resultA);


			if($QComplexity == "Easy"){
				$print_tableA = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsA; $j++) {
						$fieldnameA = pg_field_name($resultA, $j);
						$print_tableA .= "<th> $fieldnameA </th>";
				}
				$print_tableA .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsA; $ri++) {
				 $print_tableA .= "<tr>";
				 $rowA = pg_fetch_array($resultA, $ri);

				 for ($j = 0; $j < $numfieldsA; $j++) {
							$fieldnameA = pg_field_name($resultA, $j);
							$print_tableA .= "<td>" . $rowA[$fieldnameA] . "</td>";
					}
					$print_tableA .=  "</tr>";
				}

			}elseif ($QComplexity == "Medium") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsA; $ri++) {
				 $rowA = pg_fetch_array($resultA, $ri);
				 $division = (string)$rowA[$AfieldDivision];
				 $print_tableA .= $AfieldDivision . "= " . $rowA[$AfieldDivision] . " " . $AfieldChange . "= " . $rowA[$AfieldChange];
				 array_push($AdataPoints, array("label" => $division, "y" => $rowA[$AfieldChange]));

				}

			}elseif ($QComplexity == "Hard") {
				$print_tableA = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsA; $j++) {
						$fieldnameA = pg_field_name($resultA, $j);
						$print_tableA .= "<th> $fieldnameA </th>";
				}
				$print_tableA .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsA; $ri++) {
				 $print_tableA .= "<tr>";
				 $rowA = pg_fetch_array($resultA, $ri);

				 for ($j = 0; $j < $numfieldsA; $j++) {
							$fieldnameA = pg_field_name($resultA, $j);
							$print_tableA .= "<td>" . $rowA[$fieldnameA] . "</td>";
					}
					$print_tableA .=  "</tr>";
				}
			}else {
				// code...
			}
}

}

//Query for Configuration **************************************************************************************
if($QComplexity == "Easy"){
	$Qconfig= $QconfigE;
}elseif ($QComplexity == "Medium"){
	$Qconfig= $QconfigM;
}
if(!empty($Qconfig)){

	$resultC = pg_exec($link, $Qconfig);
	if(!$resultC) {

	$print_queryC = "Query «" . $Qconfig . "» error: " . pg_last_error($link);
		} else {
			$print_queryC = "Query «" . $Qconfig . "» executed successfully";

			$numrowsC = pg_numrows($resultC);
			$numfieldsC = pg_num_fields($resultC);


			if($QComplexity == "Easy"){
				$print_tableC = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsC; $j++) {
						$fieldnameC = pg_field_name($resultC, $j);
						$print_tableC .= "<th> $fieldnameC </th>";
				}
				$print_tableC .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsC; $ri++) {
				 $print_tableC .= "<tr>";
				 $rowC = pg_fetch_array($resultC, $ri);

				 for ($j = 0; $j < $numfieldsC; $j++) {
							$fieldnameC = pg_field_name($resultC, $j);
							$print_tableC .= "<td>" . $rowC[$fieldnameC] . "</td>";
					}
					$print_tableC .=  "</tr>";
				}

			}elseif ($QComplexity == "Medium") {

				$print_tableC = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsC; $j++) {
						$fieldnameC = pg_field_name($resultC, $j);
						$print_tableC .= "<th> $fieldnameC </th>";
				}
				$print_tableC .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsC; $ri++) {
				 $print_tableC .= "<tr>";
				 $rowC = pg_fetch_array($resultC, $ri);

				 for ($j = 0; $j < $numfieldsC; $j++) {
							$fieldnameC = pg_field_name($resultC, $j);
							$print_tableC .= "<td>" . $rowC[$fieldnameC] . "</td>";
					}
					$print_tableC .=  "</tr>";
				}

			}elseif ($QComplexity == "Hard") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsC; $ri++) {
				 $rowC = pg_fetch_array($resultC, $ri);
				 $division = (string)$rowC[$CfieldDivision];
				 $print_tableC .= $CfieldDivision . "= " . $rowC[$CfieldDivision] . " " . $CfieldQuantS . "= " . $rowC[$CfieldQuantS] . " " . $CfieldQuantA . "= " . $rowC[$CfieldQuantA];
				 array_push($C1dataPoints, array("label" => $division, "y" => $rowC[$CfieldQuantS]));
				 array_push($C2dataPoints, array("label" => $division, "y" => $rowC[$CfieldQuantA]));

				}
			}else {
				// code...
			}
}

}

//Query for Rules **************************************************************************************
if($QComplexity == "Easy"){
	$Qrule= $QruleE;
}elseif ($QComplexity == "Medium"){
	$Qrule= $QruleM;
}
if(!empty($Qrule)){

	$resultR = pg_exec($link, $Qrule);
	if(!$resultR) {

	$print_queryR = "Query «" . $Qrule . "» error: " . pg_last_error($link);
		} else {
			$print_queryR = "Query «" . $Qrule . "» executed successfully";

			$numrowsR = pg_numrows($resultR);
			$numfieldsR = pg_num_fields($resultR);

			if($QComplexity == "Easy"){
				$print_tableR = "<tr>";
			 // Loop on fields in the result set.
			 for ($j = 0; $j < $numfieldsR; $j++) {
						$fieldnameR = pg_field_name($resultR, $j);
						$print_tableR .= "<th> $fieldnameR </th>";
				}
				$print_tableR .= "</tr>";

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsR; $ri++) {
				 $print_tableR .= "<tr>";
				 $rowR = pg_fetch_array($resultR, $ri);

				 for ($j = 0; $j < $numfieldsR; $j++) {
							$fieldnameR = pg_field_name($resultR, $j);
							$print_tableR .= "<td>" . $rowR[$fieldnameR] . "</td>";
					}
					$print_tableR .=  "</tr>";
				}

			}elseif ($QComplexity == "Medium") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsR; $ri++) {
				 $rowR = pg_fetch_array($resultR, $ri);
				 $division = (string)$rowR[$RfieldDivision];
				 $print_tableR .= $RfieldDivision . "= " . $rowR[$RfieldDivision] . " " . $RfieldQuant . "= " . $rowR[$RfieldQuant];
				 array_push($RdataPoints, array("label" => $division, "y" => $rowR[$RfieldQuant]));
			 }

			}elseif ($QComplexity == "Hard") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsR; $ri++) {
				 $rowR = pg_fetch_array($resultR, $ri);
				 $time = (string)$rowR[$RfieldTime];
				 $print_tableR .= $RfieldTime . "= " . $rowR[$RfieldTime] . " " . $RfieldRef . "= " . $rowR[$RfieldRef] . " " . $RfieldRead . "= " . $rowR[$RfieldRead];
				 array_push($R1dataPoints, array("label" => $time, "y" => $rowR[$RfieldRef]));
				 array_push($R2dataPoints, array("label" => $time, "y" => $rowR[$RfieldRead]));

				}
			}else {
				// code...
			}
}

}


//Query for Energy **************************************************************************************
if($QComplexity == "Easy"){
	$Qenergy= $QenergyE;
}elseif ($QComplexity == "Medium"){
	$Qenergy= $QenergyM;
}
if(!empty($Qenergy)){

	$resultE = pg_exec($link, $Qenergy);
	if(!$resultE) {

	$print_queryE = "Query «" . $Qenergy . "» error: " . pg_last_error($link);
		} else {
			$print_queryE = "Query «" . $Qenergy . "» executed successfully";

			$numrowsE = pg_numrows($resultE);
			$numfieldsE = pg_num_fields($resultE);

			if($QComplexity == "Easy"){
				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsE; $ri++) {
					$rowE = pg_fetch_array($resultE, $ri);
					$time = (string)$rowE[$EfieldTime];
					$print_tableE .= $EfieldTime . "= " . $rowE[$EfieldTime] . " " . $EfieldReading . "= " . $rowE[$EfieldReading];
					array_push($EdataPoints, array("label" => $time, "y" => $rowE[$EfieldReading]));
				}

			}elseif ($QComplexity == "Medium") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsE; $ri++) {
				 $rowE = pg_fetch_array($resultE, $ri);
				 $division = (string)$rowE[$EfieldDivision];
				 $print_tableE .= $EfieldDivision . "= " . $rowE[$EfieldDivision] . " " . $EfieldQuant . "= " . $rowE[$EfieldQuant];
				 array_push($E1dataPoints, array("label" => $division, "y" => $rowE[$EfieldQuant]));
			 }

			}elseif ($QComplexity == "Hard") {

				// Loop on rows in the result set.
				for($ri = 0; $ri < $numrowsE; $ri++) {
					$rowE = pg_fetch_array($resultE, $ri);
					$division = (string)$rowE[$EfieldDivision];
					$print_tableE .= $EfieldDivision . "= " . $rowE[$EfieldDivision] . " " . $EfieldQuantP . "= " . $rowE[$EfieldQuantP] . " " . $EfieldQuantOFF . "= " . $rowE[$EfieldQuantOFF];
					array_push($E2dataPoints, array("label" => $division, "y" => $rowE[$EfieldQuantP]));
					array_push($E3dataPoints, array("label" => $division, "y" => $rowE[$EfieldQuantOFF]));

				}
			}else {
				// code...
			}
}

}

if($QComplexity=='Easy')
	pg_exec($link, $Qother);

$Qother="SET search_path TO estufa; SELECT nome AS \"Nome Divisão\" FROM divisao";
//Other Queries **************************************************************************************
if(!empty($Qother)){
	$resultO = pg_exec($link, $Qother);
	if(!$resultO) {

	$print_queryO = "Query «" . $Qother . "» error: " . pg_last_error($link);
		} else {
			$print_queryO = "Query «" . $Qother . "» executed successfully";

			$numrowsO = pg_numrows($resultO);
			$numfieldsO = pg_num_fields($resultO);

			$print_tableO = "<tr>";
		 // Loop on fields in the result set.
		 for ($j = 0; $j < $numfieldsO; $j++) {
					$fieldnameO = pg_field_name($resultO, $j);
					$print_tableO .= "<th> $fieldnameO </th>";
			}
			$print_tableO .= "</tr>";

			// Loop on rows in the result set.
			for($ri = 0; $ri < $numrowsO; $ri++) {
			 $print_tableO .= "<tr>";
			 $rowO = pg_fetch_array($resultO, $ri);

			 for ($j = 0; $j < $numfieldsO; $j++) {
						$fieldnameO = pg_field_name($resultO, $j);
						$print_tableO .= "<td>" . $rowO[$fieldnameO] . "</td>";
				}
				$print_tableO .=  "</tr>";
			}
}
}
$Qother1="SET search_path TO estufa; SELECT sensor.nome AS \"Nome Sensor\", mote.num_mot AS \"Numero Mote\", divisao.nome AS \"Nome Divisão\" FROM sensor JOIN estufa.mote ON num_mot=mot_id  JOIN estufa.divisao ON divisao_id=id_div ";
//Other Queries **************************************************************************************
if(!empty($Qother1)){
	$resultO1 = pg_exec($link, $Qother1);
	if(!$resultO1) {

	$print_queryO1 = "Query «" . $Qother1 . "» error: " . pg_last_error($link);
		} else {
			$print_queryO1 = "Query «" . $Qother1 . "» executed successfully";

			$numrowsO1 = pg_numrows($resultO1);
			$numfieldsO1 = pg_num_fields($resultO1);

			$print_tableO1 = "<tr>";
		 // Loop on fields in the result set.
		 for ($j = 0; $j < $numfieldsO1; $j++) {
					$fieldnameO1 = pg_field_name($resultO1, $j);
					$print_tableO1 .= "<th> $fieldnameO1 </th>";
			}
			$print_tableO1 .= "</tr>";

			// Loop on rows in the result set.
			for($ri = 0; $ri < $numrowsO1; $ri++) {
			 $print_tableO1 .= "<tr>";
			 $rowO = pg_fetch_array($resultO1, $ri);

			 for ($j = 0; $j < $numfieldsO1; $j++) {
						$fieldnameO1 = pg_field_name($resultO1, $j);
						$print_tableO1 .= "<td>" . $rowO[$fieldnameO1] . "</td>";
				}
				$print_tableO1 .=  "</tr>";
		}
	}
}

$Qother2="SET search_path TO estufa; SELECT atuador.nome AS \"Atuador Sensor\", divisao.nome AS \"Nome Divisão\" FROM atuador JOIN estufa.divisao ON divisao_id=id_div ";
//Other Queries **************************************************************************************
if(!empty($Qother2)){
	$resultO2 = pg_exec($link, $Qother2);
	if(!$resultO2) {

	$print_queryO2 = "Query «" . $Qother2 . "» error: " . pg_last_error($link);
		} else {
			$print_queryO2 = "Query «" . $Qother2 . "» executed successfully";

			$numrowsO2 = pg_numrows($resultO2);
			$numfieldsO2 = pg_num_fields($resultO2);

			$print_tableO2 = "<tr>";
		 // Loop on fields in the result set.
		 for ($j = 0; $j < $numfieldsO2; $j++) {
					$fieldnameO2 = pg_field_name($resultO2, $j);
					$print_tableO2 .= "<th> $fieldnameO2 </th>";
			}
			$print_tableO2 .= "</tr>";

			// Loop on rows in the result set.
			for($ri = 0; $ri < $numrowsO2; $ri++) {
			 $print_tableO2 .= "<tr>";
			 $rowO = pg_fetch_array($resultO2, $ri);

			 for ($j = 0; $j < $numfieldsO2; $j++) {
						$fieldnameO2 = pg_field_name($resultO2, $j);
						$print_tableO2 .= "<td>" . $rowO[$fieldnameO2] . "</td>";
				}
				$print_tableO2 .=  "</tr>";
		}
	}
}
// php end **************************************************************************************
?>

<!DOCTYPE html>

    <html class="no-js">
    <head>
        <meta charset="utf-8">
        <title>SINF 2018/19</title>
    	  <meta name="description" content="">
		    <meta name="keywords" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<style>
		
hr.style2 {
	border-top: 8px solid  #8c8b8b;
	
	max-width: 80%;
	 margin: 0px;
	margin-left:15% !important; margin-right:15% !important;
}

.zoom:hover {

  zoom: 130%;
}
		</style>
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/font-awesome.css">
        <link rel="stylesheet" href="css/animate.css">
        <link rel="stylesheet" href="css/templatemo_misc.css">
        <link rel="stylesheet" href="css/templatemo_style.css">

        <script src="js/vendor/modernizr-2.6.1-respond-1.1.0.min.js"></script>
		<script language="JavaScript" src="js/gen_validatorv31.js" type="text/javascript"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    </head>
    <body  >

        <div class="site-main" id="sTop">
            <div class="site-header">
                
                <div class="main-header  navbar-fixed-top">
                    <div class="container">
                        <div id="menu-wrapper">
                            <div class="row">
                                <div class="logo-wrapper col-md-4 col-sm-2 col-xs-8">
                                    <h1>
                                        <a href="index.html" >Hydroponic greenhouse</a>
                                    </h1>
                                </div> <!-- /.logo-wrapper -->
                                <div class="col-md-8 col-sm-10 col-xs-4 main-menu text-right ">
                                    <ul class="menu-first hidden-sm hidden-xs ">
                                        <li><a href="#sensor">Mote</a></li>
                                        <li><a href="#actuators">Actuators</a></li>
                                        <li><a href="#config">Configurations</a></li>
										<li><a href="#rules">Rules</a></li>
										<li><a href="#energy">Energy</a></li>
										<li><a href="#other">List</a></li>
                                    </ul>
                                    <a href="#" class="toggle-menu visible-sm visible-xs"><i class="fa fa-bars"></i></a>
                                </div> <!-- /.main-menu -->
                            </div> <!-- /.row -->
                        </div> <!-- /#menu-wrapper -->
                        <div class="menu-responsive hidden-md hidden-lg">
                            <ul>
                              <li><a href="#sensor"> Mote</a></li>
                              <li><a href="#actuators">Actuators</a></li>
                              <li><a href="#config">Configurations</a></li>
							  <li><a href="#rules">Rules</a></li>
							  <li><a href="#energy">Energy</a></li>
							  <li><a href="#other">List</a></li>
                            </ul>
                        </div> <!-- /.menu-responsive -->
                    </div> <!-- /.container -->
                </div> <!-- /.main-header -->
            </div> <!-- /.site-header -->
            <div class="site-slider ">
                <div class="slider">
                    <div class="flexslider">
                        <ul class="slides">
                            <li>
                                <div class="overlay"></div>
                                <img src="images/D13_1.jpg" alt="">
                                <div class="slider-caption visible-md visible-lg"></div>
                            </li>
                        </ul>
                    </div> <!-- /.flexslider -->
                </div> <!-- /.slider -->
            </div> <!-- /.site-slider -->
        </div> <!-- /.site-main -->
		
		<!-- ****************SENSOR*********** -->
        <div class="content-section" id="sensor"> <!-- start sensor content section -->
            <div class="container">
                <div class="row">
                    <div class="heading-section col-md-12 text-center">
                        <h2>Mote</h2>
                        <p>View Sensor Readings History</p>
                    </div> <!-- /.heading-section -->
					<div class="panel-group" id="accordion">
					<?php if ($QComplexity == "Easy") { ?>
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Mote1">
							Query Easy</a>
						  </h4>
						</div>
						<div id="Mote1" class="panel-collapse collapse ">
						    <div class="panel-body zoom"><pre ><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT</code> tempo <code style="color:#ff5050">AS</code> timestamp, valor <code style="color:#ff5050">AS</code> measurement
<code style="color:#ff5050">FROM</code> medi_sensor 
    <code style="color:#ff5050">JOIN</code> sensor <code style="color:#ff5050">ON</code> medi_sensor.sensor_nome= sensor.nome 
    <code style="color:#ff5050">JOIN</code> mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
    <code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao_id=id_div 
<code style="color:#ff5050">WHERE</code> (tempo>'<code style="color:#009933"><?echo $Timestamp_Mote?></code>' <code style="color:#ff5050">AND</code> tempo<'<code style="color:#009933"><?echo $Timestampf_Mote?></code>' <code style="color:#ff5050">AND</code> sensor_nome='<code style="color:#009933"><?echo $Sensor_Mote?></code>' <code style="color:#ff5050">AND</code> divisao.nome='<code style="color:#009933"><?echo $Divisao_Mote?></code>')
<code style="color:#ff5050">ORDER BY</code> timestamp ;
</pre>
						
							</div>
						</div>
					  </div>
					  <?php } else { ?>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Mote2">
							Query Medium</a>
						  </h4>
						</div>
						<div id="Mote2" class="panel-collapse collapse">
						  <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT AVG</code>(medi_sensor.valor) <code style="color:#ff5050">AS</code> average, divisao.nome <code style="color:#ff5050">AS</code> room 
<code style="color:#ff5050">FROM</code> medi_sensor 
     <code style="color:#ff5050">JOIN</code> sensor <code style="color:#ff5050">ON</code> medi_sensor.sensor_nome = sensor.nome
     <code style="color:#ff5050">JOIN</code> mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
     <code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao_id=id_div
<code style="color:#ff5050">WHERE</code> medi_sensor.sensor_nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933"><? echo $TipoSensor_Sensor?>%</code>' <code style="color:#ff5050">AND</code> tempo>'<code style="color:#009933"><? echo $Timestamp_Sensor?></code>' <code style="color:#ff5050">AND</code> tempo<'<code style="color:#009933"><? echo $Timestampf_Sensor?></code>'
<code style="color:#ff5050">GROUP BY</code> divisao.nome</pre></div>
						</div>
					  </div>
					 <? } ?>
					</div>
					
                </div> <!-- /.row -->
				<br><br><br>
				<div class="row" id="Sde_bug">
                  <p>Connection to DB: <?php echo $print_connect; ?></p>
                  <p>Query Execution: <?php echo $print_queryS; ?></p>
                  <p>Number of Rows: <?php echo $numrowsS; ?></p>
                  <p>Numbers of Fields: <?php echo $numfieldsS; ?></p>
				  <p>Complexity: <?php echo $QComplexity; ?></p>
				  <p>Results: <?php echo $print_tableS; ?></p>
				</div><!-- /.row -->
				<p style="font-size: 20px;"><?php if($QComplexity=='Easy') {
													echo '<b> <u>Sensor</u>: </b> '.$Sensor_Mote.'<br><b> <u>Room</u>:</b> '.$Divisao_Mote."<br><b> <u>Timestamp</u>:</b> ".$Timestamp_Mote." <strong>&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;</strong> ".$Timestampf_Mote;
				}else if($QComplexity=='Medium') {
				echo '<b> <u>Sensor Type</u>: </b> '.$TipoSensor_Sensor.'<br><b>  <u>Timestamp</u>:</b>'.$Timestamp_Sensor. " <strong>&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;</strong> ". $Timestampf_Sensor;}
				?></p>
				<div class="row">
					<div id="Seasy">
						<div id="chartContainerSeasy" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Smedium">
						<div id="chartContainerSmedium" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Shard">
						<div class="w3-container">
                        <table class="w3-table-all w3-centered ">
                        <?php echo $print_tableS; ?>
                        </table>
                    	</div>
					</div>				
				</div><!-- /.row -->

        	</div> <!-- /.container -->
			
        </div> <!-- /#sensor -->
	<div class="content-section">
		<hr class="style2" >
	</div>
	
			<!-- ****************ATUADOR*********** -->
        <div class="content-section" id="actuators"> <!-- start actuators content section -->
		
            <div class="container">
                <div class="row">
                    <div class="heading-section col-md-12 text-center">
						<h2>Actuators</h2>
                        <p>Monitor State of Actuators</p>
                    </div> <!-- /.heading-section -->
					<div class="panel-group" id="accordion">
					<?php if ($QComplexity == "Easy") { ?>
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Actuators1">
							Query Easy</a>
						  </h4>
						</div>
						<div id="Actuators1" class="panel-collapse collapse ">
						    <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT</code> medi_atuador.atuador_nome <code style="color:#ff5050">AS</code> "<code style="color:#009933">Actuador</code>", divisao.nome <code style="color:#ff5050">AS</code> "<code style="color:#009933">Room</code>", medi_atuador.on_off <code style="color:#ff5050">AS</code> "<code style="color:#009933">State</code>"
<code style="color:#ff5050">FROM</code> medi_atuador
     <code style="color:#ff5050">JOIN</code> atuador <code style="color:#ff5050">ON</code> atuador.nome= medi_atuador.atuador_nome
     <code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao.id_div = atuador.divisao_id
<code style="color:#ff5050">WHERE</code> id_ma IN (<code style="color:#ff5050">SELECT</code> <code style="color:#ff5050">MAX</code>(id_ma) 
		<code style="color:#ff5050">FROM</code> estufa.medi_atuador 
		<code style="color:#ff5050">GROUP BY</code> atuador_nome)
<code style="color:#ff5050">ORDER BY</code> divisao.id_div <code style="color:#ff5050">ASC</code>;
</pre>
						
							</div>
						</div>
					  </div>
					  <?php } else { ?>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Actuators2">
							Query Medium</a>
						  </h4>
						</div>
						<div id="Actuators2" class="panel-collapse collapse">
						  <div class="panel-body zoom"><pre><code style="color:#ff5050">SELECT COUNT</code>(divisao.nome) <code style="color:#ff5050">AS</code> change, divisao.nome <code style="color:#ff5050">AS</code> room
<code style="color:#ff5050">FROM</code> estufa.medi_atuador <code style="color:#ff5050">AS</code> a
	<code style="color:#ff5050">JOIN</code> estufa.atuador <code style="color:#ff5050">ON</code> atuador.nome= a.atuador_nome
	<code style="color:#ff5050">JOIN</code> estufa.divisao <code style="color:#ff5050">ON</code> divisao.id_div = atuador.divisao_id
<code style="color:#ff5050">WHERE</code> atuador_nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933"><? echo $TipoAtuador_Atuador ?>%</code>' <code style="color:#ff5050">AND</code> tempo>'<code style="color:#009933"><? echo $Timestamp_Atuador ?></code>' <code style="color:#ff5050">AND</code> tempo<'<code style="color:#009933"><? echo $Timestampf_Atuador ?></code>' <code style="color:#ff5050">AND</code> a.on_off<>
      ( <code style="color:#ff5050">SELECT</code> b.on_off
        <code style="color:#ff5050">FROM</code> estufa.medi_atuador <code style="color:#ff5050">AS</code> b
        <code style="color:#ff5050">WHERE</code> a.atuador_nome= b.atuador_nome <code style="color:#ff5050">AND</code> a.tempo> b.tempo
        <code style="color:#ff5050">ORDER BY</code> b.tempo DESC
        <code style="color:#ff5050">LIMIT</code> 1
      )
<code style="color:#ff5050">GROUP BY</code> divisao.nome</pre></div>
						</div>
					  </div>
					 <? } ?>
					</div>
                </div> <!-- /.row -->
				<br><br><br>
				<p style="font-size: 20px;"><?php if($QComplexity=='Medium') {
				echo '<b> <u>Actuator Type</u>: </b> '.$TipoAtuador_Atuador.'<br><b>  <u>Timestamp</u>:</b>'.$Timestamp_Atuador. " <strong>&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;</strong> ".$Timestampf_Atuador ;}
				?></p>
				<div class="row" id="Ade_bug">
                 <p>Connection to DB: <?php echo $print_connect; ?></p>
                 <p>Query Execution: <?php echo $print_queryA; ?></p>
                 <p>Number of Rows: <?php echo $numrowsA; ?></p>
                 <p>Number of Fields: <?php echo $numfieldsA; ?></p>
				 <p>Complexity: <?php echo $QComplexity; ?></p>
				 <p>Results: <?php echo $print_tableA; ?></p>
               </div><!-- /.row -->

				<div class="row">
					<div id="Aeasy">
						<div class="w3-container">
							<table class="w3-table-all w3-centered">
							<?php echo $print_tableA; ?>
							</table>
						</div>
					</div>
					<div id="Amedium">
						<div id="chartContainerAmedium" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Ahard">
						<div class="w3-container">
							<table class="w3-table-all w3-centered">
							<?php echo $print_tableA; ?>
							</table>
						</div>
					</div>
				</div><!-- /.row -->
            </div> <!-- /.container -->
        </div> <!-- /#actuator-->
	<div class="content-section">
		<hr class="style2" >
	</div>
			<!-- ****************CONFIGURATION*********** -->
        <div class="content-section" id="config"> <!-- start config content section -->
            <div class="container">
                <div class="row">
                    <div class="heading-section col-md-12 text-center">
                        <h2>Configuration</h2>
                        <p>View and Edit <b><i>HAS</i></b> Configurations</p>
                    </div> <!-- /.heading-section -->
					<div class="panel-group" id="accordion">
					<?php if ($QComplexity == "Easy") { ?>
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Configuration1">
							Query Easy</a>
						  </h4>
						</div>
						<div id="Configuration1" class="panel-collapse collapse ">
						    <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">UPDATE</code> mote
<code style="color:#ff5050">SET</code> divisao_id = mote_old.divisao_id
<code style="color:#ff5050">FROM</code> mote mote_old
     <code style="color:#ff5050">JOIN</code> sensor <code style="color:#ff5050">ON</code> sensor.mot_id = mote_old.num_mot
<code style="color:#ff5050">WHERE</code> (mote.divisao_id, mote_old.divisao_id) <code style="color:#ff5050">IN</code> ((<code style="color:#cca300"><? echo $Mote1_Config ?></code>,<code style="color:#cca300"><? echo $Mote2_Config ?></code>), (<code style="color:#cca300"><? echo $Mote2_Config ?></code>,<code style="color:#cca300"><? echo $Mote1_Config ?></code>))
<code style="color:#ff5050">RETURNING</code>  (<code style="color:#ff5050">SELECT</code> nome <code style="color:#ff5050">FROM</code> sensor <code style="color:#ff5050">WHERE</code> mot_id=mote_old.num_mot <code style="color:#ff5050">AND</code> nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933"><? echo $TipoSensor_Config ?>%</code>') <code style="color:#ff5050">AS</code> "<code style="color:#009933">Sensor Antigo</code>", 
	   mote_old.num_mot <code style="color:#ff5050">AS</code> "<code style="color:#009933">Num. Mote Antigo</code>", 
	   (<code style="color:#ff5050">SELECT</code> nome <code style="color:#ff5050">FROM</code> divisao <code style="color:#ff5050">WHERE</code> id_div=mote_old.divisao_id) <code style="color:#ff5050">AS</code> "<code style="color:#009933">Room Antigo</code>",
	   (<code style="color:#ff5050">SELECT</code> nome <code style="color:#ff5050">FROM</code> sensor <code style="color:#ff5050">WHERE</code> mot_id=mote.num_mot <code style="color:#ff5050">AND</code> nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933"><? echo $TipoSensor_Config ?>%</code>') <code style="color:#ff5050">AS</code> "<code style="color:#009933">Sensor</code>",
	   mote.num_mot <code style="color:#ff5050">AS</code> "<code style="color:#009933">Num. Mote</code>",
	   (<code style="color:#ff5050">SELECT</code> nome <code style="color:#ff5050">FROM</code> divisao <code style="color:#ff5050">WHERE</code> id_div=mote.divisao_id) <code style="color:#ff5050">AS</code> "<code style="color:#009933">Room</code>" 
</pre>
						
							</div>
						</div>
					  </div>
					  <?php } else { ?>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Configuration2">
							Query Medium</a>
						  </h4>
						</div>
						<div id="Configuration2" class="panel-collapse collapse">
						  <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT</code> divisao.nome <code style="color:#ff5050">AS</code> "<code style="color:#009933">Room</code>", <code style="color:#ff5050">COUNT</code>(DISTINCT sensor.mot_id) <code style="color:#ff5050">AS</code> "<code style="color:#009933">Mote Count</code>", <code style="color:#ff5050">COUNT</code>(sensor.nome) <code style="color:#ff5050">AS</code> "<code style="color:#009933">Sensor Count</code>"
<code style="color:#ff5050">FROM</code> sensor
	<code style="color:#ff5050">JOIN</code> mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
	<code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao_id=id_div
<code style="color:#ff5050">GROUP BY</code> divisao.nome</pre></div>
						</div>
					  </div>
					 <? } ?>
					</div>
                </div> <!-- /.row -->
								
				<div class="row" id="Cde_bug">
                  <p>Connection to DB: <?php echo $print_connect; ?></p>
                  <p>Query Execution: <?php echo $print_queryC; ?></p>
                  <p>Number of Rows: <?php echo $numrowsC; ?></p>
                  <p>Number of Fields: <?php echo $numfieldsC; ?></p>
 				  <p>Complexity: <?php echo $QComplexity; ?></p>
 				  <p>Results: <?php echo $print_tableC; ?></p>
                </div><!-- /.row -->
				<br><br><br>
				<p style="font-size: 20px;"><?php if($QComplexity=='Easy') {
				echo '<b> <u>Sensor Type</u>: </b> '.$TipoSensor_Config.'<br><b>  <u>Mote1</u>:</b> '.$Mote1_Config . '<br><b><u>Mote2</u>:</b> '.$Mote2_Config;}
				?></p>
				<div class="row">
					<div id="Ceasy">
						<div class="w3-container">
							<table class="w3-table-all w3-centered">
							<?php echo $print_tableC; ?>
							</table>
						</div>
					</div>
					<div id="Cmedium">
						<div class="w3-container">
							<table class="w3-table-all w3-centered">
							<?php echo $print_tableC; ?>
							</table>
						</div>
					</div>
					<div id="Chard">
						<div id="chartContainerChard" style="height: 370px; width: 100%;"></div>
					</div>
				</div><!-- /.row -->
            </div> <!-- /.container -->
        </div> <!-- /#config -->
	<div class="content-section">
		<hr class="style2" >
	</div>
				<!-- ****************RULES*********** -->
        <div class="content-section" id="rules"> <!-- start rules content section -->
            <div class="container">
                <div class="row">
                    <div class="heading-section col-md-12 text-center">
                        <h2>Control Rules</h2>
                        <p>View and Edit <b><i>HAS</i></b> Control Rules</p>
                    </div> <!-- /.heading-section -->
					<div class="panel-group" id="accordion">
					<?php if ($QComplexity == "Easy") { ?>
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Rules1">
							Query Easy</a>
						  </h4>
						</div>
						<div id="Rules1" class="panel-collapse collapse ">
						    <div class="panel-body zoom"><pre><code style="color:#ff5050">UPDATE</code> estufa.regra_geral <code style="color:#ff5050">SET</code> valor=<code style="color:#cca300"><? echo $Valor_rule ?></code>
<code style="color:#ff5050">WHERE</code> sensor_nome='<code style="color:#009933"><? echo $Sensor_rule ?></code>'
<code style="color:#ff5050">RETURNING</code>  regra_id <code style="color:#ff5050">AS</code> "<code style="color:#009933">Rule</code>", 
	   (<code style="color:#ff5050">SELECT</code> nome FROM estufa.divisao <code style="color:#ff5050">
	   WHERE</code> id_div=(<code style="color:#ff5050">SELECT</code> divisao_id 
			 <code style="color:#ff5050">FROM</code> estufa.atuador 
			 <code style="color:#ff5050">WHERE</code> nome=atuador_nome)
	   )<code style="color:#ff5050">AS</code> "<code style="color:#009933">Room</code>",
	   valor <code style="color:#ff5050">AS</code> "<code style="color:#009933">Reference Value</code>" ,
	   sensor_nome <code style="color:#ff5050">AS</code> "<code style="color:#009933">Sensor</code>"
</pre>
						
							</div>
						</div>
					  </div>
					  <?php } else { ?>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Rules2">
							Query Medium</a>
						  </h4>
						</div>
						<div id="Rules2" class="panel-collapse collapse">
						  <div class="panel-body zoom"><pre><code style="color:#ff5050">SELECT COUNT</code>(divisao.nome) <code style="color:#ff5050">AS</code> rules, divisao.nome <code style="color:#ff5050">AS</code> room
<code style="color:#ff5050">FROM</code> estufa.regra_geral 
     <code style="color:#ff5050">JOIN</code> estufa.sensor <code style="color:#ff5050">ON</code> estufa.regra_geral.sensor_nome= estufa.sensor.nome 
     <code style="color:#ff5050">JOIN</code> estufa.mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
     <code style="color:#ff5050">JOIN</code> estufa.divisao <code style="color:#ff5050">ON</code> divisao_id=id_div 
<code style="color:#ff5050">GROUP BY</code> divisao.nome</pre></div>
						</div>
					  </div>
					 <? } ?>
					</div>
				</div> <!-- /.row -->
				<br><br><br>
				
				<div class="row" id="Rde_bug">
                  <p>Connection to DB: <?php echo $print_connect; ?></p>
                  <p>Query Execution: <?php echo $print_queryR; ?></p>
                  <p>Number of Rows: <?php echo $numrowsR; ?></p>
                  <p>Number of Fields: <?php echo $numfieldsR; ?></p>
 				  <p>Complexity: <?php echo $QComplexity; ?></p>
 				  <p>Results: <?php echo $print_tableR; ?></p>
                </div><!-- /.row -->
				<p style="font-size: 20px;"><?php if($QComplexity=='Easy') {
					echo '<b> <u>Reference Value</u>: </b> '.$Valor_rule.'<br><b>  <u>Sensor</u>:</b> '.$Sensor_rule;}
				?></p>
				<div class="row">
					<div id="Reasy">
						<div class="w3-container">
						<table class="w3-table-all w3-centered">
						<?php echo $print_tableR; ?>
						</table>
						</div>
					</div>
					<div id="Rmedium">
						<div id="chartContainerRmedium" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Rhard">
						<div id="chartContainerRhard" style="height: 370px; width: 100%;"></div>
					</div>
				</div><!-- /.row -->
            </div> <!-- /.container -->
        </div> <!-- /#rules -->
	<div class="content-section">
		<hr class="style2" >
	</div>
		<!-- ****************ENERGY*********** -->
		<div class="content-section" id="energy"> <!-- start energy content section -->
            <div class="container">
                <div class="row">
                    <div class="heading-section col-md-12 text-center">
                        <h2>Energy Consumption</h2>
                        <p>Monitor Energy Consumption</p>
                    </div> <!-- /.heading-section -->
					<div class="panel-group" id="accordion">
					<?php if ($QComplexity == "Easy") { ?>
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Energy1">
							Query Easy</a>
						  </h4>
						</div>
						<div id="Energy1" class="panel-collapse collapse ">
						    <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT</code> (medi_sensor.valor/<code style="color:#cca300">3600</code>) *<code style="color:#cca300">220</code>*<code style="color:#cca300">1</code> <code style="color:#ff5050">AS</code> energy, tempo <code style="color:#ff5050">AS</code> timestamp
<code style="color:#ff5050">FROM</code> medi_sensor
    <code style="color:#ff5050">JOIN</code> sensor <code style="color:#ff5050">ON</code> medi_sensor.sensor_nome = sensor.nome
    <code style="color:#ff5050">JOIN</code> mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
    <code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao_id=id_div
<code style="color:#ff5050">WHERE</code> medi_sensor.sensor_nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933">corrente%</code>' <code style="color:#ff5050">AND</code> divisao.nome='<code style="color:#009933"><? echo $Room_Energy ?></code>' <code style="color:#ff5050">AND</code> tempo>'<code style="color:#009933"><? echo $TimestampInicialE_Energy ?></code></code>' <code style="color:#ff5050">AND</code> tempo<'<code style="color:#009933"><? echo $TimestampFinalE_Energy ?></code></code>'
<code style="color:#ff5050">ORDER BY</code> tempo ASC

</pre>
						
							</div>
						</div>
					  </div>
					  <?php } else { ?>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Energy2">
							Query Medium</a>
						  </h4>
						</div>
						<div id="Energy2" class="panel-collapse collapse">
						  <div class="panel-body zoom"><pre><code style="color:#ff5050">SET</code> search_path <code style="color:#ff5050">TO</code> estufa;

<code style="color:#ff5050">SELECT SUM</code>((medi_sensor.valor/<code style="color:#cca300">3600</code>) *<code style="color:#cca300">220</code>*<code style="color:#cca300">1</code>*<code style="color:#cca300">0.1544</code>) <code style="color:#ff5050">AS</code> cost, divisao.nome <code style="color:#ff5050">AS</code> room
<code style="color:#ff5050">FROM</code> medi_sensor
    <code style="color:#ff5050">JOIN</code> sensor <code style="color:#ff5050">ON</code> medi_sensor.sensor_nome = sensor.nome
    <code style="color:#ff5050">JOIN</code> mote <code style="color:#ff5050">ON</code> num_mot=mot_id 
    <code style="color:#ff5050">JOIN</code> divisao <code style="color:#ff5050">ON</code> divisao_id=id_div
<code style="color:#ff5050">WHERE</code> medi_sensor.sensor_nome <code style="color:#ff5050">LIKE</code> '<code style="color:#009933">corrente%</code>' <code style="color:#ff5050">AND</code> tempo>'<code style="color:#009933"><? echo $TimestampInicialM_Energy ?></code>' <code style="color:#ff5050">AND</code> tempo<'<code style="color:#009933"><? echo $TimestampFinalM_Energy ?></code>'
<code style="color:#ff5050">GROUP BY</code> divisao.nome</pre></div>
						</div>
					  </div>
					 <? } ?>
					</div>
				</div> <!-- /.row -->
				
				<div class="row" id="Ede_bug">
                  <p>Connection to DB: <?php echo $print_connect; ?></p>
                  <p>Query Execution: <?php echo $print_queryE; ?></p>
                  <p>Number of Rows: <?php echo $numrowsE; ?></p>
                  <p>Number of Fields: <?php echo $numfieldsE; ?></p>
 				  <p>Complexity: <?php echo $QComplexity; ?></p>
 				  <p>Results: <?php echo $print_tableE; ?></p>
                </div><!-- /.row -->
				<br><br><br>
				<p style="font-size: 20px;"><?php 
				if($QComplexity=='Easy') {
					echo '<b> <u>Room</u>: </b> '.$Room_Energy.'<br><b>  <u>Timestamp</u>:</b> '.$TimestampInicialE_Energy . " <strong>&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;</strong> ".$TimestampFinalE_Energy;
				}else if($QComplexity=='Medium'){
					echo '<br><b>  <u>Timestamp</u>:</b> '.$TimestampInicialM_Energy . " <strong>&nbsp;&nbsp;&nbsp;TO&nbsp;&nbsp;&nbsp;</strong> ".$TimestampFinalM_Energy;
				}
				?></p>
				<div class="row">
					<div id="Eeasy">
						<div id="chartContainerEeasy" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Emedium">
						<div id="chartContainerEmedium" style="height: 370px; width: 100%;"></div>
					</div>
					<div id="Ehard">
						<div id="chartContainerEhard" style="height: 370px; width: 100%;"></div>
					</div>
				</div><!-- /.row -->
            </div> <!-- /.container -->
        </div> <!-- /#energy -->
		
		
		<div class="content-section">
		<hr class="style2" >
		</div>

		<div class="content-section" id="other"> <!-- start other content section -->
			<div class="container">
				<div class="row">
					<div class="heading-section col-md-12 text-center">
						<h2>List</h2>
					</div> <!-- /.heading-section -->
					
					<div class="panel-group" id="accordion">
					  <div class="panel panel-default">
						<div class="panel-heading">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Divisao1">
							Room</a>
						  </h4>
						</div>
						<div id="Divisao1" class="panel-collapse collapse ">
						    <div class="panel-body"> <div class="row">
                	<div class="w3-container">
                    	<table class="w3-table-all w3-centered">
                    	<?php echo $print_tableO; ?>
                    	</table>
                	</div>
               </div><!-- /.row --> </div>
						</div>
					  </div>
					  <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Sensores1">
							Sensors</a>
						  </h4>
						</div>
						<div id="Sensores1" class="panel-collapse collapse">
						  <div class="panel-body"> <div class="row">
                	<div class="w3-container">
                    	<table class="w3-table-all w3-centered">
                    	<?php echo $print_tableO1; ?>
                    	</table>
                	</div>
               </div><!-- /.row --> </div>
						</div>
					  </div>
					 <div class="panel panel-default">
						<div class="panel-heading ">
						  <h4 class="panel-title text-center ">
							<a data-toggle="collapse" data-parent="#accordion" href="#Atuadores1">
							Actuators</a>
						  </h4>
						</div>
						<div id="Atuadores1" class="panel-collapse collapse">
						  <div class="panel-body"> <div class="row">
                	<div class="w3-container">
                    	<table class="w3-table-all w3-centered">
                    	<?php echo $print_tableO2; ?>
                    	</table>
                	</div>
               </div><!-- /.row --></div>
						</div>
					  </div>
					</div>
					
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /#other -->
		
	
		

        <div class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-xs-12 text-left">
                        Copyright © 2018/19 SINF - FEUP <br>Fábio Morais <br> Alberto Santos <br> Rita Ferreira
                    </div>
				
                    <div class="col-md-4 hidden-xs text-right">
                        <a href="#top" id="go-top">Back to top</a>
                    </div> <!-- /.text-center -->
                </div> <!-- /.row -->
            </div> <!-- /.container -->
        </div> <!-- /#footer -->


	<script>

	function myPluginLoadEvent(func)
	{
    var oldOnLoad = window.onload;
    	if (typeof window.onload != 'function')
		{
        	window.onload = func
    	} else 
		{ 
        	window.onload = function () 
			{
            oldOnLoad();
            func();
        	}
    	}
	}

	myPluginLoadEvent(function(){
    // your code to run on window.onload
		//alert('window loaded');

	var debug = <?php echo json_encode($Debug); ?>;
	var radio = <?php echo json_encode($QComplexity); ?>;

	var Qsensor = <?php echo json_encode($Qsensor); ?>;
	var Qactuator = <?php echo json_encode($Qactuator); ?>;
	var AfieldChange = <?php echo json_encode($AfieldChange); ?>;
	var Qconfig = <?php echo json_encode($Qconfig); ?>;
	var CfieldQuantS = <?php echo json_encode($CfieldQuantS); ?>;
	var CfieldQuantA = <?php echo json_encode($CfieldQuantA); ?>;
	var Qrule = <?php echo json_encode($Qrule); ?>;
	var RfieldRead = <?php echo json_encode($RfieldRead); ?>;
	var RfieldRef = <?php echo json_encode($RfieldRef); ?>;
	var RfieldQuant = <?php echo json_encode($RfieldQuant); ?>;

	var Qenergy = <?php echo json_encode($Qenergy); ?>;
	var EfieldQuant = <?php echo json_encode($EfieldQuant); ?>;
	var EfieldQuantP = <?php echo json_encode($EfieldQuantP); ?>;
	var EfieldQuantOFF = <?php echo json_encode($EfieldQuantOFF); ?>;

	var Qother = <?php echo json_encode($Qother); ?>;
	var Qother1 = <?php echo json_encode($Qother1); ?>;
	var Qother2 = <?php echo json_encode($Qother2); ?>;

//alert('window loaded' + Qconfig);
//	alert("radio =" + radio + " config = " + Qconfig);

		//Show and Hide Debug printing messages **************************************************************************************
		if(debug == "ON"){
			document.getElementById('Sde_bug').style.display = 'inline';
			document.getElementById('Ade_bug').style.display = 'inline';
			document.getElementById('Cde_bug').style.display = 'inline';
			document.getElementById('Rde_bug').style.display = 'inline';
			document.getElementById('Ede_bug').style.display = 'inline';

		}else {
			document.getElementById('Sde_bug').style.display = 'none';
			document.getElementById('Ade_bug').style.display = 'none';
			document.getElementById('Cde_bug').style.display = 'none';
			document.getElementById('Rde_bug').style.display = 'none';
			document.getElementById('Ede_bug').style.display = 'none';
		}

		//Present Qsensor results **************************************************************************************
		if(Qsensor != ""){
			if(radio == "Easy"){
				document.getElementById('Seasy').style.display = 'inline';
				document.getElementById('Smedium').style.display = 'none';
				document.getElementById('Shard').style.display = 'none';

					var SdataPoints = <?php echo json_encode($SdataPoints, JSON_NUMERIC_CHECK); ?>;

					var Schart = new CanvasJS.Chart("chartContainerSeasy", {
						theme: "light2",
						animationEnabled: true,
		//      	title: {
		//      		text: "Sensor Readings"
		//      	},
						axisX:{
							title: "Timestamp"
						},
						axisY:{
							title: "Measurement",
							includeZero: false,
							//suffix: " Cº"
						},
						data: [{
							type: "spline",
							yValueFormatString: "#,##0.0#",
						//	xValueFormatString: "hh:mm:ss TT",
						//	xValueType: "dateTime",
							toolTipContent: "{y} at {label}",
							dataPoints: SdataPoints
						}]
					});
					Schart.render();

			} else if (radio == "Medium") {
				document.getElementById('Seasy').style.display = 'none';
				document.getElementById('Smedium').style.display = 'inline';
				document.getElementById('Shard').style.display = 'none';

				var SdataPoints = <?php echo json_encode($SdataPoints, JSON_NUMERIC_CHECK); ?>;


				var Schart = new CanvasJS.Chart("chartContainerSmedium", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Average",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
						yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					//	toolTipContent: "{y} at {label}",
						dataPoints: SdataPoints
					}]
				});
				Schart.render();

			} else if (radio == "Hard") {
				document.getElementById('Seasy').style.display = 'none';
				document.getElementById('Smedium').style.display = 'none';
				document.getElementById('Shard').style.display = 'inline';

			} else {
				document.getElementById('Seasy').style.display = 'none';
				document.getElementById('Smedium').style.display = 'none';
				document.getElementById('Shard').style.display = 'none';
			}
		} else {
			document.getElementById('Seasy').style.display = 'none';
			document.getElementById('Smedium').style.display = 'none';
			document.getElementById('Shard').style.display = 'none';
		}

		//Present Qactuator results **************************************************************************************
		if(Qactuator != ""){
			if(radio == "Easy"){
				document.getElementById('Aeasy').style.display = 'inline';
				document.getElementById('Amedium').style.display = 'none';
				document.getElementById('Ahard').style.display = 'none';

			} else if (radio == "Medium") {
				document.getElementById('Aeasy').style.display = 'none';
				document.getElementById('Amedium').style.display = 'inline';
				document.getElementById('Ahard').style.display = 'none';

				var AdataPoints = <?php echo json_encode($AdataPoints, JSON_NUMERIC_CHECK); ?>;


				var Achart = new CanvasJS.Chart("chartContainerAmedium", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Number of Changes",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
						toolTipContent: "{y} " + AfieldChange + " at {label}",
						//yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					//	toolTipContent: "{y} at {label}",
						dataPoints: AdataPoints
					}]
				});
				Achart.render();

			} else if (radio == "Hard") {
				document.getElementById('Aeasy').style.display = 'none';
				document.getElementById('Amedium').style.display = 'none';
				document.getElementById('Ahard').style.display = 'inline';

			} else {
				document.getElementById('Aeasy').style.display = 'none';
				document.getElementById('Amedium').style.display = 'none';
				document.getElementById('Ahard').style.display = 'none';
			}
		} else {
			document.getElementById('Aeasy').style.display = 'none';
			document.getElementById('Amedium').style.display = 'none';
			document.getElementById('Ahard').style.display = 'none';
		}

		//Present Qconfig results **************************************************************************************
		if(Qconfig != ""){
			if(radio == "Easy"){
				document.getElementById('Ceasy').style.display = 'inline';
				document.getElementById('Cmedium').style.display = 'none';
				document.getElementById('Chard').style.display = 'none';

			} else if (radio == "Medium") {
				document.getElementById('Ceasy').style.display = 'none';
				document.getElementById('Cmedium').style.display = 'inline';
				document.getElementById('Chard').style.display = 'none';

			} else if (radio == "Hard") {
				document.getElementById('Ceasy').style.display = 'none';
				document.getElementById('Cmedium').style.display = 'none';
				document.getElementById('Chard').style.display = 'inline';

				var CdataPoints1 = <?php echo json_encode($C1dataPoints, JSON_NUMERIC_CHECK); ?>;
				var CdataPoints2 = <?php echo json_encode($C2dataPoints, JSON_NUMERIC_CHECK); ?>;


				var Cchart = new CanvasJS.Chart("chartContainerChard", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Device Count",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					toolTipContent: "{y} " + CfieldQuantS + " at {label}",
					showInLegend: true,
					legendText: CfieldQuantS,
						dataPoints: CdataPoints1
					},{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					toolTipContent: "{y} " + CfieldQuantA + " at {label}",
					showInLegend: true,
					legendText: CfieldQuantA,
						dataPoints: CdataPoints2
					}
				]
				});
				Cchart.render();

			} else {
				document.getElementById('Ceasy').style.display = 'none';
				document.getElementById('Cmedium').style.display = 'none';
				document.getElementById('Chard').style.display = 'none';
			}
		} else {
			document.getElementById('Ceasy').style.display = 'none';
			document.getElementById('Cmedium').style.display = 'none';
			document.getElementById('Chard').style.display = 'none';
		}


		//Present Qrule results **************************************************************************************
		if(Qrule != ""){
			if(radio == "Easy"){
				document.getElementById('Reasy').style.display = 'inline';
				document.getElementById('Rmedium').style.display = 'none';
				document.getElementById('Rhard').style.display = 'none';

			} else if (radio == "Medium") {
				document.getElementById('Reasy').style.display = 'none';
				document.getElementById('Rmedium').style.display = 'inline';
				document.getElementById('Rhard').style.display = 'none';

				var RdataPoints = <?php echo json_encode($RdataPoints, JSON_NUMERIC_CHECK); ?>;

				var Rchart = new CanvasJS.Chart("chartContainerRmedium", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Rule Count",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					//	toolTipContent: "{y} at {label}",
					toolTipContent: "{y} " + RfieldQuant + " at {label}",
						dataPoints: RdataPoints
					}]
				});
				Rchart.render();

			} else if (radio == "Hard") {
				document.getElementById('Reasy').style.display = 'none';
				document.getElementById('Rmedium').style.display = 'none';
				document.getElementById('Rhard').style.display = 'inline';

				var RdataPoints1 = <?php echo json_encode($R1dataPoints, JSON_NUMERIC_CHECK); ?>;
				var RdataPoints2 = <?php echo json_encode($R2dataPoints, JSON_NUMERIC_CHECK); ?>;

				var Rchart = new CanvasJS.Chart("chartContainerRhard", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Timestamp"
					},
					axisY:{
						title: "Value",
						includeZero: false,
						//suffix: " Cº"
					},
					toolTip: {
						shared: true
					},
					legend: {
						cursor:"pointer",
						verticalAlign: "top",
						fontSize: 22,
						fontColor: "dimGrey",
					},
					data: [{
						type: "spline",
						yValueFormatString: "#,##0.0#",
					  name: RfieldRef,
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					showInLegend: true,
					legendText: RfieldRef,
						//toolTipContent: "{y} at {label}",
						dataPoints: RdataPoints1
					}, {
						type: "spline",
						yValueFormatString: "#,##0.0#",
					  name: RfieldRead,
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					showInLegend: true,
					legendText: RfieldRead,
					//	toolTipContent: "{y} at {label}",
						dataPoints: RdataPoints2
					}]
				});
				Rchart.render();

			} else {
				document.getElementById('Reasy').style.display = 'none';
				document.getElementById('Rmedium').style.display = 'none';
				document.getElementById('Rhard').style.display = 'none';
			}
		} else {
			document.getElementById('Reasy').style.display = 'none';
			document.getElementById('Rmedium').style.display = 'none';
			document.getElementById('Rhard').style.display = 'none';
		}














		//Present Qenergy results **************************************************************************************
		if(Qenergy != ""){
			if(radio == "Easy"){
				document.getElementById('Eeasy').style.display = 'inline';
				document.getElementById('Emedium').style.display = 'none';
				document.getElementById('Ehard').style.display = 'none';

				var EdataPoints = <?php echo json_encode($EdataPoints, JSON_NUMERIC_CHECK); ?>;

					var Echart = new CanvasJS.Chart("chartContainerEeasy", {
						theme: "light2",
						animationEnabled: true,
		//      	title: {
		//      		text: "Sensor Readings"
		//      	},
						axisX:{
							title: "Timestamp"
						},
						axisY:{
							title: "Energy Consumption",
							includeZero: false,
							//suffix: " Cº"
						},
						data: [{
							type: "spline",
							yValueFormatString: "#,##0.0#",
						//	xValueFormatString: "hh:mm:ss TT",
						//	xValueType: "dateTime",
							toolTipContent: "{y} kWs at {label}",
							dataPoints: EdataPoints
						}]
					});
					Echart.render();

			} else if (radio == "Medium") {
				document.getElementById('Eeasy').style.display = 'none';
				document.getElementById('Emedium').style.display = 'inline';
				document.getElementById('Ehard').style.display = 'none';

				var EdataPoints1 = <?php echo json_encode($E1dataPoints, JSON_NUMERIC_CHECK); ?>;

				var Echart = new CanvasJS.Chart("chartContainerEmedium", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Energy Costs",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					//	toolTipContent: "{y} at {label}",
					toolTipContent: "{y} € at {label}",
						dataPoints: EdataPoints1
					}]
				});
				Echart.render();

			} else if (radio == "Hard") {
				document.getElementById('Eeasy').style.display = 'none';
				document.getElementById('Emedium').style.display = 'none';
				document.getElementById('Ehard').style.display = 'inline';

				var EdataPoints2 = <?php echo json_encode($E2dataPoints, JSON_NUMERIC_CHECK); ?>;
				var EdataPoints3 = <?php echo json_encode($E3dataPoints, JSON_NUMERIC_CHECK); ?>;


				var Echart = new CanvasJS.Chart("chartContainerEhard", {
					theme: "light2",
					animationEnabled: true,
	//      	title: {
	//      		text: "Sensor Readings"
	//      	},
					axisX:{
						title: "Room"
					},
					axisY:{
						title: "Energy Costs",
						includeZero: false,
						//suffix: " Cº"
					},
					data: [{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					toolTipContent: "{y} € at {label}",
					showInLegend: true,
					legendText: EfieldQuantP,
						dataPoints: EdataPoints2
					},{
						type: "column",
					//	yValueFormatString: "#,##0.0#",
					//	xValueFormatString: "hh:mm:ss TT",
					//	xValueType: "dateTime",
					toolTipContent: "{y} € at {label}",
					showInLegend: true,
					legendText: EfieldQuantOFF,
						dataPoints: EdataPoints3
					}
				]
				});
				Echart.render();

			} else {
				document.getElementById('Eeasy').style.display = 'none';
				document.getElementById('Emedium').style.display = 'none';
				document.getElementById('Ehard').style.display = 'none';
			}
		} else {
			document.getElementById('Eeasy').style.display = 'none';
			document.getElementById('Emedium').style.display = 'none';
			document.getElementById('Ehard').style.display = 'none';
		}


}); //end of load event


			</script>

			<script src="js/vendor/jquery-1.11.0.min.js"></script>
			<script src="js/bootstrap.js"></script>
			<script src="js/plugins.js"></script>
			<script src="js/main.js"></script>
			<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

    </body>
</html>
