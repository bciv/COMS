<?php

function curPageName() {
						return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
					}
				
function PostTrack($ruser,$AccessCode,$point,$pointno,$sessionid){
	include "dbitcon.php";
		//GetID
		$chkTrack = "";
		$tsql2 = "SELECT id FROM COMS_Track ORDER BY id";
		$getid = sqlsrv_query($conn, $tsql2);
        $newid = "";
			while( $row = sqlsrv_fetch_array($getid, SQLSRV_FETCH_ASSOC)) {
				$newid = $row['id'] + 1;
				}
		//Set Variables
		$ip_vistor=$_SERVER['REMOTE_ADDR'];
		$_SESSION['ip_vistor']= $ip_vistor;
		$compname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$_SESSION['compname'] = $compname;
		$page = curPageName();
		$_SESSION['page'] = $page;
		$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$_SESSION['url'] = $url;
		$time = date("g:i:s a");
		$date2 = date("F j, Y");
		//Insert into SQL
		$tsql1 = "INSERT INTO COMS_Track (id,ip,compname,ref,username,winauth,point,pointno,time,date2,sessionid,page) VALUES ($newid,'$ip_vistor','$compname','$url','$AccessCode','$ruser','$point','$pointno','$time','$date2','$sessionid','$page')";
		$posttrack = sqlsrv_query($conn, $tsql1);
		
		//Write Session Variables
		$dname = $_SESSION['dname'];
		$role = $_SESSION['role'];
		$rid = $_SESSION['rid'];
		$sitelist = $_SESSION['sitelist'];
		$Email = $_SESSION['Email'];
        $Domain = "";
        if (isset($_SESSION['Domain'])) {
		    $Domain = $_SESSION['Domain'];
        }
		$sessionStatus = $_SESSION['sessionStatus'];
		$TemplateAuthoring = $_SESSION['TemplateAuthoring'];
		$Role_ID = $_SESSION['Role_ID'];
        $ip = "";
        if (isset($_SESSION['ip'])) {
            $ip = $_SESSION['ip'];
        }
		$ruser = $_SESSION['ruser'];
		$NWLoginR = $_SESSION['NWLoginR'];
		$COMSLogin = $_SESSION['COMSLogin'];
		$mdws = $_SESSION['mdws'];
		$AC = $_SESSION['AC'];
		$VC = $_SESSION['VC'];
		//Insert into SQL
		$tsql2 = "INSERT INTO COMS_Sessions (sessionid,compname,ref,username,winauth,point,pointno,time,date2,page,text,chkTrack,dname,role,rid,sitelist,Email,Domain,sessionStatus,TemplateAuthoring,Role_ID,ip_vistor,ip,ruser,NWLoginR,COMSLogin,mdws,AC,VC) VALUES
           ('$sessionid','$compname','$url','$AccessCode','$ruser','$point','$pointno','$time','$date2','$page','test','$chkTrack','$dname','$role','$rid','$sitelist','$Email','$Domain','$sessionStatus','$TemplateAuthoring','$Role_ID','$ip_vistor','$ip','$ruser','$NWLoginR','$COMSLogin','$mdws','$AC','$VC')";
		   
		//$tsql2 = "INSERT INTO COMS_Sessions (sessionid,ip,compname,ref,username,winauth,point,pointno,time,date2,page) VALUES ('$sessionid','$ip_vistor','$compname','$url','$AccessCode','$ruser','$point','$pointno','$time','$date2','$page')";
		//echo $tsql2;
		$postsession = sqlsrv_query($conn, $tsql2);
	}
	
function PostSession($sessionid,$ruser,$AccessCode,$point,$pointno){
	include "dbitcon.php";
		//Set Variables
		$chkTrack = "";

		$ip_vistor=$_SERVER['REMOTE_ADDR'];
		$_SESSION['ip_vistor']= $ip_vistor;
		$compname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$_SESSION['compname'] = $compname;
		$page = curPageName();
		$_SESSION['page'] = $page;
		$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$_SESSION['url'] = $url;
		$time = date("g:i:s a");
		$date2 = date("F j, Y");
		//Write Session Variables
		$dname = $_SESSION['dname'];
		$role = $_SESSION['role'];
		$rid = $_SESSION['rid'];
		$sitelist = $_SESSION['sitelist'];
		$Email = $_SESSION['Email'];
		$domain = $_SESSION['domain'];
		$sessionStatus = $_SESSION['sessionStatus'];
		$TemplateAuthoring = $_SESSION['TemplateAuthoring'];
		$Role_ID = $_SESSION['Role_ID'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$ruser = $_SESSION['ruser'];
		$NWLoginR = $_SESSION['NWLoginR'];
		$COMSLogin = $_SESSION['COMSLogin'];
		$mdws = $_SESSION['mdws'];
		$AC = $_SESSION['AC'];
		$VC = $_SESSION['VC'];
		$chkTrack = $_SESSION['chkTrack'];
		//Insert into SQL
		$tsql2 = "INSERT INTO COMS_Sessions (sessionid,compname,ref,username,winauth,point,pointno,time,date2,page,text,chkTrack,dname,role,rid,sitelist,Email,domain,sessionStatus,TemplateAuthoring,Role_ID,ip_vistor,ip,ruser,NWLoginR,COMSLogin,mdws,AC,VC) VALUES
           ('$sessionid','$compname','$url','$AccessCode','$ruser','$point','$pointno','$time','$date2','$page','test','$chkTrack','$dname','$role','$rid','$sitelist','$Email','$domain','$sessionStatus','$TemplateAuthoring','$Role_ID','$ip_vistor','$ip','$ruser','$NWLoginR','$COMSLogin','$mdws','$AC','$VC')";
		   
		//$tsql2 = "INSERT INTO COMS_Sessions (sessionid,ip,compname,ref,username,winauth,point,pointno,time,date2,page) VALUES ('$sessionid','$ip_vistor','$compname','$url','$AccessCode','$ruser','$point','$pointno','$time','$date2','$page')";
		//echo $tsql2;
		$postsession = sqlsrv_query($conn, $tsql2);
	}

function PostSQLQuery($query){
	include "dbitcon.php";
		//Insert into SQL
		$tsql1 = "INSERT INTO COMS_Queries (query) VALUES ($query)";
		$postsqlq = sqlsrv_query($conn, $tsql1);
		
	}
function ChkSes(){
	include "dbitcon.php";
	$ipcheck = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$sessionid = $_SESSION['sessionid'];
	echo $sessionid;
	$query = "SELECT TOP 1 sessionid
      ,timestamp
      ,compname
      ,ref
      ,username
      ,winauth
      ,time
      ,date2
      ,dname
      ,role
      ,rid
      ,sitelist
      ,Role_ID	
      ,ruser
      ,NWLoginR
      ,COMSLogin
      ,mdws
      ,AC
      ,VC
		FROM COMS_Sessions
		WHERE sessionid = '$sessionid'
		order by timestamp desc ";
	
	$ChkSesq = sqlsrv_query($conn, $tsql1);
	
	foreach($ChkSesq as $row){
		$compname =  $row['compname'];
		}
		
	if ($compname = $ipcheck){
	
	}else{
	include "login.php";
	}
return ($VC);
}	

?>