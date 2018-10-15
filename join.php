<!DOCTYPE HTML>
<html>
<head>

<title>G-32 Customer Booking Management</title>

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
  border-radius: 5px;
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


<h> Customer Booking Management </h>
<hr />
<p style="font-size: 30px">Enter the Customer's phone number:</p>
<form method="POST" action="join.php">
  <p><input type="text" name="phNo" size="12" onkeypress="return event.keyCode>=48&&event.keyCode<=57" ng-pattern="/[^a-zA-Z]/" />
     <input type="submit" value="find" name="findCustomer"></p>
</form>



<?php

  $success = True;
  $db_conn = OCILogon("ora_k1h0b", "a43292093", "dbhost.ugrad.cs.ubc.ca:1522/ug");

  function printResult($result) {
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
      echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" .
                        $row[2] . "</td><td>" . $row[3] . "</td><td>" .
                        $row[4] . "</td><td>" . $row[5] . "</td><td>" .
                        $row[6] . "</td><td>" . $row[7] . "</td><td>" .
                        $row[8] . "</td><td>" . $row[9] . "</td></tr>";
    }
    echo "</table>";

  }

  // =====================
  // Connect Oracle...
  if ($db_conn) {
    if (array_key_exists('findCustomer', $_POST)) {
      echo "<br> Finding customer ... <br>";
      $sql = "select c.CID, c.Name, mb.Pt, r.Status, r.CInDate, r.COutDate, h.HName, rt.BedSize, rt.BedNum, rt.RmView
              from Customer c left outer join Member mb on c.cid = mb.cid
                              left outer join Makes mk on c.cid = mk.cid
                              left outer join Reservation r on mk.CofNo = r.CofNo
                              left outer join Hotel h on mk.HID = h.HID
                              left outer join RoomType rt on mk.TID = rt.TID
              where c.Ph= :ph_input";
      $stid = oci_parse($db_conn, $sql);
      $ph_input = $_POST["phNo"];
      echo "you have entered phone number: " . $ph_input;
      oci_bind_by_name($stid, ':ph_input', $ph_input);
      OCIExecute($stid);
      echo "<br> Got data from table customer and member.
      If a customer is not a member, the points will be shown as blank.<br>";
      echo "<table>";
      echo "<tr><th>Customer ID</th><th>Name</th><th>Points</th><th>Status</th>
                <th>Check-In</th><th>Check-Out</th><th>Hotel</th><th>Bed</th>
                <th>Bed Num</th><th>Room View</th></tr>";
      printResult($stid);
    }
    OCILogoff($db_conn);
  } else {
    echo "cannot connect";
    $e = OCI_Error();
    echo htmlentities($e['message']);
  }
?>


</body>
</head>
</html>
<!-- ===================================================== -->
