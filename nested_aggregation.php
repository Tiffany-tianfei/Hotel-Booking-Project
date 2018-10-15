<!DOCTYPE HTML>
<html>
<head>
<title>Dinner Income </title>
</head>

<link href="https://fonts.googleapis.com/css?family=Amatic+SC|Pacifico" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Chicle|Playfair+Display|Ubuntu" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Signika" rel="stylesheet">

<style>
h1 {font-family:Pacifico; font-size:35px;}
h2 {font-family:Impact; font-size:35px;color:DarkSlateGrey}
h  {font-size:35px; font-family:Ubuntu; color: #003366;font-weight:750px;}
body{background-color:AliceBlue; font-family: Signika; font-size: 30px;}

#header {
  border:1px;
  background-color: #F9F9F9;
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


<h> Find Top and Bottom Hotels<br />
    Dinner Income</h>

<form method="POST" action="nested_aggregation.php">   
<p><input type="submit" value="TopHotels" name="TopHotels"></p>
</form>
<form method="POST" action="nested_aggregation.php">   
<p><input type="submit" value="BottomHotels" name="BottomHotels"></p>
</form>

</body>
</html>

<!-- ===================================================== -->
<?php
//this tells the system that it's no longer just parsing
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_k1h0b", "a43292093", "dbhost.ugrad.cs.ubc.ca:1522/ug");


function printResult($result) { //prints results from a select statement
  // echo "<br>Found customer:<br>";
  
  while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
    echo "<tr><td>".  $row[0] . "</td><td>" . $row[1] ."</td><td>" . 
              $row[2]."</td></tr>"; 
  }
  echo "</table>";

}

// =====================

// Connect Oracle...
if ($db_conn) {

  if (array_key_exists('TopHotels',$_POST)){
    echo "<br> Finding hotels with highest dinner income <br>";
    $sql ="select h.hid, h.hname, round(SUM(hs.dinner),2) 
  from HotelStay hs left outer join Stays s on hs.hsid = s.hsid 
            left outer join hotel h on s.hid = h.hid
  group by h.hid,h.Hname
  having sum(hs.dinner) = (select max(sum(hs.dinner))from
   HotelStay hs left outer join Stays s on hs.hsid = s.hsid 
            left outer join hotel h on s.hid = h.hid
  group by h.hid)";
    $stid = oci_parse($db_conn,$sql);
  OCIExecute($stid);
  echo "<table>";
    echo "<tr><th>Hotel ID</th><th> Hotel Name </th><th> Income </th><th>";
    printResult($stid);
  }
  if (array_key_exists('BottomHotels',$_POST)){
    echo "<br> Finding hotels with lowest dinner income <br>";
    $sql ="select h.hid, h.hname, round(SUM(hs.dinner),2) 
  from HotelStay hs left outer join Stays s on hs.hsid = s.hsid 
            left outer join hotel h on s.hid = h.hid
  group by h.hid,h.Hname
  having sum(hs.dinner) = (select min(sum(hs.dinner))from
   HotelStay hs left outer join Stays s on hs.hsid = s.hsid 
            left outer join hotel h on s.hid = h.hid
  group by h.hid)";
  $stid = oci_parse($db_conn,$sql);
  echo "<table>";
    echo "<tr><th>Hotel ID</th><th> Hotel Name </th><th> Income </th><th>";
    OCIExecute($stid);
    printResult($stid);
  }
    OCILogoff($db_conn);
  } else {
    echo "cannot connect";
    $e = OCI_Error(); // For OCILogon errors pass no handle
    echo htmlentities($e['message']);
  }
  ?>