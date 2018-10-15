-- ============= Staff portal ==================
-- Selection and projection query: shuttle service schedule of a day and hotel
-- 	Input: Date, Hotel
--  Output: DateTime, Hotel Name, Customer Name, Dir, FlightNo

select sh.sDateTime, h.HName, c.Name, sh.Dir, sh.FlightNo
from ShuttleService sh left outer join Stays s on sh.HSID=s.HSID
                       left outer join Hotel h on s.HID=h.HID
                       left outer join Customer c on c.CID=s.CID
where sh.sDateTime like '%2016-12-08%' and h.HName='Holiday Sea';


-- Join query: manage customer bookings
--  Created: XSH 2018-03-24 11:42 ver1
--  Input: Customer's phone number
-- 	Output: Find customer bookings and member information

select c.CID, c.Name, mb.Pt, r.Status, r.CInDate, r.COutDate, h.HName, rt.BedSize, rt.BedNum, rt.RmView
from Customer c left outer join Member mb on c.cid = mb.cid
                left outer join Makes mk on c.cid = mk.cid
                left outer join Reservation r on mk.CofNo = r.CofNo
                left outer join Hotel h on mk.HID = h.HID
                left outer join RoomType rt on mk.TID = rt.TID
where c.Ph = '7781250088';

-- Nested aggregation: sum dinner service income by hotel, and find the max or min income hotel
--	Input: max or min
--	Output: Hotel Name and sum dinner service income
-- TODO // need to be edited 

--create a view table 
create view d_view
as select h.hid, SUM(hs.dinner) average
      from HotelStay hs left outer join Stays s on hs.hsid = s.hsid 
                        left outer join hotel h on s.hid = h.hid
      group by h.hid;
--max in sum dinner
select *
from d_view;

select d_view.hid, d_view.average
from d_view
where d_view.average in (select max(d_view.average)from d_view);

--min in average dinner
select *
from d_view;

select d_view.hid, d_view.average
from d_view
where d_view.average in (select min(d_view.average)from d_view);
-- Delete operation: Delete a customer account
--  Input: CID
--  Cascade: this action will remove the customer from Customer, Member, the reservations she made, the hotelstay records.
DELETE Customer 
where CID = '7';

--Division query: find a customer who have stayed in all hotels

select  c.CID, c.Name
from  Customer c
where not exists (select h.HID
                  from Hotel h
                  where NOT EXISTS (select s.CID
                                    from Stays s
                                    where h.HID = s.HID
                                    and s.CID = c.CID));

-- ============= Customer portal ================


-- Aggregation query: Search available room that fits location and date criteria
--  Created: XSH 2018-03-26 17:02 ver1
--  Input: City, Province, Country, from date, to date
--  Output: Available Hotel Name, Full Address, Roomtype, Price
drop view avbRooms;
create view avbRooms (fDate, tDate, HID, Hname, StNo, StName, City, Prov, Country, PostCode,
        TID, BedSize, BedNum, RmView, RegRt, MemRt, PtRt, Availability, PickNum) as
  select  '2018-12-30' as fDate, '2019-01-02' as tDate, h.HID, h.Hname, h.StNo,
          h.StName, gi.City, gi.Prov, gi.Country, gi.PostCode,
          rt.TID, rt.BedSize, rt.BedNum, rt.RmView, rp.RegRt, rp.RegRt*0.8 as MemRt, rp.RegRt*100 as PtRt,
          tt.total-NVL(bk.booked,0)-NVL(st.stayed,0) as Availability, ROWNUM as PickNum
  from  RtPlan rp left outer join hotel h on rp.HID = h.HID
                  left outer join RoomType rt on rp.TID = rt.TID
                  left outer join GeoInfo gi on h.Country = gi.country and
                                                h.PostCode = gi.PostCode
                  left outer join (select count(m.HID) as booked, m.HID as HID, m.TID as TID
                                   from Reservation r join Makes m on r.CofNo = m.CofNo
                                   where r.Status <> 'Cancelled' and '2019-01-02'>'2018-12-30' and 
                                   			 NOT ('2019-01-02' < r.CInDate or '2018-12-30' > r.COutDate)
                                   group by m.HID, m.TID) bk on bk.HID = rp.HID and bk.TID = rp.TID
                  left outer join (select count(*) as total, rm.HID as HID, rm.TID as TID
                                   from Room rm
                                   group by rm.HID, rm.TID) tt on tt.HID = rp.HID and tt.TID = rp.TID
                  left outer join (select count(*) as stayed, rom.HID as HID, rom.TID as TID
                                   from Stays s left outer join HotelStay hs on s.HSID = hs.HSID
                                                left outer join room rom on s.HID=rom.HID and s.RoomNo=rom.RoomNo
                                   where '2019-01-02'>'2018-12-30' and NOT ('2019-01-02' < hs.AInDate or '2018-12-30' > hs.AOutDate)
                                   group by rom.HID, rom.TID) st on st.HID = rp.HID and st.TID = rp.TID
  where gi.city = 'Toronto' and gi.Prov = 'Ontario' and gi.Country = 'Canada' and
        tt.total > (NVL(bk.booked,0) + NVL(st.stayed,0))
  order by rp.RegRt;

select fDate, tDate, Hname,
       BedSize, BedNum, RmView, RegRt, MemRt, PtRt, Availability, PickNum
from avbRooms;


-- Update operation: customer make a reservation
--	Check constraint: from date < to date, credit card expiredate > 2012-12
--  Input: CID, CCNo, CCExp, CCName, PickNum from the Search
insert into Reservation
  select CofNo_seq.nextval, 'Active', '1234223428371283', '1220', 'Raymond', fDate, tDate
  from avbRooms
  where PickNum=1;
insert into Makes
  select '2', HID, TID, CofNo_seq.currval
  from avbRooms
  where PickNum=1;









