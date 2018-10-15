<!DOCTYPE HTML>
<html>
<head>
<title> Delete Customer</title>
</head>

<link href="https://fonts.googleapis.com/css?family=Amatic+SC|Pacifico" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Chicle|Playfair+Display|Ubuntu" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Signika" rel="stylesheet">

<style>
h1 {font-family:Pacifico; font-size:35px;}
h2 {font-family:Impact; font-size:35px;color:#003366}
h  {font-size:35px; font-family:Ubuntu; color: #003366;font-weight:750px;}
body{background-color:AliceBlue; font-family: Signika;}
  
#header {
  border:1px solid #003366;
  background-color: #F9F9F9;
  text-align:center;
  padding: 1px
}
input{
  border: 1px solid #ccc;
  padding: 7px 8px;
  border-radius: 9px;
  padding-left:5px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
  -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
  -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s
            }
input:focus{
 border-color: #66afe9;
 outline: 0;
 -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
 box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)
            }  

ul {color:DarkGrey;}
a:link {text-decoration:none;color:#2F4F4F}
a:active {background-color:White; color:Indigo;}


</style>



<body>


<div id="header">
<h1>G-32 Worldwide Holdings Inc.</h1>
<h2 >Central Office Staff Portal </h2>
</div>
<h> Enter Customer ID <h>

<form method="POST" action="del.php">
  <p><input type="text" name="i_cid" size="12">
     <input type="submit" value="Delete Customer!" name="delCustm"></p>
</form>

<!-- ===================================================== -->
<?php

  $success = True;
  $db_conn = OCILogon("ora_k1h0b", "a43292093", "dbhost.ugrad.cs.ubc.ca:1522/ug");

  function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
  	//echo "<br>running ".$cmdstr."<br>";
  	global $db_conn, $success;
  	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

  	if (!$statement) {
  		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
  		$e = OCI_Error($db_conn); // For OCIParse errors pass the
  		// connection handle
  		echo htmlentities($e['message']);
  		$success = False;
  	}

  	$r = OCIExecute($statement, OCI_DEFAULT);
  	if (!$r) {
  		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
  		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
  		echo htmlentities($e['message']);
  		$success = False;
  	} else {

  	}
  	return $statement;

  }

  // =====================
  // Connect Oracle...
  if ($db_conn) {
    if (array_key_exists('delCustm', $_POST)) {
      echo "<br> Deleting the customer ... <br>";
      executePlainSQL("Delete customer where cid =".$_POST['i_cid']);
      OCICommit($db_conn);
    }
    if ($_POST && $success) {
		  //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		  header("location: del.php");
    }
    OCILogoff($db_conn);
  } else {
    echo "cannot connect";
    $e = OCI_Error();
    echo htmlentities($e['message']);
  }
?>

</body>
</html>


