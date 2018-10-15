
<!DOCTYPE HTML>
<html>
<head>
<title>G-32 Bonus </title>

<link href="https://fonts.googleapis.com/css?family=Amatic+SC|Pacifico" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Chicle|Playfair+Display|Ubuntu" rel="stylesheet">

<style>
h1 {font-family:Pacifico; font-size:35px;}
h2 {font-family:Impact; font-size:35px;color:#003366}
h  {font-size:30px; font-weight:900; font-family:Ubuntu;}
body{background-color:#F5FBFB;}

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
</style>

<body>

<div id="header">
<h1>32-Group Worldwide Holdings Inc.</h1>
<h2 >Central Office Staff Portal </h2>
</div>


<h>Find Customers who have stayed in all hotel. </h>
<hr />

<form method="POST" action="div.php">
<p><input type="submit" value="findVIP" name="findVIP"></p>
</form>

</body>
</head>
</html>
<!-- ===================================================== -->
<?php

  $success = True;
  $db_conn = OCILogon("ora_k1h0b", "a43292093", "dbhost.ugrad.cs.ubc.ca:1522/ug");

  function printResult($result) {
  	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
  		echo "<tr><td>" . $row[0] . "</td><td>"
                      . $row[1] . "</td><td>"
                      . $row[2] . "</td><td>"
                      . $row[3] . "</td></tr>";
  	}
  	echo "</table>";
  }

  // =====================
  // Connect Oracle...
  if ($db_conn) {
    if (array_key_exists('findVIP',$_POST)){
      $sql ="select c.cid, c.Name, c.Ph, c.Email
             from  Customer c
             where not exists (select h.hid
                               from Hotel h
                               where not exists (select s.cid
                                                 from Stays s
                                                 where s.cid = c.cid and
                                                       s.hid = h.hid))";
      $stid = oci_parse($db_conn,$sql);
      OCIExecute($stid);
      echo "<table>";
      echo "<tr><th>Customer ID</th><th>Name</th><th>Phone No.</th><th>Email</th></tr>";
      printResult($stid);
    }
      OCILogoff($db_conn);
  } else {
      echo "cannot connect";
      $e = OCI_Error();
      echo htmlentities($e['message']);
  }
?>