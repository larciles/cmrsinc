use farmacias
SELECT COUNT(distinct b.codclien) vistos,b.codmedico, ( select COUNT(distinct z.codclien) ventas
from view_percentstats z  where z.fechafac between '20180401' and '20180430' and z.codclien in ( 
 select  distinct y.codclien  from view_medpacpercentstats y 
 where y.fechafac between '20180401' and '20180430' and y.codmedico= b.codmedico ) and z.codmedico= b.codmedico ) compras, max( concat(m.nombre+' ',m.apellido)) medico
from view_medpacpercentstats b 
inner join Mmedicos m On m.Codmedico=b.codmedico
where b.fechafac between '20180401' and '20180430' and b.codmedico<>'000'
group by  b.codmedico
order by compras desc