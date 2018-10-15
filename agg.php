<!DOCTYPE HTML>
<html>
<head>
<title>Search Room</title>

<link href="https://fonts.googleapis.com/css?family=Amatic+SC|Pacifico" rel="stylesheet">

<style>
h {font-family: Georgia; font-size: 30px;color: DarkBlue;text-shadow: 5px 5px 5px white;font-weight: 800}
h1{font-family:Pacifico; font-size:45px;}
p1 {font-family:Amatic SC; font-size:35px; color:white}
p2 {font-family: Pacifico; font-size: 35px; text-shadow: 5px 5px 5px Ivory;}
body{background-color:#F5FBFB;}


#header {
    background-color: #003366;
    color: #FFFFF0;
    text-align:center;
    padding: 5px
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

</head>

<body>

<div id="header">
<h1>G-32 Worldwide Holdings Inc.</h1>
<p1>Your Second Home Around the World!</p1>
</div>

<h> Search </h>
<br />
<strong>&nbsp; Enter City, &nbsp; &nbsp; Province, &nbsp; &nbsp; Country,&nbsp; &nbsp;  From date, &nbsp; &nbsp; To date:</strong>

<form method="POST" action="agg.php">
  <p><input type="text" name="i_city" size="12">
     <input type="text" name="i_prov" size="12">
     <input type="text" name="i_country" size="12">
     <input id = "date" type="date" name="i_fdate" size="12">
     <input id = "date" type ="date" name="i_tdate" size="12">
     <input type="submit" value="Search" name="findRoom"></p>
</form>

<!-- ===================================================== -->


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

  function printResult($result) {
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
      echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" .
                        $row[2] . "</td><td>" . $row[3] . "</td><td>" .
                        $row[4] . "</td><td>" . $row[5] . "</td><td>" .
                        $row[6] . "</td><td>" . $row[7] . "</td><td>" .
                        $row[8] . "</td><td>" . $row[9] . "</td><td>" .
                        $row[10] . "</td></tr>";
    }
    echo "</table>";
  }

  // =====================
  // Connect Oracle...
  if ($db_conn) {
    if (array_key_exists('findRoom', $_POST)) {
      echo "<br> Finding available rooms ... <br>";
      executePlainSQL("drop view avbRooms");
      $sql = "create view avbRooms (fDate, tDate, HID, Hname, StNo, StName, City, Prov, Country, PostCode,
                          TID, BedSize, BedNum, RmView, RegRt, MemRt, PtRt, Availability, PickNum) as
                    select  '".$_POST['i_fdate']."' as fDate, '".$_POST['i_tdate']."' as tDate, h.HID, h.Hname, h.StNo,
                            h.StName, gi.City, gi.Prov, gi.Country, gi.PostCode,
                            rt.TID, rt.BedSize, rt.BedNum, rt.RmView, rp.RegRt, rp.RegRt*0.8 as MemRt, rp.RegRt*100 as PtRt,
                            tt.total-NVL(bk.booked,0)-NVL(st.stayed,0) as Availability, ROWNUM as PickNum
                    from  RtPlan rp left outer join hotel h on rp.HID = h.HID
                                    left outer join RoomType rt on rp.TID = rt.TID
                                    left outer join GeoInfo gi on h.Country = gi.country and
                                                                  h.PostCode = gi.PostCode
                                    left outer join (select count(m.HID) as booked, m.HID as HID, m.TID as TID
                                                     from Reservation r join Makes m on r.CofNo = m.CofNo
                                                     where r.Status <> 'Cancelled' and '".$_POST['i_tdate']."'>'".$_POST['i_fdate']."' and
                                                           NOT ('".$_POST['i_tdate']."' < r.CInDate or '".$_POST['i_fdate']."' > r.COutDate)
                                                     group by m.HID, m.TID) bk on bk.HID = rp.HID and bk.TID = rp.TID
                                    left outer join (select count(*) as total, rm.HID as HID, rm.TID as TID
                                                     from Room rm
                                                     group by rm.HID, rm.TID) tt on tt.HID = rp.HID and tt.TID = rp.TID
                                    left outer join (select count(*) as stayed, rom.HID as HID, rom.TID as TID
                                                     from Stays s left outer join HotelStay hs on s.HSID = hs.HSID
                                                                  left outer join room rom on s.HID=rom.HID and s.RoomNo=rom.RoomNo
                                                     where '".$_POST['i_tdate']."'>'".$_POST['i_fdate']."' and NOT ('".$_POST['i_tdate']."' < hs.AInDate or '".$_POST['i_fdate']."' > hs.AOutDate)
                                                     group by rom.HID, rom.TID) st on st.HID = rp.HID and st.TID = rp.TID
                    where gi.city = '" . $_POST['i_city'] . "' and gi.Prov = '".$_POST['i_prov']."' and gi.Country = '".$_POST['i_country']."' and
                          tt.total > (NVL(bk.booked,0) + NVL(st.stayed,0))
                    order by rp.RegRt";
      executePlainSQL($sql);
      OCICommit($db_conn);
    } else
      if (array_key_exists('bkRm', $_POST)) {
        executePlainSQL("insert into Reservation
                          select CofNo_seq.nextval, 'Active', '".$_POST['i_ccno']."', '".$_POST['i_ccexp']."', '".$_POST['i_ccname']."', fDate, tDate
                          from avbRooms
                          where PickNum=".$_POST['i_pkNo']);
        executePlainSQL("insert into Makes
                          select '".$_POST['i_cid']."', HID, TID, CofNo_seq.currval
                          from avbRooms
                          where PickNum=".$_POST['i_pkNo']);
        OCICommit($db_conn);
      }
    if ($_POST && $success) {
      //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
      header("location: agg.php");
    } else {
      $result = executePlainSQL("select fDate, tDate, Hname,
                                        BedSize, BedNum, RmView, RegRt, MemRt, PtRt, Availability, PickNum
                                 from avbRooms");
      echo "<table>";
      echo "<tr><th>From</th><th>To</th><th>Hotel</th><th>Bed</th>
                 <th>Number of bed</th><th>View</th><th>Regular rate</th><th>Member rate</th>
                 <th>Redeem points</th><th>Available room left</th><th>Pick No.(you need this for booking)</th></tr>";
      printResult($result);
    }
    OCILogoff($db_conn);
  } else {
    echo "cannot connect";
    $e = OCI_Error();
    echo htmlentities($e['message']);
  }
?>


<hr />
<h> Book Your Room Now</h>
<br />
<strong>Enter your <br />
  Customer ID, Credit Card Number, Expire Date(MMYY), Name on the Card ,Pick No. </strong>

<form method="POST" action="agg.php">
  <p><input type="text" name="i_cid" size="12">
     <input type="text" name="i_ccno" size="12">
     <input type="text" name="i_ccexp" size="12">
     <input type="text" name="i_ccname" size="12">
     <input type="text" name="i_pkNo" size="5">
     <input type="submit" value="Book" name="bkRm"></p>
</form>
<p style="color: grey; font-size: 10"> Pick No. is from the above search table to book the room. </p>


</html>