<?
//************************************************************************
// Leonardo XC Server, http://leonardo.thenet.gr
//
// Copyright (c) 2004-8 by Andreadakis Manolis
//
// This program is free software. You can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License.
//
// $Id: CL_pilot.php,v 1.10 2009/12/30 14:45:34 manolis Exp $                                                                 
//
//************************************************************************


class pilot{
	var $pilotID,$serverID;

	var $valuesArray;
	var $gotValues;

	function pilot($serverID="",$pilotID="") {
		global $CONF_server_id;
		if ($pilotID!="") 	$this->pilotID=$pilotID;
		if ($serverID!="") 	$this->serverID=$serverID;

		if ($this->serverID==$CONF_server_id) {
			$this->serverID=0;
		}

	    $this->valuesArray=array( "pilotID", "serverID", 
"FirstName", "LastName","countryCode", 
"NACid", "NACmemberID", "NACclubID", "CIVL_ID",
"sponsor",  "Birthdate", "BirthdateHideMask",
"Occupation", "MartialStatus", "OtherInterests", 
"PilotLicence","BestMemory", "WorstMemory", "Training", "personalDistance", "personalHeight",
"glider", "FlyingSince", "HoursFlown", "HoursPerYear", "FavoriteLocation", "UsualLocation", 
"FavoriteBooks", "FavoriteActors", "FavoriteSingers", "FavoriteMovies", "FavoriteSite", "Sign", 
"Spiral", "Bline", "FullStall", "Sat", "AsymmetricSpiral", "Spin", "OtherAcro",
"camera", "camcorder", "Vario", "GPS", "Harness",  "Helmet", "Reserve", 
"Sex","PilotPhoto", "PersonalWebPage", "FirstOlcYear",
"clubID", 
);
		
		$this->gotValues=0;
	}

	function pilotExists() {
		global $db,$pilotsTable;
		$query="SELECT * FROM $pilotsTable WHERE pilotID=".$this->pilotID." AND serverID=".($this->serverID+0) ;
		$res= $db->sql_query($query);
  		if($res <= 0){   
			 echo "Error in pilotExists() $query<BR>";
		     return;
	    }
		if ($db->sql_numrows($res) ) return 1;
		else return 0;
	}

	function pilotMapping() {
		global $db,$pilotsTable,$remotePilotsTable;
		// first see if a mapping exists
		
		$query="SELECT * FROM $remotePilotsTable  
				WHERE	( serverID=".($this->serverID+0)." AND userID=".$this->pilotID ." ) OR 
						( remoteServerID=".($this->serverID+0)." AND remoteUserID=".$this->pilotID. " ) ";
		
		$res= $db->sql_query($query);		
		if($res <= 0){
			echo("<H3> Error in checkPilot query! $query</H3>\n");
			return array();
		}		

		$i=0;
		$map=array();
		while (  $row = $db->sql_fetchrow($res) ) {	
			$map[ $row['remoteServerID'] ][ $row['remoteUserID'] ] ++;
			$map[ $row['serverID'] ][ $row['userID'] ] ++;

			/*			
			if ($this->serverID==$row['serverID']  && $this->pilotID==$row['userID']) {
				$map[$i]['serverID']=$row['remoteServerID'];
				$map[$i]['userID']=$row['remoteUserID'];			
			} else {
				$map[$i]['serverID']=$row['serverID'];
				$map[$i]['userID']=$row['userID'];
			}				
			*/
			$i++;
		}
		
		return $map;
	}
	
	
	function isPilotLocal() {
		global $CONF_server_id;
		if (! $this->serverID || $this->serverID==$CONF_server_id) return 1;
		else return 0;	
	}

	function getAbsPath() {
		global $flightsAbsPath,$CONF_server_id;
		if ( $this->isPilotLocal() ) $sPrefix='';
		else $sPrefix=$this->serverID.'_';
		return $flightsAbsPath.'/'.$sPrefix.$this->pilotID;
		
	}

	function getRelPath() {
		global $flightsWebPath,$CONF_server_id;
		if ( $this->isPilotLocal() ) $sPrefix='';
		else $sPrefix=$this->serverID.'_';
		return $flightsWebPath.'/'.$sPrefix.$this->pilotID;
		
	}

	function createDirs() {
		$pilotPath=$this->getAbsPath();
		if (! is_dir($pilotPath) ) makeDir($pilotPath);
		
		//@mkdir($pilotPath."/flights");
		//@mkdir($pilotPath."/charts");
		//@mkdir($pilotPath."/maps");
		//@mkdir($pilotPath."/photos");
	}

	function deletePilot($deleteFlights=0,$deleteFiles=0) {
		global $db,$pilotsTable;
		
		$err=0;
		$sql="DELETE FROM $pilotsTable WHERE pilotID=".$this->pilotID." AND serverID=".$this->serverID ;
		$res= $db->sql_query($sql);
  		if($res <= 0){   
			 echo "Error deleting pilot from DB $sql<BR>";
		     $err=1;
	    }
		
		if ($deleteFiles) {
			$pilotPath=$this->getAbsPath();
			delDir($pilotPath);
		}		
		
		return $err;
	}
	
	function getFromDB() {
		global $db,$pilotsTable;
		$res= $db->sql_query("SELECT * FROM $pilotsTable WHERE pilotID=".$this->pilotID." AND serverID=".$this->serverID );
  		if($res <= 0){   
			 echo "Error getting pilot from DB<BR>";
		     return;
	    }

	    $row = $db->sql_fetchrow($res);
		foreach ($this->valuesArray as $valStr) {
			$this->$valStr=$row["$valStr"];					
		}
		$this->gotValues=1;
    }

	function putToDB($update=0) {
		global $db,$pilotsTable;

		if ($update) {
			$query="REPLACE INTO ";		
			$fl_id_1="pilotID,serverID, ";
			$fl_id_2=$this->pilotID.", ".$this->serverID.",";
		}else {
			$query="INSERT INTO ";		
			$fl_id_1="";
			$fl_id_2="";
		}


		$query.=" $pilotsTable  ( ";
		foreach ($this->valuesArray as $valStr) {
				$query.= $valStr.",";		
		}
		$query=substr($query,0,-1);

		$query.= " ) VALUES ( ";
		foreach ($this->valuesArray as $valStr) {
			$query.= "'".prep_for_DB($this->$valStr)."',";
		}
		$query=substr($query,0,-1);
		$query.= " ) ";
		// echo $query;
	    $res= $db->sql_query($query);
	    if($res <= 0){
		  echo "Error putting pilot to DB : $query<BR>";
		  return 0;
	    }		
		$this->gotValues=1;			
		return 1;
    }

}

?>