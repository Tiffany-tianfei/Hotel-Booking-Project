<!DOCTYPE HTML>
<html>
<head>
<title> Shuttle Service </title>
</head>

<link href="https://fonts.googleapis.com/css?family=Amatic+SC|Pacifico" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Chicle|Playfair+Display|Ubuntu" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Signika" rel="stylesheet">

<style>
h1 {font-family:Pacifico; font-size:35px;}
h2 {font-family:Impact; font-size:35px;color:DarkSlateGrey}
h  {font-size:35px; font-family:Ubuntu; color: #003366;font-weight:750px;}
body{background-color:AliceBlue; font-family: Signika; font-size: 20px;}

#header {
  border:1px;
  background: -webkit-linear-gradient(left, #003366,AliceBlue, #F9F9F9,AliceBlue,#003366); /* Safari 5.1 - 6.0 */
  background: -o-linear-gradient(right, #003366,AliceBlue, #F9F9F9,AliceBlue,#003366)); /* Opera 11.1 - 12.0 */
  background: -moz-linear-gradient(right, #003366,AliceBlue, #F9F9F9,AliceBlue,#003366)); /* Firefox 3.6 - 15 */
  background: linear-gradient(to right, #003366,AliceBlue, #F9F9F9,AliceBlue,#003366)); /* 标准的语法（必须放在最后） */#F9F9F9;
  text-align:center;
  padding: 1px
}  

ul {color:DarkGrey;}
a:link {text-decoration:none;color:#2F4F4F}
a:active {background-color:White; color:Indigo;}

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

</style>



<body>


<div id="header">
<h1>G-32 Worldwide Holdings Inc.</h1>
<h2 >Central Office Staff Portal </h2>
</div>


<h> Print Shuttle Service </h>



<form method="POST" action="selection.php">
  <p>Date &nbsp;<input id = "date" type="date" name="i_date" size="12"></p>
  <p style="font-size: 15; color: grey">Format "yyyy-mm-dd"<br /></p>
     Hotel <input type="text" name="i_hname" size="12">
     <input type="submit" value="Generate" name="findSh"></p>
</form>

<!-- ===================================================== -->
<?php

  $success = True;
  $db_conn = OCILogon("ora_k1h0b", "a43292093", "dbhost.ugrad.cs.ubc.ca:1522/ug");

  function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr); //There is a set of c\omments at the end of the file that describe some of the OCI specific functions and how they work

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

  function printResult($result) {
    echo "<table>";
    echo "<tr><th>Date Time</th><th>Hotel</th><th>Customer Name</th><th>Direction</th>
               <th>Flight No.</th></tr>";
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
      echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" .
                        $row[2] . "</td><td>" . $row[3] . "</td><td>" .
                        $row[4] . "</td></tr>";
    }
    echo "</table>";
  }
  // =====================
  // Connect Oracle...
  if ($db_conn) {
    if (array_key_exists('findSh', $_POST)) {
      echo $_POST['i_date'];
      $result= executePlainSQL("select to_char(sh.sDateTime, 'yyyy-mm-dd hh24:mi'), h.HName, c.Name, sh.Dir, sh.FlightNo
                                from ShuttleService sh left outer join Stays s on sh.HSID=s.HSID
                                                       left outer join Hotel h on s.HID=h.HID
                                                       left outer join Customer c on c.CID=s.CID
                                where sh.sDateTime >= TIMESTAMP'".$_POST['i_date']." 00:00:00' and
                                      sh.sDateTime <= TIMESTAMP '".$_POST['i_date']." 23:59:59' and
                                      h.HName='".$_POST['i_hname']."'");
      printResult($result);
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

