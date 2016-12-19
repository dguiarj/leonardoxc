<?php
//************************************************************************
// Leonardo XC Server, http://www.leonardoxc.net
//
// Copyright (c) 2004-2010 by Andreadakis Manolis
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
// $Id: AjAX_gliders.php,v 1.29 2012/10/17 09:45:24 manolis Exp $                                                                 
//
//************************************************************************

 	require_once dirname(__FILE__)."/EXT_config_pre.php";
	require_once "config.php";
 	require_once "EXT_config.php";
	require_once "FN_functions.php";	        
	setDEBUGfromGET();

	$op=makeSane($_REQUEST['op']);
        if (!$op) { $op="gliders_list";	}

	if ($op=="gliders_list") {
		$reqBrandID=makeSane($_GET['brandID'],2);
						
		$query="SELECT `gliderID`, `brandID`, `gliderName`, `gliderSize`, `gliderCert`, `DHV_ID`, `CertificationDate` FROM `leonardo_gliders` WHERE brandID = $reqBrandID";
		$res= $db->sql_query($query);
		if($res > 0){	
			$i=0;		
                        $rowset = array();
			while ($row = mysql_fetch_assoc($res)) {
                            array_push($rowset,$row);	
			} 			
		} else {
			$JSON_str="";
		}
		$jsonStr = json_encode(array('Records'=>$rowset));
                echo($jsonStr);
	}
?>
