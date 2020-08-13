-- --------------------------------------------------------
-- Host:                         192.130.74.32
-- Server version:               Microsoft SQL Server 2014 - 12.0.2269.0
-- Server OS:                    Windows NT 6.3 <X64> (Build 17134: )
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for farmacias
CREATE DATABASE IF NOT EXISTS "farmacias";
USE "farmacias";


-- Dumping structure for view farmacias.AAATest
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "AAATest" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanul" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.Almacen
CREATE TABLE IF NOT EXISTS "Almacen" (
	"coditems" NVARCHAR(50) NOT NULL,
	"existencia" FLOAT(53) NULL DEFAULT NULL,
	"codsucursal" INT(10,0) NOT NULL,
	"costo" MONEY(19,4) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codsucursal","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.AnalisisConsulta
DELIMITER //

CREATE procedure AnalisisConsulta (@fec_I datetime,@fec_F datetime) 
as
begin
IF EXISTS(SELECT name 
	  FROM 	 sysobjects 
	  WHERE  name = 'temporal' 
	  AND 	 type = 'U')
    DROP TABLE temporal

IF EXISTS(SELECT name 
	  FROM 	 sysobjects 
	  WHERE  name = 'temporal1' 
	  AND 	 type = 'U')
    DROP TABLE temporal1

/*set @fec_I=convert(datetime,@fec_I)
set @fec_F=convert(datetime,@fec_F)*/
 SELECT DISTINCT codclien, codmedico
 INTO temporal
 FROM         dbo.Mconsultas where mconsultas.fecha_cita>=@fec_I and mconsultas.fecha_cita<=@fec_F and mconsultas.asistido=3 and activa='1'
grant select,update,insert on temporal to dbo 
EXEC sp_changeobjectowner  'TEMPORAL', 'dbo'


select codclien,codmedico,(select count(*)  from mconsultas where codclien=temporal.codclien and codmedico=temporal.codmedico  and mconsultas.primera_control='0' and mconsultas.asistido=3 and mconsultas.fecha_cita>=@fec_I and mconsultas.fecha_cita<=@fec_F and activa='1') as control,(select count(*)  from mconsultas where codclien=temporal.codclien and codmedico=temporal.codmedico and mconsultas.primera_control='1' and mconsultas.asistido=3 and mconsultas.fecha_cita>=@fec_I and mconsultas.fecha_cita<=@fec_F and activa='1' ) as nuevo
 into temporal1 from temporal
EXEC sp_changeobjectowner  'TEMPORAL1', 'dbo'
 grant select,update,insert on temporal1 to dbo
end
//
DELIMITER ;


-- Dumping structure for table farmacias.AntesCierre
CREATE TABLE IF NOT EXISTS "AntesCierre" (
	"coditems" NVARCHAR(10) NOT NULL,
	"existencia" INT(10,0) NOT NULL,
	"fecha" DATETIME(3) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.carriers
CREATE TABLE IF NOT EXISTS "carriers" (
	"id" INT(10,0) NOT NULL,
	"carrier" NVARCHAR(255) NULL DEFAULT NULL,
	"posorder" INT(10,0) NULL DEFAULT NULL,
	"created" TIMESTAMP NULL DEFAULT NULL,
	"deleted" INT(10,0) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CFactura
CREATE TABLE IF NOT EXISTS "CFactura" (
	"numfactu" NVARCHAR(7) NULL DEFAULT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codservi" NVARCHAR(3) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.cie
CREATE TABLE IF NOT EXISTS "cie" (
	"cie" NVARCHAR(8) NULL DEFAULT NULL,
	"enfermedad" NVARCHAR(255) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.cierrei
DELIMITER //

create procedure [dbo].[cierrei] 
as
declare @saldo as numeric	
declare @fec_actual as datetime
declare @hora as nvarchar(15)
declare @coditems as nvarchar(10)
declare @existencia as numeric
declare @compra as numeric
declare @DEVcompra as numeric
declare @Ventas as numeric
declare @Anulada as numeric
declare @ajustes as numeric
declare @NC as numeric
declare @NE as numeric
declare @CAntd as numeric
declare @fecha as datetime
declare @fechat as datetime
declare @fecinv as datetime
declare inventario cursor for select coditems,existencia from Minventario where prod_serv='P'

set @hora=convert(nvarchar(15),getdate(),114)
SET @fechat=convert(nvarchar(10),getdate(),112)
select @fecha=min(fechacierre) from [MCierreInventario] where  [status]='0' 
select @fec_actual=min(fechacierre) from [MCierreInventario] where  [status]='0' 
SET @fec_actual =  DATEADD(day, 1,@fec_actual)

open inventario
fetch next from inventario into @coditems,@existencia
while (@fecha < @fechat ) begin
  


	while @@fetch_status=0	begin
		/*capturas las existencia antes de cualquier cambio*/ 
		insert into AntesCierre (coditems,existencia,fecha) values (@coditems,@existencia,@fec_actual)
		/* cierre de inventario*/
		/*compras*/
		select @compra=isnull(sum(cantidad),0)from dcompra left join mcompra on dcompra.factcomp=mcompra.factcomp where dcompra.coditems=@coditems and mcompra.fechapost=@fecha and mcompra.facclose='1'
		/*devoluciones en compras*/
		select @devcompra=isnull(sum(cantidad),0) from devcompra where coditems=@coditems and fecha=@fecha
		/* ventas */
		select @Ventas=isnull(sum(cantidad),0) from dfactura where coditems=@coditems and fechafac=@fecha
		/* Anuladas */
		select @Anulada=isnull(sum(cantidad),0) from dfactura inner join mfactura on dfactura.numfactu=mfactura.numfactu where dfactura.coditems=@coditems and mfactura.fechanul=@fecha and mfactura.statfact='2'
		/* notas de entregas */   
		select @ne=isnull(sum(cantidad),0) from notaentrega inner join notentdetalle on notentdetalle.numnotent=notaentrega.numnotent  where notentdetalle.coditems = @coditems and notaentrega.statunot<>'2' and notentdetalle.fechanot=@fecha
		/*notas de creditos*/
		select @nc=isnull(sum(cantidad),0) from dnotacredito inner join mnotacredito on dnotacredito.numnotcre=mnotacredito.numnotcre  where dnotacredito.coditems = @coditems and mnotacredito.statnc<>'2' and dnotacredito.fechanot=@fecha
		/* ajustes*/
		select @ajustes=isnull(sum(cantidad),0) from dajustes where coditems=@coditems and fechajust=@fecha
		/* cierre diario por productos */
		update dcierreinventario set  compras=@compra, devcompras=@devcompra,ventas=@ventas,anulaciones=@anulada,notascreditos=@nc,notasentregas=@ne,ajustes=@ajustes,invposible=existencia-@ventas+@anulada+@compra-@devcompra-@ne+@nc+@ajustes where coditems=@coditems and fechacierre=@fecha
		/* actualiza saldo inventarios de inicio del dia*/  
		select @saldo=invposible from dcierreinventario where coditems=@coditems and fechacierre=@fecha
  
		fetch next from inventario into @coditems,@existencia
	end
	/* actualiza control de fecha de cierre */
	update empresa set fechacierreinventario=@fec_actual
	update mcierreinventario set status=1 where fechacierre=@fecha
	insert into mcierreinventario (fechacierre,hora,status) values (@fec_actual,@hora,'0')

	declare Transferencia cursor for select coditems,invposible,fechacierre from dcierreinventario where fechacierre=@fecha
	open Transferencia
	fetch next from Transferencia into @coditems,@existencia,@fecinv
	while @@fetch_status=0 		begin
			update minventario set existencia=@existencia where coditems=@coditems 
			/* captura el inventario inicial del dia */
			insert into dcierreinventario (coditems,fechacierre,existencia) values (@coditems,@fec_actual,@existencia)
			fetch next from Transferencia into @coditems,@existencia,@fecinv
	end
	close Transferencia
	deallocate Transferencia

  close inventario
  SET @fec_actual =  DATEADD(day, 1,@fec_actual)
  SET @fecha = DATEADD(day, 1,@fecha)  
  open inventario
  fetch next from inventario into @coditems,@existencia
end
deallocate inventario//
DELIMITER ;


-- Dumping structure for procedure farmacias.cierreinventario
DELIMITER //

CREATE procedure cierreinventario 
as
declare @saldo as numeric	
declare @fec_actual as nvarchar(10)
declare @hora as nvarchar(15)
declare @coditems as nvarchar(10)
declare @existencia as numeric
declare @compra as numeric
declare @DEVcompra as numeric
declare @Ventas as numeric
declare @Anulada as numeric
declare @ajustes as numeric
declare @NC as numeric
declare @NE as numeric
declare @CAntd as numeric
declare @fecha as datetime
declare @fecinv as datetime
declare inventario cursor for select coditems,existencia from Minventario where prod_serv='P'
set @fec_actual=convert(nvarchar(10),getdate() + 1,112)
set @hora=convert(nvarchar(15),getdate(),114)
select @fecha=fechacierreinventario from empresa
open inventario
fetch next from inventario into @coditems,@existencia
while @@fetch_status=0
begin
  /*capturas las existencia antes de cualquier cambio*/ 
  insert into AntesCierre (coditems,existencia,fecha) values (@coditems,@existencia,@fec_actual)
    /* cierre de inventario*/
    /*compras*/
    select @compra=isnull(sum(cantidad),0)from dcompra left join mcompra on dcompra.factcomp=mcompra.factcomp where dcompra.coditems=@coditems and mcompra.fechapost=@fecha and mcompra.facclose='1'
    /*devoluciones en compras*/
    select @devcompra=isnull(sum(cantidad),0) from devcompra where coditems=@coditems and fecha=@fecha
    /* ventas */
    select @Ventas=isnull(sum(cantidad),0) from dfactura where coditems=@coditems and fechafac=@fecha
    /* Anuladas */
    select @Anulada=isnull(sum(cantidad),0) from dfactura inner join mfactura on dfactura.numfactu=mfactura.numfactu where dfactura.coditems=@coditems and mfactura.fechanul=@fecha and mfactura.statfact='2'
    /* notas de entregas */   
    select @ne=isnull(sum(cantidad),0) from notaentrega inner join notentdetalle on notentdetalle.numnotent=notaentrega.numnotent  where notentdetalle.coditems = @coditems and notaentrega.statunot<>'2' and notentdetalle.fechanot=@fecha
    /*notas de creditos*/
    select @nc=isnull(sum(cantidad),0) from dnotacredito inner join mnotacredito on dnotacredito.numnotcre=mnotacredito.numnotcre  where dnotacredito.coditems = @coditems and mnotacredito.statnc<>'2' and dnotacredito.fechanot=@fecha
    /* ajustes*/
    select @ajustes=isnull(sum(cantidad),0) from dajustes where coditems=@coditems and fechajust=@fecha
    /* cierre diario por productos */
    update dcierreinventario set  compras=@compra, devcompras=@devcompra,ventas=@ventas,anulaciones=@anulada,notascreditos=@nc,notasentregas=@ne,ajustes=@ajustes,invposible=existencia-@ventas+@anulada+@compra-@devcompra-@ne+@nc+@ajustes where coditems=@coditems and fechacierre=@fecha
    /* actualiza saldo inventarios de inicio del dia*/  
    select @saldo=invposible from dcierreinventario where coditems=@coditems and fechacierre=@fecha
  
    fetch next from inventario into @coditems,@existencia
end
/* actualiza control de fecha de cierre */
update empresa set fechacierreinventario=@fec_actual
update mcierreinventario set status=1 where fechacierre=@fecha
insert into mcierreinventario (fechacierre,hora,status) values (@fec_actual,@hora,'0')

close inventario
deallocate inventario
declare Transferencia cursor for select coditems,invposible,fechacierre from dcierreinventario where fechacierre=@fecha
open Transferencia
fetch next from Transferencia into @coditems,@existencia,@fecinv
while @@fetch_status=0
 begin
      update minventario set existencia=@existencia where coditems=@coditems 
      /* captura el inventario inicial del dia */
     insert into dcierreinventario (coditems,fechacierre,existencia) values (@coditems,@fec_actual,@existencia)
fetch next from Transferencia into @coditems,@existencia,@fecinv
end
close Transferencia
deallocate Transferencia//
DELIMITER ;


-- Dumping structure for procedure farmacias.cierremslaser
DELIMITER //
-- Batch submitted through debugger: SQLQuery2.sql|7|0|C:\Users\ADMINI~1\AppData\Local\Temp\2\~vsAADB.sql
CREATE procedure [dbo].[cierremslaser] 
as
declare @saldo as numeric	
declare @fec_actual as datetime
declare @hora as nvarchar(15)
declare @coditems as nvarchar(10)
declare @existencia as numeric
declare @compra as numeric
declare @DEVcompra as numeric
declare @Ventas as numeric
declare @Anulada as numeric
declare @ajustes as numeric
declare @NC as numeric
declare @NE as numeric
declare @CAntd as numeric
declare @fecha as datetime
declare @fechat as datetime
declare @fecinv as datetime
declare inventario cursor for select coditems,existencia from Minventario where coditems in ('LICAT','LICAN','LIBUT')

set @hora=convert(nvarchar(15),getdate(),114)
SET @fechat=convert(nvarchar(10),getdate(),112)
--select @fecha=fechacierre from mscierre order by fechacierre desc
SELECT TOP 1 @fecha=MS.fechacierre from mscierre MS order by MS.Id DESC
set @fec_actual=convert(nvarchar(10),getdate() ,112)
SET @fec_actual =  DATEADD(day, 1,@fec_actual)

open inventario
fetch next from inventario into @coditems,@existencia
while (@fecha < @fechat ) begin
  


	while @@fetch_status=0	begin
		/*capturas las existencia antes de cualquier cambio*/ 
		insert into AntesCierre (coditems,existencia,fecha) values (@coditems,@existencia,@fec_actual)
		/* cierre de inventario*/
		/*compras*/
		select @compra=isnull(sum(cantidad),0)from dcompra left join mcompra on dcompra.factcomp=mcompra.factcomp where dcompra.coditems=@coditems and mcompra.fechapost=@fecha and mcompra.facclose='1'
		/*devoluciones en compras*/
		select @devcompra=isnull(sum(cantidad),0) from devcompra where coditems=@coditems and fecha=@fecha
		/* ventas */
		Select @Ventas=isnull(sum(L.cantidad),0) from MSSMFact M Inner Join MSSDFact L On M.numfactu=L.numfactu inner join Kit K ON L.coditems=K.coditems Inner join MInventario I ON K.codikit=I.coditems Where  K.codikit=@coditems and M.fechafac=@fecha 
		/* Anuladas */
		Select @Anulada=isnull(sum(L.cantidad),0) from MSSMFact M
			Inner Join MSSDFact L On M.numfactu=L.numfactu
			inner join Kit K ON L.coditems=K.coditems
			Inner join MInventario I ON K.codikit=I.coditems
			Where   K.codikit=@coditems and M.fechafac=@fecha  and M.statfact='2'

		/* notas de entregas */   
		select @ne=isnull(sum(cantidad),0) from notaentrega inner join notentdetalle on notentdetalle.numnotent=notaentrega.numnotent  where notentdetalle.coditems = @coditems and notaentrega.statunot<>'2' and notentdetalle.fechanot=@fecha
		/*notas de creditos*/
		Select @nc=isnull(sum(L.cantidad),0)
			from MSSMDev M 
			Inner Join MSSDDev L On M.numfactu=L.numnotcre
			inner join Kit K ON L.coditems=K.coditems
			Inner join MInventario I ON K.codikit=I.coditems
			Where K.codikit = @coditems and M.statnc<>2 and L.fechanot=@fecha
		/* ajustes*/
		select @ajustes=isnull(sum(cantidad),0) from dajustes where coditems=@coditems and fechajust=@fecha
		/* cierre diario por productos */
		update mscierre set  compras=@compra, devcompras=@devcompra,ventas=@ventas,anulaciones=@anulada,notascreditos=@nc,notasentregas=@ne,ajustes=@ajustes,invposible=existencia-@ventas+@anulada+@compra-@devcompra-@ne+@nc+@ajustes where coditems=@coditems and fechacierre=@fecha
		/* actualiza saldo inventarios de inicio del dia*/  
		select @saldo=invposible from mscierre where coditems=@coditems and fechacierre=@fecha
  
		fetch next from inventario into @coditems,@existencia
	end
	/* actualiza control de fecha de cierre */
	/*update empresa set fechacierreinventario=@fec_actual*/
	/*update mcierreinventario set status=1 where fechacierre=@fecha
	insert into mcierreinventario (fechacierre,hora,status) values (@fec_actual,@hora,'0')
	*/
	declare Transferencia cursor for select coditems,invposible,fechacierre from mscierre where fechacierre=@fecha
	open Transferencia
	fetch next from Transferencia into @coditems,@existencia,@fecinv
	while @@fetch_status=0 		begin
			/* captura el inventario inicial del dia */
			insert into mscierre (coditems,fechacierre,existencia) values (@coditems,DATEADD(day, 1,@fecha),@existencia)
			update minventario set existencia=@existencia where coditems=@coditems 
			fetch next from Transferencia into @coditems,@existencia,@fecinv			
	end
	close Transferencia
	deallocate Transferencia

  close inventario
  SET @fec_actual =  DATEADD(day, 1,@fec_actual)
  SET @fecha = DATEADD(day, 1,@fecha)  
  open inventario
  fetch next from inventario into @coditems,@existencia
end
deallocate inventario
//
DELIMITER ;


-- Dumping structure for procedure farmacias.citas_servicios
DELIMITER //

CREATE procedure citas_servicios (@producto as nvarchar(10))
as
declare @coditems as nvarchar(10)
declare @codclien as nvarchar(5)
declare @codmedico as nvarchar(3)
declare @fec as datetime
declare servicios cursor for select cma_mfactura.codclien,cma_mfactura.codmedico,cma_mfactura.fechafac,cma_dfactura.coditems  from cma_dfactura inner join cma_mfactura on cma_mfactura.numfactu=cma_dfactura.numfactu where coditems=@producto and cma_mfactura.numfactu=cma_dfactura.numfactu and cma_mfactura.statfact<>'2'
open servicios
fetch next from servicios into @codclien,@codmedico,@fec,@coditems
while @@fetch_status=0
begin
 if not exists(select codclien,codmedico,fecha_cita from mconsultas where codclien=@codclien and fecha_cita=@fec)
 insert into mconsultas (codclien,codmedico,fecha,hora,fecha_cita,citados,asistido,servicios,fecreg,primera_control) values (@codclien,@codmedico,@fec,'__:__',@fec,'1','3','1',@fec,'0')
 fetch next from servicios into @codclien,@codmedico,@fec,@coditems
end
close servicios
deallocate servicios


//
DELIMITER ;


-- Dumping structure for table farmacias.Ciudad
CREATE TABLE IF NOT EXISTS "Ciudad" (
	"Cod" DECIMAL(18,0) NOT NULL,
	"Ciudad" NVARCHAR(255) NULL DEFAULT NULL,
	"ABRE" NVARCHAR(6) NULL DEFAULT NULL,
	"CODEAREA" NVARCHAR(10) NULL DEFAULT NULL,
	"STATE" DECIMAL(18,0) NULL DEFAULT NULL,
	"PAIS" DECIMAL(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Cod")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.ClasPatologias
CREATE TABLE IF NOT EXISTS "ClasPatologias" (
	"CIE" NVARCHAR(8) NULL DEFAULT NULL,
	"Enfermedad" NVARCHAR(255) NULL DEFAULT NULL,
	"CODCIE1" NVARCHAR(4) NULL DEFAULT NULL,
	"CODCIE2" NVARCHAR(4) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.cmaci
DELIMITER //
CREATE PROCEDURE [dbo].[cmaci] AS
declare @saldo as numeric	
declare @fec_actual as nvarchar(10)
declare @hora as nvarchar(15)
declare @coditems as nvarchar(10)
declare @existencia as numeric
declare @compra as numeric
declare @DEVcompra as numeric
declare @Ventas as numeric
declare @Anulada as numeric
declare @ajustes as numeric
declare @NC as numeric
declare @NE as numeric
declare @CAntd as numeric
declare @fecha as datetime
declare @fecinv as datetime
declare @iactual as float
declare @invend as float

declare inventario cursor for select coditems,existencia from Minventario where prod_serv='S' and inventariable='1'
--set @fec_actual=convert(nvarchar(10),getdate() + 1,112)
set @fec_actual=convert(nvarchar(10),getdate() ,112)
set @hora=convert(nvarchar(15),getdate(),114)
select @fecha=fechacierre from Cmacierreinv order by fechacierre asc

set @ne=0
set @DEVcompra=0
set @invend=0
set @Ventas=0
set @Anulada=0
set @nc=0
set @ajustes=0
set @iactual=0

--set @fecha='20150731'
open inventario
fetch next from inventario into @coditems,@existencia

while (@fecha <=@fec_actual) begin
 while @@fetch_status=0
  begin

     select @iactual =isnull(invposible,0) from Cmacierreinv where coditems=@coditems and fechacierre=dateadd(day,-1,@fecha)
     
    --*compras
     select @compra=isnull(sum(cantidad*50),0)from dcompra left join mcompra on dcompra.factcomp=mcompra.factcomp where dcompra.coditems=@coditems and mcompra.fechapost=@fecha and mcompra.facclose='1'

    --*ventas
    set @Ventas=0

	Select   @Ventas= isnull(sum(a.cantidad*p.cantidad),0)  from cma_dfactura A
	INNER JOIN cma_mfactura B ON A.numfactu=B.numfactu
	INNER JOIN presentacion p  on a.coditems=p.coditems
	Where A.fechafac=@fecha AND A.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST')  AND B.statfact<>2
	GROUP BY   p.relcod ,a.fechafac

    --*Anuladas
    set @Anulada=0

	Select   @Anulada= isnull(sum(a.cantidad*p.cantidad),0)  from cma_dfactura A
	INNER JOIN cma_mfactura B ON A.numfactu=B.numfactu
	INNER JOIN presentacion p  on a.coditems=p.coditems
	Where A.fechafac=@fecha AND A.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST')  AND B.statfact=2
	GROUP BY   p.relcod ,a.fechafac


    --*devoluciones en compras     
      select @devcompra=isnull(sum(cantidad*50)*50,0) from devcompra where coditems=@coditems and fecha=@fecha   
    --*notas de entregas 
      select @ne=isnull(sum(cantidad),0) from notaentrega inner join notentdetalle on notentdetalle.numnotent=notaentrega.numnotent  where notentdetalle.coditems = @coditems and notaentrega.statunot<>'2' and notentdetalle.fechanot=@fecha
    --*notas de creditos
	set @nc=0
      --select @nc=isnull(sum(d.cantidad)*50,0) from CMA_Dnotacredito d inner join CMA_mnotacredito m on d.numnotcre=m.numnotcre  where d.coditems = @coditems and m.statnc<>'2' and d.fechanot=@fecha
	  Select   @nc=isnull(sum(a.cantidad*p.cantidad),0)    from CMA_Dnotacredito A
		INNER JOIN CMA_mnotacredito B ON A.numnotcre=B.numnotcre
		inner join presentacion p  ON a.coditems=p.coditems
		Where A.fechanot=@fecha AND A.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST') AND B.statnc<>2
		GROUP BY   p.relcod 
    --*ajustes
    select @ajustes=isnull(sum(cantidad)*50,0) from dajustes where coditems=@coditems and fechajust=@fecha
    -- cierre diario por productos

    set  @invend= @iactual-@ventas+@anulada+@compra-@devcompra-@ne+@nc+@ajustes
    
    update Cmacierreinv set  compras=@compra,
                          devcompras=@devcompra,
                              ventas=@ventas,
   anulaciones=@anulada,
                       notascreditos=@nc,
                       notasentregas=@ne,
                             ajustes=@ajustes,
                          invposible=@invend where coditems=@coditems and fechacierre=@fecha

    update minventario set GrExist=@invend, existencia=@invend/medida  where coditems=@coditems
   -- update minventario set existencia=GrExist/50 where coditems=@coditems

    insert into Cmacierreinv (coditems,fechacierre,existencia) values (@coditems,DATEADD(day, 1,@fecha),@invend)  
    fetch next from inventario into @coditems,@existencia
 end
  SET @fecha = DATEADD(day, 1,@fecha)
  close inventario
  open inventario
  fetch next from inventario into @coditems,@existencia
end
--select @invend=isnull(GrExist,0) from minventario where coditems=@coditems
--select @invend=isnull(dbo.getExistencia(@coditems),0)
--set @invend=@invend/50
--update minventario set existencia=@invend where coditems=@coditems

close inventario
deallocate inventario//
DELIMITER ;


-- Dumping structure for table farmacias.Cmacierreinv
CREATE TABLE IF NOT EXISTS "Cmacierreinv" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"fechacierre" DATETIME(3) NOT NULL,
	"existencia" NUMERIC(18,0) NULL DEFAULT (0),
	"compras" NUMERIC(18,0) NULL DEFAULT (0),
	"DevCompras" NUMERIC(18,0) NULL DEFAULT (0),
	"ventas" NUMERIC(18,0) NULL DEFAULT (0),
	"anulaciones" NUMERIC(18,0) NULL DEFAULT (0),
	"ajustes" NUMERIC(18,0) NULL DEFAULT (0),
	"NotasCreditos" NUMERIC(18,0) NULL DEFAULT (0),
	"NotasEntregas" NUMERIC(18,0) NULL DEFAULT (0),
	"InvPosible" NUMERIC(18,0) NULL DEFAULT (0),
	"InvActual" NUMERIC(18,0) NULL DEFAULT (0),
	"fallas" NUMERIC(18,0) NULL DEFAULT (0),
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT (getdate()),
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.cma_DFactura
CREATE TABLE IF NOT EXISTS "cma_DFactura" (
	"numfactu" NVARCHAR(7) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NULL DEFAULT NULL,
	"procentaje" NUMERIC(18,0) NULL DEFAULT NULL,
	"descuento" MONEY(19,4) NULL DEFAULT NULL,
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"codtecnico" NVARCHAR(2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NULL DEFAULT NULL,
	"aplicadcto" NVARCHAR(1) NULL DEFAULT NULL,
	"aplicacommed" NVARCHAR(1) NULL DEFAULT NULL,
	"aplicacomtec" NVARCHAR(1) NULL DEFAULT NULL,
	"tipo" NVARCHAR(2) NULL DEFAULT NULL,
	"rowguid" UNIQUEIDENTIFIER NULL DEFAULT NULL,
	"pvpitem" MONEY(19,4) NULL DEFAULT NULL,
	"dosis" INT(10,0) NULL DEFAULT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL DEFAULT NULL,
	"costo" FLOAT(53) NULL DEFAULT NULL,
	"monto_imp" FLOAT(53) NULL DEFAULT NULL,
	"codseguro" INT(10,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	"percentage" MONEY(19,4) NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT NULL,
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"statfact" NVARCHAR(1) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"subtotal" MONEY(19,4) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"retcan" INT(10,0) NULL DEFAULT NULL,
	"ret" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CMA_Dnotacredito
CREATE TABLE IF NOT EXISTS "CMA_Dnotacredito" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" NUMERIC(18,2) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NOT NULL,
	"porcentaje" NUMERIC(18,2) NOT NULL,
	"descuento" MONEY(19,4) NULL DEFAULT ((0)),
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto" NUMERIC(18,2) NULL DEFAULT NULL,
	"impuesto" NUMERIC(18,2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NOT NULL,
	"aplicadcto" NVARCHAR(1) NOT NULL,
	"aplicacommed" NVARCHAR(1) NOT NULL,
	"aplicacomtec" NVARCHAR(1) NOT NULL,
	"costo" MONEY(19,4) NULL DEFAULT ((0)),
	"monto_imp" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT NULL,
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"subtotal" MONEY(19,4) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	PRIMARY KEY ("numnotcre","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CMA_Empresa
CREATE TABLE IF NOT EXISTS "CMA_Empresa" (
	"nombre" NVARCHAR(100) NULL DEFAULT NULL,
	"rif" NVARCHAR(20) NULL DEFAULT NULL,
	"nit" NVARCHAR(20) NULL DEFAULT NULL,
	"direccion" TEXT(2147483647) NULL DEFAULT NULL,
	"UltimaFactura" NVARCHAR(6) NULL DEFAULT NULL,
	"tasa_iva" MONEY(19,4) NULL DEFAULT NULL,
	"sucursal" NVARCHAR(50) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CMA_MDesctFactura
CREATE TABLE IF NOT EXISTS "CMA_MDesctFactura" (
	"numfactu" NVARCHAR(7) NULL DEFAULT NULL,
	"codesc" NVARCHAR(3) NULL DEFAULT NULL,
	"total" MONEY(19,4) NULL DEFAULT NULL,
	"base" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" SMALLDATETIME(0) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.cma_MFactura
CREATE TABLE IF NOT EXISTS "cma_MFactura" (
	"numfactu" NVARCHAR(7) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT (0),
	"descuento" MONEY(19,4) NULL DEFAULT (0),
	"iva" MONEY(19,4) NULL DEFAULT (0),
	"total" MONEY(19,4) NULL DEFAULT (0),
	"statfact" NVARCHAR(1) NULL DEFAULT (1),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT (0),
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT (0),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NULL DEFAULT NULL,
	"totalpvp" MONEY(19,4) NULL DEFAULT (0),
	"tipopago" BIT NULL DEFAULT (0),
	"codseguro" INT(10,0) NULL DEFAULT (0),
	"plazo" INT(10,0) NULL DEFAULT (0),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT (0),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NULL DEFAULT (0),
	"TotImpuesto" MONEY(19,4) NULL DEFAULT (0),
	"Alicuota" FLOAT(53) NULL DEFAULT (0),
	"monto_flete" MONEY(19,4) NULL DEFAULT (0),
	"Obervacion" NVARCHAR(600) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"vcod" NVARCHAR(15) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"empresa" NVARCHAR(50) NULL DEFAULT NULL,
	"cempresa" NVARCHAR(50) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"ret" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.cma_MFactura_tmp
CREATE TABLE IF NOT EXISTS "cma_MFactura_tmp" (
	"numfactu" NVARCHAR(7) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT ((0)),
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NULL DEFAULT NULL,
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NULL DEFAULT ((0)),
	"TotImpuesto" MONEY(19,4) NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Obervacion" NVARCHAR(600) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CMA_Mnotacredito
CREATE TABLE IF NOT EXISTS "CMA_Mnotacredito" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(50) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"totalnot" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL,
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"concepto" NVARCHAR(255) NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"codtiponotcre" NVARCHAR(1) NULL DEFAULT NULL,
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"desanula" NVARCHAR(255) NULL DEFAULT NULL,
	"monto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"tasadesc" NUMERIC(18,2) NOT NULL,
	"saldo" MONEY(19,4) NOT NULL,
	"openclose" NVARCHAR(1) NULL DEFAULT NULL,
	"TotImpuesto" MONEY(19,4) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'04'),
	"Id" INT(10,0) NOT NULL,
	"ct" NVARCHAR(50) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"empresa" NVARCHAR(50) NULL DEFAULT NULL,
	"cempresa" NVARCHAR(50) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	PRIMARY KEY ("numnotcre")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.comisiones
CREATE TABLE IF NOT EXISTS "comisiones" (
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"tipo" NVARCHAR(10) NULL DEFAULT NULL,
	"comision" MONEY(19,4) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT ((1)),
	"id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.consolidated_view
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "consolidated_view" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" INT NULL,
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.ControlCitas
CREATE TABLE IF NOT EXISTS "ControlCitas" (
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"Citado" BIT NULL DEFAULT (0),
	"Confirmado" BIT NULL DEFAULT (0),
	"Asistio" BIT NULL DEFAULT (0),
	"Noasistio" BIT NULL DEFAULT (0),
	"Row" NUMERIC(18,0) NULL DEFAULT (1),
	"Col" NUMERIC(18,0) NULL DEFAULT (1),
	"CellColorBack" CHAR(10) NULL DEFAULT NULL,
	"CellcolorFore" CHAR(10) NULL DEFAULT NULL,
	"RecordDay" DATETIME(3) NULL DEFAULT NULL,
	"Horallegada" DATETIME(3) NULL DEFAULT NULL,
	"HoraI" DATETIME(3) NULL DEFAULT NULL,
	"HoraF" DATETIME(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CONTROLES
CREATE TABLE IF NOT EXISTS "CONTROLES" (
	"CONTROL" NVARCHAR(50) NULL DEFAULT NULL,
	"HABILITADO" BIT NULL DEFAULT (0),
	"PERFIL" NVARCHAR(2) NULL DEFAULT ('05'),
	"USUARIO" NVARCHAR(20) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Controlservicios
CREATE TABLE IF NOT EXISTS "Controlservicios" (
	"fechaServicio" DATETIME(3) NOT NULL,
	"codclien" NVARCHAR(5) NOT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.ControlVisistas
CREATE TABLE IF NOT EXISTS "ControlVisistas" (
	"Historia" NVARCHAR(12) NULL DEFAULT NULL,
	"Visita" NUMERIC(18,0) NULL DEFAULT NULL,
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"observaciones" TEXT(2147483647) NULL DEFAULT (''),
	"CI" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CREXPORT
CREATE TABLE IF NOT EXISTS "CREXPORT" (
	"PH_fechafac" DATETIME(3) NULL DEFAULT NULL,
	"PH_2" VARCHAR(12) NULL DEFAULT NULL,
	"PH_3" VARCHAR(9) NULL DEFAULT NULL,
	"PH_numfactu" VARCHAR(8) NULL DEFAULT NULL,
	"PH_5" VARCHAR(8) NULL DEFAULT NULL,
	"PH_DIRECCION" VARCHAR(255) NULL DEFAULT NULL,
	"PH_F_Anulada" VARCHAR(255) NULL DEFAULT NULL,
	"PH_cedula" VARCHAR(16) NULL DEFAULT NULL,
	"PH_9" VARCHAR(7) NULL DEFAULT NULL,
	"PH_Cajero" VARCHAR(255) NULL DEFAULT NULL,
	"PH_11" VARCHAR(66) NULL DEFAULT NULL,
	"DE_nombre_alterno" VARCHAR(101) NULL DEFAULT NULL,
	"DE_cantidad" FLOAT(53) NULL DEFAULT NULL,
	"DE_precunit" FLOAT(53) NULL DEFAULT NULL,
	"DE_subtotal" FLOAT(53) NULL DEFAULT NULL,
	"GT_desanul" VARCHAR(256) NULL DEFAULT NULL,
	"GT_totalitems" FLOAT(53) NULL DEFAULT NULL,
	"GT_3" VARCHAR(7) NULL DEFAULT NULL,
	"GT_4" VARCHAR(8) NULL DEFAULT NULL,
	"GT_subtotal" FLOAT(53) NULL DEFAULT NULL,
	"GT_6" VARCHAR(9) NULL DEFAULT NULL,
	"GT_descuento" FLOAT(53) NULL DEFAULT NULL,
	"GT_8" VARCHAR(5) NULL DEFAULT NULL,
	"GT_monto_flete" FLOAT(53) NULL DEFAULT NULL,
	"GT_10" VARCHAR(14) NULL DEFAULT NULL,
	"GT_11" VARCHAR(6) NULL DEFAULT NULL,
	"GT_totalfactura" FLOAT(53) NULL DEFAULT NULL,
	"GT_FACTURA" VARCHAR(255) NULL DEFAULT NULL,
	"GT_14" VARCHAR(7) NULL DEFAULT NULL,
	"GT_medico" VARCHAR(102) NULL DEFAULT NULL,
	"GT_PrintDate" DATETIME(3) NULL DEFAULT NULL,
	"GT_PrintTime" DATETIME(3) NULL DEFAULT NULL,
	"GT_18" VARCHAR(65) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Cuadre
CREATE TABLE IF NOT EXISTS "Cuadre" (
	"fecha" DATETIME(3) NOT NULL,
	"estacion" NVARCHAR(20) NOT NULL,
	"monto" INT(10,0) NOT NULL DEFAULT (0),
	"valor" MONEY(19,4) NOT NULL DEFAULT (0),
	"Id_centro" NVARCHAR(1) NOT NULL DEFAULT (1),
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"tipo" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.CuentasxCobrar
CREATE TABLE IF NOT EXISTS "CuentasxCobrar" (
	"codtipodoc" NVARCHAR(3) NOT NULL,
	"numdoc" NVARCHAR(7) NOT NULL,
	"codseguro" INT(10,0) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"monto_original" MONEY(19,4) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT NULL,
	"fecha_doc" DATETIME(3) NULL DEFAULT NULL,
	"fecha_ult_pago" DATETIME(3) NULL DEFAULT NULL,
	"status" CHAR(1) NULL DEFAULT NULL,
	"fecha_vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"id_centro" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Dajustes
CREATE TABLE IF NOT EXISTS "Dajustes" (
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"fechajust" DATETIME(3) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"codajus" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DCierreInventario
CREATE TABLE IF NOT EXISTS "DCierreInventario" (
	"coditems" NVARCHAR(10) NOT NULL,
	"fechacierre" DATETIME(3) NOT NULL,
	"existencia" NUMERIC(18,0) NULL DEFAULT (0),
	"compras" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"DevCompras" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"ventas" NUMERIC(18,0) NULL DEFAULT (0),
	"anulaciones" NUMERIC(18,0) NULL DEFAULT (0),
	"ajustes" NUMERIC(18,0) NULL DEFAULT (0),
	"NotasCreditos" NUMERIC(18,0) NULL DEFAULT (0),
	"NotasEntregas" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"InvPosible" NUMERIC(18,0) NULL DEFAULT (0),
	"InvActual" NUMERIC(18,0) NULL DEFAULT (0),
	"fallas" NUMERIC(18,0) NULL DEFAULT (0),
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("coditems","fechacierre")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Dcompra
CREATE TABLE IF NOT EXISTS "Dcompra" (
	"factcomp" NVARCHAR(6) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"cant_ant" NUMERIC(18,0) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"costo" MONEY(19,4) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("factcomp","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DCotizacion
CREATE TABLE IF NOT EXISTS "DCotizacion" (
	"numcot" NVARCHAR(10) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"cantidad" DECIMAL(18,0) NULL DEFAULT NULL,
	"precunit" DECIMAL(18,2) NULL DEFAULT NULL,
	"Dosis" DECIMAL(18,0) NULL DEFAULT (0),
	"Capsulas" DECIMAL(18,0) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DDiagnostico
CREATE TABLE IF NOT EXISTS "DDiagnostico" (
	"coditems" NVARCHAR(10) NOT NULL DEFAULT (''),
	"subcoditems" NVARCHAR(10) NOT NULL DEFAULT (''),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Devcompra
CREATE TABLE IF NOT EXISTS "Devcompra" (
	"factcomp" NVARCHAR(6) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(20) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"cant_ant" NUMERIC(18,0) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("factcomp","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DFactura
CREATE TABLE IF NOT EXISTS "DFactura" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NULL DEFAULT (N'P'),
	"procentaje" FLOAT(53) NOT NULL DEFAULT (0),
	"descuento" MONEY(19,4) NOT NULL DEFAULT (0),
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"codtecnico" NVARCHAR(2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NULL DEFAULT (0),
	"aplicadcto" NVARCHAR(1) NULL DEFAULT (0),
	"aplicacommed" NVARCHAR(1) NULL DEFAULT (1),
	"aplicacomtec" NVARCHAR(1) NULL DEFAULT (0),
	"tipo" NVARCHAR(2) NULL DEFAULT ('FA'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"pvpitem" MONEY(19,4) NULL DEFAULT (0),
	"dosis" INT(10,0) NULL DEFAULT (0),
	"cant_sugerida" NUMERIC(18,0) NULL DEFAULT (0),
	"costo" FLOAT(53) NULL DEFAULT (0),
	"monto_imp" FLOAT(53) NOT NULL DEFAULT (0),
	"codseguro" INT(10,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	"dstatfact" NVARCHAR(1) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"subtotal" MONEY(19,4) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"retcan" INT(10,0) NULL DEFAULT NULL,
	"ret" NVARCHAR(10) NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT NULL,
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"percentage" MONEY(19,4) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Diagnostico
CREATE TABLE IF NOT EXISTS "Diagnostico" (
	"Historia" NVARCHAR(12) NULL DEFAULT NULL,
	"cie" NVARCHAR(8) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"diagnostico" NUMERIC(18,0) NULL DEFAULT NULL,
	"codtto" NVARCHAR(10) NULL DEFAULT NULL,
	"observaciones" TEXT(2147483647) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Dinventario
CREATE TABLE IF NOT EXISTS "Dinventario" (
	"coditems" NVARCHAR(10) NOT NULL DEFAULT (''),
	"subcoditems" NVARCHAR(10) NOT NULL,
	"SudDesItems" NVARCHAR(50) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NOT NULL DEFAULT (0),
	"aplicadcto" NVARCHAR(1) NOT NULL DEFAULT (1),
	"aplicacommed" NVARCHAR(1) NOT NULL DEFAULT (1),
	"aplicacomtec" NVARCHAR(1) NOT NULL DEFAULT (0),
	"activo" NVARCHAR(1) NOT NULL DEFAULT ('1'),
	"existencia" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Divisiones
CREATE TABLE IF NOT EXISTS "Divisiones" (
	"id" NUMERIC(18,0) NOT NULL,
	"nombre" NVARCHAR(300) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.divisions
CREATE TABLE IF NOT EXISTS "divisions" (
	"id" INT(10,0) NOT NULL,
	"nombre" NVARCHAR(300) NULL DEFAULT NULL,
	"cod" INT(10,0) NULL DEFAULT NULL,
	"orden" INT(10,0) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Dnotacredito
CREATE TABLE IF NOT EXISTS "Dnotacredito" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" NUMERIC(18,2) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NOT NULL,
	"porcentaje" NUMERIC(18,2) NOT NULL,
	"descuento" MONEY(19,4) NULL DEFAULT (0),
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto" NUMERIC(18,2) NULL DEFAULT NULL,
	"impuesto" NUMERIC(18,2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NOT NULL,
	"aplicadcto" NVARCHAR(1) NOT NULL,
	"aplicacommed" NVARCHAR(1) NOT NULL,
	"aplicacomtec" NVARCHAR(1) NOT NULL,
	"costo" MONEY(19,4) NULL DEFAULT (0),
	"monto_imp" MONEY(19,4) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"subtotal" MONEY(19,4) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"percentage" MONEY(19,4) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(10) NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT NULL,
	"dosis" INT(10,0) NULL DEFAULT NULL,
	"cant_sugerida" INT(10,0) NULL DEFAULT NULL,
	PRIMARY KEY ("numnotcre","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DPedido
CREATE TABLE IF NOT EXISTS "DPedido" (
	"numpedido" NVARCHAR(10) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"canitdad" NUMERIC(18,0) NULL DEFAULT NULL,
	"costo" NUMERIC(18,2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horereg" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DPerfil
CREATE TABLE IF NOT EXISTS "DPerfil" (
	"codperfil" NVARCHAR(2) NULL DEFAULT NULL,
	"incluir" BIT NULL DEFAULT NULL,
	"modificar" BIT NULL DEFAULT NULL,
	"eliminar" BIT NULL DEFAULT NULL,
	"imprimir" BIT NULL DEFAULT NULL,
	"consultar" BIT NULL DEFAULT NULL,
	"cobranzas" BIT NULL DEFAULT NULL,
	"anular" BIT NULL DEFAULT NULL,
	"reimprimir" BIT NULL DEFAULT NULL,
	"formulario" NVARCHAR(20) NULL DEFAULT NULL,
	"nivel" NVARCHAR(1) NULL DEFAULT NULL,
	"monto_maxanul" INT(10,0) NULL DEFAULT NULL,
	"monto_maxprint" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DPostulados
CREATE TABLE IF NOT EXISTS "DPostulados" (
	"CodSuc" NVARCHAR(2) NOT NULL,
	"Lapso" INT(10,0) NOT NULL,
	"Mes" INT(10,0) NOT NULL,
	"cod_IM" INT(10,0) NOT NULL,
	"ds" INT(10,0) NOT NULL,
	"Cuota" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("CodSuc","Lapso","Mes","cod_IM","ds")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Dprecios
CREATE TABLE IF NOT EXISTS "Dprecios" (
	"coditems" NVARCHAR(10) NOT NULL DEFAULT (''),
	"subcoditems" NVARCHAR(10) NOT NULL DEFAULT (''),
	"precio" NUMERIC(18,2) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.DPRICES
CREATE TABLE IF NOT EXISTS "DPRICES" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"ACTUALIZADO" MONEY(19,4) NULL DEFAULT NULL,
	"PORCENT" MONEY(19,4) NULL DEFAULT NULL,
	"FECHA" DATETIME(3) NULL DEFAULT NULL,
	"HORA" DATETIME(3) NULL DEFAULT NULL,
	"CONTROL" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.dtproperties
CREATE TABLE IF NOT EXISTS "dtproperties" (
	"id" INT(10,0) NOT NULL,
	"objectid" INT(10,0) NULL DEFAULT NULL,
	"property" VARCHAR(64) NOT NULL,
	"value" VARCHAR(255) NULL DEFAULT NULL,
	"lvalue" IMAGE(2147483647) NULL DEFAULT NULL,
	"version" INT(10,0) NOT NULL DEFAULT (0),
	"uvalue" NVARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY ("id","property")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.dt_addtosourcecontrol
DELIMITER //
create proc dbo.dt_addtosourcecontrol
    @vchSourceSafeINI varchar(255) = '',
    @vchProjectName   varchar(255) ='',
    @vchComment       varchar(255) ='',
    @vchLoginName     varchar(255) ='',
    @vchPassword      varchar(255) =''

as

set nocount on

declare @iReturn int
declare @iObjectId int
select @iObjectId = 0

declare @iStreamObjectId int
select @iStreamObjectId = 0

declare @VSSGUID varchar(100)
select @VSSGUID = 'SQLVersionControl.VCS_SQL'

declare @vchDatabaseName varchar(255)
select @vchDatabaseName = db_name()

declare @iReturnValue int
select @iReturnValue = 0

declare @iPropertyObjectId int
declare @vchParentId varchar(255)

declare @iObjectCount int
select @iObjectCount = 0

    exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT
    if @iReturn <> 0 GOTO E_OAError


    /* Create Project in SS */
    exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
											'AddProjectToSourceSafe',
											NULL,
											@vchSourceSafeINI,
											@vchProjectName output,
											@@SERVERNAME,
											@vchDatabaseName,
											@vchLoginName,
											@vchPassword,
											@vchComment


    if @iReturn <> 0 GOTO E_OAError

    /* Set Database Properties */

    begin tran SetProperties

    /* add high level object */

    exec @iPropertyObjectId = dbo.dt_adduserobject_vcs 'VCSProjectID'

    select @vchParentId = CONVERT(varchar(255),@iPropertyObjectId)

    exec dbo.dt_setpropertybyid @iPropertyObjectId, 'VCSProjectID', @vchParentId , NULL
    exec dbo.dt_setpropertybyid @iPropertyObjectId, 'VCSProject' , @vchProjectName , NULL
    exec dbo.dt_setpropertybyid @iPropertyObjectId, 'VCSSourceSafeINI' , @vchSourceSafeINI , NULL
    exec dbo.dt_setpropertybyid @iPropertyObjectId, 'VCSSQLServer', @@SERVERNAME, NULL
    exec dbo.dt_setpropertybyid @iPropertyObjectId, 'VCSSQLDatabase', @vchDatabaseName, NULL

    if @@error <> 0 GOTO E_General_Error

    commit tran SetProperties
    
    select @iObjectCount = 0;

CleanUp:
    select @vchProjectName
    select @iObjectCount
    return

E_General_Error:
    /* this is an all or nothing.  No specific error messages */
    goto CleanUp

E_OAError:
    exec dbo.dt_displayoaerror @iObjectId, @iReturn
    goto CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_addtosourcecontrol_u
DELIMITER //
create proc dbo.dt_addtosourcecontrol_u
    @vchSourceSafeINI nvarchar(255) = '',
    @vchProjectName   nvarchar(255) ='',
    @vchComment       nvarchar(255) ='',
    @vchLoginName     nvarchar(255) ='',
    @vchPassword      nvarchar(255) =''

as
	-- This procedure should no longer be called;  dt_addtosourcecontrol should be called instead.
	-- Calls are forwarded to dt_addtosourcecontrol to maintain backward compatibility
	set nocount on
	exec dbo.dt_addtosourcecontrol 
		@vchSourceSafeINI, 
		@vchProjectName, 
		@vchComment, 
		@vchLoginName, 
		@vchPassword


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_adduserobject
DELIMITER //
/*
**	Add an object to the dtproperties table
*/
create procedure dbo.dt_adduserobject
as
	set nocount on
	/*
	** Create the user object if it does not exist already
	*/
	begin transaction
		insert dbo.dtproperties (property) VALUES ('DtgSchemaOBJECT')
		update dbo.dtproperties set objectid=@@identity 
			where id=@@identity and property='DtgSchemaOBJECT'
	commit
	return @@identity
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_adduserobject_vcs
DELIMITER //
create procedure dbo.dt_adduserobject_vcs
    @vchProperty varchar(64)

as

set nocount on

declare @iReturn int
    /*
    ** Create the user object if it does not exist already
    */
    begin transaction
        select @iReturn = objectid from dbo.dtproperties where property = @vchProperty
        if @iReturn IS NULL
        begin
            insert dbo.dtproperties (property) VALUES (@vchProperty)
            update dbo.dtproperties set objectid=@@identity
                    where id=@@identity and property=@vchProperty
            select @iReturn = @@identity
        end
    commit
    return @iReturn


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_checkinobject
DELIMITER //
create proc dbo.dt_checkinobject
    @chObjectType  char(4),
    @vchObjectName varchar(255),
    @vchComment    varchar(255)='',
    @vchLoginName  varchar(255),
    @vchPassword   varchar(255)='',
    @iVCSFlags     int = 0,
    @iActionFlag   int = 0,   /* 0 => AddFile, 1 => CheckIn */
    @txStream1     Text = '', /* drop stream   */ /* There is a bug that if items are NULL they do not pass to OLE servers */
    @txStream2     Text = '', /* create stream */
    @txStream3     Text = ''  /* grant stream  */


as

	set nocount on

	declare @iReturn int
	declare @iObjectId int
	select @iObjectId = 0
	declare @iStreamObjectId int

	declare @VSSGUID varchar(100)
	select @VSSGUID = 'SQLVersionControl.VCS_SQL'

	declare @iPropertyObjectId int
	select @iPropertyObjectId  = 0

    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    declare @vchProjectName   varchar(255)
    declare @vchSourceSafeINI varchar(255)
    declare @vchServerName    varchar(255)
    declare @vchDatabaseName  varchar(255)
    declare @iReturnValue	  int
    declare @pos			  int
    declare @vchProcLinePiece varchar(255)

    
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSProject',       @vchProjectName   OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSourceSafeINI', @vchSourceSafeINI OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLServer',     @vchServerName    OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLDatabase',   @vchDatabaseName  OUT

    if @chObjectType = 'PROC'
    begin
        if @iActionFlag = 1
        begin
            /* Procedure Can have up to three streams
            Drop Stream, Create Stream, GRANT stream */

            begin tran compile_all

            /* try to compile the streams */
            exec (@txStream1)
            if @@error <> 0 GOTO E_Compile_Fail

            exec (@txStream2)
            if @@error <> 0 GOTO E_Compile_Fail

            exec (@txStream3)
            if @@error <> 0 GOTO E_Compile_Fail
        end

        exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT
        if @iReturn <> 0 GOTO E_OAError

        exec @iReturn = master.dbo.sp_OAGetProperty @iObjectId, 'GetStreamObject', @iStreamObjectId OUT
        if @iReturn <> 0 GOTO E_OAError
        
        if @iActionFlag = 1
        begin
            
            declare @iStreamLength int
			
			select @pos=1
			select @iStreamLength = datalength(@txStream2)
			
			if @iStreamLength > 0
			begin
			
				while @pos < @iStreamLength
				begin
						
					select @vchProcLinePiece = substring(@txStream2, @pos, 255)
					
					exec @iReturn = master.dbo.sp_OAMethod @iStreamObjectId, 'AddStream', @iReturnValue OUT, @vchProcLinePiece
            		if @iReturn <> 0 GOTO E_OAError
            		
					select @pos = @pos + 255
					
				end
            
				exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
														'CheckIn_StoredProcedure',
														NULL,
														@sProjectName = @vchProjectName,
														@sSourceSafeINI = @vchSourceSafeINI,
														@sServerName = @vchServerName,
														@sDatabaseName = @vchDatabaseName,
														@sObjectName = @vchObjectName,
														@sComment = @vchComment,
														@sLoginName = @vchLoginName,
														@sPassword = @vchPassword,
														@iVCSFlags = @iVCSFlags,
														@iActionFlag = @iActionFlag,
														@sStream = ''
                                        
			end
        end
        else
        begin
        
            select colid, text into #ProcLines
            from syscomments
            where id = object_id(@vchObjectName)
            order by colid

            declare @iCurProcLine int
            declare @iProcLines int
            select @iCurProcLine = 1
            select @iProcLines = (select count(*) from #ProcLines)
            while @iCurProcLine <= @iProcLines
            begin
                select @pos = 1
                declare @iCurLineSize int
                select @iCurLineSize = len((select text from #ProcLines where colid = @iCurProcLine))
                while @pos <= @iCurLineSize
                begin                
                    select @vchProcLinePiece = convert(varchar(255),
                        substring((select text from #ProcLines where colid = @iCurProcLine),
                                  @pos, 255 ))
                    exec @iReturn = master.dbo.sp_OAMethod @iStreamObjectId, 'AddStream', @iReturnValue OUT, @vchProcLinePiece
                    if @iReturn <> 0 GOTO E_OAError
                    select @pos = @pos + 255                  
                end
                select @iCurProcLine = @iCurProcLine + 1
            end
            drop table #ProcLines

            exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
													'CheckIn_StoredProcedure',
													NULL,
													@sProjectName = @vchProjectName,
													@sSourceSafeINI = @vchSourceSafeINI,
													@sServerName = @vchServerName,
													@sDatabaseName = @vchDatabaseName,
													@sObjectName = @vchObjectName,
													@sComment = @vchComment,
													@sLoginName = @vchLoginName,
													@sPassword = @vchPassword,
													@iVCSFlags = @iVCSFlags,
													@iActionFlag = @iActionFlag,
													@sStream = ''
        end

        if @iReturn <> 0 GOTO E_OAError

        if @iActionFlag = 1
        begin
            commit tran compile_all
            if @@error <> 0 GOTO E_Compile_Fail
        end

    end

CleanUp:
	return

E_Compile_Fail:
	declare @lerror int
	select @lerror = @@error
	rollback tran compile_all
	RAISERROR (@lerror,16,-1)
	goto CleanUp

E_OAError:
	if @iActionFlag = 1 rollback tran compile_all
	exec dbo.dt_displayoaerror @iObjectId, @iReturn
	goto CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_checkinobject_u
DELIMITER //
create proc dbo.dt_checkinobject_u
    @chObjectType  char(4),
    @vchObjectName nvarchar(255),
    @vchComment    nvarchar(255)='',
    @vchLoginName  nvarchar(255),
    @vchPassword   nvarchar(255)='',
    @iVCSFlags     int = 0,
    @iActionFlag   int = 0,   /* 0 => AddFile, 1 => CheckIn */
    @txStream1     text = '',  /* drop stream   */ /* There is a bug that if items are NULL they do not pass to OLE servers */
    @txStream2     text = '',  /* create stream */
    @txStream3     text = ''   /* grant stream  */

as	
	-- This procedure should no longer be called;  dt_checkinobject should be called instead.
	-- Calls are forwarded to dt_checkinobject to maintain backward compatibility.
	set nocount on
	exec dbo.dt_checkinobject
		@chObjectType,
		@vchObjectName,
		@vchComment,
		@vchLoginName,
		@vchPassword,
		@iVCSFlags,
		@iActionFlag,   
		@txStream1,		
		@txStream2,		
		@txStream3		


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_checkoutobject
DELIMITER //
create proc dbo.dt_checkoutobject
    @chObjectType  char(4),
    @vchObjectName varchar(255),
    @vchComment    varchar(255),
    @vchLoginName  varchar(255),
    @vchPassword   varchar(255),
    @iVCSFlags     int = 0,
    @iActionFlag   int = 0/* 0 => Checkout, 1 => GetLatest, 2 => UndoCheckOut */

as

	set nocount on

	declare @iReturn int
	declare @iObjectId int
	select @iObjectId =0

	declare @VSSGUID varchar(100)
	select @VSSGUID = 'SQLVersionControl.VCS_SQL'

	declare @iReturnValue int
	select @iReturnValue = 0

	declare @vchTempText varchar(255)

	/* this is for our strings */
	declare @iStreamObjectId int
	select @iStreamObjectId = 0

    declare @iPropertyObjectId int
    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    declare @vchProjectName   varchar(255)
    declare @vchSourceSafeINI varchar(255)
    declare @vchServerName    varchar(255)
    declare @vchDatabaseName  varchar(255)
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSProject',       @vchProjectName   OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSourceSafeINI', @vchSourceSafeINI OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLServer',     @vchServerName    OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLDatabase',   @vchDatabaseName  OUT

    if @chObjectType = 'PROC'
    begin
        /* Procedure Can have up to three streams
           Drop Stream, Create Stream, GRANT stream */

        exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT

        if @iReturn <> 0 GOTO E_OAError

        exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
												'CheckOut_StoredProcedure',
												NULL,
												@sProjectName = @vchProjectName,
												@sSourceSafeINI = @vchSourceSafeINI,
												@sObjectName = @vchObjectName,
												@sServerName = @vchServerName,
												@sDatabaseName = @vchDatabaseName,
												@sComment = @vchComment,
												@sLoginName = @vchLoginName,
												@sPassword = @vchPassword,
												@iVCSFlags = @iVCSFlags,
												@iActionFlag = @iActionFlag

        if @iReturn <> 0 GOTO E_OAError


        exec @iReturn = master.dbo.sp_OAGetProperty @iObjectId, 'GetStreamObject', @iStreamObjectId OUT

        if @iReturn <> 0 GOTO E_OAError

        create table #commenttext (id int identity, sourcecode varchar(255))


        select @vchTempText = 'STUB'
        while @vchTempText is not null
        begin
            exec @iReturn = master.dbo.sp_OAMethod @iStreamObjectId, 'GetStream', @iReturnValue OUT, @vchTempText OUT
            if @iReturn <> 0 GOTO E_OAError
            
            if (@vchTempText = '') set @vchTempText = null
            if (@vchTempText is not null) insert into #commenttext (sourcecode) select @vchTempText
        end

        select 'VCS'=sourcecode from #commenttext order by id
        select 'SQL'=text from syscomments where id = object_id(@vchObjectName) order by colid

    end

CleanUp:
    return

E_OAError:
    exec dbo.dt_displayoaerror @iObjectId, @iReturn
    GOTO CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_checkoutobject_u
DELIMITER //
create proc dbo.dt_checkoutobject_u
    @chObjectType  char(4),
    @vchObjectName nvarchar(255),
    @vchComment    nvarchar(255),
    @vchLoginName  nvarchar(255),
    @vchPassword   nvarchar(255),
    @iVCSFlags     int = 0,
    @iActionFlag   int = 0/* 0 => Checkout, 1 => GetLatest, 2 => UndoCheckOut */

as

	-- This procedure should no longer be called;  dt_checkoutobject should be called instead.
	-- Calls are forwarded to dt_checkoutobject to maintain backward compatibility.
	set nocount on
	exec dbo.dt_checkoutobject
		@chObjectType,  
		@vchObjectName, 
		@vchComment,    
		@vchLoginName,  
		@vchPassword,  
		@iVCSFlags,    
		@iActionFlag 


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_displayoaerror
DELIMITER //
CREATE PROCEDURE dbo.dt_displayoaerror
    @iObject int,
    @iresult int
as

set nocount on

declare @vchOutput      varchar(255)
declare @hr             int
declare @vchSource      varchar(255)
declare @vchDescription varchar(255)

    exec @hr = master.dbo.sp_OAGetErrorInfo @iObject, @vchSource OUT, @vchDescription OUT

    select @vchOutput = @vchSource + ': ' + @vchDescription
    raiserror (@vchOutput,16,-1)

    return

//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_displayoaerror_u
DELIMITER //
CREATE PROCEDURE dbo.dt_displayoaerror_u
    @iObject int,
    @iresult int
as
	-- This procedure should no longer be called;  dt_displayoaerror should be called instead.
	-- Calls are forwarded to dt_displayoaerror to maintain backward compatibility.
	set nocount on
	exec dbo.dt_displayoaerror
		@iObject,
		@iresult


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_droppropertiesbyid
DELIMITER //
/*
**	Drop one or all the associated properties of an object or an attribute 
**
**	dt_dropproperties objid, null or '' -- drop all properties of the object itself
**	dt_dropproperties objid, property -- drop the property
*/
create procedure dbo.dt_droppropertiesbyid
	@id int,
	@property varchar(64)
as
	set nocount on

	if (@property is null) or (@property = '')
		delete from dbo.dtproperties where objectid=@id
	else
		delete from dbo.dtproperties 
			where objectid=@id and property=@property

//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_dropuserobjectbyid
DELIMITER //
/*
**	Drop an object from the dbo.dtproperties table
*/
create procedure dbo.dt_dropuserobjectbyid
	@id int
as
	set nocount on
	delete from dbo.dtproperties where objectid=@id
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_generateansiname
DELIMITER //
/* 
**	Generate an ansi name that is unique in the dtproperties.value column 
*/ 
create procedure dbo.dt_generateansiname(@name varchar(255) output) 
as 
	declare @prologue varchar(20) 
	declare @indexstring varchar(20) 
	declare @index integer 
 
	set @prologue = 'MSDT-A-' 
	set @index = 1 
 
	while 1 = 1 
	begin 
		set @indexstring = cast(@index as varchar(20)) 
		set @name = @prologue + @indexstring 
		if not exists (select value from dtproperties where value = @name) 
			break 
		 
		set @index = @index + 1 
 
		if (@index = 10000) 
			goto TooMany 
	end 
 
Leave: 
 
	return 
 
TooMany: 
 
	set @name = 'DIAGRAM' 
	goto Leave 
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getobjwithprop
DELIMITER //
/*
**	Retrieve the owner object(s) of a given property
*/
create procedure dbo.dt_getobjwithprop
	@property varchar(30),
	@value varchar(255)
as
	set nocount on

	if (@property is null) or (@property = '')
	begin
		raiserror('Must specify a property name.',-1,-1)
		return (1)
	end

	if (@value is null)
		select objectid id from dbo.dtproperties
			where property=@property

	else
		select objectid id from dbo.dtproperties
			where property=@property and value=@value
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getobjwithprop_u
DELIMITER //
/*
**	Retrieve the owner object(s) of a given property
*/
create procedure dbo.dt_getobjwithprop_u
	@property varchar(30),
	@uvalue nvarchar(255)
as
	set nocount on

	if (@property is null) or (@property = '')
	begin
		raiserror('Must specify a property name.',-1,-1)
		return (1)
	end

	if (@uvalue is null)
		select objectid id from dbo.dtproperties
			where property=@property

	else
		select objectid id from dbo.dtproperties
			where property=@property and uvalue=@uvalue
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getpropertiesbyid
DELIMITER //
/*
**	Retrieve properties by id's
**
**	dt_getproperties objid, null or '' -- retrieve all properties of the object itself
**	dt_getproperties objid, property -- retrieve the property specified
*/
create procedure dbo.dt_getpropertiesbyid
	@id int,
	@property varchar(64)
as
	set nocount on

	if (@property is null) or (@property = '')
		select property, version, value, lvalue
			from dbo.dtproperties
			where  @id=objectid
	else
		select property, version, value, lvalue
			from dbo.dtproperties
			where  @id=objectid and @property=property
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getpropertiesbyid_u
DELIMITER //
/*
**	Retrieve properties by id's
**
**	dt_getproperties objid, null or '' -- retrieve all properties of the object itself
**	dt_getproperties objid, property -- retrieve the property specified
*/
create procedure dbo.dt_getpropertiesbyid_u
	@id int,
	@property varchar(64)
as
	set nocount on

	if (@property is null) or (@property = '')
		select property, version, uvalue, lvalue
			from dbo.dtproperties
			where  @id=objectid
	else
		select property, version, uvalue, lvalue
			from dbo.dtproperties
			where  @id=objectid and @property=property
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getpropertiesbyid_vcs
DELIMITER //
create procedure dbo.dt_getpropertiesbyid_vcs
    @id       int,
    @property varchar(64),
    @value    varchar(255) = NULL OUT

as

    set nocount on

    select @value = (
        select value
                from dbo.dtproperties
                where @id=objectid and @property=property
                )

//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_getpropertiesbyid_vcs_u
DELIMITER //
create procedure dbo.dt_getpropertiesbyid_vcs_u
    @id       int,
    @property varchar(64),
    @value    nvarchar(255) = NULL OUT

as

    -- This procedure should no longer be called;  dt_getpropertiesbyid_vcsshould be called instead.
	-- Calls are forwarded to dt_getpropertiesbyid_vcs to maintain backward compatibility.
	set nocount on
    exec dbo.dt_getpropertiesbyid_vcs
		@id,
		@property,
		@value output

//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_isundersourcecontrol
DELIMITER //
create proc dbo.dt_isundersourcecontrol
    @vchLoginName varchar(255) = '',
    @vchPassword  varchar(255) = '',
    @iWhoToo      int = 0 /* 0 => Just check project; 1 => get list of objs */

as

	set nocount on

	declare @iReturn int
	declare @iObjectId int
	select @iObjectId = 0

	declare @VSSGUID varchar(100)
	select @VSSGUID = 'SQLVersionControl.VCS_SQL'

	declare @iReturnValue int
	select @iReturnValue = 0

	declare @iStreamObjectId int
	select @iStreamObjectId   = 0

	declare @vchTempText varchar(255)

    declare @iPropertyObjectId int
    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    declare @vchProjectName   varchar(255)
    declare @vchSourceSafeINI varchar(255)
    declare @vchServerName    varchar(255)
    declare @vchDatabaseName  varchar(255)
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSProject',       @vchProjectName   OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSourceSafeINI', @vchSourceSafeINI OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLServer',     @vchServerName    OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLDatabase',   @vchDatabaseName  OUT

    if (@vchProjectName = '')	set @vchProjectName		= null
    if (@vchSourceSafeINI = '') set @vchSourceSafeINI	= null
    if (@vchServerName = '')	set @vchServerName		= null
    if (@vchDatabaseName = '')	set @vchDatabaseName	= null
    
    if (@vchProjectName is null) or (@vchSourceSafeINI is null) or (@vchServerName is null) or (@vchDatabaseName is null)
    begin
        RAISERROR('Not Under Source Control',16,-1)
        return
    end

    if @iWhoToo = 1
    begin

        /* Get List of Procs in the project */
        exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT
        if @iReturn <> 0 GOTO E_OAError

        exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
												'GetListOfObjects',
												NULL,
												@vchProjectName,
												@vchSourceSafeINI,
												@vchServerName,
												@vchDatabaseName,
												@vchLoginName,
												@vchPassword

        if @iReturn <> 0 GOTO E_OAError

        exec @iReturn = master.dbo.sp_OAGetProperty @iObjectId, 'GetStreamObject', @iStreamObjectId OUT

        if @iReturn <> 0 GOTO E_OAError

        create table #ObjectList (id int identity, vchObjectlist varchar(255))

        select @vchTempText = 'STUB'
        while @vchTempText is not null
        begin
            exec @iReturn = master.dbo.sp_OAMethod @iStreamObjectId, 'GetStream', @iReturnValue OUT, @vchTempText OUT
            if @iReturn <> 0 GOTO E_OAError
            
            if (@vchTempText = '') set @vchTempText = null
            if (@vchTempText is not null) insert into #ObjectList (vchObjectlist ) select @vchTempText
        end

        select vchObjectlist from #ObjectList order by id
    end

CleanUp:
    return

E_OAError:
    exec dbo.dt_displayoaerror @iObjectId, @iReturn
    goto CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_isundersourcecontrol_u
DELIMITER //
create proc dbo.dt_isundersourcecontrol_u
    @vchLoginName nvarchar(255) = '',
    @vchPassword  nvarchar(255) = '',
    @iWhoToo      int = 0 /* 0 => Just check project; 1 => get list of objs */

as
	-- This procedure should no longer be called;  dt_isundersourcecontrol should be called instead.
	-- Calls are forwarded to dt_isundersourcecontrol to maintain backward compatibility.
	set nocount on
	exec dbo.dt_isundersourcecontrol
		@vchLoginName,
		@vchPassword,
		@iWhoToo 


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_removefromsourcecontrol
DELIMITER //
create procedure dbo.dt_removefromsourcecontrol

as

    set nocount on

    declare @iPropertyObjectId int
    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    exec dbo.dt_droppropertiesbyid @iPropertyObjectId, null

    /* -1 is returned by dt_droppopertiesbyid */
    if @@error <> 0 and @@error <> -1 return 1

    return 0


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_setpropertybyid
DELIMITER //
/*
**	If the property already exists, reset the value; otherwise add property
**		id -- the id in sysobjects of the object
**		property -- the name of the property
**		value -- the text value of the property
**		lvalue -- the binary value of the property (image)
*/
create procedure dbo.dt_setpropertybyid
	@id int,
	@property varchar(64),
	@value varchar(255),
	@lvalue image
as
	set nocount on
	declare @uvalue nvarchar(255) 
	set @uvalue = convert(nvarchar(255), @value) 
	if exists (select * from dbo.dtproperties 
			where objectid=@id and property=@property)
	begin
		--
		-- bump the version count for this row as we update it
		--
		update dbo.dtproperties set value=@value, uvalue=@uvalue, lvalue=@lvalue, version=version+1
			where objectid=@id and property=@property
	end
	else
	begin
		--
		-- version count is auto-set to 0 on initial insert
		--
		insert dbo.dtproperties (property, objectid, value, uvalue, lvalue)
			values (@property, @id, @value, @uvalue, @lvalue)
	end

//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_setpropertybyid_u
DELIMITER //
/*
**	If the property already exists, reset the value; otherwise add property
**		id -- the id in sysobjects of the object
**		property -- the name of the property
**		uvalue -- the text value of the property
**		lvalue -- the binary value of the property (image)
*/
create procedure dbo.dt_setpropertybyid_u
	@id int,
	@property varchar(64),
	@uvalue nvarchar(255),
	@lvalue image
as
	set nocount on
	-- 
	-- If we are writing the name property, find the ansi equivalent. 
	-- If there is no lossless translation, generate an ansi name. 
	-- 
	declare @avalue varchar(255) 
	set @avalue = null 
	if (@uvalue is not null) 
	begin 
		if (convert(nvarchar(255), convert(varchar(255), @uvalue)) = @uvalue) 
		begin 
			set @avalue = convert(varchar(255), @uvalue) 
		end 
		else 
		begin 
			if 'DtgSchemaNAME' = @property 
			begin 
				exec dbo.dt_generateansiname @avalue output 
			end 
		end 
	end 
	if exists (select * from dbo.dtproperties 
			where objectid=@id and property=@property)
	begin
		--
		-- bump the version count for this row as we update it
		--
		update dbo.dtproperties set value=@avalue, uvalue=@uvalue, lvalue=@lvalue, version=version+1
			where objectid=@id and property=@property
	end
	else
	begin
		--
		-- version count is auto-set to 0 on initial insert
		--
		insert dbo.dtproperties (property, objectid, value, uvalue, lvalue)
			values (@property, @id, @avalue, @uvalue, @lvalue)
	end
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_validateloginparams
DELIMITER //
create proc dbo.dt_validateloginparams
    @vchLoginName  varchar(255),
    @vchPassword   varchar(255)
as

set nocount on

declare @iReturn int
declare @iObjectId int
select @iObjectId =0

declare @VSSGUID varchar(100)
select @VSSGUID = 'SQLVersionControl.VCS_SQL'

    declare @iPropertyObjectId int
    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    declare @vchSourceSafeINI varchar(255)
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSourceSafeINI', @vchSourceSafeINI OUT

    exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT
    if @iReturn <> 0 GOTO E_OAError

    exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
											'ValidateLoginParams',
											NULL,
											@sSourceSafeINI = @vchSourceSafeINI,
											@sLoginName = @vchLoginName,
											@sPassword = @vchPassword
    if @iReturn <> 0 GOTO E_OAError

CleanUp:
    return

E_OAError:
    exec dbo.dt_displayoaerror @iObjectId, @iReturn
    GOTO CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_validateloginparams_u
DELIMITER //
create proc dbo.dt_validateloginparams_u
    @vchLoginName  nvarchar(255),
    @vchPassword   nvarchar(255)
as

	-- This procedure should no longer be called;  dt_validateloginparams should be called instead.
	-- Calls are forwarded to dt_validateloginparams to maintain backward compatibility.
	set nocount on
	exec dbo.dt_validateloginparams
		@vchLoginName,
		@vchPassword 


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_vcsenabled
DELIMITER //
create proc dbo.dt_vcsenabled

as

set nocount on

declare @iObjectId int
select @iObjectId = 0

declare @VSSGUID varchar(100)
select @VSSGUID = 'SQLVersionControl.VCS_SQL'

    declare @iReturn int
    exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT
    if @iReturn <> 0 raiserror('', 16, -1) /* Can't Load Helper DLLC */


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_verstamp006
DELIMITER //
/*
**	This procedure returns the version number of the stored
**    procedures used by legacy versions of the Microsoft
**	Visual Database Tools.  Version is 7.0.00.
*/
create procedure dbo.dt_verstamp006
as
	select 7000
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_verstamp007
DELIMITER //
/*
**	This procedure returns the version number of the stored
**    procedures used by the the Microsoft Visual Database Tools.
**	Version is 7.0.05.
*/
create procedure dbo.dt_verstamp007
as
	select 7005
//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_whocheckedout
DELIMITER //
create proc dbo.dt_whocheckedout
        @chObjectType  char(4),
        @vchObjectName varchar(255),
        @vchLoginName  varchar(255),
        @vchPassword   varchar(255)

as

set nocount on

declare @iReturn int
declare @iObjectId int
select @iObjectId =0

declare @VSSGUID varchar(100)
select @VSSGUID = 'SQLVersionControl.VCS_SQL'

    declare @iPropertyObjectId int

    select @iPropertyObjectId = (select objectid from dbo.dtproperties where property = 'VCSProjectID')

    declare @vchProjectName   varchar(255)
    declare @vchSourceSafeINI varchar(255)
    declare @vchServerName    varchar(255)
    declare @vchDatabaseName  varchar(255)
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSProject',       @vchProjectName   OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSourceSafeINI', @vchSourceSafeINI OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLServer',     @vchServerName    OUT
    exec dbo.dt_getpropertiesbyid_vcs @iPropertyObjectId, 'VCSSQLDatabase',   @vchDatabaseName  OUT

    if @chObjectType = 'PROC'
    begin
        exec @iReturn = master.dbo.sp_OACreate @VSSGUID, @iObjectId OUT

        if @iReturn <> 0 GOTO E_OAError

        declare @vchReturnValue varchar(255)
        select @vchReturnValue = ''

        exec @iReturn = master.dbo.sp_OAMethod @iObjectId,
												'WhoCheckedOut',
												@vchReturnValue OUT,
												@sProjectName = @vchProjectName,
												@sSourceSafeINI = @vchSourceSafeINI,
												@sObjectName = @vchObjectName,
												@sServerName = @vchServerName,
												@sDatabaseName = @vchDatabaseName,
												@sLoginName = @vchLoginName,
												@sPassword = @vchPassword

        if @iReturn <> 0 GOTO E_OAError

        select @vchReturnValue

    end

CleanUp:
    return

E_OAError:
    exec dbo.dt_displayoaerror @iObjectId, @iReturn
    GOTO CleanUp


//
DELIMITER ;


-- Dumping structure for procedure farmacias.dt_whocheckedout_u
DELIMITER //
create proc dbo.dt_whocheckedout_u
        @chObjectType  char(4),
        @vchObjectName nvarchar(255),
        @vchLoginName  nvarchar(255),
        @vchPassword   nvarchar(255)

as

	-- This procedure should no longer be called;  dt_whocheckedout should be called instead.
	-- Calls are forwarded to dt_whocheckedout to maintain backward compatibility.
	set nocount on
	exec dbo.dt_whocheckedout
		@chObjectType, 
		@vchObjectName,
		@vchLoginName, 
		@vchPassword  


//
DELIMITER ;


-- Dumping structure for table farmacias.ElectroTerapia
CREATE TABLE IF NOT EXISTS "ElectroTerapia" (
	"codelectro" NVARCHAR(5) NOT NULL,
	"deselectro" NVARCHAR(100) NOT NULL,
	"hospitalizacion" NVARCHAR(1) NOT NULL DEFAULT ('0'),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Empresa
CREATE TABLE IF NOT EXISTS "Empresa" (
	"Rif" NVARCHAR(30) NULL DEFAULT NULL,
	"Nit" NVARCHAR(30) NULL DEFAULT NULL,
	"Nombre" NVARCHAR(100) NULL DEFAULT NULL,
	"Direccion" NVARCHAR(255) NULL DEFAULT NULL,
	"Telefonos" NVARCHAR(50) NULL DEFAULT NULL,
	"Fax" NVARCHAR(50) NULL DEFAULT NULL,
	"Surcursal" NVARCHAR(100) NULL DEFAULT NULL,
	"Desde" DATETIME(3) NULL DEFAULT NULL,
	"UltimaFactura" NVARCHAR(10) NULL DEFAULT NULL,
	"UltimaFacturaVirtual" NVARCHAR(7) NULL DEFAULT NULL,
	"UltimoCliente" NVARCHAR(10) NULL DEFAULT NULL,
	"UltimoPedido" NVARCHAR(10) NULL DEFAULT NULL,
	"UltimaCotizacion" NVARCHAR(10) NULL DEFAULT NULL,
	"UltimoAjuste" NVARCHAR(10) NULL DEFAULT NULL,
	"UltimoMedico" NVARCHAR(3) NULL DEFAULT NULL,
	"ultimoEntrega" NVARCHAR(10) NULL DEFAULT (0),
	"UltimoCredito" NVARCHAR(10) NULL DEFAULT (0),
	"UltimoPresupuesto" NVARCHAR(6) NULL DEFAULT NULL,
	"Ultimoservicio" NVARCHAR(10) NOT NULL DEFAULT (''),
	"Id_centro" NVARCHAR(1) NULL DEFAULT (0),
	"FechaCierreInventario" DATETIME(3) NULL DEFAULT NULL,
	"PWTRANFEREN" NVARCHAR(10) NULL DEFAULT NULL,
	"PRESUPUESTO" NUMERIC(18,0) NULL DEFAULT NULL,
	"PRESU_PIEPAGINA" TEXT(2147483647) NULL DEFAULT NULL,
	"ALICUOTA" MONEY(19,4) NULL DEFAULT NULL,
	"PUNTO_COMA" NVARCHAR(1) NULL DEFAULT NULL,
	"factor" NUMERIC(18,0) NULL DEFAULT NULL,
	"sucursal" NVARCHAR(100) NULL DEFAULT NULL,
	"Ultimodescuento" NVARCHAR(3) NULL DEFAULT NULL,
	"Ultimotecnico" NVARCHAR(3) NULL DEFAULT NULL,
	"codsuc" NVARCHAR(5) NULL DEFAULT NULL,
	"ultimahistoria" NVARCHAR(10) NULL DEFAULT NULL,
	"tasa_iva" MONEY(19,4) NULL DEFAULT NULL,
	"descuento" BIT NULL DEFAULT NULL,
	"impuesto" BIT NOT NULL DEFAULT (0),
	"ultimatransf" NVARCHAR(10) NOT NULL DEFAULT (0),
	"formato" NVARCHAR(50) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.emt_exo_view
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "emt_exo_view" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totimpuesto" INT NOT NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" INT NOT NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tventa" INT NOT NULL,
	"medico" NVARCHAR(52) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(8) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.emt_view
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "emt_view" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" INT NULL,
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"Dventa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(52) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.estaciones
CREATE TABLE IF NOT EXISTS "estaciones" (
	"codestac" NVARCHAR(2) NOT NULL,
	"desestac" NVARCHAR(30) NULL DEFAULT NULL,
	"id_centro" NVARCHAR(1) NULL DEFAULT (1),
	"fondo_inicial" MONEY(19,4) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Estadistica
CREATE TABLE IF NOT EXISTS "Estadistica" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"mes" INT(10,0) NULL DEFAULT NULL,
	"ayear" INT(10,0) NULL DEFAULT NULL,
	"venta" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.EstadisticasCMA
DELIMITER //


CREATE  procedure EstadisticasCMA
as 
begin
 declare @codmedico nvarchar(3)
 declare @paccon numeric     /* pacientes de control       */
 declare @pacnue numeric     /* pacientes de nuevos        */
 declare @pacser numeric     /* pacientes de servicios     */
 declare @it numeric         /* indice de tratamiento      */
 declare @potes numeric      /* total de potes             */
 declare @facprod numeric    /* facturacion de potes en Bs */
 declare @facserv numeric    /* facturacion de servicios   */
 declare @totene numeric     /* total energimetros         */
 declare @totsue numeric     /* total suero                */
 declare @totele numeric     /* total electrocardiogramas  */
 declare @comisiones numeric /* totla comisiones por productos */
 declare @terneural numeric  /* Calcula total de terapias neurales por medicos */
 declare @comserv numeric    /* totla comisionespopr sevicios  */
 declare @pacprobs numeric   /* total de pacientes que llevaron producto*/
 declare @dcto  numeric    /* calcula los descuentos por medicos*/
 declare @nc numeric /* total notas de creditos por medicos*/
 declare @fec_i datetime
 declare @codsuc  nvarchar(2)
 set @fec_i =convert(nvarchar(10),getdate() - 1,112)
 set @codsuc='01'
 if exists(select * from view_it where codsuc=@codsuc and fecha_cita=@fec_i )
  begin
  if not exists(select * from  dbo.EstadisticasGlobales where codsuc=@codsuc and fecha=@fec_i )
  begin
   declare medicos cursor for select codmedico from dbo.mmedicos
   open medicos
   fetch next from medicos into @codmedico  
   while @@fetch_status=0 /* medicos */
    begin
     
      /* inicio de recorrido de fecha */
     
       /* calcula pacientes de contol*/
       select @paccon = isnull(count(*),0) from dbo.mconsultas where codmedico=@codmedico and primera_control='0' and fecha_cita=@fec_i and asistido='3' and activa='1'
       /* calcula pacientes de nuevos*/
       select @pacnue = isnull(count(*),0) from dbo.mconsultas where codmedico=@codmedico and primera_control='1' and fecha_cita=@fec_i and asistido='3' and activa='1'       
       /* calcula total pacientes de servcio por medicos */
       select @pacser = isnull(count(*),0) from dbo.mconsultas where codmedico=@codmedico and servicios='1' and fecha_cita=@fec_i and asistido='3' and activa='1'       
       /* calcula facturacion de potes por dia del medico en unidades y en Bs */
       select @potes=isnull(sum(cantidad),0), @facprod=isnull(sum(cantidad * precunit),0) from dbo.dfactura left join dbo.mfactura on dbo.dfactura.numfactu=dbo.mfactura.numfactu where dbo.mfactura.statfact <>'2' and dbo.mfactura.codmedico=@codmedico and dbo.mfactura.fechafac=@fec_i
       /* calcula facturacion de servicios por dia del medico */
       select @facserv=isnull(sum(cantidad*precunit),0) from dbo.cma_dfactura left join dbo.cma_mfactura on dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu where dbo.cma_mfactura.statfact<>'2' and dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_mfactura.fechafac=@fec_i
       /* calcula total de energimetros por medicos */
       select @totene=sum(cantidad) from dbo.cma_dfactura left JOIN  dbo.cma_mfactura  on dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu where dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_mfactura.fechafac=@fec_i and dbo.cma_mfactura.statfact<>'2' and (dbo.cma_dfactura.coditems='3000000002' or dbo.cma_dfactura.coditems='3000000003' or dbo.cma_dfactura.coditems='3000000005' or dbo.cma_dfactura.coditems='3000000006')
       /*  calcula total de sueroterapias por medicos */
       select @totsue= sum(cantidad) from dbo.cma_dfactura left JOIN  dbo.cma_mfactura  on dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu where dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_mfactura.fechafac=@fec_i and dbo.cma_mfactura.statfact<>'2' and (dbo.cma_dfactura.coditems='3000000001' )
       /*  calcula total de electrocardiograma  por medicos */
       select @totele=sum(cantidad) from dbo.cma_dfactura left JOIN  dbo.cma_mfactura  on dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu where dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_mfactura.fechafac=@fec_i and dbo.cma_mfactura.statfact<>'2' and (dbo.cma_dfactura.coditems='3000000004' )
 /*  calcula total de terapias neurales  por medicos */
       select @terneural=isnull(sum(cantidad),0) from dbo.cma_dfactura left JOIN  dbo.cma_mfactura  on dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu where dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_mfactura.fechafac=@fec_i and dbo.cma_mfactura.statfact<>'2' and (dbo.cma_dfactura.coditems='3000000007' )
       /* Base Comisiones por productos de  medicos diarias */
        select @comisiones=isnull(sum(cantidad*precunit),0) from dbo.dfactura left   join dbo.minventario on (dbo.dfactura.coditems=dbo.minventario.coditems) left   join dbo.mfactura on (dbo.dfactura.numfactu=dbo.mfactura.numfactu) where dbo.mfactura.codmedico=@codmedico and dbo.dfactura.fechafac=@fec_i and dbo.dfactura.aplicacommed='1' and dbo.mfactura.statfact<>'2' 
       /* calcula descuentos por productos de medicos*/
       select @dcto=isnull(sum(descuento),0) from dbo.mfactura where codmedico=@codmedico and fechafac=@fec_i and statfact<>'2'
       set @comisiones = @comisiones-@dcto
       /**/
       /* Base Comisiones por servicios de  medicos diarias */
       select @comserv=isnull(sum(cantidad*precunit),0) from dbo.cma_dfactura left  join dbo.minventario on (dbo.cma_dfactura.coditems=dbo.minventario.coditems) left   join dbo.cma_mfactura on (dbo.cma_dfactura.numfactu=dbo.cma_mfactura.numfactu) where dbo.cma_mfactura.codmedico=@codmedico and dbo.cma_dfactura.fechafac=@fec_i and dbo.cma_dfactura.aplicacommed='1' and dbo.cma_mfactura.statfact<>'2'          
       /* calcula los descuentos por servicios de medicos*/
        select @dcto=isnull(sum(descuento),0) from dbo.cma_mfactura where codmedico=@codmedico and fechafac=@fec_i and statfact<>'2'
        set @comserv=@comserv-@dcto
	/* Calcula las notas de creditos por medicos para calculo de comisiones  */
        select @nc=isnull(sum(cantidad*precunit),0)  from dbo.dnotacredito left join dbo.minventario on dbo.dnotacredito.coditems=dbo.minventario.coditems left join dbo.mnotacredito on dbo.dnotacredito.numnotcre=dbo.mnotacredito.numnotcre where statnc<>'2' and codmedico=@codmedico and dbo.mnotacredito.fechanot=@Fec_i
        /* */
       /* total de pacientes por medico que asisterion a consulta y que compraron */
       select @pacprobs=isnull(count(*),0) from view_it where codsuc=@codsuc and codmedico=@codmedico and fecha_cita=@fec_i and MONP_Bs > 0
       insert into dbo.EstadisticasGlobales (codsuc,fecha,codmedico,paccon,pacnue,pacser,potes,facprod,facserv,totene,totsue,totele,comisiones,pacprodbs,comserv,nc,terneural) values(@codsuc,@fec_i,@codmedico,@paccon,@pacnue,@pacser,@potes,@facprod,@facserv,@totene,@totsue,@totele,@comisiones,@pacprobs,@comserv,@nc,@terneural)
      /* fin de recorrido de fecha */
      fetch next from medicos into @codmedico  
   end
  close medicos
  deallocate medicos
 end
end
end//
DELIMITER ;


-- Dumping structure for table farmacias.EstadisticasConsultas
CREATE TABLE IF NOT EXISTS "EstadisticasConsultas" (
	"fecha" DATETIME(3) NOT NULL,
	"usuario" NVARCHAR(15) NOT NULL,
	"citados" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"confirmado" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"asistido" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"noasistido" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"Pnuevos" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"pcontrol" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"efectividad" NUMERIC(18,2) NOT NULL DEFAULT (0),
	"movido" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"NoCitados" NUMERIC(18,0) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("fecha","usuario")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.EstadisticasGlobales
CREATE TABLE IF NOT EXISTS "EstadisticasGlobales" (
	"codsuc" NVARCHAR(2) NOT NULL,
	"fecha" DATETIME(3) NOT NULL,
	"codmedico" NVARCHAR(3) NOT NULL,
	"paccon" NUMERIC(18,0) NULL DEFAULT NULL,
	"pacnue" NUMERIC(18,0) NULL DEFAULT NULL,
	"pacser" NUMERIC(18,0) NULL DEFAULT NULL,
	"IT" NUMERIC(18,0) NULL DEFAULT NULL,
	"potpac" NUMERIC(18,0) NULL DEFAULT NULL,
	"facpas" NUMERIC(18,0) NULL DEFAULT NULL,
	"potes" NUMERIC(18,0) NULL DEFAULT NULL,
	"facprod" NUMERIC(18,2) NULL DEFAULT NULL,
	"facserv" NUMERIC(18,2) NULL DEFAULT NULL,
	"promfac" NUMERIC(18,2) NULL DEFAULT NULL,
	"totene" NUMERIC(18,0) NULL DEFAULT NULL,
	"totsue" NUMERIC(18,0) NULL DEFAULT NULL,
	"totele" NUMERIC(18,0) NULL DEFAULT NULL,
	"comisiones" NUMERIC(18,2) NULL DEFAULT NULL,
	"PacProdBs" NUMERIC(18,0) NULL DEFAULT NULL,
	"comServ" NUMERIC(18,2) NULL DEFAULT NULL,
	"NC" NUMERIC(18,2) NULL DEFAULT NULL,
	"Terneural" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codsuc","fecha","codmedico")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.EstadisticasMIT
DELIMITER //
CREATE  procedure EstadisticasMIT
as
begin
declare @fec datetime
set @fec=convert(nvarchar(10),getdate() - 1,112)

  /* cma BAYAMON */
  if exists(select * from farmacias.dbo.mcierreinventario where fechacierre=@fec and status='1') 
   begin
    if not exists(select * from farmacias.dbo.mit where fecha_cita=@fec and codsuc='01')
     begin 
      insert into farmacias.dbo.MIT  (codclien,fecha,fecha_cita,primera_control,asistido,codmedico,nombre,apellido,monp_bs,mons_bs,items,activa,horamf,horacma,codsuc)
      select * from farmacias.dbo.view_IT where farmacias.dbo.view_IT.fecha_cita = @fec
    end 
   end 

end//
DELIMITER ;


-- Dumping structure for table farmacias.evaluacion
CREATE TABLE IF NOT EXISTS "evaluacion" (
	"Historia" NVARCHAR(12) NULL DEFAULT NULL,
	"CI" NVARCHAR(10) NULL DEFAULT NULL,
	"cie" NVARCHAR(8) NULL DEFAULT NULL,
	"Comentario" TEXT(2147483647) NULL DEFAULT (''),
	"coditems" NVARCHAR(20) NULL DEFAULT NULL,
	"clascie" NVARCHAR(8) NULL DEFAULT NULL,
	"Visita" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.exclusivos
CREATE TABLE IF NOT EXISTS "exclusivos" (
	"id" INT(10,0) NOT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"producto" NVARCHAR(100) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Feriado
CREATE TABLE IF NOT EXISTS "Feriado" (
	"FERIADO" NVARCHAR(40) NULL DEFAULT NULL,
	"FECHA" SMALLDATETIME(0) NOT NULL,
	"Activo" NUMERIC(18,0) NULL DEFAULT NULL,
	"codigo" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.filelog
DELIMITER //

CREATE proc filelog as
backup log farmacias  with truncate_only
dbcc shrinkfile (farmacias_log,100)
//
DELIMITER ;


-- Dumping structure for table farmacias.FormaPago
CREATE TABLE IF NOT EXISTS "FormaPago" (
	"codformapago" INT(10,0) NOT NULL,
	"nombre" NVARCHAR(50) NULL DEFAULT NULL,
	"trans_electronica" BIT NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	"initials" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.FORMULA500
CREATE TABLE IF NOT EXISTS "FORMULA500" (
	"CODIGO" INT(10,0) NULL DEFAULT NULL,
	"CODITEMS" NVARCHAR(12) NULL DEFAULT NULL,
	"TRIANGULO" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.gateways
CREATE TABLE IF NOT EXISTS "gateways" (
	"id" INT(10,0) NOT NULL,
	"idcarrier" INT(10,0) NULL DEFAULT NULL,
	"gateway" NVARCHAR(255) NULL DEFAULT NULL,
	"tipo" NVARCHAR(50) NULL DEFAULT NULL,
	"created" TIMESTAMP NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.GeneraPagos
DELIMITER //
CREATE PROCEDURE GeneraPagos  AS

DELETE FROM PagosPR
DELETE FROM PagosPRCMA

INSERT INTO PagosPR
                      (fechapago, modopago, monto, codsuc)
SELECT     fechapago, DesTipoTargeta, SUM(monto) AS MontoTotal, '01' AS suc
FROM         VIEWpagosPR WHERE statfact <> '2'
GROUP BY fechapago, DesTipoTargeta
INSERT INTO PagosPRCMA
                      (fechapago, modopago, monto, codsuc)
SELECT     fechapago, DesTipoTargeta, SUM(monto) AS MontoTotal, '01' AS suc
FROM         VIEWpagosPRCMA  WHERE statfact <> '2'
GROUP BY fechapago, DesTipoTargeta//
DELIMITER ;


-- Dumping structure for procedure farmacias.GeneraSemanas
DELIMITER //
CREATE PROCEDURE GeneraSemanas AS
declare @fechainicio as datetime
declare @fechafinal as datetime
declare @fecha as datetime
declare @semana as integer
declare @semanaold as integer

set @fechainicio = cast('01/01/2008' as datetime) 
set @fechafinal = cast('12/31/2008' as datetime) 
set @semanaold = 0

WHILE @fechainicio < @fechafinal
BEGIN

   set @semana = DATEPART(wk, @fechainicio)
   if @semana <> @semanaold 
   begin   
      if not exists(select codsuc, codsemana,fec_I, fec_F from Msemanas where codsuc='01' and codsemana=@semana and fec_I=@fechainicio and fec_F=@fechainicio + 5 )
      begin
          insert into Msemanas (codsuc, codsemana,fec_I, fec_F) values ('01',@semana,@fechainicio,@fechainicio + 5)
      end
   end 
   set @semanaold = @semana
   set @fechainicio = @fechainicio + 1
   
END
//
DELIMITER ;


-- Dumping structure for function farmacias.getDateMaxMinTD
DELIMITER //
CREATE function [dbo].[getDateMaxMinTD](@fi datetime , @ff datetime ,@codcl VARCHAR(5) ,@minMax VARCHAR(3) )
  returns datetime
  as
  begin
   
    declare @fecha datetime
	If @minMax='Max'
	BEGIN
		Select @fecha = MAX(fecha_cita) from MconsultaSS  where codclien=@codcl and fecha_cita between @fi and @ff and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12')
	end
	If @minMax='Min'
	BEGIN
		Select @fecha = MIN(fecha_cita) from MconsultaSS  where codclien=@codcl and fecha_cita between @fi and @ff and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12')
	end
    return @fecha 

 end

//
DELIMITER ;


-- Dumping structure for function farmacias.getExistencia
DELIMITER //
create function getExistencia(@cod VARCHAR(10))
  returns varchar(10)
  as
  begin
    declare @saldo float
    Select @saldo= GrExist from MInventario where coditems=@cod
    return @saldo
 end
//
DELIMITER ;


-- Dumping structure for function farmacias.getMaxMinRepeatV4
DELIMITER //
Create function [dbo].[getMaxMinRepeatV4](@codcl VARCHAR(5),@coditems VARCHAR(10) ,@minMax VARCHAR(3) )
  returns datetime
  as
  begin
   
    declare @fecha datetime
	If @minMax='Max'
	BEGIN
		--Select @fecha = MAX(fecha_cita) from MconsultaSS  where codclien=@codcl and fecha_cita between @fi and @ff and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12')
	
	select @fecha = MAX(a.fechafac )  from MFactura  a 
inner join DFactura b on a.numfactu=b.numfactu
inner join MClientes c on a.codclien=c.codclien
inner join MInventario d on b.coditems=d.coditems
where a.codclien =@codcl and b.coditems=@coditems
	
	end
	If @minMax='Min'
	BEGIN
	--	Select @fecha = MIN(fecha_cita) from MconsultaSS  where codclien=@codcl and fecha_cita between @fi and @ff and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12')
	
		select @fecha = MIN(a.fechafac )  from MFactura  a 
inner join DFactura b on a.numfactu=b.numfactu
inner join MClientes c on a.codclien=c.codclien
inner join MInventario d on b.coditems=d.coditems
where a.codclien =@codcl and b.coditems=@coditems
	end
    return @fecha 

 end
//
DELIMITER ;


-- Dumping structure for function farmacias.getRemainingTD
DELIMITER //
CREATE function [dbo].[getRemainingTD](@fi datetime , @ff datetime ,@codcl VARCHAR(5) ,@asistido int )
  returns int
  as
  begin
    declare @saldo int
    Select @saldo=count(*) from MconsultaSS  where codclien=@codcl and fecha_cita between  @fi and @ff and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12') and asistido=@asistido
	--Select @saldo=count(*) from MconsultaSS  where codclien=@codcl and fecha_cita between  @fi and @ff -- and coditems in ('TD01','TD03','TD05','TD06','TD09','TD10','TD12') and asistido=@asistido
    return @saldo
 end
//
DELIMITER ;


-- Dumping structure for table farmacias.hexistencia
CREATE TABLE IF NOT EXISTS "hexistencia" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"existencia" NUMERIC(18,0) NULL DEFAULT NULL,
	"fechacierre" DATETIME(3) NULL DEFAULT NULL,
	"ventas" NUMERIC(18,0) NULL DEFAULT NULL,
	"DVentas" NUMERIC(18,0) NULL DEFAULT NULL,
	"compra" NUMERIC(18,0) NULL DEFAULT NULL,
	"devcompra" NUMERIC(18,0) NULL DEFAULT NULL,
	"Ajustes_mas" NUMERIC(18,0) NULL DEFAULT NULL,
	"Ajustes_menos" NUMERIC(18,0) NULL DEFAULT NULL,
	"NE" NUMERIC(18,0) NULL DEFAULT NULL,
	"Nc" NUMERIC(18,0) NULL DEFAULT NULL,
	"desitems" NVARCHAR(255) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Horario
CREATE TABLE IF NOT EXISTS "Horario" (
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"HoraI" NVARCHAR(20) NULL DEFAULT NULL,
	"HoraS" NVARCHAR(20) NULL DEFAULT NULL,
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.idcard
CREATE TABLE IF NOT EXISTS "idcard" (
	"id" INT(10,0) NOT NULL,
	"idcompany" NVARCHAR(10) NULL DEFAULT NULL,
	"factura" NVARCHAR(50) NULL DEFAULT NULL,
	"cnumber" NVARCHAR(50) NULL DEFAULT NULL,
	"tipo_doc" NVARCHAR(10) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"codforpa" NVARCHAR(10) NULL DEFAULT NULL,
	"monto" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(50) NULL DEFAULT NULL,
	"hora" NVARCHAR(20) NULL DEFAULT NULL,
	"tipotarjeta" NVARCHAR(10) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.IM
CREATE TABLE IF NOT EXISTS "IM" (
	"cod_IM" INT(10,0) NOT NULL,
	"Descripcion" NVARCHAR(50) NULL DEFAULT NULL,
	"enfoque" NVARCHAR(10) NULL DEFAULT NULL,
	"cuota_en" NVARCHAR(10) NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(10) NULL DEFAULT NULL,
	"cod_sub_grupo" NVARCHAR(10) NULL DEFAULT NULL,
	"abreviatura" NVARCHAR(10) NULL DEFAULT NULL,
	"campo_graf" NVARCHAR(20) NOT NULL,
	"activo" NVARCHAR(1) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("cod_IM")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.IMPORTACION
CREATE TABLE IF NOT EXISTS "IMPORTACION" (
	"FACTURA_I" NVARCHAR(50) NULL DEFAULT NULL,
	"FECHA" DATETIME(3) NULL DEFAULT NULL,
	"IP" NVARCHAR(50) NULL DEFAULT NULL,
	"CODITEMS" NVARCHAR(10) NULL DEFAULT NULL,
	"CANTIDAD" NUMERIC(18,0) NULL DEFAULT NULL,
	"TRANSFERIDO" NVARCHAR(1) NULL DEFAULT NULL,
	"FAC_TRANS" NVARCHAR(50) NULL DEFAULT NULL,
	"USUARIO" NVARCHAR(30) NULL DEFAULT NULL,
	"HORA" DATETIME(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Impresoras
CREATE TABLE IF NOT EXISTS "Impresoras" (
	"DeviceName" NVARCHAR(50) NULL DEFAULT NULL,
	"DriverName" NVARCHAR(50) NULL DEFAULT NULL,
	"Port" NVARCHAR(50) NULL DEFAULT NULL,
	"codestac" NVARCHAR(50) NULL DEFAULT NULL,
	"CODVENDE" NVARCHAR(15) NULL DEFAULT NULL,
	"ANU_AUTO" NVARCHAR(2) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Impuestos
CREATE TABLE IF NOT EXISTS "Impuestos" (
	"Impuesto" NVARCHAR(50) NULL DEFAULT NULL,
	"Porcentaje" FLOAT(53) NULL DEFAULT (0),
	"Activo" INT(10,0) NULL DEFAULT (1),
	"AplicaYN" INT(10,0) NULL DEFAULT (1),
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"Usuario" CHAR(10) NULL DEFAULT NULL,
	"Codigo" NUMERIC(18,0) NOT NULL,
	"Borrado" INT(10,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Codigo")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.ImpxFact
CREATE TABLE IF NOT EXISTS "ImpxFact" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"codimp" NUMERIC(18,0) NOT NULL,
	"base" MONEY(19,4) NOT NULL,
	"porcentaje" FLOAT(53) NOT NULL DEFAULT (0),
	"montoimp" MONEY(19,4) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codimp")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.ImpxFactCMA
CREATE TABLE IF NOT EXISTS "ImpxFactCMA" (
	"numfactu" NVARCHAR(7) NOT NULL,
	"codimp" NUMERIC(18,0) NOT NULL,
	"base" MONEY(19,4) NOT NULL,
	"porcentaje" FLOAT(53) NOT NULL,
	"montoimp" MONEY(19,4) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.ImpxFactDevProd
CREATE TABLE IF NOT EXISTS "ImpxFactDevProd" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"codimp" NUMERIC(18,0) NOT NULL,
	"base" MONEY(19,4) NOT NULL,
	"porcentaje" FLOAT(53) NOT NULL DEFAULT ((0)),
	"montoimp" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codimp")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.INVASLT
CREATE TABLE IF NOT EXISTS "INVASLT" (
	"CODITEMS" NVARCHAR(20) NULL DEFAULT NULL,
	"CANTIDAD" DECIMAL(18,0) NULL DEFAULT NULL,
	"FECHA" DATETIME(3) NULL DEFAULT NULL,
	"HORA" NVARCHAR(20) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.inventario
CREATE TABLE IF NOT EXISTS "inventario" (
	"coditems" NVARCHAR(10) NOT NULL,
	"desitems" NVARCHAR(255) NOT NULL DEFAULT (''),
	"existencia" FLOAT(53) NULL DEFAULT ((0)),
	"ubicacion" NVARCHAR(50) NULL DEFAULT NULL,
	"fecing" DATETIME(3) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT ((1)),
	"ultent" NUMERIC(18,0) NULL DEFAULT ((0)),
	"Exisminima" NUMERIC(18,0) NULL DEFAULT ((0)),
	"Exismaxima" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fecultent" DATETIME(3) NULL DEFAULT NULL,
	"ultlote" NVARCHAR(7) NULL DEFAULT NULL,
	"Prod_serv" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(8) NULL DEFAULT NULL,
	"aplicaIva" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"aplicadcto" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"aplicaComMed" NVARCHAR(1) NULL DEFAULT ((0)),
	"aplicaComTec" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"especial" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"rowguid" UNIQUEIDENTIFIER NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT ('003'),
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"costo" FLOAT(53) NULL DEFAULT ((0)),
	"nombre_alterno" NVARCHAR(100) NULL DEFAULT NULL,
	"Inventariable" NVARCHAR(50) NULL DEFAULT NULL,
	"GrStock" FLOAT(53) NULL DEFAULT NULL,
	"GrExist" FLOAT(53) NULL DEFAULT NULL,
	"medida" FLOAT(53) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"presentacion" NVARCHAR(50) NULL DEFAULT NULL,
	"usemult" NVARCHAR(5) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"orden" INT(10,0) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Kit
CREATE TABLE IF NOT EXISTS "Kit" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"codikit" NVARCHAR(10) NULL DEFAULT NULL,
	"id" NUMERIC(18,0) NOT NULL,
	"disminuir" NUMERIC(18,0) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.laser_sales_view
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "laser_sales_view" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totimpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" INT NULL,
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.laverdad
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "laverdad" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.LogConsultas
CREATE TABLE IF NOT EXISTS "LogConsultas" (
	"codclien" NVARCHAR(10) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(50) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"workstation" CHAR(10) NULL DEFAULT NULL,
	"systemUser" NVARCHAR(100) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.loginpass
CREATE TABLE IF NOT EXISTS "loginpass" (
	"login" NVARCHAR(20) NOT NULL,
	"passwork" NVARCHAR(10) NULL DEFAULT NULL,
	"Nombre" NVARCHAR(30) NULL DEFAULT NULL,
	"apellido" NVARCHAR(50) NULL DEFAULT NULL,
	"cedula" NVARCHAR(10) NULL DEFAULT NULL,
	"codperfil" NVARCHAR(2) NULL DEFAULT NULL,
	"CODVENDE" NVARCHAR(5) NULL DEFAULT NULL,
	"codestat" NVARCHAR(2) NULL DEFAULT NULL,
	"controlcita" NVARCHAR(1) NOT NULL DEFAULT (0),
	"permisobusqueda" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"initials" NVARCHAR(5) NULL DEFAULT NULL,
	"access" NVARCHAR(10) NULL DEFAULT NULL,
	"prninvoice" NVARCHAR(1) NULL DEFAULT NULL,
	"pathprn" NVARCHAR(150) NULL DEFAULT NULL,
	"autoposprn" NVARCHAR(1) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT ((1)),
	PRIMARY KEY ("login")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Majustes
CREATE TABLE IF NOT EXISTS "Majustes" (
	"codajus" NVARCHAR(10) NOT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT (''),
	"fechajus" DATETIME(3) NULL DEFAULT NULL,
	"observacion" TEXT(2147483647) NULL DEFAULT NULL,
	"transferido" NVARCHAR(1) NULL DEFAULT (0),
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codajus")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mbanco
CREATE TABLE IF NOT EXISTS "Mbanco" (
	"CODBANCO" NVARCHAR(5) NULL DEFAULT NULL,
	"DESBANCO" NVARCHAR(25) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MCierreInventario
CREATE TABLE IF NOT EXISTS "MCierreInventario" (
	"fechacierre" DATETIME(3) NOT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"status" NVARCHAR(1) NULL DEFAULT (0),
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("fechacierre")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MClientes
CREATE TABLE IF NOT EXISTS "MClientes" (
	"codclien" NVARCHAR(15) NOT NULL,
	"Cedula" NVARCHAR(15) NULL DEFAULT NULL,
	"nombre" NVARCHAR(255) NULL DEFAULT (''),
	"apellido" NVARCHAR(255) NULL DEFAULT (''),
	"direccionH" NVARCHAR(255) NULL DEFAULT NULL,
	"telfhabit" NVARCHAR(50) NULL DEFAULT NULL,
	"celular" NVARCHAR(50) NULL DEFAULT (''),
	"telfofic" NVARCHAR(50) NULL DEFAULT NULL,
	"NaturaJuri" NVARCHAR(1) NULL DEFAULT NULL,
	"Empresa" NVARCHAR(100) NULL DEFAULT NULL,
	"DireccionOfic" NVARCHAR(255) NULL DEFAULT NULL,
	"email" NVARCHAR(50) NULL DEFAULT (' '),
	"CliDesde" DATETIME(3) NULL DEFAULT NULL,
	"Nacionalidad" NVARCHAR(1) NULL DEFAULT NULL,
	"NombreCompleto" NVARCHAR(255) NULL DEFAULT NULL,
	"Historia" NVARCHAR(10) NULL DEFAULT (N'No Asign'),
	"Edad" NUMERIC(18,0) NULL DEFAULT NULL,
	"sexo" DECIMAL(18,0) NULL DEFAULT NULL,
	"Visita" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fecult_cita" DATETIME(3) NULL DEFAULT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"nombres" NVARCHAR(100) NULL DEFAULT (' '),
	"fallecido" NVARCHAR(1) NULL DEFAULT ((0)),
	"inactivo" NVARCHAR(1) NULL DEFAULT ((0)),
	"codmedico" NVARCHAR(3) NULL DEFAULT ('000'),
	"NACIMIENTO" DATETIME(3) NULL DEFAULT NULL,
	"fechaemail" DATETIME(3) NULL DEFAULT NULL,
	"Pais" NUMERIC(18,0) NULL DEFAULT NULL,
	"ESTADO" NUMERIC(18,0) NULL DEFAULT NULL,
	"ciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fechareg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(20) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codpostal" NVARCHAR(50) NULL DEFAULT NULL,
	"exonerado" BIT NULL DEFAULT NULL,
	"fecprox_cita" DATETIME(3) NULL DEFAULT NULL,
	"codhab1" NVARCHAR(4) NULL DEFAULT (' '),
	"codhab2" NVARCHAR(4) NULL DEFAULT (' '),
	"codofc1" NVARCHAR(4) NULL DEFAULT (' '),
	"codofc2" NVARCHAR(4) NULL DEFAULT (' '),
	"codcel1" NVARCHAR(4) NULL DEFAULT (' '),
	"codcel2" NVARCHAR(4) NULL DEFAULT (' '),
	"telhaba2" NVARCHAR(3) NULL DEFAULT (' '),
	"telhabb2" NVARCHAR(4) NULL DEFAULT (' '),
	"telhaba1" NVARCHAR(3) NULL DEFAULT (' '),
	"telhabb1" NVARCHAR(4) NULL DEFAULT (' '),
	"telofa1" NVARCHAR(3) NULL DEFAULT (' '),
	"telofb1" NVARCHAR(4) NULL DEFAULT (' '),
	"telofa2" NVARCHAR(3) NULL DEFAULT (' '),
	"telofb2" NVARCHAR(4) NULL DEFAULT (' '),
	"cela1" NVARCHAR(3) NULL DEFAULT (' '),
	"celb1" NVARCHAR(4) NULL DEFAULT (' '),
	"cela2" NVARCHAR(3) NULL DEFAULT (' '),
	"celb2" NVARCHAR(4) NULL DEFAULT (' '),
	"hcasa" NVARCHAR(150) NULL DEFAULT NULL,
	"htorre" NVARCHAR(150) NULL DEFAULT NULL,
	"hpiso" NVARCHAR(10) NULL DEFAULT NULL,
	"hcalle" NVARCHAR(254) NULL DEFAULT NULL,
	"hurb" NVARCHAR(254) NULL DEFAULT NULL,
	"hCiudad" NVARCHAR(100) NULL DEFAULT NULL,
	"hedo" NVARCHAR(100) NULL DEFAULT NULL,
	"oempresa" NVARCHAR(150) NULL DEFAULT NULL,
	"ocasa" NVARCHAR(150) NULL DEFAULT NULL,
	"otorre" NVARCHAR(150) NULL DEFAULT NULL,
	"opiso" NVARCHAR(10) NULL DEFAULT NULL,
	"odepartamento" NVARCHAR(100) NULL DEFAULT NULL,
	"ourb" NVARCHAR(254) NULL DEFAULT NULL,
	"oedo" NVARCHAR(100) NULL DEFAULT NULL,
	"ociudad" NVARCHAR(100) NULL DEFAULT NULL,
	"TELNUEVO" NVARCHAR(50) NULL DEFAULT NULL,
	"cel" NVARCHAR(50) NULL DEFAULT NULL,
	"telnuevo2" NVARCHAR(50) NULL DEFAULT NULL,
	"NroCitas" NUMERIC(18,0) NULL DEFAULT ((0)),
	"codarea1" NVARCHAR(6) NULL DEFAULT NULL,
	"codarea2" NVARCHAR(6) NULL DEFAULT NULL,
	"zipcode" NVARCHAR(6) NULL DEFAULT NULL,
	"codciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"codreg" NUMERIC(18,0) NULL DEFAULT NULL,
	"codciudad1" NUMERIC(18,0) NULL DEFAULT NULL,
	"PROVINCIA" NVARCHAR(50) NULL DEFAULT NULL,
	"Epais" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eestado" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eaddress" NVARCHAR(260) NULL DEFAULT NULL,
	"carrier" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	PRIMARY KEY ("codclien")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MClientes_tmp
CREATE TABLE IF NOT EXISTS "MClientes_tmp" (
	"codclien" NVARCHAR(15) NOT NULL,
	"Cedula" NVARCHAR(15) NULL DEFAULT NULL,
	"nombre" NVARCHAR(255) NULL DEFAULT (''),
	"apellido" NVARCHAR(255) NULL DEFAULT (''),
	"direccionH" NVARCHAR(255) NULL DEFAULT NULL,
	"telfhabit" NVARCHAR(50) NULL DEFAULT NULL,
	"celular" NVARCHAR(50) NULL DEFAULT (''),
	"telfofic" NVARCHAR(50) NULL DEFAULT NULL,
	"NaturaJuri" NVARCHAR(1) NULL DEFAULT NULL,
	"Empresa" NVARCHAR(100) NULL DEFAULT NULL,
	"DireccionOfic" NVARCHAR(255) NULL DEFAULT NULL,
	"email" NVARCHAR(50) NULL DEFAULT (' '),
	"CliDesde" DATETIME(3) NULL DEFAULT NULL,
	"Nacionalidad" NVARCHAR(1) NULL DEFAULT NULL,
	"NombreCompleto" NVARCHAR(255) NULL DEFAULT NULL,
	"Historia" NVARCHAR(10) NULL DEFAULT (N'No Asign'),
	"Edad" NUMERIC(18,0) NULL DEFAULT NULL,
	"sexo" DECIMAL(18,0) NULL DEFAULT NULL,
	"Visita" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fecult_cita" DATETIME(3) NULL DEFAULT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"nombres" NVARCHAR(100) NULL DEFAULT (' '),
	"fallecido" NVARCHAR(1) NULL DEFAULT ((0)),
	"inactivo" NVARCHAR(1) NULL DEFAULT ((0)),
	"codmedico" NVARCHAR(3) NULL DEFAULT ('000'),
	"NACIMIENTO" DATETIME(3) NULL DEFAULT NULL,
	"fechaemail" DATETIME(3) NULL DEFAULT NULL,
	"Pais" NUMERIC(18,0) NULL DEFAULT NULL,
	"ESTADO" NUMERIC(18,0) NULL DEFAULT NULL,
	"ciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fechareg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(20) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codpostal" NVARCHAR(50) NULL DEFAULT NULL,
	"exonerado" BIT NULL DEFAULT NULL,
	"fecprox_cita" DATETIME(3) NULL DEFAULT NULL,
	"codhab1" NVARCHAR(4) NULL DEFAULT (' '),
	"codhab2" NVARCHAR(4) NULL DEFAULT (' '),
	"codofc1" NVARCHAR(4) NULL DEFAULT (' '),
	"codofc2" NVARCHAR(4) NULL DEFAULT (' '),
	"codcel1" NVARCHAR(4) NULL DEFAULT (' '),
	"codcel2" NVARCHAR(4) NULL DEFAULT (' '),
	"telhaba2" NVARCHAR(3) NULL DEFAULT (' '),
	"telhabb2" NVARCHAR(4) NULL DEFAULT (' '),
	"telhaba1" NVARCHAR(3) NULL DEFAULT (' '),
	"telhabb1" NVARCHAR(4) NULL DEFAULT (' '),
	"telofa1" NVARCHAR(3) NULL DEFAULT (' '),
	"telofb1" NVARCHAR(4) NULL DEFAULT (' '),
	"telofa2" NVARCHAR(3) NULL DEFAULT (' '),
	"telofb2" NVARCHAR(4) NULL DEFAULT (' '),
	"cela1" NVARCHAR(3) NULL DEFAULT (' '),
	"celb1" NVARCHAR(4) NULL DEFAULT (' '),
	"cela2" NVARCHAR(3) NULL DEFAULT (' '),
	"celb2" NVARCHAR(4) NULL DEFAULT (' '),
	"hcasa" NVARCHAR(150) NULL DEFAULT NULL,
	"htorre" NVARCHAR(150) NULL DEFAULT NULL,
	"hpiso" NVARCHAR(10) NULL DEFAULT NULL,
	"hcalle" NVARCHAR(254) NULL DEFAULT NULL,
	"hurb" NVARCHAR(254) NULL DEFAULT NULL,
	"hCiudad" NVARCHAR(100) NULL DEFAULT NULL,
	"hedo" NVARCHAR(100) NULL DEFAULT NULL,
	"oempresa" NVARCHAR(150) NULL DEFAULT NULL,
	"ocasa" NVARCHAR(150) NULL DEFAULT NULL,
	"otorre" NVARCHAR(150) NULL DEFAULT NULL,
	"opiso" NVARCHAR(10) NULL DEFAULT NULL,
	"odepartamento" NVARCHAR(100) NULL DEFAULT NULL,
	"ourb" NVARCHAR(254) NULL DEFAULT NULL,
	"oedo" NVARCHAR(100) NULL DEFAULT NULL,
	"ociudad" NVARCHAR(100) NULL DEFAULT NULL,
	"TELNUEVO" NVARCHAR(50) NULL DEFAULT NULL,
	"cel" NVARCHAR(50) NULL DEFAULT NULL,
	"telnuevo2" NVARCHAR(50) NULL DEFAULT NULL,
	"NroCitas" NUMERIC(18,0) NULL DEFAULT ((0)),
	"codarea1" NVARCHAR(6) NULL DEFAULT NULL,
	"codarea2" NVARCHAR(6) NULL DEFAULT NULL,
	"zipcode" NVARCHAR(6) NULL DEFAULT NULL,
	"codciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"codreg" NUMERIC(18,0) NULL DEFAULT NULL,
	"codciudad1" NUMERIC(18,0) NULL DEFAULT NULL,
	"PROVINCIA" NVARCHAR(50) NULL DEFAULT NULL,
	"Epais" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eestado" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"Eaddress" NVARCHAR(260) NULL DEFAULT NULL,
	"carrier" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codclien")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MCompra
CREATE TABLE IF NOT EXISTS "MCompra" (
	"factcomp" NVARCHAR(6) NOT NULL,
	"fechacomp" DATETIME(3) NOT NULL,
	"observacion" NVARCHAR(255) NULL DEFAULT NULL,
	"codprov" NVARCHAR(3) NULL DEFAULT NULL,
	"facclose" NVARCHAR(1) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"total" MONEY(19,4) NULL DEFAULT NULL,
	"fechapost" DATETIME(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("factcomp")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mconsultas
CREATE TABLE IF NOT EXISTS "Mconsultas" (
	"codclien" NVARCHAR(15) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(10) NULL DEFAULT (''),
	"fecha_cita" DATETIME(3) NOT NULL,
	"citacontrol" NVARCHAR(1) NULL DEFAULT ((0)),
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"citados" NUMERIC(10,0) NOT NULL DEFAULT ((0)),
	"confirmado" INT(10,0) NOT NULL DEFAULT ((0)),
	"asistido" INT(10,0) NOT NULL DEFAULT ((0)),
	"noasistido" NUMERIC(10,0) NOT NULL DEFAULT ((0)),
	"primera_control" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"activa" NVARCHAR(1) NOT NULL DEFAULT ((1)),
	"observacion" NVARCHAR(255) NOT NULL DEFAULT ('Sin Observaciones'),
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"Nro_asistencias" NUMERIC(18,0) NOT NULL DEFAULT ((0)),
	"NoCitados" NVARCHAR(1) NULL DEFAULT ((0)),
	"userconf" NVARCHAR(20) NULL DEFAULT (''),
	"fecmov" DATETIME(3) NULL DEFAULT NULL,
	"servicios" NVARCHAR(1) NULL DEFAULT ((0)),
	"CONTADOR" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT NULL,
	"MT" NVARCHAR(2) NULL DEFAULT NULL,
	"regusuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codconsulta" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraIn" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraOut" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraRegistro" NVARCHAR(20) NULL DEFAULT NULL,
	"llegada" NVARCHAR(20) NULL DEFAULT NULL,
	"prioridad" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"salio" NCHAR(10) NULL DEFAULT NULL,
	"pacienteleft" NVARCHAR(10) NULL DEFAULT NULL,
	"answered" NVARCHAR(1) NULL DEFAULT (''),
	"tsanswered" NVARCHAR(50) NULL DEFAULT NULL,
	"paciente" NVARCHAR(100) NULL DEFAULT NULL,
	"record" NVARCHAR(15) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	PRIMARY KEY ("codclien","fecha_cita")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.mconsultasAud
CREATE TABLE IF NOT EXISTS "mconsultasAud" (
	"Fecha_Actual" DATETIME(3) NULL DEFAULT NULL,
	"Hora_Actual" NVARCHAR(15) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NOT NULL,
	"Fecha_Cita_anterior" DATETIME(3) NULL DEFAULT NULL,
	"Fecha_Nueva_Cita" DATETIME(3) NULL DEFAULT NULL,
	"Workstation_Anterior" NVARCHAR(20) NULL DEFAULT NULL,
	"Workstation_Actual" NVARCHAR(20) NULL DEFAULT NULL,
	"Usuario_Anterior" NVARCHAR(15) NULL DEFAULT NULL,
	"Usuario_Actual" NVARCHAR(15) NULL DEFAULT NULL,
	"confirmado" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MconsultaSS
CREATE TABLE IF NOT EXISTS "MconsultaSS" (
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(20) NULL DEFAULT NULL,
	"fecha_cita" DATETIME(3) NULL DEFAULT NULL,
	"citacontrol" NVARCHAR(1) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"citados" NUMERIC(18,0) NULL DEFAULT NULL,
	"confirmado" NUMERIC(18,0) NULL DEFAULT NULL,
	"asistido" NUMERIC(10,0) NULL DEFAULT NULL,
	"noasistido" NUMERIC(10,0) NULL DEFAULT ((0)),
	"primera_control" NVARCHAR(1) NULL DEFAULT NULL,
	"activa" NVARCHAR(1) NULL DEFAULT ((1)),
	"observacion" NVARCHAR(255) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(25) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"Nro_asistencias" NUMERIC(18,0) NULL DEFAULT ((0)),
	"NoCitados" NVARCHAR(1) NULL DEFAULT NULL,
	"userconf" NVARCHAR(20) NULL DEFAULT (' '),
	"fecmov" DATETIME(3) NULL DEFAULT NULL,
	"servicios" NVARCHAR(1) NULL DEFAULT NULL,
	"CONTADOR" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT NULL,
	"MT" NVARCHAR(2) NULL DEFAULT NULL,
	"regusuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codconsulta" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraIn" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraOut" NVARCHAR(20) NULL DEFAULT (' '),
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraRegistro" NVARCHAR(20) NULL DEFAULT NULL,
	"llegada" NVARCHAR(20) NULL DEFAULT NULL,
	"mls" INT(10,0) NULL DEFAULT NULL,
	"hilt" INT(10,0) NULL DEFAULT NULL,
	"terapias" INT(10,0) NULL DEFAULT NULL,
	"prioridad" INT(10,0) NULL DEFAULT NULL,
	"enddate" DATETIME(3) NULL DEFAULT NULL,
	"endtime" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"salio" NCHAR(10) NULL DEFAULT NULL,
	"pacienteleft" NVARCHAR(10) NULL DEFAULT NULL,
	"answered" NVARCHAR(1) NULL DEFAULT (''),
	"tsanswered" NVARCHAR(50) NULL DEFAULT NULL,
	"paciente" NVARCHAR(100) NULL DEFAULT NULL,
	"record" NVARCHAR(15) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MconsultaSS_tmp
CREATE TABLE IF NOT EXISTS "MconsultaSS_tmp" (
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(20) NULL DEFAULT NULL,
	"fecha_cita" DATETIME(3) NULL DEFAULT NULL,
	"citacontrol" NVARCHAR(1) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"citados" NUMERIC(18,0) NULL DEFAULT NULL,
	"confirmado" NUMERIC(18,0) NULL DEFAULT NULL,
	"asistido" NUMERIC(10,0) NULL DEFAULT NULL,
	"noasistido" NUMERIC(10,0) NULL DEFAULT ((0)),
	"primera_control" NVARCHAR(1) NULL DEFAULT NULL,
	"activa" NVARCHAR(1) NULL DEFAULT ((1)),
	"observacion" NVARCHAR(255) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(25) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"Nro_asistencias" NUMERIC(18,0) NULL DEFAULT ((0)),
	"NoCitados" NVARCHAR(1) NULL DEFAULT NULL,
	"userconf" NVARCHAR(20) NULL DEFAULT (' '),
	"fecmov" DATETIME(3) NULL DEFAULT NULL,
	"servicios" NVARCHAR(1) NULL DEFAULT NULL,
	"CONTADOR" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT NULL,
	"MT" NVARCHAR(2) NULL DEFAULT NULL,
	"regusuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codconsulta" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraIn" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraOut" NVARCHAR(20) NULL DEFAULT (' '),
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraRegistro" NVARCHAR(20) NULL DEFAULT NULL,
	"llegada" NVARCHAR(20) NULL DEFAULT NULL,
	"mls" INT(10,0) NULL DEFAULT NULL,
	"hilt" INT(10,0) NULL DEFAULT NULL,
	"terapias" INT(10,0) NULL DEFAULT NULL,
	"prioridad" INT(10,0) NULL DEFAULT NULL,
	"enddate" DATETIME(3) NULL DEFAULT NULL,
	"endtime" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mconsultas_tmp
CREATE TABLE IF NOT EXISTS "Mconsultas_tmp" (
	"codclien" NVARCHAR(15) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(10) NULL DEFAULT (''),
	"fecha_cita" DATETIME(3) NOT NULL,
	"citacontrol" NVARCHAR(1) NULL DEFAULT ((0)),
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"citados" NUMERIC(10,0) NOT NULL DEFAULT ((0)),
	"confirmado" INT(10,0) NOT NULL DEFAULT ((0)),
	"asistido" INT(10,0) NOT NULL DEFAULT ((0)),
	"noasistido" NUMERIC(10,0) NOT NULL DEFAULT ((0)),
	"primera_control" NVARCHAR(1) NOT NULL DEFAULT ((0)),
	"activa" NVARCHAR(1) NOT NULL DEFAULT ((1)),
	"observacion" NVARCHAR(255) NOT NULL DEFAULT ('Sin Observaciones'),
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"Nro_asistencias" NUMERIC(18,0) NOT NULL DEFAULT ((0)),
	"NoCitados" NVARCHAR(1) NULL DEFAULT ((0)),
	"userconf" NVARCHAR(20) NULL DEFAULT (''),
	"fecmov" DATETIME(3) NULL DEFAULT NULL,
	"servicios" NVARCHAR(1) NULL DEFAULT ((0)),
	"CONTADOR" NUMERIC(18,0) NULL DEFAULT NULL,
	"medio" NUMERIC(18,0) NULL DEFAULT NULL,
	"MT" NVARCHAR(2) NULL DEFAULT NULL,
	"regusuario" NVARCHAR(20) NULL DEFAULT NULL,
	"codconsulta" NVARCHAR(10) NULL DEFAULT NULL,
	"HoraIn" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraOut" NVARCHAR(20) NULL DEFAULT (' '),
	"HoraRegistro" NVARCHAR(20) NULL DEFAULT NULL,
	"llegada" NVARCHAR(20) NULL DEFAULT NULL,
	"prioridad" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codclien","fecha_cita")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MCotizacion
CREATE TABLE IF NOT EXISTS "MCotizacion" (
	"numcot" NVARCHAR(10) NOT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"fechacot" DATETIME(3) NULL DEFAULT NULL,
	"fechacit" DATETIME(3) NULL DEFAULT NULL,
	"DiasPrescripcion" DECIMAL(18,0) NULL DEFAULT NULL,
	"subtotal" DECIMAL(19,4) NULL DEFAULT NULL,
	"descuento" DECIMAL(18,2) NULL DEFAULT NULL,
	"total" DECIMAL(18,2) NULL DEFAULT NULL,
	"codseguro" NVARCHAR(4) NULL DEFAULT NULL,
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"transferida" NVARCHAR(1) NULL DEFAULT NULL,
	"status" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"vcod" NVARCHAR(15) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY ("numcot")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MCotizacion_tmp
CREATE TABLE IF NOT EXISTS "MCotizacion_tmp" (
	"numcot" NVARCHAR(10) NOT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"fechacot" DATETIME(3) NULL DEFAULT NULL,
	"fechacit" DATETIME(3) NULL DEFAULT NULL,
	"DiasPrescripcion" DECIMAL(18,0) NULL DEFAULT NULL,
	"subtotal" DECIMAL(19,4) NULL DEFAULT NULL,
	"descuento" DECIMAL(18,2) NULL DEFAULT NULL,
	"total" DECIMAL(18,2) NULL DEFAULT NULL,
	"codseguro" NVARCHAR(4) NULL DEFAULT NULL,
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"transferida" NVARCHAR(1) NULL DEFAULT NULL,
	"status" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numcot")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MDesctFactura
CREATE TABLE IF NOT EXISTS "MDesctFactura" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"codesc" NVARCHAR(3) NOT NULL,
	"total" MONEY(19,4) NULL DEFAULT NULL,
	"base" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"porcentaje" DECIMAL(18,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codesc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MDesctFacturaCMA
CREATE TABLE IF NOT EXISTS "MDesctFacturaCMA" (
	"numfactu" NVARCHAR(7) NOT NULL,
	"codesc" NVARCHAR(3) NOT NULL,
	"total" MONEY(19,4) NULL DEFAULT NULL,
	"base" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codesc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mdescuentos
CREATE TABLE IF NOT EXISTS "Mdescuentos" (
	"codesc" NVARCHAR(3) NOT NULL,
	"desdesct" NVARCHAR(30) NULL DEFAULT NULL,
	"porcentaje" NUMERIC(18,2) NULL DEFAULT (0),
	"monto" MONEY(19,4) NULL DEFAULT (0),
	"activo" BIT NOT NULL DEFAULT (1),
	"permiso" BIT NOT NULL DEFAULT (1),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codesc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MDevCompra
CREATE TABLE IF NOT EXISTS "MDevCompra" (
	"factcomp" NVARCHAR(6) NOT NULL,
	"fechacomp" DATETIME(3) NOT NULL,
	"observacion" NTEXT(1073741823) NULL DEFAULT NULL,
	"codprov" NVARCHAR(3) NULL DEFAULT NULL,
	"facclose" NVARCHAR(1) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("factcomp")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MDocumentos
CREATE TABLE IF NOT EXISTS "MDocumentos" (
	"codtipodoc" NVARCHAR(10) NULL DEFAULT NULL,
	"nombre" NVARCHAR(50) NULL DEFAULT NULL,
	"abreviatura" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Medios
CREATE TABLE IF NOT EXISTS "Medios" (
	"codigo" NUMERIC(18,0) NOT NULL,
	"medio" NVARCHAR(50) NULL DEFAULT NULL,
	"del" BIT NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codigo")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MenuPerfil
CREATE TABLE IF NOT EXISTS "MenuPerfil" (
	"codperfil" NVARCHAR(2) NULL DEFAULT NULL,
	"nomopcion" NVARCHAR(20) NULL DEFAULT NULL,
	"acceso" BIT NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MenuPerfilSecundario
CREATE TABLE IF NOT EXISTS "MenuPerfilSecundario" (
	"codperfil" NVARCHAR(2) NULL DEFAULT NULL,
	"nomopcion" NVARCHAR(20) NULL DEFAULT NULL,
	"nomsubopcion" NVARCHAR(20) NULL DEFAULT NULL,
	"acceso" BIT NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MenuPrincipal
CREATE TABLE IF NOT EXISTS "MenuPrincipal" (
	"nomopcion" NVARCHAR(20) NOT NULL,
	"desopcion" NVARCHAR(50) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MenuSecundario
CREATE TABLE IF NOT EXISTS "MenuSecundario" (
	"nomopcion" NVARCHAR(20) NULL DEFAULT NULL,
	"nomsubopsion" NVARCHAR(20) NULL DEFAULT NULL,
	"dessubopcion" NVARCHAR(40) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MEquipos
CREATE TABLE IF NOT EXISTS "MEquipos" (
	"CodEquipo" NVARCHAR(2) NOT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"Workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"activo" INT(10,0) NULL DEFAULT (1),
	"codestac" NVARCHAR(2) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("CodEquipo")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.messenger
CREATE TABLE IF NOT EXISTS "messenger" (
	"id" BIGINT(19,0) NOT NULL,
	"telefono" NVARCHAR(20) NULL DEFAULT NULL,
	"mensaje" NVARCHAR(max) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"visto" INT(10,0) NULL DEFAULT ((0)),
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"modo" INT(10,0) NULL DEFAULT ((1)),
	"fecha_visto" DATETIME(3) NULL DEFAULT NULL,
	"hora_visto" NVARCHAR(15) NULL DEFAULT NULL,
	"user_visto" NVARCHAR(15) NULL DEFAULT NULL,
	"aa" INT(10,0) NULL DEFAULT ((0)),
	"ts" DATETIME(3) NULL DEFAULT (getdate())
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MEStado
CREATE TABLE IF NOT EXISTS "MEStado" (
	"estado" NVARCHAR(1) NULL DEFAULT NULL,
	"desestado" NVARCHAR(20) NULL DEFAULT (''),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MFactura
CREATE TABLE IF NOT EXISTS "MFactura" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"TotImpuesto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	"empresa" NVARCHAR(50) NULL DEFAULT NULL,
	"cempresa" NVARCHAR(50) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"ret" NVARCHAR(10) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MFactura_tmp
CREATE TABLE IF NOT EXISTS "MFactura_tmp" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"TotImpuesto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MFormaPago
CREATE TABLE IF NOT EXISTS "MFormaPago" (
	"codforpa" NVARCHAR(2) NULL DEFAULT NULL,
	"desforpa" NVARCHAR(30) NULL DEFAULT NULL,
	"filtra_targeta" NVARCHAR(2) NULL DEFAULT NULL,
	"filtra_banco" BIT NULL DEFAULT (0),
	"ACTIVO" NUMERIC(18,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MInstrumentoPago
CREATE TABLE IF NOT EXISTS "MInstrumentoPago" (
	"Codinst" NVARCHAR(3) NULL DEFAULT NULL,
	"DesInst" NVARCHAR(255) NULL DEFAULT NULL,
	"afec_tipoInst" BIT NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MInventario
CREATE TABLE IF NOT EXISTS "MInventario" (
	"coditems" NVARCHAR(10) NOT NULL,
	"desitems" NVARCHAR(255) NOT NULL DEFAULT (''),
	"existencia" FLOAT(53) NULL DEFAULT (0),
	"ubicacion" NVARCHAR(50) NULL DEFAULT NULL,
	"fecing" DATETIME(3) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT (1),
	"ultent" NUMERIC(18,0) NULL DEFAULT (0),
	"Exisminima" NUMERIC(18,0) NULL DEFAULT (0),
	"Exismaxima" NUMERIC(18,0) NULL DEFAULT (0),
	"fecultent" DATETIME(3) NULL DEFAULT NULL,
	"ultlote" NVARCHAR(7) NULL DEFAULT NULL,
	"Prod_serv" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(8) NULL DEFAULT NULL,
	"aplicaIva" NVARCHAR(1) NOT NULL DEFAULT (0),
	"aplicadcto" NVARCHAR(1) NOT NULL DEFAULT (0),
	"aplicaComMed" NVARCHAR(1) NULL DEFAULT (0),
	"aplicaComTec" NVARCHAR(1) NOT NULL DEFAULT (0),
	"especial" NVARCHAR(1) NOT NULL DEFAULT (0),
	"rowguid" UNIQUEIDENTIFIER NULL DEFAULT NULL,
	"cod_grupo" NVARCHAR(20) NULL DEFAULT ('003'),
	"cod_subgrupo" NVARCHAR(20) NULL DEFAULT NULL,
	"costo" FLOAT(53) NULL DEFAULT (0),
	"nombre_alterno" NVARCHAR(100) NULL DEFAULT NULL,
	"Inventariable" NVARCHAR(50) NULL DEFAULT NULL,
	"GrStock" FLOAT(53) NULL DEFAULT NULL,
	"GrExist" FLOAT(53) NULL DEFAULT NULL,
	"medida" FLOAT(53) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"presentacion" NVARCHAR(50) NULL DEFAULT NULL,
	"usemult" NVARCHAR(5) NULL DEFAULT NULL,
	"kit" NVARCHAR(1) NULL DEFAULT NULL,
	"orden" INT(10,0) NULL DEFAULT NULL,
	"group_a" NVARCHAR(50) NULL DEFAULT NULL,
	"group_b" NVARCHAR(50) NULL DEFAULT NULL,
	"clase_a" NVARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY ("coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MIT
CREATE TABLE IF NOT EXISTS "MIT" (
	"codclien" NVARCHAR(5) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"fecha_cita" DATETIME(3) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL,
	"asistido" DECIMAL(10,0) NOT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"nombre" NVARCHAR(255) NULL DEFAULT NULL,
	"apellido" NVARCHAR(50) NULL DEFAULT NULL,
	"MONP_Bs" DECIMAL(38,2) NULL DEFAULT NULL,
	"MONS_Bs" DECIMAL(19,2) NULL DEFAULT NULL,
	"items" INT(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL,
	"horamf" NVARCHAR(15) NULL DEFAULT NULL,
	"horacma" NVARCHAR(15) NULL DEFAULT NULL,
	"codsuc" VARCHAR(2) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mmedicos
CREATE TABLE IF NOT EXISTS "Mmedicos" (
	"Codmedico" NVARCHAR(3) NOT NULL,
	"cedula" NVARCHAR(12) NULL DEFAULT NULL,
	"nombre" NVARCHAR(50) NULL DEFAULT NULL,
	"apellido" NVARCHAR(50) NULL DEFAULT NULL,
	"msds" NVARCHAR(10) NULL DEFAULT NULL,
	"cmdf" NVARCHAR(10) NULL DEFAULT NULL,
	"especialidad" NVARCHAR(255) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NOT NULL DEFAULT (1),
	"codcma" NVARCHAR(10) NULL DEFAULT (0),
	"meliminado" BIT NOT NULL DEFAULT (1),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Codmedico")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mnotacredito
CREATE TABLE IF NOT EXISTS "Mnotacredito" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(50) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT (0),
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL DEFAULT (0),
	"totalnot" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL,
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"concepto" NVARCHAR(255) NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"codtiponotcre" NVARCHAR(1) NULL DEFAULT NULL,
	"codseguro" INT(10,0) NULL DEFAULT (0),
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"desanula" NVARCHAR(255) NULL DEFAULT NULL,
	"monto" MONEY(19,4) NOT NULL DEFAULT (0),
	"tasadesc" NUMERIC(18,2) NOT NULL,
	"saldo" MONEY(19,4) NOT NULL,
	"openclose" NVARCHAR(1) NULL DEFAULT NULL,
	"TotImpuesto" MONEY(19,4) NULL DEFAULT (0),
	"monto_flete" MONEY(19,4) NULL DEFAULT (0),
	"Impuesto" BIT NOT NULL DEFAULT (1),
	"alicuota" FLOAT(53) NULL DEFAULT (0),
	"tipopago" BIT NULL DEFAULT (0),
	"monto_abonado" MONEY(19,4) NULL DEFAULT (0),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'04'),
	"Id" INT(10,0) NOT NULL,
	"ct" NVARCHAR(50) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"cempresa" NVARCHAR(50) NULL DEFAULT NULL,
	"desxmonto" NVARCHAR(1) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"por" NVARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY ("numnotcre")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mpagos
CREATE TABLE IF NOT EXISTS "Mpagos" (
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"fechapago" DATETIME(3) NULL DEFAULT NULL,
	"codforpa" NVARCHAR(2) NULL DEFAULT NULL,
	"codbanco" NVARCHAR(5) NULL DEFAULT NULL,
	"codtipotargeta" NVARCHAR(2) NULL DEFAULT NULL,
	"nro_forpa" NVARCHAR(20) NULL DEFAULT NULL,
	"monto" NUMERIC(18,2) NULL DEFAULT NULL,
	"monto_abonado" NUMERIC(18,2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"id_centro" NVARCHAR(1) NULL DEFAULT NULL,
	"tipo_doc" NVARCHAR(10) NOT NULL DEFAULT ('01'),
	"Id" INT(10,0) NOT NULL,
	"idempresa" NVARCHAR(1) NULL DEFAULT ((3)),
	"cnumber" NVARCHAR(50) NULL DEFAULT NULL,
	"por" NVARCHAR(50) NULL DEFAULT NULL,
	"cnumbre" NVARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MParametros
CREATE TABLE IF NOT EXISTS "MParametros" (
	"grupo" NVARCHAR(5) NOT NULL,
	"subgrupo" NVARCHAR(5) NOT NULL,
	"alfa1" NVARCHAR(80) NOT NULL,
	"alfa2" NVARCHAR(80) NOT NULL,
	"numero1" MONEY(19,4) NOT NULL,
	"numero2" MONEY(19,4) NOT NULL,
	"descripcion" NVARCHAR(80) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mpedido
CREATE TABLE IF NOT EXISTS "Mpedido" (
	"numpedido" NVARCHAR(10) NOT NULL,
	"fecha_pedido" DATETIME(3) NULL DEFAULT NULL,
	"observacion" TEXT(2147483647) NULL DEFAULT NULL,
	"fact_compra" NVARCHAR(10) NULL DEFAULT NULL,
	"fecha_recepcion" DATETIME(3) NULL DEFAULT NULL,
	"cod_proveedor" NVARCHAR(3) NULL DEFAULT NULL,
	"statpedido" NVARCHAR(1) NULL DEFAULT NULL,
	"NroDias" NUMERIC(18,0) NULL DEFAULT NULL,
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" TEXT(2147483647) NULL DEFAULT NULL,
	"transferido" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstatition" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numpedido")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MPerfil
CREATE TABLE IF NOT EXISTS "MPerfil" (
	"codperfil" NVARCHAR(2) NOT NULL,
	"desperfil" NVARCHAR(30) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MPostulados
CREATE TABLE IF NOT EXISTS "MPostulados" (
	"CodSuc" NVARCHAR(2) NOT NULL,
	"Lapso" INT(10,0) NOT NULL,
	"Mes" INT(10,0) NOT NULL,
	"cod_IM" INT(10,0) NOT NULL,
	"Cuota" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("CodSuc","Lapso","Mes","cod_IM")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MPrecios
CREATE TABLE IF NOT EXISTS "MPrecios" (
	"coditems" NVARCHAR(10) NOT NULL,
	"codtipre" NVARCHAR(2) NOT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"factor" DECIMAL(18,0) NULL DEFAULT (0),
	"sugerido" MONEY(19,4) NULL DEFAULT (0),
	"prec_ant" NUMERIC(18,2) NOT NULL DEFAULT (0),
	"prec_prog" NUMERIC(18,2) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	"desde" DATETIME(3) NULL DEFAULT NULL,
	"hasta" DATETIME2(7) NULL DEFAULT NULL,
	"activo" NVARCHAR(10) NULL DEFAULT ((1)),
	PRIMARY KEY ("coditems","codtipre")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.mprecios030907
CREATE TABLE IF NOT EXISTS "mprecios030907" (
	"coditems" NVARCHAR(10) NOT NULL,
	"codtipre" NVARCHAR(2) NOT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"factor" DECIMAL(18,0) NULL DEFAULT NULL,
	"sugerido" MONEY(19,4) NULL DEFAULT NULL,
	"prec_ant" NUMERIC(18,2) NOT NULL,
	"prec_prog" NUMERIC(18,2) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MPRICES
CREATE TABLE IF NOT EXISTS "MPRICES" (
	"FECHA" DATETIME(3) NULL DEFAULT NULL,
	"HORA" DATETIME(3) NULL DEFAULT NULL,
	"CONTROL" NUMERIC(18,0) NULL DEFAULT NULL,
	"USUARIO" NVARCHAR(20) NULL DEFAULT NULL,
	"PORCENT" MONEY(19,4) NULL DEFAULT NULL,
	"Prod_serv" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mproveedor
CREATE TABLE IF NOT EXISTS "Mproveedor" (
	"cod_proveedor" NVARCHAR(3) NULL DEFAULT NULL,
	"desproveedor" NVARCHAR(60) NULL DEFAULT NULL,
	"DirDespacho" NVARCHAR(100) NULL DEFAULT NULL,
	"dirproveedor" NVARCHAR(100) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MProveedores
CREATE TABLE IF NOT EXISTS "MProveedores" (
	"codProv" NVARCHAR(3) NOT NULL,
	"Desprov" NVARCHAR(250) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" CHAR(15) NULL DEFAULT NULL,
	"Activo" BIT NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.mscierre
CREATE TABLE IF NOT EXISTS "mscierre" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"fechacierre" DATETIME(3) NOT NULL,
	"existencia" NUMERIC(18,0) NULL DEFAULT ((0)),
	"compras" NUMERIC(18,0) NULL DEFAULT ((0)),
	"DevCompras" NUMERIC(18,0) NULL DEFAULT ((0)),
	"ventas" NUMERIC(18,0) NULL DEFAULT ((0)),
	"anulaciones" NUMERIC(18,0) NULL DEFAULT ((0)),
	"ajustes" NUMERIC(18,0) NULL DEFAULT ((0)),
	"NotasCreditos" NUMERIC(18,0) NULL DEFAULT ((0)),
	"NotasEntregas" NUMERIC(18,0) NULL DEFAULT ((0)),
	"InvPosible" NUMERIC(18,0) NULL DEFAULT ((0)),
	"InvActual" NUMERIC(18,0) NULL DEFAULT ((0)),
	"fallas" NUMERIC(18,0) NULL DEFAULT ((0)),
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT (getdate()),
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.mseguros
CREATE TABLE IF NOT EXISTS "mseguros" (
	"codseguro" INT(10,0) NOT NULL,
	"seguro" NVARCHAR(254) NULL DEFAULT NULL,
	"rif" NVARCHAR(50) NULL DEFAULT NULL,
	"nit" NVARCHAR(50) NULL DEFAULT NULL,
	"dirFiscal" NVARCHAR(254) NULL DEFAULT NULL,
	"telefono1" NVARCHAR(50) NULL DEFAULT NULL,
	"telefono2" NVARCHAR(50) NULL DEFAULT NULL,
	"status" BIT NOT NULL DEFAULT (1),
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"cod_pais" NUMERIC(18,0) NULL DEFAULT NULL,
	"cod_estado" NUMERIC(18,0) NULL DEFAULT NULL,
	"codciudad" NUMERIC(18,0) NULL DEFAULT NULL,
	"zipcode" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codseguro")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSemanas
CREATE TABLE IF NOT EXISTS "MSemanas" (
	"codsuc" NVARCHAR(2) NOT NULL DEFAULT ('01'),
	"fec_I" DATETIME(3) NOT NULL,
	"fec_F" DATETIME(3) NOT NULL,
	"codsemana" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codsuc","fec_F","codsemana")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSDDev
CREATE TABLE IF NOT EXISTS "MSSDDev" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" NUMERIC(18,2) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NOT NULL,
	"porcentaje" NUMERIC(18,2) NOT NULL,
	"descuento" MONEY(19,4) NULL DEFAULT NULL,
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto" NUMERIC(18,2) NULL DEFAULT NULL,
	"impuesto" NUMERIC(18,2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NOT NULL,
	"aplicadcto" NVARCHAR(1) NOT NULL,
	"aplicacommed" NVARCHAR(1) NOT NULL,
	"aplicacomtec" NVARCHAR(1) NOT NULL,
	"costo" MONEY(19,4) NULL DEFAULT NULL,
	"monto_imp" MONEY(19,4) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"l_cod_group" NVARCHAR(20) NULL DEFAULT NULL,
	"l_cod_sgroup" NVARCHAR(20) NULL DEFAULT NULL,
	"statfact" NVARCHAR(1) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate())
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSDFact
CREATE TABLE IF NOT EXISTS "MSSDFact" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NULL DEFAULT (N'P'),
	"procentaje" FLOAT(53) NOT NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"codtecnico" NVARCHAR(2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NULL DEFAULT ((0)),
	"aplicadcto" NVARCHAR(1) NULL DEFAULT ((0)),
	"aplicacommed" NVARCHAR(1) NULL DEFAULT ((1)),
	"aplicacomtec" NVARCHAR(1) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(2) NULL DEFAULT ('FA'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"pvpitem" MONEY(19,4) NULL DEFAULT ((0)),
	"dosis" INT(10,0) NULL DEFAULT ((0)),
	"cant_sugerida" NUMERIC(18,0) NULL DEFAULT ((0)),
	"costo" FLOAT(53) NULL DEFAULT ((0)),
	"monto_imp" FLOAT(53) NOT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"statfact" NVARCHAR(1) NULL DEFAULT NULL,
	"l_cod_group" NVARCHAR(20) NULL DEFAULT NULL,
	"l_cod_sgroup" NVARCHAR(20) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	PRIMARY KEY ("numfactu","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.mssfaclaser
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "mssfaclaser" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotalFac" NUMERIC(38,4) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(52) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.mssfaclaser_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "mssfaclaser_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotalFac" NUMERIC(38,4) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.MSSImpxFact
CREATE TABLE IF NOT EXISTS "MSSImpxFact" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"codimp" NUMERIC(18,0) NOT NULL,
	"base" MONEY(19,4) NOT NULL,
	"porcentaje" FLOAT(53) NOT NULL DEFAULT ((0)),
	"montoimp" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codimp")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSMDesFact
CREATE TABLE IF NOT EXISTS "MSSMDesFact" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"codesc" NVARCHAR(3) NOT NULL,
	"total" MONEY(19,4) NULL DEFAULT NULL,
	"base" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"porcentaje" DECIMAL(18,0) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu","codesc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSMDev
CREATE TABLE IF NOT EXISTS "MSSMDev" (
	"numnotcre" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(50) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL,
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"concepto" NVARCHAR(255) NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"codtiponotcre" NVARCHAR(1) NULL DEFAULT NULL,
	"codseguro" INT(10,0) NULL DEFAULT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"desanula" NVARCHAR(255) NULL DEFAULT NULL,
	"monto" MONEY(19,4) NOT NULL,
	"tasadesc" NUMERIC(18,2) NOT NULL,
	"saldo" MONEY(19,4) NOT NULL,
	"openclose" NVARCHAR(1) NULL DEFAULT NULL,
	"TotImpuesto" MONEY(19,4) NULL DEFAULT NULL,
	"monto_flete" MONEY(19,4) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL,
	"alicuota" FLOAT(53) NULL DEFAULT NULL,
	"tipopago" BIT NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT NULL,
	"tipo" NVARCHAR(10) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"ct" NVARCHAR(50) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate())
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSMFact
CREATE TABLE IF NOT EXISTS "MSSMFact" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"TotImpuesto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"vcod" NVARCHAR(15) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	"ts" DATETIME(3) NULL DEFAULT (getdate()),
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MSSMFact_tmp
CREATE TABLE IF NOT EXISTS "MSSMFact_tmp" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"TotImpuesto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Mstatus
CREATE TABLE IF NOT EXISTS "Mstatus" (
	"status" NVARCHAR(1) NOT NULL,
	"destatus" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("status")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Msucursales
CREATE TABLE IF NOT EXISTS "Msucursales" (
	"codsuc" NVARCHAR(2) NOT NULL,
	"Sucursal" NVARCHAR(50) NULL DEFAULT NULL,
	"iniciales" NVARCHAR(3) NULL DEFAULT NULL,
	"activo" NVARCHAR(1) NULL DEFAULT NULL,
	"contenedor" NVARCHAR(20) NULL DEFAULT NULL,
	"propietario" NVARCHAR(50) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codsuc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MTecnicos
CREATE TABLE IF NOT EXISTS "MTecnicos" (
	"codtecnico" NVARCHAR(2) NOT NULL,
	"destecnico" NVARCHAR(50) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codtecnico")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.MTipoTargeta
CREATE TABLE IF NOT EXISTS "MTipoTargeta" (
	"codtipotargeta" NVARCHAR(2) NOT NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL DEFAULT NULL,
	"debito_credito" NVARCHAR(2) NULL DEFAULT NULL,
	"sumacon" NVARCHAR(2) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"initials" NVARCHAR(10) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.newconsol
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsol" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"qtySold" NUMERIC(38,0) NULL,
	"general" MONEY(19,4) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.newconsol2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsol2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"qtySold" NUMERIC(38,0) NULL,
	"general" NUMERIC(38,3) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.newconsol3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsol3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.newconsol3_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsol3_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"cod_subgrupo" VARCHAR(11) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.newconsol3_2_w_cm
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsol3_2_w_cm" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" NUMERIC(38,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,3) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"general" NUMERIC(38,3) NULL,
	"cod_subgrupo" VARCHAR(13) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.newconsolrepeated
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "newconsolrepeated" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"reapeted" INT NULL,
	"general" MONEY(19,4) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"lastday" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.NotaEntrega
CREATE TABLE IF NOT EXISTS "NotaEntrega" (
	"numnotent" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"observacion" NVARCHAR(255) NULL DEFAULT NULL,
	"statunot" NVARCHAR(1) NOT NULL DEFAULT (1),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"vcod" NVARCHAR(15) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	PRIMARY KEY ("numnotent")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.NotaEntrega_tmp
CREATE TABLE IF NOT EXISTS "NotaEntrega_tmp" (
	"numnotent" NVARCHAR(10) NOT NULL,
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"observacion" NVARCHAR(255) NULL DEFAULT NULL,
	"statunot" NVARCHAR(1) NOT NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numnotent")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.NotEntDetalle
CREATE TABLE IF NOT EXISTS "NotEntDetalle" (
	"numnotent" NVARCHAR(10) NOT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT (0),
	"costo" NUMERIC(10,2) NULL DEFAULT (0),
	"fechanot" DATETIME(3) NULL DEFAULT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"workstation" NVARCHAR(20) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(20) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("numnotent","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.NTPRODUCTOS
CREATE TABLE IF NOT EXISTS "NTPRODUCTOS" (
	"Cod_prod" NVARCHAR(50) NULL DEFAULT NULL,
	"Nombre" NVARCHAR(50) NULL DEFAULT NULL,
	"CapsulasXUni" DECIMAL(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.OperadorTel
CREATE TABLE IF NOT EXISTS "OperadorTel" (
	"codopera" NUMERIC(18,0) NOT NULL,
	"usuario" NVARCHAR(20) NULL DEFAULT NULL,
	"bandera" NVARCHAR(1) NOT NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("codopera")
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.Pacientes_sin_factura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "Pacientes_sin_factura" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.PagosPR
CREATE TABLE IF NOT EXISTS "PagosPR" (
	"fechapago" DATETIME(3) NOT NULL,
	"modopago" CHAR(20) NOT NULL,
	"monto" MONEY(19,4) NOT NULL,
	"codsuc" NVARCHAR(2) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("fechapago","modopago","codsuc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.PagosPRCMA
CREATE TABLE IF NOT EXISTS "PagosPRCMA" (
	"fechapago" DATETIME(3) NOT NULL,
	"modopago" CHAR(20) NOT NULL,
	"monto" MONEY(19,4) NOT NULL,
	"codsuc" NVARCHAR(2) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("fechapago","modopago","codsuc")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Pais
CREATE TABLE IF NOT EXISTS "Pais" (
	"COD" NUMERIC(18,0) NOT NULL,
	"PAIS" NVARCHAR(255) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("COD")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.patientarrived
CREATE TABLE IF NOT EXISTS "patientarrived" (
	"id" BIGINT(19,0) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(20) NULL DEFAULT NULL,
	"historia" NVARCHAR(20) NULL DEFAULT NULL,
	"hora" NVARCHAR(50) NULL DEFAULT NULL,
	"hostname" NVARCHAR(50) NULL DEFAULT NULL,
	"usuario" NVARCHAR(50) NULL DEFAULT NULL,
	"llegada" NVARCHAR(50) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.PATOLOGIA500
CREATE TABLE IF NOT EXISTS "PATOLOGIA500" (
	"CODIGO" INT(10,0) NULL DEFAULT NULL,
	"PATOLOGIA" NVARCHAR(255) NULL DEFAULT NULL,
	"fecha" SMALLDATETIME(0) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"Factura" NVARCHAR(7) NULL DEFAULT NULL,
	"Masculino" NUMERIC(18,0) NULL DEFAULT NULL,
	"Femenino" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Perfilesintegrales
CREATE TABLE IF NOT EXISTS "Perfilesintegrales" (
	"codperfil" NVARCHAR(2) NULL DEFAULT NULL,
	"Menu" NVARCHAR(250) NULL DEFAULT NULL,
	"nombremnu" NVARCHAR(50) NULL DEFAULT NULL,
	"habilitado" NUMERIC(18,0) NULL DEFAULT NULL,
	"Tipo" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.PlanMedico
CREATE TABLE IF NOT EXISTS "PlanMedico" (
	"NroPlan" NVARCHAR(8) NULL DEFAULT NULL,
	"Codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"Pincipal" NVARCHAR(1) NULL DEFAULT (1),
	"Principal_Cod" NVARCHAR(8) NULL DEFAULT NULL,
	"Plan_Cod" NVARCHAR(8) NULL DEFAULT NULL,
	"Poliza_Nro" NVARCHAR(8) NULL DEFAULT NULL,
	"Grupo_Nro" NVARCHAR(8) NULL DEFAULT (N' '),
	"Rel_Con_Principal" NVARCHAR(10) NULL DEFAULT (N' '),
	"Plan_Adicional" NVARCHAR(8) NULL DEFAULT (N' '),
	"Deducible" NUMERIC(18,0) NULL DEFAULT (0),
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Presentacion
CREATE TABLE IF NOT EXISTS "Presentacion" (
	"Presentacion" NVARCHAR(10) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"relcod" NVARCHAR(10) NULL DEFAULT NULL,
	"descripcion" NVARCHAR(50) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"orden" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.presmedica
CREATE TABLE IF NOT EXISTS "presmedica" (
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"numfactu" NVARCHAR(10) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"dosis" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.presupuesto_d
CREATE TABLE IF NOT EXISTS "presupuesto_d" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"tipoitems" NVARCHAR(1) NULL DEFAULT (N'P'),
	"procentaje" FLOAT(53) NOT NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"Codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"codtecnico" NVARCHAR(2) NULL DEFAULT NULL,
	"aplicaiva" NVARCHAR(1) NULL DEFAULT ((0)),
	"aplicadcto" NVARCHAR(1) NULL DEFAULT ((0)),
	"aplicacommed" NVARCHAR(1) NULL DEFAULT ((1)),
	"aplicacomtec" NVARCHAR(1) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(2) NULL DEFAULT ('FA'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"pvpitem" MONEY(19,4) NULL DEFAULT ((0)),
	"dosis" INT(10,0) NULL DEFAULT ((0)),
	"cant_sugerida" NUMERIC(18,0) NULL DEFAULT ((0)),
	"costo" FLOAT(53) NULL DEFAULT ((0)),
	"monto_imp" FLOAT(53) NOT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"dstatfact" NVARCHAR(1) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu","coditems")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.presupuesto_m
CREATE TABLE IF NOT EXISTS "presupuesto_m" (
	"numfactu" NVARCHAR(10) NOT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"codclien" NVARCHAR(15) NULL DEFAULT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"subtotal" MONEY(19,4) NULL DEFAULT ((0)),
	"descuento" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"iva" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"total" MONEY(19,4) NULL DEFAULT ((0)),
	"statfact" NVARCHAR(1) NULL DEFAULT ((1)),
	"fechanul" DATETIME(3) NULL DEFAULT NULL,
	"desanul" NVARCHAR(255) NULL DEFAULT NULL,
	"recipe" BIT NULL DEFAULT NULL,
	"cancelado" NVARCHAR(1) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"monto_abonado" MONEY(19,4) NULL DEFAULT ((0)),
	"tipo" NVARCHAR(10) NULL DEFAULT (N'01'),
	"rowguid" UNIQUEIDENTIFIER NOT NULL DEFAULT (newid()),
	"totalpvp" MONEY(19,4) NULL DEFAULT ((0)),
	"tipopago" BIT NULL DEFAULT ((0)),
	"codseguro" INT(10,0) NULL DEFAULT ((0)),
	"plazo" INT(10,0) NULL DEFAULT ((0)),
	"vencimiento" DATETIME(3) NULL DEFAULT NULL,
	"codaltcliente" NVARCHAR(20) NULL DEFAULT NULL,
	"dias_tratamiento" INT(10,0) NULL DEFAULT ((0)),
	"patologia" INT(10,0) NULL DEFAULT NULL,
	"presupuesto" NVARCHAR(7) NULL DEFAULT NULL,
	"Impuesto" BIT NOT NULL DEFAULT ((1)),
	"TotImpuesto" MONEY(19,4) NOT NULL DEFAULT ((0)),
	"Alicuota" FLOAT(53) NULL DEFAULT ((0)),
	"monto_flete" MONEY(19,4) NULL DEFAULT ((0)),
	"Id" INT(10,0) NOT NULL,
	"medios" NUMERIC(18,0) NULL DEFAULT NULL,
	"medico" NVARCHAR(100) NULL DEFAULT NULL,
	"mediconame" NVARCHAR(50) NULL DEFAULT NULL,
	PRIMARY KEY ("numfactu")
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.PRUEBA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "PRUEBA" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.purchaseOM
CREATE TABLE IF NOT EXISTS "purchaseOM" (
	"pon" NVARCHAR(20) NOT NULL,
	"fecha" DATETIME(3) NULL DEFAULT (getdate()),
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"ipa" NVARCHAR(15) NULL DEFAULT NULL,
	"status" CHAR(1) NULL DEFAULT (1),
	"items" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"type" NVARCHAR(5) NULL DEFAULT NULL,
	PRIMARY KEY ("pon")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.purchaseorder
CREATE TABLE IF NOT EXISTS "purchaseorder" (
	"coditems" NVARCHAR(10) NOT NULL,
	"compra" NUMERIC(18,0) NULL DEFAULT NULL,
	"fecha" DATETIME(3) NULL DEFAULT NULL,
	"hora" NVARCHAR(15) NULL DEFAULT NULL,
	"purchaseOrder" NVARCHAR(20) NOT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"ipa" NVARCHAR(15) NULL DEFAULT NULL,
	"costo" MONEY(19,4) NULL DEFAULT (0),
	"status" CHAR(1) NULL DEFAULT (1),
	"Id" INT(10,0) NOT NULL,
	"type" NVARCHAR(5) NULL DEFAULT NULL,
	PRIMARY KEY ("coditems","purchaseOrder")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Region
CREATE TABLE IF NOT EXISTS "Region" (
	"Cod" DECIMAL(18,0) NULL DEFAULT NULL,
	"Reg" NVARCHAR(255) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.RFactura
CREATE TABLE IF NOT EXISTS "RFactura" (
	"numfactu" NVARCHAR(7) NULL DEFAULT NULL,
	"fechafac" DATETIME(3) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"precunit" MONEY(19,4) NULL DEFAULT NULL,
	"usuario" NVARCHAR(15) NULL DEFAULT NULL,
	"workstation" NVARCHAR(15) NULL DEFAULT NULL,
	"ipaddress" NVARCHAR(15) NULL DEFAULT NULL,
	"fecreg" DATETIME(3) NULL DEFAULT NULL,
	"horareg" NVARCHAR(8) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.SEGURIDAD
CREATE TABLE IF NOT EXISTS "SEGURIDAD" (
	"control" NVARCHAR(50) NULL DEFAULT NULL,
	"habilitado" BIT NULL DEFAULT (1),
	"usuario" NVARCHAR(50) NULL DEFAULT NULL,
	"perfil" NVARCHAR(2) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.SeguridadControl
CREATE TABLE IF NOT EXISTS "SeguridadControl" (
	"usuario" NVARCHAR(50) NULL DEFAULT NULL,
	"formulario" NCHAR(100) NULL DEFAULT NULL,
	"controlname" NVARCHAR(100) NULL DEFAULT NULL,
	"perfil" NVARCHAR(2) NULL DEFAULT NULL,
	"habilitado" BIT NULL DEFAULT NULL,
	"visible" BIT NULL DEFAULT NULL,
	"id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.seguridadintegral
CREATE TABLE IF NOT EXISTS "seguridadintegral" (
	"login" NVARCHAR(20) NULL DEFAULT NULL,
	"Menu" NVARCHAR(250) NULL DEFAULT NULL,
	"nombremnu" NVARCHAR(50) NULL DEFAULT NULL,
	"habilitado" NUMERIC(18,0) NULL DEFAULT NULL,
	"Tipo" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.SeguridadxDias
CREATE TABLE IF NOT EXISTS "SeguridadxDias" (
	"Login" NVARCHAR(20) NULL DEFAULT NULL,
	"Dia" INT(10,0) NULL DEFAULT NULL,
	"HoraI" NVARCHAR(20) NULL DEFAULT NULL,
	"HoraF" NVARCHAR(20) NULL DEFAULT NULL,
	"HoraHabitada" NVARCHAR(1) NULL DEFAULT (0),
	"DiaHabilitado" NVARCHAR(1) NULL DEFAULT (0),
	"testHora" DATETIME(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Servicios
CREATE TABLE IF NOT EXISTS "Servicios" (
	"Cedula" NVARCHAR(12) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"Cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"Hora" CHAR(20) NULL DEFAULT NULL,
	"codtecnico" NVARCHAR(3) NULL DEFAULT NULL,
	"facturado" NVARCHAR(1) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.setexistenciassuero
DELIMITER //
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE setexistenciassuero
	-- Add the parameters for the stored procedure here
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	declare @existencia as numeric
    -- Insert statements for procedure here
	

	SELECT @existencia=isnull(sum(I.GrExist),0) from MInventario I WHERE I.coditems='50GST'
	IF @existencia>0
		UPDATE MInventario SET  existencia=(GrExist/medida)  WHERE coditems='50GST'

END
//
DELIMITER ;


-- Dumping structure for procedure farmacias.sin_medico_asociado
DELIMITER //
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================

CREATE PROCEDURE sin_medico_asociado

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Insert statements for procedure here
	UPDATE MClientes SET codmedico='000'  where Historia is null or Historia='' OR Historia like 'NO%' and codmedico!='000' 
END
//
DELIMITER ;


-- Dumping structure for table farmacias.sms
CREATE TABLE IF NOT EXISTS "sms" (
	"cellnumber" NVARCHAR(50) NULL DEFAULT NULL,
	"nombre" NVARCHAR(100) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.spAlertas
DELIMITER //
CREATE procedure spAlertas as

declare @coditems nvarchar(10)
declare @desitems nvarchar(100)
declare @des as nvarchar(max)
 
declare inventario cursor for select coditems,desitems from Minventario where prod_serv='P' and activo='1'


open inventario
fetch next from inventario into @coditems,@desitems
while @@fetch_status=0
 begin
  if ( Select sum(pedido)  from [farmacias].[dbo].[viewAlertaPedido] a  where a.coditems= @coditems )<=1
  begin
    Select @des= [desitems] +'	  -  Existencia real :'+STR([existencia])     from [farmacias].[dbo].[viewAlertaPedido] a  where a.coditems= @coditems
	exec dbo.spSendMail 'Alerta de productos por debajo del nilvel',@des,'joseolalde@gmail.com;larciles@gmail.com'
  end
  fetch next from inventario into @coditems,@desitems
end
close inventario
deallocate inventario
Select 'EL producto' vas//
DELIMITER ;


-- Dumping structure for procedure farmacias.spCocientes
DELIMITER //

CREATE procedure [dbo].[spCocientes]
--@prod char(10),
--@fi datetime,
--@ff datetime

as 

begin

declare @ventas table(
			 coditems nvarchar(10)
			,precunit MONEY
			,costo float
			,ventas float
			,fecha datetime
			,anuladas numeric
			,producto nvarchar(100)
			)
insert into @ventas
select  t1.coditems, t1.precunit,t1.costo ,(t2.ventas-t2.anulaciones)-(t2.NotasCreditos)ventas, t2.fecha, t2.anulaciones,t1.desitems
from (select pr.coditems, pr.precunit,mi.costo,mi.desitems from mprecios pr
inner join minventario mi on pr.coditems=mi.coditems
where codtipre='00' and  mi.activo=1  ) as t1
join ( select ci.fechacierre fecha, ci.coditems, ci.ventas,ci.anulaciones ,ci.NotasCreditos  from dcierreinventario ci) as t2
on t1.coditems=t2.coditems

Select 
coditems
,precunit
,costo
,ventas
,fecha
,anuladas
,producto
,(precunit*ventas) vbrutas
,(ventas*costo)cbruto
 from @ventas vt
--where coditems in (@prod) and fecha between @fi and @ff


end
//
DELIMITER ;


-- Dumping structure for procedure farmacias.spConsolidado
DELIMITER //
CREATE procedure [dbo].[spConsolidado]
as
declare @ventasPrd table(
			 numfactu nvarchar(7)
			,nombres nvarchar(100)
			,[fechafac] datetime
			,[subtotal] MONEY
			,[descuento] MONEY
			,[total] MONEY
			,[statfact] nvarchar(1)
			,[tipopago] bit
			,[TotImpuesto] MONEY
			,[monto_flete] MONEY
			,[doc] nvarchar(10)
			,[workstation] nvarchar(15)
			,[cancelado] nvarchar(1)
			,[tipo] int	
			,qtySold int		
			)
declare @ventasSer table(
			 numfactu nvarchar(7)
			,nombres nvarchar(100)
			,[fechafac] datetime
			,[subtotal] MONEY
			,[descuento] MONEY
			,[total] MONEY
			,[statfact] nvarchar(1)
			,[tipopago] bit
			,[TotImpuesto] MONEY
			,[monto_flete] MONEY
			,[doc] nvarchar(10)
			,[workstation] nvarchar(15)
			,[cancelado] nvarchar(1)
			,[tipo] int	
			,qtySold int	
			)
			
declare @ventasSerMS table(
			 numfactu nvarchar(7)
			,nombres nvarchar(100)
			,[fechafac] datetime
			,[subtotal] MONEY
			,[descuento] MONEY
			,[total] MONEY
			,[statfact] nvarchar(1)
			,[tipopago] bit
			,[TotImpuesto] MONEY
			,[monto_flete] MONEY
			,[doc] nvarchar(10)
			,[workstation] nvarchar(15)
			,[cancelado] nvarchar(1)
			,[tipo] int
			,qtySold int			
			)
			 
insert into @ventasPrd 
SELECT numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 1 AS tipo, 0 qtySold FROM dbo.VentasDiarias

insert into @ventasSer 
SELECT numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 2 AS tipo, 0 qtySold FROM dbo.VentasDiariasCMA


insert @ventasSerMS
exec spTestVentasMSS

select *,da.Workstation,da.id_centro,da.desestac,da.codestac from @ventasPrd a
inner join Vestaciones da on  a.workstation =da.Workstation and    a.tipo =da.id_centro
--inner join Vestaciones ea on  a.tipo =ea.id_centro
union all
select *,db.Workstation,db.id_centro,db.desestac,db.codestac  from @ventasSer b
inner join Vestaciones db on  b.workstation =db.Workstation and b.tipo =db.id_centro
--inner join Vestaciones eb on  b.tipo =eb.id_centro
union all
select *,d.Workstation,d.id_centro,d.desestac,d.codestac  from @ventasSerMS c
inner join Vestaciones d on  c.workstation =d.Workstation and c.tipo =d.id_centro
--inner join Vestaciones e on  c.tipo =e.id_centro//
DELIMITER ;


-- Dumping structure for procedure farmacias.spRepeatV4
DELIMITER //

CREATE  procedure [dbo].[spRepeatV4]  
(@coditems nvarchar(10),@fi datetime, @ff datetime)
as
/*
declare @fi datetime
declare @ff datetime
declare @coditems nvarchar(10)

set @coditems='150'
set @fi='20160401'
set @ff='20160531'
*/
declare @ViewA table(
			 codclien nvarchar(5)
			,v int
			,coditems nvarchar(10)
			,desitems nvarchar(255)
			)

insert into @ViewA

select a.codclien, count(*) v , a.coditems,a.desitems /*, a.fechafac */ from [dbo].[viewRepeat] a
where coditems=@coditems and fechafac >=  @fi and  fechafac <= @ff
group by a.codclien , a.coditems,a.desitems
order by count(*) desc

select v , count(*) veces,  b.coditems,b.desitems from @ViewA b
group by v,  b.coditems,b.desitems
//
DELIMITER ;


-- Dumping structure for procedure farmacias.spSendMail
DELIMITER //
Create procedure spSendMail

@asunto nvarchar(255),
@cuerpo nvarchar(255),
@email  nvarchar(255)
as 
Begin


EXEC msdb.dbo.sp_send_dbmail @profile_name='CMA SERVER'
,@recipients=@email
,@subject=@asunto
,@body=@cuerpo

END

//
DELIMITER ;


-- Dumping structure for procedure farmacias.spSueroTerapia
DELIMITER //
--use farmacias
--declare @fi datetime
--declare @ff datetime

--set @fi='20160501'
--set @ff='20160517'

Create  procedure [dbo].[spSueroTerapia] 
(@fi datetime, @ff datetime)
as

select  b.coditems, c.desitems, b.cantidad,b.precunit,(( ( b.cantidad*b.precunit)*a.Alicuota)/100) descuento, a.Alicuota, (b.cantidad*b.precunit) - (((b.cantidad*b.precunit)*a.Alicuota)/100) Total, a.statfact,a.usuario,a.workstation, a.numfactu, a.fechafac, d.nombres from cma_MFactura a
inner join cma_DFactura b on a.numfactu=b.numfactu
inner join MInventario c on b.coditems=c.coditems
inner join MClientes d on a.codclien=d.codclien
where a.statfact=3 and b.coditems in ('20150727ST','15GST','20GST','25GST','30GST','35GST','40GST','45GST','50GST','55GST','60GST','65GST','70GST','75GST','80GST','85GST','90GST','95GST','100GST') and a.fechafac between @fi and @ff

//
DELIMITER ;


-- Dumping structure for procedure farmacias.spTerapiaDelDolor
DELIMITER //
CREATE procedure [dbo].[spTerapiaDelDolor] 
(@fi datetime, @ff datetime)
as

declare @ventasTD table(
			 codclien VARCHAR(5)					
			,cantidad int
			,total money			
			)
insert into @ventasTD
 
Select a.codclien, sum(b.cantidad) cantidad, sum(a.[total]) total from MSSMFact a 
inner join mssdfact b on  a.numfactu=b.numfactu
where a.statfact=3 and a.tipo='01' and a.fechafac between @fi  and @ff 
group by a.codclien

select c.nombres,a.codclien,a.cantidad, dbo.getRemainingTD(@fi,@ff,a.codclien,3) tconsumidas, a.cantidad-dbo.getRemainingTD(@fi,@ff,a.codclien,3) porconsumir, dbo.[getDateMaxMinTD](@fi,@ff,a.codclien,'Min') Inicio, dbo.[getDateMaxMinTD](@fi,@ff,a.codclien,'Max') Proxima, c.Historia Record, a.total from @ventasTD a 
inner join MClientes c on a.codclien=c.codclien

//
DELIMITER ;


-- Dumping structure for procedure farmacias.spTerapiaDelDolorDetails
DELIMITER //

Create procedure [dbo].[spTerapiaDelDolorDetails] 
(@fi datetime, @ff datetime)
as

select  b.coditems,b.cantidad, c.desitems,d.nombres,  dbo.getRemainingTD(a.fechafac,a.fechafac,a.codclien,3) Aplicadas, (Select count(*) from MSSDFact x  where a.numfactu=x.numfactu) items , d.Historia Record,* from mssmfact a
inner join MSSDFact b on a.numfactu=b.numfactu
inner join MInventario c on b.coditems=c.coditems
inner join MClientes d on a.codclien=d.codclien
where a.statfact<>2  and a.fechafac between @fi and @ff//
DELIMITER ;


-- Dumping structure for procedure farmacias.spTestVentasMSS
DELIMITER //
CREATE procedure [dbo].[spTestVentasMSS]
as
if exists (select top 1 * from dbo .VentasDiariasMSS )
    SELECT     numfactu, nombres, fechafac, ISNULL(subtotal, 0) subtotal, ISNULL(descuento, 0) descuento, ISNULL(total, 0) total , statfact, tipopago, ISNULL(TotImpuesto, 0) TotImpuesto, ISNULL(monto_flete, 0) monto_flete, doc, 
                      workstation, cancelado, 3 AS tipo, (select sum(b.cantidad) from MSSDFact b where b.numfactu= VentasDiariasMSS.numfactu) qtySold
FROM         dbo.VentasDiariasMSS
else
    select 0 numfactu ,'' nombres ,GETDATE() fechafac,0 subtotal,0 descuento,0 total,3 statfact,0 tipopago,0 TotImpuesto,0 monto_flete,0 doc,'ATENCION02' workstation,1 cancelado,3 tipo, 0 qtySold
//
DELIMITER ;


-- Dumping structure for table farmacias.States
CREATE TABLE IF NOT EXISTS "States" (
	"State" NVARCHAR(255) NULL DEFAULT NULL,
	"Abre" NVARCHAR(8) NULL DEFAULT NULL,
	"PAIS" NUMERIC(18,0) NULL DEFAULT NULL,
	"COD" NUMERIC(18,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("COD")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Status
CREATE TABLE IF NOT EXISTS "Status" (
	"statfact" NVARCHAR(1) NULL DEFAULT NULL,
	"Status" NVARCHAR(15) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.SubDpresupuestos
CREATE TABLE IF NOT EXISTS "SubDpresupuestos" (
	"numpre" NVARCHAR(6) NOT NULL,
	"coditems" NVARCHAR(10) NOT NULL,
	"subcoditems" NVARCHAR(10) NOT NULL,
	"cantidad" DECIMAL(18,0) NOT NULL,
	"precunit" DECIMAL(18,2) NOT NULL,
	"baseimponible" DECIMAL(18,2) NOT NULL,
	"subtotal" DECIMAL(18,2) NOT NULL,
	"iva" DECIMAL(18,2) NOT NULL,
	"aplicaiva" NVARCHAR(1) NOT NULL,
	"aplicadcto" NVARCHAR(1) NOT NULL,
	"aplicacommed" NVARCHAR(1) NOT NULL,
	"aplicacomtec" NVARCHAR(1) NOT NULL,
	"descuento" DECIMAL(18,2) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Sucursal
CREATE TABLE IF NOT EXISTS "Sucursal" (
	"sucursal" NVARCHAR(50) NULL DEFAULT NULL,
	"codsucursal" INT(10,0) NOT NULL,
	"Id" INT(10,0) NOT NULL,
	PRIMARY KEY ("Id")
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.temporal
CREATE TABLE IF NOT EXISTS "temporal" (
	"codclien" NVARCHAR(5) NOT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.temporal1
CREATE TABLE IF NOT EXISTS "temporal1" (
	"codclien" NVARCHAR(5) NOT NULL,
	"codmedico" NVARCHAR(3) NULL DEFAULT NULL,
	"control" INT(10,0) NULL DEFAULT NULL,
	"nuevo" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.TEST011516
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "TEST011516" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacierre" DATETIME NOT NULL,
	"existencia" NUMERIC(18,0) NULL,
	"compra" NUMERIC(18,0) NOT NULL,
	"DevCompra" NUMERIC(18,0) NOT NULL,
	"ventas" NUMERIC(18,0) NULL,
	"anulaciones" NUMERIC(18,0) NULL,
	"NE" NUMERIC(18,0) NOT NULL,
	"nc" NUMERIC(18,0) NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ajustesPos" NUMERIC(18,0) NULL,
	"ajustesNeg" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.tipoconsulta
CREATE TABLE IF NOT EXISTS "tipoconsulta" (
	"codconsulta" NVARCHAR(2) NULL DEFAULT NULL,
	"descons" NVARCHAR(80) NULL DEFAULT NULL,
	"tipo" NVARCHAR(2) NULL DEFAULT NULL,
	"DuracionC" INT(10,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.TipoInstrumentoPago
CREATE TABLE IF NOT EXISTS "TipoInstrumentoPago" (
	"Codinst" NVARCHAR(3) NULL DEFAULT NULL,
	"CodTipInst" NVARCHAR(3) NULL DEFAULT NULL,
	"DesTipInst" NVARCHAR(25) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.tiponotacredito
CREATE TABLE IF NOT EXISTS "tiponotacredito" (
	"codtiponotcre" NVARCHAR(1) NOT NULL,
	"destiponotcre" NVARCHAR(60) NULL DEFAULT NULL,
	"afecta_inv_actual" BIT NULL DEFAULT NULL,
	"afecta_inv_vencido" BIT NULL DEFAULT NULL,
	"req_fact" BIT NULL DEFAULT NULL,
	"req_porcen" BIT NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.TipoPrecio
CREATE TABLE IF NOT EXISTS "TipoPrecio" (
	"codtipre" NVARCHAR(2) NULL DEFAULT NULL,
	"destipre" NVARCHAR(30) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL,
	"byorder" INT(10,0) NULL DEFAULT ((99))
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.tipoterapia
CREATE TABLE IF NOT EXISTS "tipoterapia" (
	"id" NVARCHAR(10) NULL DEFAULT NULL,
	"terapia" NVARCHAR(50) NULL DEFAULT NULL,
	"codconsulta" NVARCHAR(10) NULL DEFAULT NULL,
	"empresa" NVARCHAR(10) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.tmdpatologia
CREATE TABLE IF NOT EXISTS "tmdpatologia" (
	"CODIGO" INT(10,0) NULL DEFAULT NULL,
	"PATOLOGIA" NVARCHAR(255) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.tmpdbcompair
DELIMITER //
CREATE PROCEDURE tmpdbcompair
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

 declare @ventasPrd table(amount money, periodo nvarchar(7),nombremedico nvarchar(100), year int, mes int, fechafac datetime) 

insert into @ventasPrd
   Select sum(cantidad*precunit)- sum(Descuento) amount  ,periodo,nombremedico,year,vp.mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac
from  VIEW_Week_Report_ vp where fechafac between  '01-01-2017' and '03-29-2017' and Prod_serv='p' and coditems in('150')  group by mes, periodo,nombremedico,year,month(fechafac) order by year desc,mes desc 

END
//
DELIMITER ;


-- Dumping structure for table farmacias.tmpdproducto
CREATE TABLE IF NOT EXISTS "tmpdproducto" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"dosis" INT(10,0) NULL DEFAULT NULL,
	"potes" INT(10,0) NULL DEFAULT NULL,
	"descripcion" NVARCHAR(255) NULL DEFAULT NULL,
	"codclien" NVARCHAR(5) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.Transferencia
CREATE TABLE IF NOT EXISTS "Transferencia" (
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"Fecha" DATETIME(3) NULL DEFAULT NULL,
	"De_Existencia" NUMERIC(18,0) NULL DEFAULT NULL,
	"Para_Existencia" NUMERIC(18,0) NULL DEFAULT NULL,
	"Costo" MONEY(19,4) NULL DEFAULT NULL,
	"De" INT(10,0) NULL DEFAULT NULL,
	"Para" INT(10,0) NULL DEFAULT NULL,
	"Hora" CHAR(20) NULL DEFAULT NULL,
	"Usuario" NVARCHAR(50) NULL DEFAULT NULL,
	"Transf" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for procedure farmacias.ultimoPresupuesto
DELIMITER //

CREATE  procedure ultimoPresupuesto (@NroPresu nvarchar(6) output)  
as 
 update empresa set ultimopresupuesto=cast(ultimopresupuesto as  numeric)+1
 select @NroPresu = ultimopresupuesto from empresa
//
DELIMITER ;


-- Dumping structure for procedure farmacias.updatecmai
DELIMITER //
CREATE PROCEDURE updatecmai AS
declare @coditems as nvarchar(10)
set @coditems='50GST'
update minventario set existencia=GrExist/medida where coditems=@coditems
//
DELIMITER ;


-- Dumping structure for view farmacias.VaTEST
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VaTEST" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VCOMPRASXFACTURACION
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VCOMPRASXFACTURACION" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiarias
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiarias" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"general" MONEY(19,4) NULL,
	"ct" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMA" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"presentacion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"general" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMAa
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMAa" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"presentacion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACELMADRESnoCons
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACELMADRESnoCons" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST1" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST1_3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST1_3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACSTRep
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACSTRep" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMABLOQ
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2CEMABLOQ" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" VARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAINTRAVCIN
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2CEMAINTRAVCIN" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" VARCHAR(11) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMALASERCIN
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2CEMALASERCIN" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" VARCHAR(13) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAnoCons
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2CEMAnoCons" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAnoConsNew
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_2CEMAnoConsNew" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL,
	"medico" NVARCHAR(52) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_BLOQUEO_REYES
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_BLOQUEO_REYES" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CM
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_CM" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Id" INT NOT NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CONS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_CONS" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CONS_CINTRON
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_CONS_CINTRON" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_EXO
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_EXO" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_INTRA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_INTRA" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_LASER
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_LASER" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_NOCM
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_NOCM" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_SUERO
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasCMACST_4_SUERO" (
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuari" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL,
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ts" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"general" MONEY(19,4) NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"initials" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSIV
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSIV" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSIV_3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSIV_4" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_5
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSIV_5" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSLA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSLA" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSSLA_3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSSLA_3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotalnew" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuentonew" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"totalnew" NUMERIC(38,3) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(38,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" NUMERIC(38,3) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasMSS_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasMSS_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" MONEY(19,4) NULL,
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiariasn
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiariasn" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VentasDiarias_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VentasDiarias_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"general" MONEY(19,4) NULL,
	"ct" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"mediconame" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.ventasMedicos01
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "ventasMedicos01" (
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"venta" MONEY(19,4) NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.Vestaciones
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "Vestaciones" (
	"codestac" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desestac" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fondo_inicial" MONEY(19,4) NULL,
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW1" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW10
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW10" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NOT NULL,
	"fecha_cita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW11
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW11" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(15) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW2" (
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW3" (
	"numfactu" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW3ANULADASCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW3ANULADASCMA" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanul" DATETIME NULL,
	"desanula" NVARCHAR(600) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW4" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" MONEY(19,4) NOT NULL,
	"compare" NUMERIC(38,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"Expr1" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW5
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW5" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW6
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW6" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"Alicuota" FLOAT(53) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"subtotalitem" NUMERIC(38,4) NULL,
	"Descdet" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW7" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"Expr1" NUMERIC(38,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW8
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW8" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" NUMERIC(18,0) NULL,
	"fechacierre" DATETIME NOT NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ventas" NUMERIC(38,0) NULL,
	"DVentas" NUMERIC(38,0) NULL,
	"compra" NUMERIC(38,0) NULL,
	"devcompra" NUMERIC(38,0) NULL,
	"Ajustes_mas" NUMERIC(38,0) NULL,
	"Ajustes_menos" NUMERIC(38,0) NULL,
	"NE" NUMERIC(38,0) NULL,
	"Nc" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewAlertaPedido
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewAlertaPedido" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" FLOAT(53) NULL,
	"PO" NUMERIC(38,0) NULL,
	"Estimada" FLOAT(53) NULL,
	"Promedio" NUMERIC(38,6) NULL,
	"pedido" FLOAT(53) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_grupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewappconf_a
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewappconf_a" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"appt_date_es" VARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"appt_date_en" VARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"esp_day" VARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"eng_day" VARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cel" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" VARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewcmareturn
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewcmareturn" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" MONEY(19,4) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"destatus" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"iva" MONEY(19,4) NOT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"alicuota" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"monto_abonado" MONEY(19,4) NULL,
	"tasadesc" NUMERIC(18,2) NOT NULL,
	"saldo" MONEY(19,4) NOT NULL,
	"Id" INT NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewcompairprod
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewcompairprod" (
	"amount" MONEY(19,4) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"months" INT NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWConsolidado
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWConsolidado" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWConsolidadot
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWConsolidadot" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWdescuentos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWdescuentos" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codesc" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desdesct" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWHorario
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWHorario" (
	"Codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraI" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Fecha" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewMconsutasAll
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewMconsutasAll" (
	"citados" NUMERIC(18,0) NULL,
	"confirmado" NUMERIC(18,0) NULL,
	"asistido" NUMERIC(10,0) NULL,
	"noasistido" NUMERIC(10,0) NULL,
	"fecha_cita" DATETIME NULL,
	"activa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewmedicos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewmedicos" (
	"codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesCitadosXMedico" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"noasistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nro_asistencias" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraRegistro" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico02
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesCitadosXMedico02" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"noasistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nro_asistencias" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraRegistro" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico03
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesCitadosXMedico03" (
	"codclien" NVARCHAR(15) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"noasistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nro_asistencias" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraRegistro" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico04
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesCitadosXMedico04" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"noasistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nro_asistencias" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraRegistro" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico05
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesCitadosXMedico05" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"noasistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nro_asistencias" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"HoraRegistro" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"veces" INT NULL,
	"ultimaCita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesMayorInversion" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"codseguro" INT NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"Alicuota" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion01
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesMayorInversion01" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"codseguro" INT NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"Alicuota" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion02
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPacientesMayorInversion02" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"codseguro" INT NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"Alicuota" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosDEV
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosDEV" (
	"fechapago" DATETIME NULL,
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosDEVCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosDEVCMA" (
	"fechapago" DATETIME NULL,
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"idempresa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosDEVMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosDEVMSS" (
	"fechapago" DATETIME NULL,
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosFAC
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosFAC" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" FLOAT(53) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"TaxA" FLOAT(53) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosFACCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosFACCMA" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"idempresa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosFACMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosFACMSS" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" FLOAT(53) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"TaxA" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPAGOSFACMSS_W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPAGOSFACMSS_W7" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"userfac" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"userpagos" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPAGOSFAC_W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPAGOSFAC_W7" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosPR
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosPR" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" FLOAT(53) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"montototal" FLOAT(53) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosPRCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosPRCMA" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"idempresa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosPRCMACST
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosPRCMACST" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"idempresa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosPRCMACST_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosPRCMACST_1" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"idempresa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWpagosPRMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWpagosPRMSS" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" FLOAT(53) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"montototal" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosPRMSS_W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosPRMSS_W7" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"montototal" NUMERIC(18,2) NULL,
	"userfac" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"userpagos" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagosPR_W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagosPR_W7" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"trans_electronica" BIT NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto_flete" MONEY(19,4) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"montototal" NUMERIC(18,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagoTotal
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagoTotal" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"pago" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPagoTotalMMS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPagoTotalMMS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"pago" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWParaAjusteVentas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWParaAjusteVentas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPararAjusteNotaEntrega
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPararAjusteNotaEntrega" (
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"statunot" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewpivot
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewpivot" (
	"amount" MONEY(19,4) NULL,
	"medico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"months" INT NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewprodcompair
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewprodcompair" (
	"amount" MONEY(19,4) NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"months" INT NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPRODLASERSUEROINTRA" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tventa" INT NOT NULL,
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(12) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRASALES
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPRODLASERSUEROINTRASALES" (
	"fechafac" DATETIME NULL,
	"total" NUMERIC(38,4) NULL,
	"medico" NVARCHAR(52) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(11) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRATITL
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPRODLASERSUEROINTRATITL" (
	"fechafac" DATETIME NULL,
	"Dventa" VARCHAR(11) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRA_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWPRODLASERSUEROINTRA_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tventa" INT NOT NULL,
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(15) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewProLaserSuero
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewProLaserSuero" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"tventa" INT NOT NULL,
	"medico" NVARCHAR(52) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(16) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewProLaserSuero_WC
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewProLaserSuero_WC" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" INT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"tventa" INT NOT NULL,
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dventa" VARCHAR(16) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWRecordRepetido
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWRecordRepetido" (
	"fecha" DATETIME NULL,
	"fecha_cita" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" VARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeat
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeat" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeat2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeat2" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"veces" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeat3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeat3" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"veces" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeatV4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeatV4" (
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"v" INT NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"maxi" DATETIME NULL,
	"mini" DATETIME NULL,
	"cantidad" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeatV5
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeatV5" (
	"v" INT NULL,
	"descripcion" VARCHAR(45) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalCell" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeatV5All
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeatV5All" (
	"v" INT NOT NULL,
	"descripcion" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewRepeatWHOLE
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewRepeatWHOLE" (
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"v" INT NULL,
	"mini" DATETIME NULL,
	"maxi" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWrepeti2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWrepeti2" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"expresion" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWrepetid
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWrepetid" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"inactivo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" INT NULL,
	"Expr2" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"expresion" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWrepetidos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWrepetidos" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" INT NULL,
	"Expr2" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"expresion" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEWrepetidos2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEWrepetidos2" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"expresion" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.ViewSoloProductos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "ViewSoloProductos" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewSSNoasistidos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewSSNoasistidos" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(18,0) NULL,
	"confirmado" NUMERIC(18,0) NULL,
	"asistido" NUMERIC(10,0) NULL,
	"noasistido" NUMERIC(10,0) NULL,
	"activa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"hora" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.ViewSumPagosPRMSSW7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "ViewSumPagosPRMSSW7" (
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" NUMERIC(38,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.ViewSumPagosPRW7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "ViewSumPagosPRW7" (
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" NUMERIC(38,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewTerapiaDelDolor
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewTerapiaDelDolor" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewtipofacturascma
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewtipofacturascma" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewTipoPagoLaser
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewTipoPagoLaser" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" FLOAT(53) NULL,
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewTipoPagoServicios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewTipoPagoServicios" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TipoDePago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NumeroDePagos" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.viewVentasDiarasGrmSt
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "viewVentasDiarasGrmSt" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL,
	"presentacion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Asistidos_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Asistidos_0215" (
	"fecha_cita" DATETIME NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" INT NOT NULL,
	"citacontrol" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_audit
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_audit" (
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"company" VARCHAR(8) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codcompany" INT NOT NULL,
	"id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_BaseImponible
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_BaseImponible" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Imp1" FLOAT(53) NULL,
	"Imp2" FLOAT(53) NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"subtotal" MONEY(19,4) NULL,
	"fecha" DATETIME NULL,
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"documento" VARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"iva" MONEY(19,4) NOT NULL,
	"Base" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ChequeaFacturas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ChequeaFacturas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"bruto" NUMERIC(38,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"descuentosi" MONEY(19,4) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"impuestos" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"totalitems" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_cierreInventario
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_cierreInventario" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacierre" DATETIME NULL,
	"existencia" NUMERIC(18,0) NULL,
	"ventas" NUMERIC(18,0) NULL,
	"anulaciones" NUMERIC(18,0) NULL,
	"ajustes" NUMERIC(18,0) NULL,
	"InvPosible" NUMERIC(18,0) NULL,
	"InvActual" NUMERIC(18,0) NULL,
	"fallas" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_cma_consultas_detalle_facturas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_cma_consultas_detalle_facturas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"rowguid" UNIQUEIDENTIFIER NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_DescFactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_DescFactura" (
	"desdesct" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"base" MONEY(19,4) NULL,
	"porcentaje" NUMERIC(18,2) NULL,
	"codesc" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_cma_dfactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_cma_dfactura" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,0) NULL,
	"numfactu" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_cma_dfactura_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_cma_dfactura_1" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"Impuesto" FLOAT(53) NULL,
	"subtotal" FLOAT(53) NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" FLOAT(53) NULL,
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_Mfactura" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"iva" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_Mfactura_1" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"Impuesto1" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalpvp" MONEY(19,4) NULL,
	"Impuesto2" MONEY(19,4) NULL,
	"Id" INT NOT NULL,
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_Mfactura_2" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_Mfactura_3" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"tocantidad" NUMERIC(38,0) NULL,
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CMA_Mfactura_4" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_cma_suero_detalle_facturas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_cma_suero_detalle_facturas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"procentaje" NUMERIC(18,0) NULL,
	"descuento" MONEY(19,4) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtecnico" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"rowguid" UNIQUEIDENTIFIER NULL,
	"pvpitem" MONEY(19,4) NULL,
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"costo" FLOAT(53) NULL,
	"monto_imp" FLOAT(53) NULL,
	"codseguro" INT NULL,
	"Id" INT NOT NULL,
	"percentage" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_cma__detalle_devolucion
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_cma__detalle_devolucion" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_cma__detalle_facturas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_cma__detalle_facturas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_cocientes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_cocientes" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL,
	"costo" FLOAT(53) NULL,
	"ventas" NUMERIC(20,0) NULL,
	"fecha" DATETIME NOT NULL,
	"anuladas" NUMERIC(18,0) NULL,
	"producto" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_comisioinesMed
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_comisioinesMed" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(22,4) NULL,
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" NUMERIC(19,2) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_comisiones
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_comisiones" (
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NOT NULL,
	"codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"comisiones" NUMERIC(18,2) NULL,
	"comServ" NUMERIC(18,2) NULL,
	"NC" NUMERIC(18,2) NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Compras
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Compras" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_consolidado
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_consolidado" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" NUMERIC(22,4) NULL,
	"descuento" NUMERIC(20,4) NOT NULL,
	"total" NUMERIC(22,4) NULL,
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" INT NULL,
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"tipo" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ConsProductosV
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ConsProductosV" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ConsultaServicios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ConsultaServicios" (
	"codcons" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(12) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ConsultasMed
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ConsultasMed" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"fecha_cita" DATETIME NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" INT NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"MONP_Bs" NUMERIC(38,4) NULL,
	"MONS_Bs" MONEY(19,4) NULL,
	"items" INT NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ConsultasPacientes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ConsultasPacientes" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cita_nro" INT NULL,
	"item2" INT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Consulta_Serv
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Consulta_Serv" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"citados" NUMERIC(18,0) NULL,
	"confirmado" NUMERIC(18,0) NULL,
	"asistido" NUMERIC(10,0) NULL,
	"noasistido" NUMERIC(10,0) NULL,
	"activa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ControlServicios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ControlServicios" (
	"fechaServicio" DATETIME NOT NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_CreaPreciosRemoto
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_CreaPreciosRemoto" (
	"nombre_alterno" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecing" DATETIME NULL,
	"Exisminima" NUMERIC(18,0) NULL,
	"Exismaxima" NUMERIC(18,0) NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaIva" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComTec" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"CapsulasXUni" NUMERIC(18,0) NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Cuadre
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Cuadre" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"total" NUMERIC(22,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" INT NULL,
	"tipo" INT NOT NULL,
	"tipodoc" VARCHAR(27) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"operacion" VARCHAR(8) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dajuste
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dajuste" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codajus" NVARCHAR(6) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dajuste_inv
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dajuste_inv" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codajus" NVARCHAR(6) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechajust" DATETIME NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" TEXT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dcompras
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dcompras" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dcompras_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dcompras_1" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" MONEY(19,4) NULL,
	"total" NUMERIC(38,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DCotizacion
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DCotizacion" (
	"numcot" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Dosis" DECIMAL(18,0) NULL,
	"Capsulas" DECIMAL(18,0) NULL,
	"precunit" DECIMAL(18,2) NULL,
	"cantidad" DECIMAL(18,0) NULL,
	"subtotal" DECIMAL(37,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DescFactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DescFactura" (
	"desdesct" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"base" MONEY(19,4) NULL,
	"porcentaje" NUMERIC(18,2) NULL,
	"codesc" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DescFacturaCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DescFacturaCMA" (
	"desdesct" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"base" MONEY(19,4) NULL,
	"porcentaje" NUMERIC(18,2) NULL,
	"codesc" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DESCRIP_PAGOS1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DESCRIP_PAGOS1" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DESCRIP_PAGOS2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DESCRIP_PAGOS2" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DetImpxFact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DetImpxFact" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Imp1" FLOAT(53) NULL,
	"Imp2" FLOAT(53) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DetImpxNC
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DetImpxNC" (
	"numnotcre" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Imp1" FLOAT(53) NULL,
	"Imp2" FLOAT(53) NULL,
	"TotImpuesto" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_DevCompra
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_DevCompra" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dfactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dfactura" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"subtotal" NUMERIC(38,4) NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dfactura1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dfactura1" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" NUMERIC(19,4) NULL,
	"tipo" VARCHAR(4) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dfactura2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dfactura2" (
	"numfactu" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dfactura_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dfactura_0215" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"procentaje" FLOAT(53) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dfactura_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dfactura_1" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto" FLOAT(53) NOT NULL,
	"subtotal" FLOAT(53) NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" FLOAT(53) NULL,
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"procentaje" FLOAT(53) NOT NULL,
	"Id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dfactura_NC1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dfactura_NC1" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"facturado" NUMERIC(18,0) NULL,
	"Acreditado" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" NUMERIC(19,2) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dimpuesto
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dimpuesto" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Imp1" FLOAT(53) NULL,
	"Imp2" FLOAT(53) NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"doc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dimpuestoMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dimpuestoMSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Imp1" FLOAT(53) NULL,
	"Imp2" FLOAT(53) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"doc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_displaypagos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_displaypagos" (
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" VARCHAR(8) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"total" MONEY(19,4) NULL,
	"usuario" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"metodo_pago" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"keypass" NVARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"disp" NVARCHAR(68) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dnotacre
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dnotacre" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"Impuesto" MONEY(19,4) NULL,
	"subtotal" NUMERIC(38,3) NULL,
	"numnotcre" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dnotacredito2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dnotacredito2" (
	"numnotcre" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precio" NUMERIC(18,2) NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"Acreditado" NUMERIC(18,0) NULL,
	"monto" NUMERIC(18,2) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" MONEY(19,4) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dobles1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dobles1" (
	"Expr1" INT NULL,
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_dobles2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_dobles2" (
	"Expr1" INT NULL,
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dpedido
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dpedido" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"canitdad" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numpedido" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dpostulados
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dpostulados" (
	"CodSuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Lapso" INT NOT NULL,
	"Mes" INT NOT NULL,
	"cod_IM" INT NOT NULL,
	"ds" INT NOT NULL,
	"Cuota" NUMERIC(18,0) NOT NULL,
	"xfecha" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Dpresupuestos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Dpresupuestos" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" DECIMAL(18,0) NOT NULL,
	"precunit" DECIMAL(18,2) NOT NULL,
	"numpre" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_estadisticas medicos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_estadisticas medicos" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"Cant" NUMERIC(38,0) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Estadisticas_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Estadisticas_0215" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(24,6) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"dTotal" NUMERIC(38,4) NULL,
	"unidades" NUMERIC(38,0) NULL,
	"formulas" NUMERIC(38,0) NULL,
	"unidadesT" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_facturasxcliente
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_facturasxcliente" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"total" MONEY(19,4) NULL,
	"tipo" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codseguro" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_FinalEsta0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_FinalEsta0215" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(24,6) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"dTotal" NUMERIC(38,4) NULL,
	"unidades" NUMERIC(38,0) NULL,
	"formulas" NUMERIC(38,0) NULL,
	"unidadesT" NUMERIC(38,0) NULL,
	"medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_GrupoConsultas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_GrupoConsultas" (
	"fechafac" DATETIME NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(38,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_GrupoConsultas_2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_GrupoConsultas_2" (
	"fechafac" DATETIME NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(38,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Impuestos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Impuestos" (
	"Impuesto" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Porcentaje" FLOAT(53) NULL,
	"Monto" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codigo" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ImpuestosxFact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ImpuestosxFact" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"pt" FLOAT(53) NULL,
	"p1" FLOAT(53) NOT NULL,
	"p2" FLOAT(53) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ImpuestosxFactMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ImpuestosxFactMSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"pt" FLOAT(53) NULL,
	"p1" FLOAT(53) NOT NULL,
	"p2" FLOAT(53) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ImpuestosxNC
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ImpuestosxNC" (
	"numnotcre" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"pt" FLOAT(53) NULL,
	"p1" FLOAT(53) NOT NULL,
	"p2" FLOAT(53) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ImpxFact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ImpxFact" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codimp" NUMERIC(18,0) NOT NULL,
	"montoimp" MONEY(19,4) NOT NULL,
	"porcentaje" FLOAT(53) NOT NULL,
	"base" MONEY(19,4) NOT NULL,
	"Impuesto" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Descripcion" NVARCHAR(83) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_inventario
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_inventario" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CANTIDAD" DECIMAL(18,0) NULL,
	"FECHA" DATETIME NULL,
	"HORA" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEw_inventarios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEw_inventarios" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODITEMS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CANTIDAD" DECIMAL(18,0) NULL,
	"FECHA" DATETIME NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_IR
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_IR" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"citacontrol" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" INT NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_it
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_it" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"fecha_cita" DATETIME NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"MONP_Bs" NUMERIC(38,4) NULL,
	"MONS_Bs" MONEY(19,4) NULL,
	"items" INT NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horamf" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horaCMA" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_kardex
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_kardex" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" NUMERIC(18,0) NULL,
	"fechacierre" DATETIME NOT NULL,
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ventas" NUMERIC(38,0) NULL,
	"DVentas" NUMERIC(38,0) NULL,
	"compra" NUMERIC(38,0) NULL,
	"devcompra" NUMERIC(38,0) NULL,
	"Ajustes_mas" NUMERIC(38,0) NULL,
	"Ajustes_menos" NUMERIC(38,0) NULL,
	"NE" NUMERIC(38,0) NULL,
	"Nc" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Label0815
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Label0815" (
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NACIMIENTO" DATETIME NULL,
	"sexo" DECIMAL(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_Laser_00
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_Laser_00" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_listadeprecios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_listadeprecios" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_LISTA_DE_PRECIOS_AS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_LISTA_DE_PRECIOS_AS" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_lugar
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_lugar" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"PAIS" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"State" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mcompra
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mcompra" (
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacomp" DATETIME NOT NULL,
	"Desprov" NVARCHAR(250) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"facclose" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_mcompra_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_mcompra_1" (
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"Desprov" NVARCHAR(250) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"codprov" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"OnStock" VARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacomp" DATETIME NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mconsultas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mconsultas" (
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CITADOS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CONFIRMADO" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ASISTIDOS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NO_ASISTIO" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citacontrol" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nocitados" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_mconsultas01
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_mconsultas01" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NOT NULL,
	"primera_control" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_mconsultas_02
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_mconsultas_02" (
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"hora" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(157) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CITADOS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CONFIRMADO" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ASISTIDOS" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NO_ASISTIO" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citacontrol" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"primera_control" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nocitados" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"exonerado" BIT NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconfirm" NUMERIC(18,0) NULL,
	"tipo" INT NOT NULL,
	"horain" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horaout" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"llegada" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"mls" INT NULL,
	"hilt" INT NULL,
	"terapias" INT NULL,
	"prioridad" INT NULL,
	"endtime" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"enddate" DATETIME NULL,
	"pacienteleft" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"answered" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_mconsultas_03
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_mconsultas_03" (
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"hora" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CITADOS" VARCHAR(6) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CONFIRMADO" VARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ASISTIDOS" VARCHAR(8) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NO_ASISTIO" VARCHAR(11) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nocitados" VARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citacontrol" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activa" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"primera_control" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconfirm" NUMERIC(18,0) NULL,
	"tipo" INT NOT NULL,
	"horain" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horaout" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id" INT NOT NULL,
	"llegada" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"mls" INT NULL,
	"hilt" INT NULL,
	"terapias" INT NULL,
	"prioridad" INT NULL,
	"endtime" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"enddate" DATETIME NULL,
	"pacienteleft" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"answered" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MCotizacion
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MCotizacion" (
	"numcot" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacot" DATETIME NULL,
	"cliente" NVARCHAR(511) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"transferida" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mcotizacion2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mcotizacion2" (
	"numcot" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacot" DATETIME NULL,
	"DiasPrescripcion" DECIMAL(18,0) NULL,
	"codseguro" NVARCHAR(4) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacit" DATETIME NULL,
	"Expr1" DECIMAL(18,0) NULL,
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MDevCompra
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MDevCompra" (
	"factcomp" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacomp" DATETIME NOT NULL,
	"Desprov" NVARCHAR(250) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"facclose" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Medicos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Medicos" (
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cedula" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Sucursal" VARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"meliminado" BIT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_medpacpercentstats
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_medpacpercentstats" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(15) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MenuPerfil
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MenuPerfil" (
	"desopcion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"acceso" BIT NULL,
	"codperfil" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nomopcion" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MenuPerfilSecundario
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MenuPerfilSecundario" (
	"dessubopcion" NVARCHAR(40) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"acceso" BIT NULL,
	"codperfil" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nomopcion" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nomsubopcion" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Meses
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Meses" (
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" NUMERIC(18,0) NULL,
	"totnue" NUMERIC(38,0) NULL,
	"totcon" NUMERIC(38,0) NULL,
	"totpacser" NUMERIC(38,0) NULL,
	"totgi" NUMERIC(38,2) NULL,
	"totpotes" NUMERIC(38,0) NULL,
	"totene" NUMERIC(38,0) NULL,
	"totpac" NUMERIC(38,0) NULL,
	"totpacconnueser" NUMERIC(38,0) NULL,
	"it" NUMERIC(38,6) NULL,
	"totsue" NUMERIC(38,0) NULL,
	"totele" NUMERIC(38,0) NULL,
	"totneural" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mfactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mfactura" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"iva" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"monto_abonado" MONEY(19,4) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"Alicuota" FLOAT(53) NULL,
	"monto_flete" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mfactura_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mfactura_1" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto1" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalpvp" MONEY(19,4) NULL,
	"Impuesto2" MONEY(19,4) NOT NULL,
	"Id" INT NOT NULL,
	"monto_flete" MONEY(19,4) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Minventario
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Minventario" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" NUMERIC(18,0) NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Minventario_Precios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Minventario_Precios" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"destipre" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"sugerido" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_mnotacre
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_mnotacre" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"alicuota" FLOAT(53) NULL,
	"Total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"Id" INT NOT NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mnotacredito
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mnotacredito" (
	"numnotcre" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NOT NULL,
	"descuento" NUMERIC(18,2) NOT NULL,
	"totalnot" NUMERIC(18,2) NOT NULL,
	"destatus" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MovInvent
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MovInvent" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ventas" NUMERIC(38,0) NULL,
	"compras" NUMERIC(38,0) NULL,
	"devcompra" NUMERIC(38,0) NULL,
	"devVentas" NUMERIC(38,0) NULL,
	"NE" NUMERIC(38,0) NULL,
	"NC" NUMERIC(38,0) NULL,
	"Ajustes_mas" NUMERIC(38,0) NULL,
	"Ajustes_neg" NUMERIC(38,0) NULL,
	"existencia" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MPagos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MPagos" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"desforpa" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DESBANCO" NVARCHAR(25) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MPagos1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MPagos1" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"desforpa" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DESBANCO" NVARCHAR(25) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codbanco" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"DesTipoTargeta" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mpagos2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mpagos2" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codbanco" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"monto_abonado" NUMERIC(18,2) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codestac" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desestac" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mpagos2CST
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mpagos2CST" (
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapago" DATETIME NULL,
	"codforpa" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codbanco" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipotargeta" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nro_forpa" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"monto_abonado" NUMERIC(18,2) NULL,
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ipaddress" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecreg" DATETIME NULL,
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"id_centro" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codestac" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desestac" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mpedido
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mpedido" (
	"numpedido" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_pedido" DATETIME NULL,
	"desproveedor" NVARCHAR(60) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fact_compra" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_recepcion" DATETIME NULL,
	"transferido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MPostulados
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MPostulados" (
	"CodSuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Lapso" INT NOT NULL,
	"Mes" INT NOT NULL,
	"cod_IM" INT NOT NULL,
	"Descripcion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cuota_en" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cuota" NUMERIC(18,0) NOT NULL,
	"abreviatura" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mprecios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mprecios" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Mpresupuestos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Mpresupuestos" (
	"numpresu" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechapre" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"destatus" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statpre" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"baseimponible" DECIMAL(18,2) NOT NULL,
	"tasa" DECIMAL(18,2) NOT NULL,
	"iva" DECIMAL(18,2) NOT NULL,
	"subtotal" DECIMAL(18,2) NOT NULL,
	"total" DECIMAL(18,2) NOT NULL,
	"fechavencimiento" DATETIME NULL,
	"facturado" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSemanas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSemanas" (
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsemana" NUMERIC(18,0) NOT NULL,
	"fec_I" DATETIME NOT NULL,
	"fec_F" DATETIME NOT NULL,
	"totnue" NUMERIC(38,0) NULL,
	"totcon" NUMERIC(38,0) NULL,
	"totpacser" NUMERIC(38,0) NULL,
	"totGI" NUMERIC(38,2) NULL,
	"totpac" NUMERIC(38,0) NULL,
	"totpotes" NUMERIC(38,0) NULL,
	"totene" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSDDev
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSDDev" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,2) NULL,
	"descuento" MONEY(19,4) NULL,
	"Impuesto" MONEY(19,4) NULL,
	"subtotal" NUMERIC(38,3) NULL,
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSDescFact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSDescFact" (
	"desdesct" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"base" MONEY(19,4) NULL,
	"porcentaje" NUMERIC(18,2) NULL,
	"codesc" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"horareg" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSDfact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSDfact" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto" FLOAT(53) NOT NULL,
	"subtotal" FLOAT(53) NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_MSSDFact_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_MSSDFact_1" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto" FLOAT(53) NOT NULL,
	"subtotal" FLOAT(53) NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" FLOAT(53) NULL,
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"procentaje" FLOAT(53) NOT NULL,
	"Id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSMDet
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSMDet" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" MONEY(19,4) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"destatus" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSMfact
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSMfact" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto1" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalpvp" MONEY(19,4) NULL,
	"Impuesto2" MONEY(19,4) NOT NULL,
	"tipo" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_MSSMfact_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_MSSMfact_1" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto1" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalpvp" MONEY(19,4) NULL,
	"Impuesto2" MONEY(19,4) NOT NULL,
	"tipo" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numnotcre" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Id" INT NOT NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_M_Cliente
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_M_Cliente" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"direccionH" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CliDesde" DATETIME NULL,
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"PAIS" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"State" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Ciudad" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medio" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_M_FACTURA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_M_FACTURA" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" INT NOT NULL,
	"TaxB" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_M_FACTURAMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_M_FACTURAMSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" INT NOT NULL,
	"TaxB" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_n
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_n" (
	"TABLE_CATALOG" NVARCHAR(128) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TABLE_SCHEMA" NVARCHAR(128) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TABLE_NAME" NVARCHAR(128) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"VIEW_DEFINITION" NVARCHAR(4000) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CHECK_OPTION" VARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"IS_UPDATABLE" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_name
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_name" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"presentacion" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NE3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NE3" (
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NEConsolidado
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NEConsolidado" (
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statunot" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" NUMERIC(38,2) NULL,
	"items" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NotasEntregas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NotasEntregas" (
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"destatus" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NotasEntregas1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NotasEntregas1" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"costo" NUMERIC(10,2) NULL,
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"statunot" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NotEntDetalle
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NotEntDetalle" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"fechanot" DATETIME NULL,
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Not_In_Mconsultas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Not_In_Mconsultas" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Not_In_Mconsultas2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Not_In_Mconsultas2" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_No_Citados
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_No_Citados" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"citados" NUMERIC(18,0) NOT NULL,
	"NoCitados" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"direccionH" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NueRepVen
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NueRepVen" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" NUMERIC(20,4) NULL,
	"descuento" NUMERIC(20,4) NOT NULL,
	"total" NUMERIC(20,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" INT NULL,
	"doc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TotImpuesto" MONEY(19,4) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_NueRepVen1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_NueRepVen1" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" INT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"doc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Pacientes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Pacientes" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"direccionH" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codpostal" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Pacientes1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Pacientes1" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"direccionH" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codpostal" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PacientesRepetidos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PacientesRepetidos" (
	"Cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_pacientes_sin_productos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_pacientes_sin_productos" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_pagosgroupmss
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_pagosgroupmss" (
	"fechapago" DATETIME NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" NUMERIC(18,2) NULL,
	"tipo_doc" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"modopago" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PATOLOGIAS POR EDAD
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PATOLOGIAS POR EDAD" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"NACIMIENTO" DATETIME NULL,
	"patologia" INT NULL,
	"DesPatologia" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"Masculino" NUMERIC(18,0) NULL,
	"Femenino" NUMERIC(18,0) NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Patologias500
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Patologias500" (
	"PATOLOGIA" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODIGO" INT NULL,
	"CODITEMS" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TRIANGULO" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PATOLOGIA_T20
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PATOLOGIA_T20" (
	"PATOLOGIA" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODIGO" INT NULL,
	"fecha" SMALLDATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"Factura" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_patologia_t20F
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_patologia_t20F" (
	"PATOLOGIA" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODIGO" INT NULL,
	"fecha" SMALLDATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"Factura" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Masculino" NUMERIC(18,0) NULL,
	"Femenino" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_patologia_t20M
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_patologia_t20M" (
	"PATOLOGIA" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODIGO" INT NULL,
	"fecha" SMALLDATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"Factura" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Masculino" NUMERIC(18,0) NULL,
	"Femenino" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_percentstats
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_percentstats" (
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PMPF_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PMPF_0215" (
	"fecha_cita" DATETIME NOT NULL,
	"cantUni" NUMERIC(38,0) NULL,
	"canFor" NUMERIC(38,0) NULL,
	"canPVistos" INT NULL,
	"UxP" NUMERIC(38,6) NULL,
	"FxP" NUMERIC(38,6) NULL,
	"FacXTMedico" NUMERIC(38,4) NULL,
	"promPacmed" NUMERIC(38,6) NULL,
	"porcentUniForm" NUMERIC(38,6) NULL,
	"NumRecords" INT NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"porcntUnitario" FLOAT(53) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PO
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PO" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Podtransf
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Podtransf" (
	"coditems" NVARCHAR(50) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"existencia" FLOAT(53) NULL,
	"codsucursal" INT NOT NULL,
	"blanco" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_postulado
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_postulado" (
	"CodSuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha" DATETIME NULL,
	"Cuota" NUMERIC(18,0) NOT NULL,
	"cod_IM" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_PRECIOS_VIEJOS_AUDITORIA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_PRECIOS_VIEJOS_AUDITORIA" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"precunit" MONEY(19,4) NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_presupuesto_1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_presupuesto_1" (
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto" FLOAT(53) NOT NULL,
	"subtotal" FLOAT(53) NULL,
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"costo" FLOAT(53) NULL,
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"dosis" INT NULL,
	"cant_sugerida" NUMERIC(18,0) NULL,
	"procentaje" FLOAT(53) NOT NULL,
	"Id" INT NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_presupuesto_list
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_presupuesto_list" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"Impuesto1" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"Status" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"totalpvp" MONEY(19,4) NULL,
	"Impuesto2" MONEY(19,4) NOT NULL,
	"Id" INT NOT NULL,
	"monto_flete" MONEY(19,4) NULL,
	"TotImpuesto" MONEY(19,4) NOT NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ProtocoloxFactura
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ProtocoloxFactura" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"patologia" INT NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODITEMS" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tratamiento" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ProtocoloxFactura2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ProtocoloxFactura2" (
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"patologia" INT NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CODITEMS" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tratamiento" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_RegControl
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_RegControl" (
	"CodSuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"UltimaFecha" DATETIME NULL,
	"suc" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_repconsultas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_repconsultas" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"noasistido" NUMERIC(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Id" INT NULL,
	"celular" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultas1
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultas1" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ProximaCita" DATETIME NOT NULL,
	"asistido" INT NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultas1W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultas1W7" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ProximaCita" DATETIME NOT NULL,
	"asistido" INT NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultas3
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultas3" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"noasistido" NUMERIC(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" INT NOT NULL,
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ProximaCita" DATETIME NULL,
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Id" INT NULL,
	"celular" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultas3W7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultas3W7" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"noasistido" NUMERIC(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"regusuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ProximaCita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultas4
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultas4" (
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"noasistido" NUMERIC(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"hora" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"UltimaAsistida" DATETIME NULL,
	"observacion" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultasb
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultasb" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"UltimaAsistida" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_repconsultasNCitas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_repconsultasNCitas" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NumeroCitas" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_repconsultasW7
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_repconsultasW7" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Medicos" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"citados" NUMERIC(10,0) NOT NULL,
	"confirmado" INT NOT NULL,
	"asistido" INT NOT NULL,
	"noasistido" NUMERIC(10,0) NOT NULL,
	"activa" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NOT NULL,
	"hora" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fallecido" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"regusuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Reporte
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Reporte" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"direccionH" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"celular" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"email" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_REPORTE_CLI_MED
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_REPORTE_CLI_MED" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"total" MONEY(19,4) NULL,
	"nombre" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Reposicion
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Reposicion" (
	"periodo" VARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad1" NUMERIC(38,0) NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_RETURN_CONSSUERO
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_RETURN_CONSSUERO" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,2) NULL,
	"ST" NUMERIC(37,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"DISCOUNT" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"concepto" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_RETURN_LASER_SPLIT
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_RETURN_LASER_SPLIT" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" NUMERIC(18,2) NULL,
	"ST" NUMERIC(37,2) NULL,
	"subtotal" MONEY(19,4) NULL,
	"DISCOUNT" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"concepto" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_RETURN_PRODUCTOS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_RETURN_PRODUCTOS" (
	"numnotcre" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ST" MONEY(19,4) NULL,
	"DISCOUNT" MONEY(19,4) NOT NULL,
	"statnc" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descuento" MONEY(19,4) NOT NULL,
	"totalnot" MONEY(19,4) NOT NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"numfactu" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"concepto" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Semanas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Semanas" (
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsemana" NUMERIC(18,0) NOT NULL,
	"fec_I" DATETIME NOT NULL,
	"fec_F" DATETIME NOT NULL,
	"totnue" NUMERIC(38,0) NULL,
	"totcon" NUMERIC(38,0) NULL,
	"totpacser" NUMERIC(38,0) NULL,
	"totGI" NUMERIC(38,2) NULL,
	"totpac" NUMERIC(38,0) NULL,
	"totpotes" NUMERIC(38,0) NULL,
	"totene" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Servicios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Servicios" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"aplicaiva" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicadcto" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacommed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicacomtec" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codtipre" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_sinrepetir
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_sinrepetir" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_SoloConsulta
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_SoloConsulta" (
	"codcons" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" VARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_SoloServicios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_SoloServicios" (
	"codcons" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(12) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Enl_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Enl_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_FacturacionxMedico
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_FacturacionxMedico" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Total" MONEY(19,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_FINAL_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_FINAL_0215" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"porcntUnitario" FLOAT(53) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numeroPacientes" INT NULL,
	"registros" INT NULL,
	"FACTURACIONXMEDICO" MONEY(19,4) NULL,
	"canFor" NUMERIC(38,0) NULL,
	"cantUni" NUMERIC(38,0) NULL,
	"formulaxpaciente" NUMERIC(38,6) NULL,
	"unidadxpacientes" NUMERIC(38,6) NULL,
	"facturacionxpaciente" NUMERIC(38,6) NULL,
	"FxM" NUMERIC(38,6) NULL,
	"TF" NUMERIC(38,6) NULL,
	"MFMP" NUMERIC(38,6) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Formulas_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Formulas_0215" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"fechafac" DATETIME NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(53) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_For_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_For_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"canFor" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_FTM_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_FTM_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"FacXTMedico" NUMERIC(38,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_MedFactProd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_MedFactProd" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"MonFacMedPod" NUMERIC(38,4) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_NunRecords_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_NunRecords_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"NumRecords" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_PacientesVistos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_PacientesVistos" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numeroPacientes" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Registros
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Registros" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"registros" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Rel01_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Rel01_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantUni" NUMERIC(38,0) NULL,
	"canFor" NUMERIC(38,0) NULL,
	"canPVistos" INT NULL,
	"UxP" NUMERIC(38,6) NULL,
	"FxP" NUMERIC(38,6) NULL,
	"FacXTMedico" NUMERIC(38,4) NULL,
	"promPacmed" NUMERIC(38,6) NULL,
	"porcentUniForm" NUMERIC(38,6) NULL,
	"NumRecords" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Unidades_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Unidades_0215" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"fechafac" DATETIME NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(53) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Stat_Uni_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Stat_Uni_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantUni" NUMERIC(38,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Sta_Vis_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Sta_Vis_0215" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"canPVistos" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_SubDpresupuesto
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_SubDpresupuesto" (
	"SudDesItems" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" DECIMAL(18,0) NOT NULL,
	"precunit" DECIMAL(18,2) NOT NULL,
	"numpre" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subcoditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_SxI0715
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_SxI0715" (
	"fechafac" DATETIME NULL,
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"telfhabit" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"procentaje" FLOAT(53) NOT NULL,
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TaxesInvoice" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" FLOAT(53) NULL,
	"TaxB" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice2
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TaxesInvoice2" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" FLOAT(53) NULL,
	"TaxB" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice2MSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TaxesInvoice2MSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" FLOAT(53) NULL,
	"TaxB" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TaxesInvoiceMSS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TaxesInvoiceMSS" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"TaxA" FLOAT(53) NULL,
	"TaxB" FLOAT(53) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_tipoconsulta
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_tipoconsulta" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" NVARCHAR(2) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"noasistido" NUMERIC(10,0) NOT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TotalProtocol
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TotalProtocol" (
	"numfactu" NVARCHAR(7) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Facturados" INT NULL,
	"Totales" INT NULL,
	"patologia" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TotPagos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TotPagos" (
	"fechapago" DATETIME NOT NULL,
	"modopago" CHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" MONEY(19,4) NOT NULL,
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Tipo" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_TotVentas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_TotVentas" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"neto" MONEY(19,4) NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"totalfac" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ultasistencia
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ultasistencia" (
	"codclien" NVARCHAR(5) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ultimacita" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_usuarios
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_usuarios" (
	"cedula" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Nombre" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"apellido" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"login" NVARCHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desperfil" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desestac" NVARCHAR(30) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_VentaProd_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_VentaProd_0215" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"tipoitems" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"porcntUnitario" FLOAT(53) NOT NULL,
	"descuento" MONEY(19,4) NOT NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"total" MONEY(19,4) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(102) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"ventas" NUMERIC(38,0) NULL,
	"lapsoFAC" INT NULL,
	"mesFAC" INT NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas01
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas01" (
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"iva" MONEY(19,4) NULL,
	"flete" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"fechafac" DATETIME NULL,
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas02
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas02" (
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"iva" MONEY(19,4) NULL,
	"flete" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"fechafac" DATETIME NULL,
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_VentasBS
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_VentasBS" (
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"iva" MONEY(19,4) NULL,
	"flete" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"fechafac" DATETIME NULL,
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipo" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_VentasDiarias_0215
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_VentasDiarias_0215" (
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"TotalFac" NUMERIC(25,7) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medio" NUMERIC(18,0) NULL,
	"medico" NVARCHAR(53) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_VentasDiarias_CMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_VentasDiarias_CMA" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(50) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_VentasDiarias_CMACST
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_VentasDiarias_CMACST" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"subtotal" MONEY(19,4) NULL,
	"descuento" MONEY(19,4) NULL,
	"total" MONEY(19,4) NULL,
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"usuario" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"tipopago" BIT NULL,
	"TotImpuesto" MONEY(19,4) NULL,
	"monto_flete" MONEY(19,4) NULL,
	"doc" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"workstation" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cancelado" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cod_subgrupo" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_ventasPotes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_ventasPotes" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechacierre" DATETIME NOT NULL,
	"ventas" NUMERIC(18,0) NULL,
	"NotasCreditos" NUMERIC(18,0) NULL,
	"NotasEntregas" NUMERIC(18,0) NOT NULL,
	"anulaciones" NUMERIC(18,0) NULL,
	"InvPosible" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"especial" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codsuc" VARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.View_Ventas_Diarias_W_Ret_Qty
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "View_Ventas_Diarias_W_Ret_Qty" (
	"qty" NUMERIC(38,0) NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL,
	"mes" INT NULL,
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_ventas_formulas_Selected
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_ventas_formulas_Selected" (
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_ventas_formulas_Selected_F
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_ventas_formulas_Selected_F" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"qty" NUMERIC(38,0) NOT NULL,
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_ventas_generales_12_17
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_ventas_generales_12_17" (
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"fechafac" DATETIME NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas_Medicos" (
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_Laser
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas_Medicos_Laser" (
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_Suero
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas_Medicos_Suero" (
	"nombremedico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_W_Ret
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Ventas_Medicos_W_Ret" (
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_" (
	"mes" INT NULL,
	"week" INT NULL,
	"year" INT NULL,
	"ch" VARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"precunit" MONEY(19,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"meliminado" BIT NOT NULL,
	"activo" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_CompLaser
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_CompLaser" (
	"amount" NUMERIC(38,4) NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL,
	"mes" INT NULL,
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_CompProd
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_CompProd" (
	"amount" NUMERIC(38,4) NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL,
	"mes" INT NULL,
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_CompProd_W_Ret
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_CompProd_W_Ret" (
	"amount" NUMERIC(38,4) NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL,
	"mes" INT NULL,
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_CompSuero
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_CompSuero" (
	"amount" NUMERIC(38,4) NULL,
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"year" INT NULL,
	"mes" INT NULL,
	"fechafac" DATETIME NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_Laser
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_Laser" (
	"mes" INT NULL,
	"week" INT NULL,
	"year" INT NULL,
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_Suero
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_Suero" (
	"mes" INT NULL,
	"week" INT NULL,
	"year" INT NULL,
	"nombremedico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VIEW_Week_Report_W_Ret
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VIEW_Week_Report_W_Ret" (
	"mes" INT NULL,
	"week" INT NULL,
	"year" INT NULL,
	"ch" VARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombremedico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"numfactu" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"statfact" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"aplicaComMed" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(20,0) NULL,
	"precunit" NUMERIC(20,4) NULL,
	"Descuento" MONEY(19,4) NULL,
	"id_centro" VARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"meliminado" BIT NOT NULL,
	"activo" NVARCHAR(1) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"periodo" VARCHAR(25) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.view_whathappend
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_whathappend" (
	"Historia" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fecha_cita" DATETIME NULL,
	"usuario" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observacion" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"asistido" NUMERIC(10,0) NULL,
	"codclien" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codconsulta" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"descons" NVARCHAR(80) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VPagosPR
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VPagosPR" (
	"fechapago" DATETIME NOT NULL,
	"modopago" CHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" MONEY(19,4) NOT NULL,
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VPagosPRCMA
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VPagosPRCMA" (
	"fechapago" DATETIME NOT NULL,
	"modopago" CHAR(20) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"monto" MONEY(19,4) NOT NULL,
	"codsuc" NVARCHAR(2) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VWANULAR
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VWANULAR" (
	"numfactu" NVARCHAR(7) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechafac" DATETIME NULL,
	"desanul" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VWCIERREINV
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VWCIERREINV" (
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Expr1" NUMERIC(18,0) NULL,
	"fechacierre" DATETIME NULL,
	"ventas" NUMERIC(18,0) NULL,
	"anulaciones" NUMERIC(18,0) NULL,
	"ajustes" NUMERIC(18,0) NULL,
	"InvPosible" NUMERIC(18,0) NULL,
	"InvActual" NUMERIC(18,0) NULL,
	"fallas" NUMERIC(18,0) NULL
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VWLISTADEPRECIO
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VWLISTADEPRECIO" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL,
	"Prod_serv" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"activo" NVARCHAR(1) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.VW_DPRICES
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "VW_DPRICES" (
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"precunit" MONEY(19,4) NULL,
	"ACTUALIZADO" MONEY(19,4) NULL,
	"PORCENT" MONEY(19,4) NULL,
	"FECHA" DATETIME NULL,
	"HORA" DATETIME NULL,
	"CONTROL" NUMERIC(18,0) NULL,
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.V_ControlVisitas
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "V_ControlVisitas" (
	"Visita" NUMERIC(18,0) NULL,
	"Fecha" DATETIME NULL,
	"NOMBRE" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"observaciones" TEXT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"CI" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.V_EVALUACION
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "V_EVALUACION" (
	"enfermedad" NVARCHAR(255) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Historia" NVARCHAR(12) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"Visita" NUMERIC(18,0) NULL,
	"cie" NVARCHAR(8) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"clascie" NVARCHAR(8) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(20) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for view farmacias.V_Medicos
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "V_Medicos" (
	"Codmedico" NVARCHAR(3) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"medico" NVARCHAR(101) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for table farmacias.Zipcodes
CREATE TABLE IF NOT EXISTS "Zipcodes" (
	"Zipcode" NVARCHAR(5) NULL DEFAULT NULL,
	"City" NVARCHAR(40) NULL DEFAULT NULL,
	"State" NVARCHAR(5) NULL DEFAULT NULL,
	"Areacode" NVARCHAR(5) NULL DEFAULT NULL,
	"COD" NUMERIC(18,0) NULL DEFAULT NULL,
	"PAIS" NUMERIC(18,0) NULL DEFAULT NULL,
	"CodCity" NUMERIC(18,0) NULL DEFAULT NULL,
	"Id" INT(10,0) NOT NULL
);

-- Data exporting was unselected.


-- Dumping structure for table farmacias.TDnotacredito
CREATE TABLE IF NOT EXISTS "TDnotacredito" (
	"NumNotCre" NVARCHAR(6) NULL DEFAULT NULL,
	"coditems" NVARCHAR(10) NULL DEFAULT NULL,
	"cantidad" NUMERIC(18,0) NULL DEFAULT NULL,
	"fecreg" SMALLDATETIME(0) NULL DEFAULT NULL,
	"horareg" NVARCHAR(15) NULL DEFAULT NULL,
	"idusuario_sis" NVARCHAR(20) NULL DEFAULT NULL
);

-- Data exporting was unselected.


-- Dumping structure for view farmacias.view_NE
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE "view_NE" (
	"numnotent" NVARCHAR(6) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"fechanot" DATETIME NULL,
	"codclien" NVARCHAR(5) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"coditems" NVARCHAR(10) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cantidad" NUMERIC(18,0) NULL,
	"desitems" NVARCHAR(255) NOT NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"cedula" NVARCHAR(15) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"nombres" NVARCHAR(100) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS',
	"codmedico" NVARCHAR(3) NULL COLLATE 'SQL_Latin1_General_CP1_CI_AS'
) ENGINE=MyISAM;


-- Dumping structure for trigger farmacias.testone
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_COD_SUBGROUP
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_COD_SUBGROUP_DEVCMA
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos_CMA_DEV
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos_FAR_DEV
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos_FAR_FAC
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos_MSS_DEV
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Add_Medicos_MSS_FAC
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.TRG_Patient_Arrived
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.UPDATE_IN_MCONSULTASS_REC_MED
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.UPDATE_IN_MCONSULTAS_REC_MED
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for trigger farmacias.UPDATE_IN_MCONSULTAS_REC_MED_FMCL
/* SQL Error (156): Incorrect syntax near the keyword 'FROM'. */

-- Dumping structure for view farmacias.AAATest
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "AAATest";
CREATE VIEW dbo.AAATest
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.cantidad, dbo.DFactura.coditems, dbo.MInventario.desitems, 
                      dbo.MFactura.fechanul
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.consolidated_view
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "consolidated_view";
CREATE VIEW dbo.consolidated_view
AS
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias_2
WHERE        statfact <> '2'
UNION
SELECT        L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         'Laser' cod_subgrupo
FROM            VentasDiariasMSSLA_3 L
WHERE        statfact <> '2'
GROUP BY L.numfactu, fechafac, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         'Intravenoso' cod_subgrupo
FROM            VentasDiariasMSSIV_4 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo
FROM            VentasDiariasCMACST_4_CONS
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo
FROM            VentasDiariasCMACST_4_INTRA
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo
FROM            VentasDiariasCMACST_4_LASER
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo
FROM            VentasDiariasCMACST_4_SUERO
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo
FROM            VentasDiariasCMACST_4_EXO
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, 
                         'CINTRON CONCULTA' AS cod_subgrupo
FROM            VentasDiariasCMACST_4_CONS_CINTRON
UNION
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, 
                         'BLOQUEO CON EXO' AS cod_subgrupo
FROM            VentasDiariasCMACST_4_BLOQUEO_REYES
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 5 AS tipo, Sum(I.general) AS general, 
                         'Bloqueo' cod_subgrupo
FROM            VentasDiariasMSSIV_5 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
;


-- Dumping structure for view farmacias.emt_exo_view
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "emt_exo_view";
create view emt_exo_view as
SELECT        numfactu, fechafac, a.codmedico,(cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, ((cantidad * precunit) - descuento) TotalFac, 0 monto_flete, doc, '01' codsuc,3 tventa 
,SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido medico, 'EXOSOMAS' Dventa

FROM            VentasDiariasCMACST_4_EXO a
inner join Mmedicos b on a.codmedico=b.Codmedico;


-- Dumping structure for view farmacias.emt_view
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "emt_view";
create view emt_view as
 SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, 'Productos' Dventa
 , SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            dbo.VentasDiarias_2 a
inner join Mmedicos b On a.codmedico=b.Codmedico 
WHERE        statfact <> '2'


UNION
SELECT        L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         'Laser' Dventa
						  , SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasMSSLA_3 L
inner join Mmedicos b On L.codmedico=b.Codmedico 
WHERE        statfact <> '2'
GROUP BY L.numfactu, fechafac, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado,SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido


UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         'Intravenoso' Dventa
						 , SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasMSSIV_4 I
inner join Mmedicos b On I.codmedico=b.Codmedico 
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado,SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido


UNION
 
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo Dventa
, SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasCMACST_4_INTRA a
inner join Mmedicos b On a.codmedico=b.Codmedico 
UNION


SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo Dventa
, SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasCMACST_4_LASER a
inner join Mmedicos b On a.codmedico=b.Codmedico 
UNION


SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, cod_subgrupo Dventa
, SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasCMACST_4_SUERO a
inner join Mmedicos b On a.codmedico=b.Codmedico 
UNION


SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, 'EXOSOMAS' Dventa
, SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasCMACST_4_EXO a
inner join Mmedicos b On a.codmedico=b.Codmedico 
UNION


 
SELECT        numfactu, fechafac, (cantidad * precunit) subtotal, descuento, ((cantidad * precunit) - descuento) total, statfact, 0 totimpuesto, 0 monto_flete, doc, 1 cancelado, tipo, ((cantidad * precunit) - descuento) general, 
                         'BLOQUEO CON EXO' AS Dventa
						 , SUBSTRING(b.nombre, 1, 1) + ' ' + b.apellido AS medico
FROM            VentasDiariasCMACST_4_BLOQUEO_REYES a
inner join Mmedicos b On a.codmedico=b.Codmedico 
UNION


SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 5 AS tipo, Sum(I.general) AS general, 
                         'Bloqueo' Dventa
						  , ;


-- Dumping structure for view farmacias.laser_sales_view
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "laser_sales_view";
CREATE  view laser_sales_view as
SELECT        L.numfactu, L.fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         'Laser' cod_subgrupo, max(m.usuario) usuario
FROM            VentasDiariasMSSLA_3 L
inner join MSSMFact m on L.numfactu=m.numfactu
WHERE        L.statfact <> '2'
GROUP BY L.numfactu, L.fechafac, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado



UNION


SELECT        I.numfactu, I.fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         'Intravenoso' cod_subgrupo, max(m.usuario) usuario
FROM            VentasDiariasMSSIV_4 I
inner join MSSMFact m on I.numfactu=m.numfactu
WHERE        I.statfact <> '2'
GROUP BY I.numfactu, I.fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado



union
 select numfactu, fechafac, (cantidad*precunit)  subtotal, descuento, ( (cantidad*precunit) -descuento )  total, statfact , 0 totimpuesto, 0 monto_flete, doc , 1 cancelado, tipo , ( (cantidad*precunit) -descuento )  general, cod_subgrupo,usuario from
 VentasDiariasCMACST_4_INTRA 


 union

 select numfactu, fechafac, (cantidad*precunit)  subtotal, descuento, ( (cantidad*precunit) -descuento )  total, statfact , 0 totimpuesto, 0 monto_flete, doc , 1 cancelado, tipo , ( (cantidad*precunit) -descuento )  general, cod_subgrupo,usuario from
 VentasDiariasCMACST_4_LASER ;


-- Dumping structure for view farmacias.laverdad
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "laverdad";
CREATE VIEW dbo.laverdad
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.MFactura.subtotal, dbo.MFactura.descuento, 
                      dbo.MFactura.total, dbo.MFactura.statfact, dbo.MFactura.usuario, dbo.MFactura.tipopago, dbo.MFactura.TotImpuesto, dbo.MFactura.monto_flete, 
                      dbo.mfactura.tipo AS doc, dbo.MFactura.workstation, dbo.MFactura.cancelado
FROM         dbo.MFactura
UNION ALL
SELECT     dbo.Mnotacredito.numnotcre AS numfactu, dbo.Mnotacredito.fechanot AS fechafac, dbo.Mnotacredito.codclien, dbo.Mnotacredito.codmedico, 
                      dbo.Mnotacredito.subtotal * - 1 AS subtotal, dbo.Mnotacredito.descuento * - 1 AS descuento, dbo.Mnotacredito.totalnot * - 1 AS total, 
                      dbo.Mnotacredito.statnc AS statfact, dbo.Mnotacredito.usuario, dbo.mnotacredito.tipopago, dbo.mnotacredito.totimpuesto * - 1 AS totimpuesto, 
                      dbo.mnotacredito.monto_flete, dbo.mnotacredito.tipo AS Doc, dbo.mnotacredito.workstation, dbo.mnotacredito.cancelado
FROM         dbo.Mnotacredito
;


-- Dumping structure for view farmacias.mssfaclaser
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "mssfaclaser";
CREATE view mssfaclaser as
SELECT     a.numfactu, MAX(a.fechafac) AS fechafac, SUM(b.cantidad * b.precunit) AS subtotal, SUM(b.descuento) AS descuento, SUM(b.cantidad * b.precunit - b.descuento) 
                      AS total, MAX(a.statfact) AS statfact, SUM(b.cantidad * b.precunit - b.descuento) AS TotalFac, c.cod_subgrupo, a.codmedico, SUM(b.cantidad * b.precunit - b.descuento) 
                      AS general, a.doc, a.codsuc,  CONCAT(SUBSTRING(d.nombre, 1, 1), ' ', d.apellido) AS medico
FROM         dbo.VentasDiariasMSS AS a LEFT OUTER JOIN
                      dbo.MSSDFact AS b ON a.numfactu = b.numfactu LEFT OUTER JOIN
                      dbo.MInventario AS c ON b.coditems = c.coditems LEFT OUTER JOIN
                      dbo.Mmedicos AS d ON d.Codmedico = a.codmedico
GROUP BY c.cod_subgrupo, a.numfactu, a.codmedico, a.doc, a.codsuc,  CONCAT(SUBSTRING(d.nombre, 1, 1), ' ', d.apellido) ;


-- Dumping structure for view farmacias.mssfaclaser_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "mssfaclaser_2";
create view mssfaclaser_2 as
SELECT        a.numfactu, MAX(a.fechafac) AS fechafac, SUM(b.cantidad * b.precunit) AS subtotal, SUM(b.descuento) AS descuento, SUM(b.cantidad * b.precunit - b.descuento) AS total, MAX(a.statfact) AS statfact, 
                         SUM(b.cantidad * b.precunit - b.descuento) AS TotalFac, c.cod_subgrupo, a.codmedico, SUM(b.cantidad * b.precunit - b.descuento) AS general, a.doc, a.codsuc,  CONCAT(SUBSTRING(a.mediconame, 1, 1), ' ', a.medico) 
                          AS medico
FROM            dbo.VentasDiariasMSS_2 AS a LEFT OUTER JOIN
                         dbo.MSSDFact AS b ON a.numfactu = b.numfactu LEFT OUTER JOIN
                         dbo.MInventario AS c ON b.coditems = c.coditems 
GROUP BY c.cod_subgrupo, a.numfactu, a.codmedico, a.doc, a.codsuc,  CONCAT(SUBSTRING(a.mediconame, 1, 1), ' ', a.medico) 
;


-- Dumping structure for view farmacias.newconsol
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsol";
CREATE VIEW dbo.newconsol
AS
SELECT        numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 1 AS tipo, 0 qtySold, general, codmedico, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias
UNION ALL
SELECT  distinct  a.numfactu
, b.nombres
, b.fechafac
, b.subtotal
, b.descuento
, b.total
, b.statfact
, b.tipopago
, b.TotImpuesto
, b.monto_flete
, b.doc
, b.workstation
, b.cancelado
, 2 AS tipo
, (
SELECT     sum(cantidad)
                            FROM          cma_DFactura
                            WHERE      cma_DFactura.numfactu = a.numfactu
) qtySold

, b.total general
, b.codmedico
,(
  Select max(g.cod_subgrupo) from VentasDiariasCMACST1 g  Where g.numfactu=a.numfactu group by g.numfactu

  ) as cod_subgrupo

FROM  VentasDiariasCMACST1 a
inner join VentasDiariasCMACST1 b On a.numfactu=b.numfactu
UNION ALL
SELECT        numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 3 AS tipo,
                             (SELECT        sum(cantidad)
                               FROM            MSSDFact
                               WHERE        MSSDFact.numfactu = dbo.VentasDiariasMSS.numfactu) qtySold, general, codmedico, 'Laser' cod_subgrupo
FROM            dbo.VentasDiariasMSS
;


-- Dumping structure for view farmacias.newconsol2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsol2";
CREATE VIEW dbo.newconsol2
AS
SELECT     numfactu, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, 0 qtySold, general, codmedico, 
                      'Productos' cod_subgrupo
FROM         dbo.VentasDiarias
UNION ALL
SELECT     numfactu, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, cancelado, 2 AS tipo,
                          (SELECT     sum(cantidad)
                            FROM          cma_DFactura
                            WHERE      cma_DFactura.numfactu = a.numfactu) qtySold, total general, codmedico, cod_subgrupo
FROM         VentasDiariasCMACST1 a
UNION ALL
SELECT     L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.tipopago, L.totimpuesto, L.monto_flete, 
                      L.doc, L.cancelado, 3 AS tipo, 0 qtySold, Sum(L.general) AS general, L.codmedico, 'Laser' cod_subgrupo
FROM         VentasDiariasMSSLA L
/*where L.numfactu='000067'*/ GROUP BY L.numfactu, fechafac, L.codclien, L.statfact, L.tipopago, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, L.codmedico
UNION ALL
SELECT     I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.tipopago, I.totimpuesto, I.monto_flete, I.doc, 
                      I.cancelado, 4 AS tipo, 0 qtySold, Sum(I.general) AS general, I.codmedico, 'Intravenoso' cod_subgrupo
FROM         VentasDiariasMSSIV I
/*where I.numfactu='000067'*/ GROUP BY I.numfactu, fechafac, I.codclien, I.statfact, I.tipopago, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, I.codmedico
;


-- Dumping structure for view farmacias.newconsol3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsol3";
CREATE VIEW dbo.newconsol3
AS
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, codmedico, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias
WHERE        statfact <> '2'
UNION
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, cancelado, 2 AS tipo, total general, codmedico, cod_subgrupo
FROM            VentasDiariasCMACST1 a
WHERE        statfact <> '2'
UNION
SELECT        L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.tipopago, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         L.codmedico, 'Laser' cod_subgrupo
FROM            VentasDiariasMSSLA L
WHERE        statfact <> '2'
GROUP BY L.numfactu, fechafac, L.codclien, L.statfact, L.tipopago, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, L.codmedico
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.tipopago, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         I.codmedico, 'Intravenoso' cod_subgrupo
FROM            VentasDiariasMSSIV I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.codclien, I.statfact, I.tipopago, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, I.codmedico
;


-- Dumping structure for view farmacias.newconsol3_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsol3_2";
CREATE VIEW dbo.newconsol3_2
AS
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias_2
WHERE        statfact <> '2'
UNION
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 2 AS tipo, total general, '' cod_subgrupo
FROM            VentasDiariasCMACST_4
/* VentasDiariasCMACST1_3  VentasDiariasCMACST_4  */ WHERE statfact <> '2'
UNION
SELECT        L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         'Laser' cod_subgrupo
FROM            VentasDiariasMSSLA_3 L
WHERE        statfact <> '2'
GROUP BY L.numfactu, fechafac, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         'Intravenoso' cod_subgrupo
FROM            VentasDiariasMSSIV_4 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 5 AS tipo, Sum(I.general) AS general, 
                         'Bloqueo' cod_subgrupo
FROM            VentasDiariasMSSIV_5 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
;


-- Dumping structure for view farmacias.newconsol3_2_w_cm
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsol3_2_w_cm";
CREATE VIEW newconsol3_2_w_cm as
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias_2
WHERE        statfact <> '2'
UNION
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 2 AS tipo, total general, '' cod_subgrupo
FROM            VentasDiariasCMACST_4_NOCM
WHERE statfact <> '2'
UNION

SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 6 AS tipo, total general, 'CELULAS MADRE' cod_subgrupo
FROM            VentasDiariasCMACST_4_CM
WHERE statfact <> '2'

UNION
SELECT        L.numfactu, fechafac, sum(L.subtotalnew) subtotal, sum(L.descuentonew) AS descuento, sum(L.totalnew) AS total, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado, 3 AS tipo, Sum(L.general) AS general, 
                         'Laser' cod_subgrupo
FROM            VentasDiariasMSSLA_3 L
WHERE        statfact <> '2'
GROUP BY L.numfactu, fechafac, L.statfact, L.totimpuesto, L.monto_flete, L.doc, L.cancelado
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 4 AS tipo, Sum(I.general) AS general, 
                         'Intravenoso' cod_subgrupo
FROM            VentasDiariasMSSIV_4 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
UNION
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 5 AS tipo, Sum(I.general) AS general, 
                         'Bloqueo' cod_subgrupo
FROM            VentasDiariasMSSIV_5 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado
;


-- Dumping structure for view farmacias.newconsolrepeated
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "newconsolrepeated";
 CREATE view  newconsolrepeated as Select nombres, count(*) reapeted, sum(general) general,codmedico, MAX(fechafac) AS lastday FROM [farmacias].[dbo].[newconsol]  where fechafac between '20170415' AND '20170415' and statfact<>'2' AND cod_subgrupo IN ('CONSULTA','Productos') group by codmedico,nombres;


-- Dumping structure for view farmacias.Pacientes_sin_factura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "Pacientes_sin_factura";
CREATE VIEW dbo.Pacientes_sin_factura AS SELECT TOP 100 PERCENT codclien, nombres, fecha_cita, telfhabit, Medico, cedula From dbo.VIEW_Mconsultas WHERE (activa = '1') AND (fecha_cita >= '20161220') AND (fecha_cita <= '20170120') AND (codclien NOT IN (SELECT dbo.MFactura.codclien From dbo.MFactura WHERE dbo.MFactura.statfact <> '2' AND mfactura.fechafac >= '20161215'  AND mfactura.fechafac <= '20170125' )) AND (ASISTIDOS = 'asistido');


-- Dumping structure for view farmacias.PRUEBA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "PRUEBA";

CREATE VIEW dbo.PRUEBA
AS
SELECT     TOP 100 PERCENT dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.coditems, dbo.DFactura.cantidad, 
                      dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.existencia
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.MInventario.Prod_serv = 'P') AND (dbo.MFactura.statfact <> '2')

;


-- Dumping structure for view farmacias.TEST011516
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "TEST011516";

CREATE VIEW dbo.TEST011516
AS
SELECT     dbo.MInventario.desitems, dbo.DCierreInventario.fechacierre, dbo.DCierreInventario.existencia, dbo.DCierreInventario.compras AS compra, 
                      dbo.DCierreInventario.DevCompras AS DevCompra, dbo.DCierreInventario.ventas, dbo.DCierreInventario.anulaciones, dbo.DCierreInventario.NotasEntregas AS NE, 
                      dbo.DCierreInventario.NotasCreditos AS nc, dbo.MInventario.activo, dbo.DCierreInventario.coditems, 
                      CASE WHEN dbo.DCierreInventario.ajustes > 0 THEN dbo.DCierreInventario.ajustes ELSE 0 END AS ajustesPos, 
                      CASE WHEN dbo.DCierreInventario.ajustes < 0 THEN dbo.DCierreInventario.ajustes ELSE 0 END AS ajustesNeg
FROM         dbo.MInventario INNER JOIN
                      dbo.DCierreInventario ON dbo.MInventario.coditems = dbo.DCierreInventario.coditems INNER JOIN
                      dbo.MCierreInventario ON dbo.DCierreInventario.fechacierre = dbo.MCierreInventario.fechacierre
WHERE     (dbo.MInventario.activo = '1')

;


-- Dumping structure for view farmacias.VaTEST
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VaTEST";
CREATE VIEW dbo.VaTEST
AS
SELECT     TOP 100 PERCENT codmedico, desitems, SUM(cantidad) AS Expr1
FROM         dbo.VIEW_Stat_Enl_0215 s
WHERE     (fechafac BETWEEN '20150226' AND '20150226') AND (cod_subgrupo = '1')
GROUP BY codmedico, desitems
ORDER BY Expr1 DESC
;


-- Dumping structure for view farmacias.VCOMPRASXFACTURACION
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VCOMPRASXFACTURACION";

CREATE VIEW dbo.VCOMPRASXFACTURACION
AS
SELECT     TOP 100 PERCENT dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.coditems, dbo.DFactura.cantidad, 
                      dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.existencia
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.MInventario.Prod_serv = 'P') AND (dbo.MFactura.statfact <> '2')

;


-- Dumping structure for view farmacias.VentasDiarias
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiarias";
CREATE VIEW dbo.VentasDiarias
AS
SELECT        dbo.MFactura.numfactu, dbo.MClientes.nombres, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.total, dbo.MFactura.statfact, 
                         dbo.MFactura.usuario, dbo.MFactura.tipopago, dbo.MFactura.TotImpuesto, 
                         TotalFac = CASE WHEN dbo.MFactura.TotImpuesto = 0 THEN dbo.MFactura.subtotal - dbo.MFactura.descuento ELSE dbo.MFactura.subtotal - dbo.MFactura.descuento + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) 
                         * 0.105, 2) + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) * 0.01, 2) END, dbo.MFactura.monto_flete, dbo.mfactura.tipo AS doc, dbo.MFactura.workstation, dbo.MFactura.cancelado, '01' AS codsuc, 
                         dbo.MClientes.medio, dbo.MFactura.total + dbo.MFactura.monto_flete general, 'na' ct, dbo.MClientes.Historia, l.initials
FROM            dbo.MFactura LEFT JOIN
                         dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien LEFT OUTER JOIN
                         loginpass l ON MFactura.usuario = l.login
UNION ALL
SELECT        dbo.Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.Mnotacredito.fechanot AS fechafac, dbo.Mnotacredito.codclien, dbo.Mnotacredito.codmedico, dbo.Mnotacredito.subtotal * - 1 AS subtotal, 
                         dbo.Mnotacredito.descuento * - 1 AS descuento, dbo.Mnotacredito.totalnot * - 1 AS total, dbo.Mnotacredito.statnc AS statfact, dbo.Mnotacredito.usuario, dbo.mnotacredito.tipopago, 
                         dbo.mnotacredito.totimpuesto * - 1 AS totimpuesto, totalcred = 0, dbo.mnotacredito.monto_flete * - 1 AS monto_flete, dbo.mnotacredito.tipo AS Doc, dbo.mnotacredito.workstation, dbo.mnotacredito.cancelado, '01' AS codsuc, 
                         dbo.MClientes.medio, (dbo.Mnotacredito.totalnot + dbo.mnotacredito.monto_flete) * - 1 general, ct, dbo.MClientes.Historia, l.initials
FROM            dbo.Mnotacredito LEFT JOIN
                         dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien LEFT OUTER JOIN
                         loginpass l ON Mnotacredito.usuario = l.login
;


-- Dumping structure for view farmacias.VentasDiariasCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMA";
CREATE VIEW dbo.VentasDiariasCMA
AS
SELECT        dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, 
                         dbo.cma_MFactura.workstation, dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, d .presentacion, d .cantidad, dbo.cma_MFactura.monto_flete +dbo.cma_MFactura.total general 
FROM            dbo.cma_MFactura LEFT OUTER JOIN
                         dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON dbo.cma_MFactura.numfactu = c.numfactu LEFT OUTER JOIN
                         dbo.viewVentasDiarasGrmSt AS d ON dbo.cma_MFactura.numfactu = d .numfactu
UNION ALL
SELECT        dbo.CMA_Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.CMA_Mnotacredito.fechanot AS fechafac, dbo.CMA_Mnotacredito.codclien, dbo.CMA_Mnotacredito.codmedico, 
                         dbo.CMA_Mnotacredito.subtotal * - 1 AS subtotal, dbo.CMA_Mnotacredito.descuento * - 1 AS descuento, dbo.CMA_Mnotacredito.totalnot * - 1 AS total, dbo.CMA_Mnotacredito.statnc AS statfact, 
                         dbo.CMA_Mnotacredito.usuario, dbo.CMA_Mnotacredito.tipopago, dbo.CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, dbo.CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, 
                         dbo.CMA_Mnotacredito.tipo AS Doc, dbo.CMA_Mnotacredito.workstation, dbo.CMA_Mnotacredito.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, '' presentacion, 0 cantidad, (dbo.CMA_Mnotacredito.totalnot+ dbo.CMA_Mnotacredito.monto_flete )*-1 general 
FROM            dbo.CMA_Mnotacredito LEFT JOIN
                         dbo.MClientes ON dbo.CMA_Mnotacredito.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON dbo.CMA_Mnotacredito.numfactu = c.numfactu
;


-- Dumping structure for view farmacias.VentasDiariasCMAa
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMAa";
/****** Script for SelectTopNRows command from SSMS  ******/
CREATE VIEW VentasDiariasCMAa AS
SELECT Distinct [numfactu]
      ,[nombres]
      ,[fechafac]
      ,[codclien]
      ,[codmedico]
      ,[subtotal]
      ,[descuento]
      ,[total]
      ,[statfact]
      ,[usuario]
      ,[tipopago]
      ,[TotImpuesto]
      ,[monto_flete]
      ,[doc]
      ,[workstation]
      ,[cancelado]
      ,[codsuc]
      ,[medio]
      ,max([cod_subgrupo])  cod_subgrupo
      ,[presentacion]
      ,[cantidad]
  FROM [farmacias].[dbo].[VentasDiariasCMA]
  [numfactu]
  group by  [numfactu]  ,[nombres]
      ,[fechafac]
      ,[codclien]
      ,[codmedico]
      ,[subtotal]
      ,[descuento]
      ,[total]
      ,[statfact]
      ,[usuario]
      ,[tipopago]
      ,[TotImpuesto]
      ,[monto_flete]
      ,[doc]
      ,[workstation]
      ,[cancelado]
      ,[codsuc]
      ,[medio]      
      ,[presentacion]
      ,[cantidad];


-- Dumping structure for view farmacias.VentasDiariasCMACELMADRESnoCons
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACELMADRESnoCons";
CREATE VIEW dbo.VentasDiariasCMACELMADRESnoCons
AS
SELECT        dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, 
                         dbo.cma_MFactura.workstation, dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, '' numnotcre, dbo.cma_MFactura.id, 
                         max(dbo.MClientes.Historia) Historia, max(l.initials) initials
FROM            dbo.cma_MFactura LEFT OUTER JOIN
                         dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON dbo.cma_MFactura.numfactu = c.numfactu INNER JOIN
                         view_cma__detalle_facturas d ON dbo.cma_MFactura.numfactu = d .numfactu LEFT OUTER JOIN
                         loginpass l ON dbo.cma_MFactura.usuario = l.login
WHERE        d .coditems not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) AND c.cod_subgrupo = 'CEL MADRE'
GROUP BY dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, 
                         dbo.cma_MFactura.workstation, dbo.cma_MFactura.cancelado, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, d .coditems, dbo.cma_MFactura.id
UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.codclien, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, 
                         CMA_Mnotacredito.descuento * - 1 AS descuento, CMA_Mnotacredito.totalnot * - 1 AS total, CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.usuario, CMA_Mnotacredito.tipopago, 
                         CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, CMA_Mnotacredito.workstation, CMA_Mnotacredito.cancelado, '01' AS codsuc, 
                         dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, max(dbo.MClientes.Historia) Historia, max(l.initials) initials
FROM            CMA_Mnotacredito LEFT JOIN
                         dbo.MClientes ON CMA_Mnotacredito.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON CMA_Mnotacredito.numfactu = c.numfactu INNER JOIN
                         view_cma__detalle_devolucion AS d ON CMA_Mnotacredito.numnotcre = d .numnotcre LEFT OUTER JOIN
                         loginpass l ON dbo.cma_Mnotacredito.usuario = l.login
WHERE        d .coditems not in ( 'CMKCINTRON','CMKCINT1CO','CMKCINTSEG' ) AND c.cod_subgrupo = 'CEL MADRE'
GROUP BY CMA_Mnotacredito.numnotcre, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codclien, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, 
                         CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc, CMA_Mnotacredito.usuario, CMA_Mnotacredito.tipopago, CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, 
                         CMA_Mnotacredito.workstation, CMA_Mnotacredito.cancelado, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, d .coditems, CMA_Mnotacredito.numnotcre, CMA_Mn;


-- Dumping structure for view farmacias.VentasDiariasCMACST
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST";
CREATE VIEW dbo.VentasDiariasCMACST
AS
SELECT     dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, 
                      dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, 
                      dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, dbo.cma_MFactura.workstation, 
                      dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, '' numnotcre, 
                      dbo.cma_MFactura.id, max(dbo.MClientes.Historia) Historia, max(l.initials) initials
FROM         dbo.cma_MFactura LEFT OUTER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.viewtipofacturascma AS c ON dbo.cma_MFactura.numfactu = c.numfactu INNER JOIN
                      view_cma__detalle_facturas d ON dbo.cma_MFactura.numfactu = d .numfactu
left outer join            loginpass l ON dbo.cma_MFactura.usuario=l.login
GROUP BY dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, 
                      dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, 
                      dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, dbo.cma_MFactura.workstation, 
                      dbo.cma_MFactura.cancelado, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, d .coditems, dbo.cma_MFactura.id
UNION ALL
SELECT     CMA_Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.codclien, 
                      CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, CMA_Mnotacredito.descuento * - 1 AS descuento, CMA_Mnotacredito.totalnot * - 1 AS total, 
                      CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.usuario, CMA_Mnotacredito.tipopago, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, 
                      CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, CMA_Mnotacredito.workstation, CMA_Mnotacredito.cancelado, '01' AS codsuc, 
                      dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, 
                      max(dbo.MClientes.Historia) Historia, max(l.initials) initials
FROM         CMA_Mnotacredito LEFT JOIN
                      dbo.MClientes ON CMA_Mnotacredito.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.viewtipofacturascma AS c ON CMA_Mnotacredito.numfactu = c.numfactu INNER JOIN
                      view_cma__detalle_devolucion AS d ON CMA_Mnotacredito.numnotcre = d .numnotcre
left outer join            loginpass l ON dbo.cma_Mnotacredito.usuario=l.login
GROUP BY CMA_Mnotacredito.numnotcre, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codclien, CMA_Mnotacredito.codmedico, 
                      CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc, CMA_Mnotacredito.usuario, 
                      CMA_Mnotacredito.tipopago, CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, CMA_Mnotacredito.workstation, 
                      CMA_Mnotacredito.cancelado, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, d .coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id
;


-- Dumping structure for view farmacias.VentasDiariasCMACST1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST1";
/* and a.fechafac ='20180202'*/
CREATE VIEW dbo.VentasDiariasCMACST1
AS
SELECT DISTINCT 
                         a.numfactu, c.nombres, c.fechafac, c.codclien, c.codmedico, c.subtotal, c.descuento, c.total, c.statfact, c.usuario, c.tipopago, c.TotImpuesto, c.monto_flete, c.doc, c.workstation, c.cancelado, c.codsuc, c.medio, c.cod_subgrupo, 
                         c.cantidad, c.tocantidad, c.numnotcre, c.id, c.Historia, c.initials
FROM            dbo.VentasDiariasCMACST AS a LEFT OUTER JOIN
                             (SELECT        numfactu, nombres, fechafac, codclien, codmedico, subtotal, descuento, total, statfact, usuario, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, codsuc, medio, cod_subgrupo, cantidad, tocantidad,
                                                          coditems, numnotcre, id, Historia, initials
                               FROM            dbo.VentasDiariasCMACST AS b) AS c ON a.numfactu = c.numfactu
WHERE        (1 = 1)
;


-- Dumping structure for view farmacias.VentasDiariasCMACST1_3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST1_3";
create view VentasDiariasCMACST1_3 as
						  SELECT DISTINCT 
                         a.numfactu, c.nombres, c.fechafac, c.codclien, c.codmedico, c.subtotal, c.descuento, c.total, c.statfact, c.usuario, c.tipopago, c.TotImpuesto, c.monto_flete, c.doc, c.workstation, c.cancelado, c.codsuc, c.medio, c.cod_subgrupo, 
                         c.cantidad, c.tocantidad, c.numnotcre, c.id, c.Historia, c.initials
FROM            dbo.VentasDiariasCMACST_3 AS a LEFT OUTER JOIN
                             (SELECT        numfactu, nombres, fechafac, codclien, codmedico, subtotal, descuento, total, statfact, usuario, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, codsuc, medio, cod_subgrupo, cantidad, tocantidad,
                                                          coditems, numnotcre, id, Historia, initials
                               FROM            dbo.VentasDiariasCMACST AS b) AS c ON a.numfactu = c.numfactu
WHERE        (1 = 1);


-- Dumping structure for view farmacias.VentasDiariasCMACSTRep
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACSTRep";
CREATE VIEW dbo.VentasDiariasCMACSTRep
AS
SELECT        dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, 
                         dbo.cma_MFactura.workstation, dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, dbo.cma_MFactura.total+ dbo.cma_MFactura.monto_flete general
FROM            dbo.cma_MFactura LEFT OUTER JOIN
                         dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON dbo.cma_MFactura.numfactu = c.numfactu
GROUP BY dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, 
                         dbo.cma_MFactura.workstation, dbo.cma_MFactura.cancelado, dbo.MClientes.medio, c.cod_subgrupo

union all



SELECT        dbo.CMA_Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.CMA_Mnotacredito.fechanot AS fechafac, dbo.CMA_Mnotacredito.codclien, dbo.CMA_Mnotacredito.codmedico, 
                         dbo.CMA_Mnotacredito.subtotal * - 1 AS subtotal, dbo.CMA_Mnotacredito.descuento * - 1 AS descuento, dbo.CMA_Mnotacredito.totalnot * - 1 AS total, dbo.CMA_Mnotacredito.statnc AS statfact, 
                         dbo.CMA_Mnotacredito.usuario, dbo.CMA_Mnotacredito.tipopago, dbo.CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, dbo.CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, 
                         dbo.CMA_Mnotacredito.tipo AS Doc, dbo.CMA_Mnotacredito.workstation, dbo.CMA_Mnotacredito.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo,
                         (dbo.CMA_Mnotacredito.totalnot + dbo.CMA_Mnotacredito.monto_flete) * - 1 general
FROM            dbo.CMA_Mnotacredito LEFT JOIN
                         dbo.MClientes ON dbo.CMA_Mnotacredito.codclien = dbo.MClientes.codclien INNER JOIN
                         dbo.viewtipofacturascma AS c ON dbo.CMA_Mnotacredito.numfactu = c.numfactu
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2";
CREATE VIEW dbo.VentasDiariasCMACST_2
AS
SELECT        dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, 
                         dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, dbo.cma_MFactura.cancelado, '01' AS codsuc, c.cod_subgrupo, c.coditems, '' numnotcre, dbo.cma_MFactura.id, 
                         dbo.cma_MFactura.mediconame, dbo.cma_MFactura.medico
FROM            dbo.cma_MFactura INNER JOIN
                         dbo.cma_DFactura AS c ON dbo.cma_MFactura.numfactu = c.numfactu
GROUP BY dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, 
                         dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, dbo.cma_MFactura.cancelado, c.cod_subgrupo, c.coditems, dbo.cma_MFactura.id, dbo.cma_MFactura.mediconame, 
                         dbo.cma_MFactura.medico
UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, CMA_Mnotacredito.descuento * - 1 AS descuento, 
                         CMA_Mnotacredito.totalnot * - 1 AS total, CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, 
                         CMA_Mnotacredito.cancelado, '01' AS codsuc, c.cod_subgrupo, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, dbo.CMA_Mnotacredito.mediconame, dbo.CMA_Mnotacredito.medico
FROM            CMA_Mnotacredito INNER JOIN
                         dbo.CMA_Dnotacredito AS c ON CMA_Mnotacredito.numfactu = c.numnotcre
GROUP BY CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc, 
                         CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, CMA_Mnotacredito.cancelado, c.cod_subgrupo, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, 
                         dbo.CMA_Mnotacredito.mediconame, dbo.CMA_Mnotacredito.medico
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMABLOQ
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2CEMABLOQ";
create view VentasDiariasCMACST_2CEMABLOQ as
SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 
              a.TotImpuesto, a.monto_flete, a.tipo AS doc, a.cancelado, '01' AS codsuc , 'BLOQUEO' as cod_subgrupo , '' coditems
               , '' numnotcre, a.id, a.mediconame, a.medico
FROM             cma_MFactura a 
 
WHERE  a.numfactu  in( Select b.numfactu  from cma_DFactura b where  b.cod_subgrupo = 'BLOQUEO' )
UNION ALL

SELECT        a.numnotcre AS numfactu
, a.fechanot AS fechafac
, a.codmedico
, a.subtotal * - 1 AS subtotal
, a.descuento * - 1 AS descuento
, a.totalnot * - 1 AS total
, a.statnc AS statfact
, a.totimpuesto * - 1 AS totimpuesto
, a.monto_flete * - 1 AS monto_flete
, a.tipo AS Doc
, a.cancelado
, '01' AS codsuc
, 'BLOQUEO' as cod_subgrupo
, '' coditems
, a.numnotcre
, a.id
, a.mediconame
, a.medico
FROM            CMA_Mnotacredito a

WHERE  a.numnotcre  in( Select b.numnotcre  from CMA_Dnotacredito b where  b.cod_subgrupo = 'BLOQUEO' )
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAINTRAVCIN
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2CEMAINTRAVCIN";
create view VentasDiariasCMACST_2CEMAINTRAVCIN as

SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, a.TotImpuesto, a.monto_flete, a.tipo AS doc, a.cancelado, '01' AS codsuc, 'INTRAVENOSO' AS cod_subgrupo, '' coditems, '' numnotcre, a.id, 
                         a.mediconame, a.medico
FROM            cma_MFactura a
WHERE        a.numfactu IN
                             (SELECT        b.numfactu
                               FROM            cma_DFactura b
                               WHERE        b.cod_subgrupo = 'INTRAVENOSO')
UNION ALL
SELECT        a.numnotcre AS numfactu, a.fechanot AS fechafac, a.codmedico, a.subtotal * - 1 AS subtotal, a.descuento * - 1 AS descuento, a.totalnot * - 1 AS total, a.statnc AS statfact, a.totimpuesto * - 1 AS totimpuesto, 
                         a.monto_flete * - 1 AS monto_flete, a.tipo AS Doc, a.cancelado, '01' AS codsuc, 'INTRAVENOSO' AS cod_subgrupo, '' coditems, a.numnotcre, a.id, a.mediconame, a.medico
FROM            CMA_Mnotacredito a
WHERE        a.numnotcre IN
                             (SELECT        b.numnotcre
                               FROM            CMA_Dnotacredito b
                               WHERE        b.cod_subgrupo = 'INTRAVENOSO')
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMALASERCIN
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2CEMALASERCIN";
create view VentasDiariasCMACST_2CEMALASERCIN as

SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, a.TotImpuesto, a.monto_flete, a.tipo AS doc, a.cancelado, '01' AS codsuc, 'TERAPIA LASER' AS cod_subgrupo, '' coditems, '' numnotcre, a.id, 
                         a.mediconame, a.medico
FROM            cma_MFactura a
WHERE        a.numfactu IN
                             (SELECT        b.numfactu
                               FROM            cma_DFactura b
                               WHERE        b.cod_subgrupo = 'TERAPIA LASER')
UNION ALL
SELECT        a.numnotcre AS numfactu, a.fechanot AS fechafac, a.codmedico, a.subtotal * - 1 AS subtotal, a.descuento * - 1 AS descuento, a.totalnot * - 1 AS total, a.statnc AS statfact, a.totimpuesto * - 1 AS totimpuesto, 
                         a.monto_flete * - 1 AS monto_flete, a.tipo AS Doc, a.cancelado, '01' AS codsuc, 'TERAPIA LASER' AS cod_subgrupo, '' coditems, a.numnotcre, a.id, a.mediconame, a.medico
FROM            CMA_Mnotacredito a
WHERE        a.numnotcre IN
                             (SELECT        b.numnotcre
                               FROM            CMA_Dnotacredito b
                               WHERE        b.cod_subgrupo = 'TERAPIA LASER')

;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAnoCons
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2CEMAnoCons";
CREATE VIEW dbo.VentasDiariasCMACST_2CEMAnoCons
AS
SELECT       a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 
                         a.TotImpuesto, a.monto_flete, a.tipo AS doc, a.cancelado, '01' AS codsuc, 'CEL MADRE' cod_subgrupo, '' coditems, '' numnotcre, a.id, 
                         a.mediconame, a.medico
FROM            dbo.cma_MFactura a
where numfactu in( SELECT b.numfactu FROM cma_DFactura b WHERE b.coditems NOT IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG') AND b.cod_subgrupo = 'CEL MADRE'  ) 
UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, CMA_Mnotacredito.descuento * - 1 AS descuento, 
                         CMA_Mnotacredito.totalnot * - 1 AS total, CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, 
                         CMA_Mnotacredito.cancelado, '01' AS codsuc, c.cod_subgrupo, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, dbo.CMA_Mnotacredito.mediconame, dbo.CMA_Mnotacredito.medico
FROM            CMA_Mnotacredito INNER JOIN
                         dbo.CMA_Dnotacredito AS c ON CMA_Mnotacredito.numfactu = c.numnotcre
WHERE        c.coditems NOT IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG') AND c.cod_subgrupo = 'CEL MADRE'
GROUP BY CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc, 
                         CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, CMA_Mnotacredito.cancelado, c.cod_subgrupo, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, 
                         dbo.CMA_Mnotacredito.mediconame, dbo.CMA_Mnotacredito.medico
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_2CEMAnoConsNew
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_2CEMAnoConsNew";
create view VentasDiariasCMACST_2CEMAnoConsNew as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 7 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, a. ts
						 , SUBSTRING(d.nombre, 1, 1) + ' ' + d.apellido medico
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems 
						 INNER JOIN   dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
						 INNER JOIN   Mmedicos d ON c.codmedico=d.Codmedico
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CEL MADRE') AND (coditems NOT IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG'))));


-- Dumping structure for view farmacias.VentasDiariasCMACST_3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_3";
create view VentasDiariasCMACST_3 as
SELECT        dbo.cma_MFactura.numfactu,  dbo.cma_MFactura.fechafac,  dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, 
                         dbo.cma_MFactura.cancelado, '01' AS codsuc,  c.cod_subgrupo, c.cantidad, SUM(c.cantidad) AS tocantidad,c.coditems, '' numnotcre, dbo.cma_MFactura.id
FROM            dbo.cma_MFactura
 LEFT OUTER JOIN               
                         dbo.cma_DFactura AS c ON dbo.cma_MFactura.numfactu = c.numfactu 
						 
						 

GROUP BY dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac,  dbo.cma_MFactura.codmedico, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, 
                         dbo.cma_MFactura.total, dbo.cma_MFactura.statfact,dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, 
                          dbo.cma_MFactura.cancelado,c.cod_subgrupo, c.cantidad, c.coditems, dbo.cma_MFactura.id
UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu,  CMA_Mnotacredito.fechanot AS fechafac,  CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, 
                         CMA_Mnotacredito.descuento * - 1 AS descuento, CMA_Mnotacredito.totalnot * - 1 AS total, CMA_Mnotacredito.statnc AS statfact,  CMA_Mnotacredito.tipopago, 
                         CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, CMA_Mnotacredito.cancelado, '01' AS codsuc, 
                         c.cod_subgrupo, c.cantidad, SUM(c.cantidad) AS tocantidad, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id
FROM            CMA_Mnotacredito 
INNER JOIN                          dbo.CMA_Dnotacredito AS c ON CMA_Mnotacredito.numfactu = c.numnotcre
 
 
GROUP BY CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.fechanot,  CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, 
                         CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc,  CMA_Mnotacredito.tipopago, CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, 
                          CMA_Mnotacredito.cancelado,  c.cod_subgrupo, c.cantidad, c.coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4";
Create view VentasDiariasCMACST_4 as
SELECT dbo.cma_MFactura.numfactu
, dbo.cma_MFactura.fechafac
, dbo.cma_MFactura.subtotal
, dbo.cma_MFactura.descuento
, dbo.cma_MFactura.total
, dbo.cma_MFactura.statfact
, dbo.cma_MFactura.tipopago
, dbo.cma_MFactura.TotImpuesto
, dbo.cma_MFactura.monto_flete
, dbo.cma_MFactura.tipo AS doc
, dbo.cma_MFactura.cancelado
, '01' AS codsuc
, dbo.cma_MFactura.id
FROM            dbo.cma_MFactura --WHERE fechafac=@fec
UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu
, CMA_Mnotacredito.fechanot AS fechafac
, CMA_Mnotacredito.subtotal * - 1 AS subtotal
, CMA_Mnotacredito.descuento * - 1 AS descuento
, CMA_Mnotacredito.totalnot * - 1 AS total
, CMA_Mnotacredito.statnc AS statfact
, CMA_Mnotacredito.tipopago
, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto
, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete
, CMA_Mnotacredito.tipo AS Doc, CMA_Mnotacredito.cancelado
, '01' AS codsuc
, CMA_Mnotacredito.id
FROM            CMA_Mnotacredito --WHERE fechanot=@fec
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_BLOQUEO_REYES
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_BLOQUEO_REYES";

CREATE view VentasDiariasCMACST_4_BLOQUEO_REYES as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 6 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'BLOQUEO') ))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 6 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'BLOQUEO') ));


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CM
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_CM";
CREATE VIEW dbo.VentasDiariasCMACST_4_CM
AS
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, tipo AS doc, cancelado, '01' AS codsuc, Id, usuario
FROM            dbo.cma_MFactura
WHERE        (numfactu IN
                             (SELECT        numfactu
                               FROM            dbo.cma_DFactura AS b
                               WHERE        (coditems IN
                                                             (SELECT        coditems
                                                               FROM            dbo.MInventario AS c
                                                               WHERE        (group_a = 'CELULAS MADRE')))
                               GROUP BY numfactu))
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CONS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_CONS";
CREATE view VentasDiariasCMACST_4_CONS as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 2 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CONSULTA') ))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 2 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CONSULTA') ));


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_CONS_CINTRON
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_CONS_CINTRON";

CREATE view VentasDiariasCMACST_4_CONS_CINTRON as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 8 tipo,   a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CEL MADRE' and group_a='CONSULTA') AND (coditems   IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG'))))
union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 8 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CEL MADRE' and group_a='CONSULTA') AND (coditems   IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG')) ))
					
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_EXO
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_EXO";
CREATE view VentasDiariasCMACST_4_EXO as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 7 tipo,   a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CEL MADRE') AND (coditems NOT IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG'))))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 7 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'CEL MADRE') AND (coditems NOT IN ('CMKCINTRON', 'CMKCINT1CO', 'CMKCINTSEG')) ))
					
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_INTRA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_INTRA";
CREATE view VentasDiariasCMACST_4_INTRA as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 4 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'INTRAVENOSO') ))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 4 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'INTRAVENOSO') ))
					
;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_LASER
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_LASER";

CREATE view VentasDiariasCMACST_4_LASER as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 3 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'TERAPIA LASER') ))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 3 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'TERAPIA LASER') ));


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_NOCM
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_NOCM";
 CREATE view VentasDiariasCMACST_4_NOCM as 
SELECT  dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.tipopago, 
        dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.cma_MFactura.id
FROM    dbo.cma_MFactura 
where   dbo.cma_MFactura.numfactu in(SELECT numfactu 
FROM cma_DFactura b 
WHERE  b.coditems  in (select c.coditems from MInventario c where c.group_a<>'CELULAS MADRE'   or  c.group_a is null  )
group by numfactu
 )
 UNION ALL
SELECT        CMA_Mnotacredito.numnotcre AS numfactu, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.subtotal * - 1 AS subtotal, CMA_Mnotacredito.descuento * - 1 AS descuento, CMA_Mnotacredito.totalnot * - 1 AS total, 
                         CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.tipopago, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, 
                         CMA_Mnotacredito.cancelado, '01' AS codsuc, CMA_Mnotacredito.id
FROM            CMA_Mnotacredito


;


-- Dumping structure for view farmacias.VentasDiariasCMACST_4_SUERO
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasCMACST_4_SUERO";
CREATE view VentasDiariasCMACST_4_SUERO as
SELECT        c.tipo AS doc, c.usuario, a.numfactu, a.fechafac, a.coditems, a.cantidad, a.precunit, a.tipoitems, a.procentaje, a.descuento, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                         a.codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 9 tipo, a.pvpitem, a.dosis, a.cant_sugerida, a.costo, a.monto_imp, a.codseguro, a.Id, a.percentage, a.cod_grupo, b.cod_subgrupo, a.statfact, 
                         a.ts
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.cma_MFactura AS c ON a.numfactu = c.numfactu AND c.statfact = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'SUEROTERAPIA') ))

union all
SELECT        c.tipo AS doc, c.usuario, a.numnotcre, a.fechanot, a.coditems, a.cantidad*-1 as cantidad, a.precunit, a.tipoitems, 0 procentaje, a.descuento*-1, a.codtipre, a.usuario AS usuari, a.workstation, a.ipaddress, a.fecreg, a.horareg, c.codmedico, 
                        '' codtecnico, a.aplicaiva, a.aplicadcto, a.aplicacommed, a.aplicacomtec, 9 tipo,  0 pvpitem, '' dosis,0 cant_sugerida, a.costo, a.monto_imp, c.codseguro, a.Id, 0 percentage, a.cod_grupo, b.cod_subgrupo, c.statnc, 
                         a.ts
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                         dbo.CMA_Mnotacredito AS c ON a.numnotcre = c.numnotcre AND c.statnc = 3
WHERE        (a.coditems IN
                             (SELECT        coditems
                               FROM            dbo.MInventario AS c
                               WHERE        (cod_subgrupo = 'SUEROTERAPIA') ))
;


-- Dumping structure for view farmacias.VentasDiariasMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSS";
CREATE VIEW dbo.VentasDiariasMSS
AS
SELECT     dbo.MSSMFact.numfactu, dbo.MClientes.nombres, dbo.MSSMFact.fechafac, dbo.MSSMFact.codclien, dbo.MSSMFact.codmedico, dbo.MSSMFact.subtotal, 
                      dbo.MSSMFact.descuento, dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.usuario, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, 
                      CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento ELSE dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento + round((dbo.MSSMFact.subtotal
                       - dbo.MSSMFact.descuento) * 0.105, 2) + round((dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, 
                      dbo.MSSMFact.tipo AS doc, dbo.MSSMFact.workstation, dbo.MSSMFact.cancelado, '01' AS codsuc, dbo.MClientes.medio, 
                      dbo.MSSMFact.total + dbo.MSSMFact.monto_flete general, dbo.MClientes.Historia,l.initials
FROM         dbo.MSSMFact LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MSSMFact.codclien = dbo.MClientes.codclien
                   LEFT OUTER JOIN loginpass l ON MSSMFact.usuario=l.login
WHERE     (dbo.MSSMFact.tipo = N'01')
UNION ALL
SELECT     dbo.MSSMDev.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codclien, dbo.MSSMDev.codmedico, 
                      dbo.MSSMDev.subtotal * - 1 AS subtotal, dbo.MSSMDev.descuento * - 1 AS descuento, dbo.MSSMDev.totalnot * - 1 AS total, dbo.MSSMDev.statnc AS statfact, 
                      dbo.MSSMDev.usuario, dbo.MSSMDev.tipopago, dbo.MSSMDev.totimpuesto * - 1 AS totimpuesto, totalcred = 0, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, 
                      dbo.MSSMDev.tipo AS Doc, dbo.MSSMDev.workstation, dbo.MSSMDev.cancelado, '01' AS codsuc, dbo.MClientes.medio, (dbo.MSSMDev.totalnot) * - 1 general, 
                      dbo.MClientes.Historia,l.initials
FROM         dbo.MSSMDev LEFT JOIN
                      dbo.MClientes ON dbo.MSSMDev.codclien = dbo.MClientes.codclien
                     LEFT OUTER JOIN loginpass l ON MSSMDev.usuario=l.login
;


-- Dumping structure for view farmacias.VentasDiariasMSSIV
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSIV";
CREATE VIEW dbo.VentasDiariasMSSIV
AS
SELECT        dbo.MSSMFact.numfactu, dbo.MClientes.nombres, dbo.MSSMFact.fechafac, dbo.MSSMFact.codclien, dbo.MSSMFact.codmedico, (d .cantidad * d .precunit) subtotalnew, dbo.MSSMFact.subtotal, d .descuento descuentonew, 
                         dbo.MSSMFact.descuento, (d .cantidad * d .precunit) - D .descuento totalnew, dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.usuario, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, 
                         CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) 
                         + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc, dbo.MSSMFact.workstation, dbo.MSSMFact.cancelado, '01' AS codsuc, dbo.MClientes.medio, 
                         ((d .cantidad * d .precunit) - D .descuento) + dbo.MSSMFact.monto_flete general, D .coditems, I.cod_subgrupo
FROM            dbo.MSSMFact INNER JOIN
                         MSSDFact D ON dbo.MSSMFact.numfactu = D .numfactu INNER JOIN
                         MInventario I ON D .coditems = I.coditems LEFT OUTER JOIN
                         dbo.MClientes ON dbo.MSSMFact.codclien = dbo.MClientes.codclien
WHERE        (dbo.MSSMFact.tipo = N'01') AND cod_subgrupo = 'INTRAVENOSO'
/*and dbo.MSSMFact.fechafac='20180301'*/ UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codclien, dbo.MSSMDev.codmedico, (d .cantidad * d .precunit) * - 1 subtotalnew, 
                         dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, 
                         dbo.MSSMDev.statnc AS statfact, dbo.MSSMDev.usuario, dbo.MSSMDev.tipopago, dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, 
                         dbo.MSSMDev.workstation, dbo.MSSMDev.cancelado, '01' AS codsuc, dbo.MClientes.medio, ((d .cantidad * d .precunit) - D .descuento) * - 1 generalnew, D .coditems, I.cod_subgrupo
FROM            dbo.MSSMDev INNER JOIN
                         MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre INNER JOIN
                         MInventario I ON D .coditems = I.coditems LEFT JOIN
                         dbo.MClientes ON dbo.MSSMDev.codclien = dbo.MClientes.codclien
WHERE        cod_subgrupo = 'INTRAVENOSO'
;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSIV_3";
create view VentasDiariasMSSIV_3 as
SELECT        dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, dbo.MSSMFact.codclien, dbo.MSSMFact.codmedico, (d .cantidad * d .precunit) subtotalnew, dbo.MSSMFact.subtotal, d .descuento descuentonew, 
                         dbo.MSSMFact.descuento, (d .cantidad * d .precunit) - D .descuento totalnew, dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, 
                         CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) 
                         + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc,   dbo.MSSMFact.cancelado, '01' AS codsuc, 
                         ((d .cantidad * d .precunit) - D .descuento) + dbo.MSSMFact.monto_flete general, D .coditems, D.cod_subgrupo
FROM            dbo.MSSMFact 
INNER JOIN  MSSDFact D ON dbo.MSSMFact.numfactu = D .numfactu 


WHERE        (dbo.MSSMFact.tipo = N'01') AND D.cod_subgrupo = 'INTRAVENOSO'
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu,  dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codclien, dbo.MSSMDev.codmedico, (d .cantidad * d .precunit) * - 1 subtotalnew, 
                         dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, 
                         dbo.MSSMDev.statnc AS statfact,  dbo.MSSMDev.tipopago, dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, 
                           dbo.MSSMDev.cancelado, '01' AS codsuc,  ((d .cantidad * d .precunit) - D .descuento) * - 1 generalnew, D .coditems, D.cod_subgrupo
FROM            dbo.MSSMDev 
INNER JOIN MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre 


WHERE        d.cod_subgrupo = 'INTRAVENOSO'








 







;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSIV_4";
CREATE VIEW dbo.VentasDiariasMSSIV_4
AS
SELECT        dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, (d .cantidad * d .precunit) subtotalnew, dbo.MSSMFact.subtotal, d .descuento descuentonew, dbo.MSSMFact.descuento, (d .cantidad * d .precunit) - D .descuento totalnew, 
                         dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) 
                         - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc, 
                         dbo.MSSMFact.cancelado, '01' AS codsuc, ((d .cantidad * d .precunit) - D .descuento) + dbo.MSSMFact.monto_flete general, D .coditems, I.cod_subgrupo,   dbo.MSSMFact.codmedico
FROM            dbo.MSSMFact INNER JOIN
                         MSSDFact D ON dbo.MSSMFact.numfactu = D .numfactu INNER JOIN
                         MInventario I ON D .coditems = I.coditems
WHERE        (dbo.MSSMFact.tipo = N'01') AND cod_subgrupo = 'INTRAVENOSO'
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MSSMDev.fechanot AS fechafac, (d .cantidad * d .precunit) * - 1 subtotalnew, dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, 
                         dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, dbo.MSSMDev.statnc AS statfact, dbo.MSSMDev.tipopago, 
                         dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, dbo.MSSMDev.cancelado, '01' AS codsuc, ((d .cantidad * d .precunit) - D .descuento) 
                         * - 1 generalnew, D .coditems, I.cod_subgrupo,dbo.MSSMDev .codmedico
FROM            dbo.MSSMDev INNER JOIN
                         MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre INNER JOIN
                         MInventario I ON D .coditems = I.coditems
WHERE        cod_subgrupo = 'INTRAVENOSO'
;


-- Dumping structure for view farmacias.VentasDiariasMSSIV_5
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSIV_5";
CREATE VIEW dbo.VentasDiariasMSSIV_5
AS
SELECT        dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, (d .cantidad * d .precunit) subtotalnew, dbo.MSSMFact.subtotal, d .descuento descuentonew, dbo.MSSMFact.descuento, (d .cantidad * d .precunit) - D .descuento totalnew, 
                         dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) 
                         - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc, 
                         dbo.MSSMFact.cancelado, '01' AS codsuc, ((d .cantidad * d .precunit) - D .descuento) + dbo.MSSMFact.monto_flete general, D .coditems, I.cod_subgrupo, dbo.MSSMFact.codmedico
FROM            dbo.MSSMFact INNER JOIN
                         MSSDFact D ON dbo.MSSMFact.numfactu = D .numfactu INNER JOIN
                         MInventario I ON D .coditems = I.coditems
WHERE        (dbo.MSSMFact.tipo = N'01') AND cod_subgrupo = 'BLOQUEO'
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MSSMDev.fechanot AS fechafac, (d .cantidad * d .precunit) * - 1 subtotalnew, dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, 
                         dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, dbo.MSSMDev.statnc AS statfact, dbo.MSSMDev.tipopago, 
                         dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, dbo.MSSMDev.cancelado, '01' AS codsuc, ((d .cantidad * d .precunit) - D .descuento) 
                         * - 1 generalnew, D .coditems, I.cod_subgrupo, dbo.MSSMDev.codmedico
FROM            dbo.MSSMDev INNER JOIN
                         MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre INNER JOIN
                         MInventario I ON D .coditems = I.coditems
WHERE        cod_subgrupo = 'BLOQUEO'
;


-- Dumping structure for view farmacias.VentasDiariasMSSLA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSLA";
CREATE VIEW dbo.VentasDiariasMSSLA
AS
SELECT        dbo.MSSMFact.numfactu, dbo.MClientes.nombres, dbo.MSSMFact.fechafac, dbo.MSSMFact.codclien, dbo.MSSMFact.codmedico, D .cantidad * D .precunit AS subtotalnew, dbo.MSSMFact.subtotal, D .descuento AS descuentonew,
                          dbo.MSSMFact.descuento, D .cantidad * D .precunit - D .descuento AS totalnew, dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.usuario, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, 
                         CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) 
                         + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc, dbo.MSSMFact.workstation, dbo.MSSMFact.cancelado, '01' AS codsuc, dbo.MClientes.medio, 
                         D .cantidad * D .precunit - D .descuento + dbo.MSSMFact.monto_flete AS general, D .coditems, I.cod_subgrupo
FROM            dbo.MSSMFact LEFT OUTER JOIN
                         dbo.MSSDFact AS D ON dbo.MSSMFact.numfactu = D .numfactu LEFT OUTER JOIN
                         dbo.MInventario AS I ON D .coditems = I.coditems LEFT OUTER JOIN
                         dbo.MClientes ON dbo.MSSMFact.codclien = dbo.MClientes.codclien
WHERE        (dbo.MSSMFact.tipo = N'01') AND ((I.cod_subgrupo <> 'INTRAVENOSO') OR
                         (I.cod_subgrupo IS NULL))
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codclien, dbo.MSSMDev.codmedico, (d .cantidad * d .precunit) * - 1 subtotalnew, 
                         dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, 
                         dbo.MSSMDev.statnc AS statfact, dbo.MSSMDev.usuario, dbo.MSSMDev.tipopago, dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, 
                         dbo.MSSMDev.workstation, dbo.MSSMDev.cancelado, '01' AS codsuc, dbo.MClientes.medio, ((d .cantidad * d .precunit) - D .descuento) * - 1 generalnew, D .coditems, I.cod_subgrupo
FROM            dbo.MSSMDev INNER JOIN
                         MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre INNER JOIN
                         MInventario I ON D .coditems = I.coditems LEFT JOIN
                         dbo.MClientes ON dbo.MSSMDev.codclien = dbo.MClientes.codclien
WHERE        ((I.cod_subgrupo <> 'INTRAVENOSO') OR
                         (I.cod_subgrupo IS NULL))
;


-- Dumping structure for view farmacias.VentasDiariasMSSLA_3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSSLA_3";
CREATE VIEW dbo.VentasDiariasMSSLA_3
AS
SELECT        dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, dbo.MSSMFact.codmedico, D .cantidad * D .precunit AS subtotalnew, dbo.MSSMFact.subtotal, D .descuento AS descuentonew, dbo.MSSMFact.descuento, 
                         D .cantidad * D .precunit - D .descuento AS totalnew, dbo.MSSMFact.total, dbo.MSSMFact.statfact, dbo.MSSMFact.tipopago, dbo.MSSMFact.TotImpuesto, 
                         CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN (d .cantidad * d .precunit) - D .descuento ELSE (d .cantidad * d .precunit) - D .descuento + round(((d .cantidad * d .precunit) - D .descuento) * 0.105, 2) 
                         + round(((d .cantidad * d .precunit) - D .descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc, dbo.MSSMFact.cancelado, '01' AS codsuc, 
                         D .cantidad * D .precunit - D .descuento + dbo.MSSMFact.monto_flete AS general, D .coditems, I.cod_subgrupo, dbo.MSSMFact.usuario
FROM            dbo.MSSMFact LEFT OUTER JOIN
                         dbo.MSSDFact AS D ON dbo.MSSMFact.numfactu = D .numfactu LEFT OUTER JOIN
                         dbo.MInventario AS I ON D .coditems = I.coditems
WHERE        (dbo.MSSMFact.tipo = N'01') AND ((I.cod_subgrupo <> 'INTRAVENOSO') OR
                         (I.cod_subgrupo IS NULL))
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codmedico, (d .cantidad * d .precunit) * - 1 subtotalnew, dbo.MSSMDev.subtotal * - 1 AS subtotal, d .descuento * - 1 descuentonew, 
                         dbo.MSSMDev.descuento * - 1 AS descuento, ((d .cantidad * d .precunit) - D .descuento) * - 1 totalnew, dbo.MSSMDev.totalnot * - 1 AS total, dbo.MSSMDev.statnc AS statfact, dbo.MSSMDev.tipopago, 
                         dbo.MSSMDev.totimpuesto * - 1 AS TotImpuesto, 0 TotalFac, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, dbo.MSSMDev.cancelado, '01' AS codsuc, ((d .cantidad * d .precunit) - D .descuento) 
                         * - 1 generalnew, D .coditems, I.cod_subgrupo,dbo.MSSMDev.usuario
FROM            dbo.MSSMDev INNER JOIN
                         MSSDDev D ON dbo.MSSMDev.numnotcre = D .numnotcre INNER JOIN
                         MInventario I ON D .coditems = I.coditems
WHERE        ((I.cod_subgrupo <> 'INTRAVENOSO') OR
                         (I.cod_subgrupo IS NULL))
;


-- Dumping structure for view farmacias.VentasDiariasMSS_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasMSS_2";
CREATE VIEW dbo.VentasDiariasMSS_2
AS
SELECT        dbo.MSSMFact.numfactu,  dbo.MSSMFact.fechafac, dbo.MSSMFact.codclien, dbo.MSSMFact.codmedico, dbo.MSSMFact.subtotal, dbo.MSSMFact.descuento, dbo.MSSMFact.total, dbo.MSSMFact.statfact, 
                        dbo.MSSMFact.TotImpuesto, 
                         CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento ELSE dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento + round((dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento) 
                         * 0.105, 2) + round((dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento) * 0.01, 2) END AS TotalFac, dbo.MSSMFact.monto_flete, dbo.MSSMFact.tipo AS doc,  dbo.MSSMFact.cancelado, '01' AS codsuc, 
                         dbo.MSSMFact.total + dbo.MSSMFact.monto_flete general,  dbo.MSSMFact.mediconame, dbo.MSSMFact.medico
FROM            dbo.MSSMFact 
WHERE        (dbo.MSSMFact.tipo = N'01')
UNION ALL
SELECT        dbo.MSSMDev.numnotcre AS numfactu, dbo.MSSMDev.fechanot AS fechafac, dbo.MSSMDev.codclien, dbo.MSSMDev.codmedico, dbo.MSSMDev.subtotal * - 1 AS subtotal, 
                         dbo.MSSMDev.descuento * - 1 AS descuento, dbo.MSSMDev.totalnot * - 1 AS total, dbo.MSSMDev.statnc AS statfact,   dbo.MSSMDev.totimpuesto * - 1 AS totimpuesto, 
                         totalcred = 0, dbo.MSSMDev.monto_flete * - 1 AS monto_flete, dbo.MSSMDev.tipo AS Doc, dbo.MSSMDev.cancelado, '01' AS codsuc, (dbo.MSSMDev.totalnot) * - 1 general, 
                          dbo.MSSMDev.mediconame, dbo.MSSMDev.medico
FROM            dbo.MSSMDev 
;


-- Dumping structure for view farmacias.VentasDiariasn
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiariasn";

CREATE VIEW [dbo].[VentasDiariasn]
AS
SELECT     dbo.MFactura.numfactu, dbo.MClientes.nombres, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.MFactura.subtotal, 
                      dbo.MFactura.descuento, dbo.MFactura.total, dbo.MFactura.statfact, dbo.MFactura.usuario, dbo.MFactura.tipopago, dbo.MFactura.TotImpuesto, 
                      TotalFac = CASE WHEN dbo.MFactura.TotImpuesto = 0 THEN dbo.MFactura.subtotal - dbo.MFactura.descuento ELSE dbo.MFactura.subtotal - dbo.MFactura.descuento
                       + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) * 0.105, 2) + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) * 0.01, 2) END, 
                      dbo.MFactura.monto_flete, dbo.mfactura.tipo AS doc, dbo.MFactura.workstation, dbo.MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio
FROM         dbo.MFactura LEFT JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien
UNION ALL
SELECT     dbo.Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.Mnotacredito.fechanot AS fechafac, dbo.Mnotacredito.codclien, 
                      dbo.Mnotacredito.codmedico, dbo.Mnotacredito.subtotal  AS subtotal, dbo.Mnotacredito.descuento AS descuento, 
                      dbo.Mnotacredito.totalnot  AS total, dbo.Mnotacredito.statnc AS statfact, dbo.Mnotacredito.usuario, dbo.mnotacredito.tipopago, 
                      dbo.mnotacredito.totimpuesto  AS totimpuesto, totalcred = 0, dbo.mnotacredito.monto_flete  AS monto_flete, dbo.mnotacredito.tipo AS Doc, 
                      dbo.mnotacredito.workstation, dbo.mnotacredito.cancelado, '01' AS codsuc, dbo.MClientes.medio
FROM         dbo.Mnotacredito LEFT JOIN
                      dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VentasDiarias_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VentasDiarias_2";
CREATE VIEW dbo.VentasDiarias_2
AS
SELECT        dbo.MFactura.numfactu,  dbo.MFactura.fechafac,  dbo.MFactura.codmedico, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.total, dbo.MFactura.statfact, 
                         dbo.MFactura.TotImpuesto, 
                         TotalFac = CASE WHEN dbo.MFactura.TotImpuesto = 0 THEN dbo.MFactura.subtotal - dbo.MFactura.descuento ELSE dbo.MFactura.subtotal - dbo.MFactura.descuento + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) 
                         * 0.105, 2) + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) * 0.01, 2) END, dbo.MFactura.monto_flete, dbo.mfactura.tipo AS doc,  dbo.MFactura.cancelado, '01' AS codsuc, 
                          dbo.MFactura.total + dbo.MFactura.monto_flete general, 'na' ct,  dbo.MFactura.medico, dbo.MFactura.mediconame
FROM            dbo.MFactura 
UNION ALL
SELECT        dbo.Mnotacredito.numnotcre AS numfactu, dbo.Mnotacredito.fechanot AS fechafac,  dbo.Mnotacredito.codmedico, dbo.Mnotacredito.subtotal * - 1 AS subtotal, 
                         dbo.Mnotacredito.descuento * - 1 AS descuento, dbo.Mnotacredito.totalnot * - 1 AS total, dbo.Mnotacredito.statnc AS statfact,  
                         dbo.mnotacredito.totimpuesto * - 1 AS totimpuesto, totalcred = 0, dbo.mnotacredito.monto_flete * - 1 AS monto_flete, dbo.mnotacredito.tipo AS Doc,  dbo.mnotacredito.cancelado, '01' AS codsuc, 
                          (dbo.Mnotacredito.totalnot + dbo.mnotacredito.monto_flete) * - 1 general, ct,  dbo.Mnotacredito.medico, dbo.Mnotacredito.mediconame
FROM            dbo.Mnotacredito 

;


-- Dumping structure for view farmacias.ventasMedicos01
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "ventasMedicos01";
create view ventasMedicos01 as 
select a.fechafac,a.codmedico, sum(a.total) venta, b.apellido+' ' + b.nombre medico from MFactura a 
inner join Mmedicos b on a.codmedico=b.Codmedico
where statfact<>'2'
group by a.fechafac,a.codmedico, b.apellido, b.nombre;


-- Dumping structure for view farmacias.Vestaciones
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "Vestaciones";
CREATE VIEW dbo.Vestaciones
AS
SELECT     dbo.estaciones.codestac, dbo.estaciones.desestac, dbo.estaciones.id_centro, dbo.MEquipos.Workstation, dbo.estaciones.fondo_inicial, dbo.MEquipos.usuario, 
                      dbo.MEquipos.ipaddress, dbo.MEquipos.Id
FROM         dbo.estaciones INNER JOIN
                      dbo.MEquipos ON dbo.estaciones.codestac = dbo.MEquipos.codestac
;


-- Dumping structure for view farmacias.VIEW1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW1";

CREATE VIEW dbo.VIEW1
AS
SELECT dbo.MFactura.numfactu, dbo.MFactura.fechafac, 
    dbo.MInventario.coditems, dbo.MInventario.desitems, 
    dbo.DFactura.cantidad, dbo.MFactura.usuario, 
    dbo.MFactura.ipaddress
FROM dbo.MFactura INNER JOIN
    dbo.DFactura ON 
    dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
    dbo.MInventario ON 
    dbo.DFactura.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW10
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW10";


CREATE VIEW dbo.VIEW10
AS
SELECT     dbo.MClientes.nombres, dbo.MClientes.email, dbo.Mconsultas.codclien, dbo.Mconsultas.asistido, dbo.Mconsultas.fecha_cita
FROM         dbo.Mconsultas INNER JOIN
                      dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien


;


-- Dumping structure for view farmacias.VIEW11
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW11";
CREATE VIEW VIEW11 AS SELECT DISTINCT nombres, email, codclien, asistido FROM view10  where fecha_cita>='20180115'  and  fecha_cita<='20180115' and asistido =3 ;


-- Dumping structure for view farmacias.VIEW2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW2";

CREATE VIEW dbo.VIEW2
AS
SELECT     TOP 100 PERCENT dbo.MFactura.fechafac, dbo.DFactura.coditems, dbo.DFactura.cantidad, dbo.DFactura.precunit, dbo.MInventario.desitems
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.MFactura.fechafac >= '01/11/2003') AND (dbo.MFactura.fechafac <= '30/11/2003') AND (dbo.DFactura.coditems <> '0000000003') AND 
                      (dbo.DFactura.coditems <> '0000000005') AND (dbo.DFactura.coditems <> '0000000018') AND (dbo.DFactura.coditems <> '0000000082') AND 
                      (dbo.DFactura.coditems <> '0000000080') AND (dbo.DFactura.coditems <> '0000000006')
ORDER BY dbo.MInventario.desitems

;


-- Dumping structure for view farmacias.VIEW3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW3";

CREATE VIEW dbo.VIEW3
AS
SELECT     TOP 100 PERCENT dbo.CMA_MFactura.numfactu, dbo.CMA_MFactura.fechafac, dbo.CMA_DFactura.coditems, dbo.MInventario.desitems, 
                      dbo.CMA_MFactura.statfact, dbo.MInventario.Prod_serv, dbo.CMA_DFactura.cantidad, dbo.CMA_DFactura.precunit
FROM         dbo.CMA_DFactura INNER JOIN
                      dbo.CMA_MFactura ON dbo.CMA_DFactura.numfactu = dbo.CMA_MFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.CMA_DFactura.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW3ANULADASCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW3ANULADASCMA";
CREATE VIEW dbo.VIEW3ANULADASCMA
AS
SELECT     TOP 100 PERCENT dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.MClientes.nombres, dbo.cma_MFactura.subtotal, 
                      dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.fechanul, dbo.cma_MFactura.Obervacion AS desanula
FROM         dbo.cma_MFactura INNER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.numfactu = dbo.MClientes.numfactu
WHERE     (dbo.cma_MFactura.fechafac >= '20040101') AND (dbo.cma_MFactura.fechafac <= '20040229') AND (dbo.cma_MFactura.statfact = 2)
ORDER BY dbo.cma_MFactura.fechafac
;


-- Dumping structure for view farmacias.VIEW4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW4";

CREATE VIEW dbo.VIEW4
AS
SELECT     numfactu, descuento,
                          (SELECT     SUM(dfactura.descuento)
                            FROM          dfactura
                            WHERE      dfactura.numfactu = mfactura.numfactu) AS compare, subtotal, descuento * 100 / subtotal AS Expr1
FROM         dbo.MFactura
WHERE     (descuento <> 0)

;


-- Dumping structure for view farmacias.VIEW5
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW5";

CREATE VIEW dbo.VIEW5
AS
SELECT     TOP 100 PERCENT dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MClientes.Cedula, dbo.MClientes.nombres
FROM         dbo.MFactura INNER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien
WHERE     (dbo.MClientes.Cedula = '8212550')
ORDER BY dbo.MFactura.fechafac

;


-- Dumping structure for view farmacias.VIEW6
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW6";
CREATE VIEW dbo.VIEW6
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.subtotal, dbo.MFactura.Alicuota, dbo.MFactura.descuento, 
                      SUM(dbo.DFactura.precunit * dbo.DFactura.cantidad) AS subtotalitem, SUM(dbo.DFactura.descuento) AS Descdet
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu
GROUP BY dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.subtotal, dbo.MFactura.Alicuota, dbo.MFactura.descuento
;


-- Dumping structure for view farmacias.VIEW7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW7";
CREATE VIEW dbo.VIEW7
AS
SELECT     TOP 100 PERCENT dbo.Mpagos.numfactu, dbo.MFactura.total, SUM(dbo.Mpagos.monto) AS Expr1
FROM         dbo.MFactura INNER JOIN
                      dbo.Mpagos ON dbo.MFactura.numfactu = dbo.Mpagos.numfactu
GROUP BY dbo.Mpagos.numfactu, dbo.MFactura.total
ORDER BY dbo.Mpagos.numfactu DESC
;


-- Dumping structure for view farmacias.VIEW8
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW8";
CREATE VIEW dbo.VIEW8
AS
SELECT     dbo.MInventario.desitems, dbo.DCierreInventario.existencia, dbo.DCierreInventario.fechacierre, dbo.MInventario.activo,
                          (SELECT     isnull(SUM(dbo.dfactura.cantidad), 0)
                            FROM          dfactura RIGHT JOIN
                                                   mfactura ON dfactura.numfactu = mfactura.numfactu
                            WHERE      coditems = dbo.minventario.coditems AND dbo.dfactura.fechafac = dbo.DCIERREINVENTARIO.FECHACIERRE) AS ventas,
                          (SELECT     isnull(SUM(dbo.dfactura.cantidad), 0)
                            FROM          dfactura RIGHT JOIN
                                                   mfactura ON dfactura.numfactu = mfactura.numfactu
                            WHERE      coditems = dbo.minventario.coditems AND dbo.mfactura.fechanul = dbo.DCIERREINVENTARIO.FECHACIERRE AND 
                                                   dbo.mfactura.statfact = '2') AS DVentas,
                          (SELECT     isnull(SUM(dbo.dcompra.cantidad), 0)
                            FROM          dcompra LEFT JOIN
                                                   mcompra ON dcompra.factcomp = mcompra.factcomp
                            WHERE      coditems = dbo.minventario.coditems AND mcompra.fechapost = dbo.DCIERREINVENTARIO.FECHACIERRE) AS compra,
                          (SELECT     isnull(SUM(dbo.devcompra.cantidad), 0)
                            FROM          devcompra
                            WHERE      coditems = dbo.minventario.coditems AND fecreg = dbo.DCIERREINVENTARIO.FECHACIERRE) AS devcompra,
                          (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0)
                            FROM          dajustes
                            WHERE      coditems = dbo.minventario.coditems AND fechajust = dbo.DCIERREINVENTARIO.FECHACIERRE AND dbo.dajustes.cantidad > 0) 
                      AS Ajustes_mas,
                          (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0)
                            FROM          dajustes
                            WHERE      coditems = dbo.minventario.coditems AND fechajust = dbo.DCIERREINVENTARIO.FECHACIERRE AND dbo.dajustes.cantidad < 0) 
                      AS Ajustes_menos,
                          (SELECT     isnull(SUM(dbo.notentdetalle.cantidad), 0)
                            FROM          notentdetalle RIGHT JOIN
                                                   notaentrega ON notentdetalle.numnotent = notaentrega.numnotent
                            WHERE      coditems = dbo.minventario.coditems AND dbo.notentdetalle.fechanot = dbo.DCIERREINVENTARIO.FECHACIERRE AND 
                                                   notaentrega.statunot <> '2') AS NE,
                          (SELECT     isnull(SUM(dbo.dnotacredito.cantidad), 0)
                            FROM          dnotacredito RIGHT JOIN
                                                   mnotacredito ON dnotacredito.numnotcre = mnotacredito.numnotcre
                            WHERE      coditems = dbo.minventario.coditems AND dbo.dnotacredito.fechanot = dbo.DCIERREINVENTARIO.FECHACIERRE AND 
                                                   mnotacredito.statnc <> '2') AS Nc, dbo.DCierreInventario.coditems
FROM         dbo.DCierreInventario LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DCierreInventario.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.viewAlertaPedido
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewAlertaPedido";
CREATE view viewAlertaPedido as
select a.coditems,a.existencia,(select sum(c.compra) from purchaseOM b
inner join purchaseorder c ON b.pon = c.purchaseOrder
 where b.status ='1' and c.coditems= a.coditems) PO, a.existencia+(select sum(c.compra) from purchaseOM b
inner join purchaseorder c ON b.pon = c.purchaseOrder
 where b.status ='1' and c.coditems= a.coditems) Estimada, 
 (  SELECT  SUM(cantidad) /3  AS venta From VIEW_PO Where (fechafac >= DateAdd(Month, -3 , GETDATE())) and coditems=a.coditems GROUP BY coditems
 ) Promedio,

( a.existencia+(select sum(c.compra) from purchaseOM b
inner join purchaseorder c ON b.pon = c.purchaseOrder
 where b.status ='1' and c.coditems= a.coditems))/ (  SELECT  SUM(cantidad) /3  AS venta From VIEW_PO Where (fechafac >= DateAdd(Month, -3 , GETDATE())) and coditems=a.coditems GROUP BY coditems
 )  pedido,d.desitems,d.Prod_serv,d.activo,d.cod_grupo,d.cod_subgrupo
  from MInventario a 
 inner join MInventario d ON a.coditems=d.coditems;


-- Dumping structure for view farmacias.viewappconf_a
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewappconf_a";
CREATE VIEW dbo.viewappconf_a
AS
SELECT        c.codclien, c.fecha_cita, '' coditems, CASE WHEN CONVERT(date, SYSDATETIME()) = CONVERT(date, c.fecha_cita) THEN 'Hoy' WHEN CONVERT(date, DATEADD(day, 1, SYSDATETIME())) = CONVERT(date, 
                         c.fecha_cita) THEN 'Mañana' ELSE CONVERT(VARCHAR(10), c.fecha_cita, 110) END appt_date_es, CASE WHEN CONVERT(date, SYSDATETIME()) = CONVERT(date, c.fecha_cita) 
                         THEN 'Today' WHEN CONVERT(date, DATEADD(day, 1, SYSDATETIME())) = CONVERT(date, c.fecha_cita) THEN 'Tomorrow' ELSE CONVERT(VARCHAR(10), c.fecha_cita, 110) END appt_date_en, 
                         CASE DATEPART(WEEKDAY, c.fecha_cita) WHEN 1 THEN 'Dom' WHEN 2 THEN 'Lun' WHEN 3 THEN 'Mar' WHEN 4 THEN 'Mié' WHEN 5 THEN 'Jue' WHEN 6 THEN 'Vie' WHEN 7 THEN 'Sáb' END esp_day, 
                         CASE DATEPART(WEEKDAY, c.fecha_cita) WHEN 1 THEN 'Sun' WHEN 2 THEN 'Mon' WHEN 3 THEN 'Tue' WHEN 4 THEN 'Wed' WHEN 5 THEN 'Thu' WHEN 6 THEN 'Fri' WHEN 7 THEN 'Sat' END eng_day, 
                         nombres, SUBSTRING(replace(replace(replace(cl.telfhabit, '-', ''), '/', ''), ' ', ''), 1, 10) cel, CONVERT(VARCHAR(10), c.fecha_cita, 110) fecha
FROM            mconsultas c INNER JOIN
                         mclientes cl ON c.codclien = cl.codclien
UNION ALL
SELECT        c.codclien, c.fecha_cita, c.coditems, CASE WHEN CONVERT(date, SYSDATETIME()) = CONVERT(date, c.fecha_cita) THEN 'Hoy' WHEN CONVERT(date, DATEADD(day, 1, SYSDATETIME())) = CONVERT(date, 
                         c.fecha_cita) THEN 'Mañana' ELSE CONVERT(VARCHAR(10), c.fecha_cita, 110) END appt_date_es, CASE WHEN CONVERT(date, SYSDATETIME()) = CONVERT(date, c.fecha_cita) 
                         THEN 'Today' WHEN CONVERT(date, DATEADD(day, 1, SYSDATETIME())) = CONVERT(date, c.fecha_cita) THEN 'Tomorrow' ELSE CONVERT(VARCHAR(10), c.fecha_cita, 110) END appt_date_en, 
                         CASE DATEPART(WEEKDAY, c.fecha_cita) WHEN 1 THEN 'Dom' WHEN 2 THEN 'Lun' WHEN 3 THEN 'Mar' WHEN 4 THEN 'Mié' WHEN 5 THEN 'Jue' WHEN 6 THEN 'Vie' WHEN 7 THEN 'Sáb' END esp_day, 
                         CASE DATEPART(WEEKDAY, c.fecha_cita) WHEN 1 THEN 'Sun' WHEN 2 THEN 'Mon' WHEN 3 THEN 'Tue' WHEN 4 THEN 'Wed' WHEN 5 THEN 'Thu' WHEN 6 THEN 'Fri' WHEN 7 THEN 'Sat' END eng_day, 
                         nombres, SUBSTRING(replace(replace(replace(cl.telfhabit, '-', ''), '/', ''), ' ', ''), 1, 10) cel, CONVERT(VARCHAR(10), c.fecha_cita, 110) fecha
FROM            mconsultass c INNER JOIN
                         mclientes cl ON c.codclien = cl.codclien
;


-- Dumping structure for view farmacias.viewcmareturn
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewcmareturn";
CREATE view viewcmareturn as
SELECT     m.numnotcre, m.fechanot, m.numfactu, c.nombres, CONCAT(md.apellido, '  ', md.nombre) medico, m.monto, m.descuento, m.totalnot, s.destatus, m.subtotal, 
                      m.iva, m.TotImpuesto, m.alicuota, m.monto_flete, m.monto_abonado, m.tasadesc, m.saldo, m.Id, m.statnc, m.usuario
FROM         dbo.CMA_Mnotacredito AS m LEFT OUTER JOIN
                      dbo.Mstatus AS s ON m.statnc = s.status LEFT OUTER JOIN
                      dbo.Mmedicos AS md ON m.codmedico = md.Codmedico LEFT OUTER JOIN
                      dbo.MClientes AS c ON m.codclien = c.codclien;


-- Dumping structure for view farmacias.viewcompairprod
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewcompairprod";
create view viewcompairprod as  
select sum(mf.total) amount,mf.codmedico,me.medico,MONTH(fechafac) months, concat(YEAR(fechafac),'/',MONTH(fechafac)) periodo from mfactura mf 
inner join viewmedicos me on mf.codmedico=me.codmedico
where /* mf.fechafac between '01-01-2017' and '03-24-2017' and */ mf.statfact!=2
group by mf.codmedico,me.medico,MONTH(fechafac),YEAR(fechafac);


-- Dumping structure for view farmacias.VIEWConsolidado
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWConsolidado";
CREATE VIEW dbo.VIEWConsolidado
AS
SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 1 AS tipo
FROM         dbo.VentasDiarias
UNION ALL
SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 2 AS tipo
FROM         dbo.VentasDiariasCMA
Union All
SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 3 AS tipo
FROM         dbo.VentasDiariasMSS

;


-- Dumping structure for view farmacias.VIEWConsolidadot
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWConsolidadot";
CREATE VIEW dbo.VIEWConsolidadot
AS
SELECT        numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 1 AS tipo
FROM            dbo.VentasDiarias
UNION ALL
SELECT        numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 2 AS tipo
FROM            dbo.VentasDiariasCMA
UNION ALL
SELECT        numfactu, nombres, fechafac, ISNULL( subtotal,0), ISNULL(  descuento,0),  ISNULL( total,0), statfact, tipopago,  ISNULL( TotImpuesto,0),  ISNULL( monto_flete,0), doc, workstation, cancelado, 3 AS tipo
FROM            dbo.VentasDiariasMSS
;


-- Dumping structure for view farmacias.VIEWdescuentos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWdescuentos";


CREATE VIEW dbo.VIEWdescuentos
AS
SELECT dbo.MDesctFactura.numfactu, dbo.MDesctFactura.codesc, dbo.Mdescuentos.desdesct, dbo.MDesctFactura.total
FROM  dbo.MDesctFactura INNER JOIN
               dbo.Mdescuentos ON dbo.MDesctFactura.codesc = dbo.Mdescuentos.codesc


;


-- Dumping structure for view farmacias.VIEWHorario
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWHorario";

CREATE VIEW dbo.VIEWHorario
AS
SELECT     dbo.Horario.Codmedico, dbo.Horario.codclien, dbo.MClientes.nombres, dbo.Horario.HoraI, dbo.Horario.HoraS, dbo.Horario.Fecha
FROM         dbo.Horario INNER JOIN
                      dbo.MClientes ON dbo.Horario.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.viewMconsutasAll
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewMconsutasAll";
CREATE view viewMconsutasAll as 
select citados ,confirmado,asistido, noasistido  ,fecha_cita, activa,usuario from  mconsultas 
union all
select citados ,confirmado,asistido, noasistido  ,fecha_cita, activa,usuario  from  mconsultass 
;


-- Dumping structure for view farmacias.viewmedicos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewmedicos";
create view viewmedicos as
select codmedico, concat(nombre,' ',apellido) medico,activo  from Mmedicos;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesCitadosXMedico";
CREATE VIEW dbo.VIEWPacientesCitadosXMedico
AS
SELECT     TOP 100 PERCENT dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado, 
                      dbo.Mconsultas.asistido, dbo.Mmedicos.nombre, dbo.Mmedicos.apellido, dbo.Mmedicos.Codmedico, dbo.MClientes.telfhabit, 
                      dbo.Mconsultas.noasistido, dbo.Mconsultas.primera_control, dbo.Mconsultas.Nro_asistencias, dbo.Mconsultas.NoCitados, 
                      dbo.Mconsultas.HoraRegistro, dbo.MClientes.Historia, dbo.MClientes.fallecido, dbo.MClientes.inactivo
FROM         dbo.MClientes INNER JOIN
                      dbo.Mconsultas ON dbo.MClientes.codclien = dbo.Mconsultas.codclien INNER JOIN
                      dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.MClientes.fallecido = 0)
ORDER BY dbo.Mconsultas.fecha_cita
;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico02
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesCitadosXMedico02";

CREATE VIEW dbo.VIEWPacientesCitadosXMedico02
AS
SELECT     TOP 100 PERCENT dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado, 
                      dbo.Mconsultas.asistido, dbo.Mmedicos.nombre, dbo.Mmedicos.apellido, dbo.Mmedicos.Codmedico, dbo.MClientes.telfhabit, dbo.Mconsultas.noasistido, 
                      dbo.Mconsultas.primera_control, dbo.Mconsultas.Nro_asistencias, dbo.Mconsultas.NoCitados, dbo.Mconsultas.HoraRegistro, dbo.MClientes.Historia, 
                      dbo.MClientes.fallecido, dbo.MClientes.inactivo
FROM         dbo.MClientes INNER JOIN
                      dbo.Mconsultas ON dbo.MClientes.codclien = dbo.Mconsultas.codclien INNER JOIN
                      dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.MClientes.fallecido = 0) AND (dbo.MClientes.Historia IS NULL OR
                      dbo.MClientes.Historia = '' OR
                      dbo.MClientes.Historia LIKE '%Cortes%' OR
                      dbo.MClientes.Historia LIKE '%No asign')
ORDER BY dbo.Mconsultas.fecha_cita

;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico03
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesCitadosXMedico03";
CREATE VIEW    VIEWPacientesCitadosXMedico03 AS SELECT  TOP 100 PERCENT dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado,  dbo.Mconsultas.asistido, dbo.Mmedicos.apellido + ' ' + dbo.Mmedicos.nombre AS medico, dbo.Mmedicos.Codmedico, dbo.MClientes.telfhabit,   dbo.Mconsultas.noasistido, dbo.Mconsultas.primera_control, dbo.Mconsultas.Nro_asistencias, dbo.Mconsultas.NoCitados, dbo.Mconsultas.HoraRegistro,   dbo.MClientes.Historia , dbo.MClientes.fallecido, dbo.MClientes.inactivo   FROM  dbo.MClientes INNER JOIN  dbo.Mconsultas ON dbo.MClientes.codclien = dbo.Mconsultas.codclien INNER JOIN   dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico   WHERE     (dbo.MClientes.fallecido = 0) AND (dbo.MClientes.Historia IS NOT NULL) AND (dbo.MClientes.Historia <> '') AND (dbo.MClientes.Historia NOT LIKE '%Cortes%') AND   (dbo.MClientes.Historia NOT LIKE '%No asign') AND (dbo.MClientes.telfhabit NOT LIKE '%000%') AND (dbo.MClientes.Historia NOT LIKE '%0000%') AND   (dbo.MClientes.Historia NOT LIKE '%**%') AND (NOT EXISTS       (SELECT     *         FROM          Mconsultas sc         WHERE      (sc.codclien = dbo.MClientes.codclien) AND (fecha_cita > '20180403')))  ORDER BY dbo.Mconsultas.fecha_cita ;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico04
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesCitadosXMedico04";
 CREATE VIEW   VIEWPacientesCitadosXMedico04 AS SELECT     TOP 100 PERCENT dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado, dbo.Mconsultas.asistido, dbo.Mmedicos.apellido + ' ' + dbo.Mmedicos.nombre AS medico, dbo.Mmedicos.Codmedico, dbo.MClientes.telfhabit,  dbo.Mconsultas.noasistido, dbo.Mconsultas.primera_control, dbo.Mconsultas.Nro_asistencias, dbo.Mconsultas.NoCitados, dbo.Mconsultas.HoraRegistro,  dbo.MClientes.Historia , dbo.MClientes.fallecido, dbo.MClientes.inactivo  FROM dbo.MClientes INNER JOIN  dbo.Mconsultas ON dbo.MClientes.codclien = dbo.Mconsultas.codclien INNER JOIN  dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico  Where (dbo.MClientes.fallecido = 0) and   (1 = (SELECT     COUNT(codclien)  FROM mconsultas co WHERE dbo.MClientes.codclien = co.codclien)) ORDER BY dbo.Mconsultas.fecha_cita ;


-- Dumping structure for view farmacias.VIEWPacientesCitadosXMedico05
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesCitadosXMedico05";

CREATE VIEW dbo.VIEWPacientesCitadosXMedico05
AS
SELECT     *,
                          (SELECT     COUNT(codclien)
                            FROM          VIEWPacientesCitadosXMedico04 vp
                            WHERE      vp.codclien = VIEWPacientesCitadosXMedico04.codclien) AS veces,
                          (SELECT     MAX(fecha_cita)
                            FROM          mconsultas c
                            WHERE      c.codclien = dbo.VIEWPacientesCitadosXMedico04.codclien) AS ultimaCita
FROM         dbo.VIEWPacientesCitadosXMedico04

;


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesMayorInversion";
 CREATE VIEW  VIEWPacientesMayorInversion AS SELECT dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.iva,  dbo.MFactura.total, dbo.MFactura.statfact, dbo.MFactura.tipopago, dbo.MFactura.codseguro, dbo.MFactura.TotImpuesto, dbo.MFactura.Alicuota,  dbo.MFactura.monto_flete, dbo.MClientes.nombres, dbo.MClientes.fallecido, dbo.MClientes.inactivo, dbo.Mmedicos.apellido + ' ' + dbo.Mmedicos.nombre AS medico,  dbo.MClientes.telfhabit , dbo.MClientes.email, dbo.MClientes.Historia  FROM dbo.MFactura INNER JOIN  dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien INNER JOIN  dbo.Mmedicos ON dbo.MFactura.codmedico = dbo.Mmedicos.Codmedico  Where (dbo.MClientes.fallecido = 0) And (dbo.MFactura.statfact = 3) And (dbo.MFactura.total > 150) ;


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion01
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesMayorInversion01";
 CREATE VIEW VIEWPacientesMayorInversion01 AS  SELECT *  From VIEWPacientesMayorInversion  WHERE (fechafac BETWEEN '20160401' AND '20160331' );


-- Dumping structure for view farmacias.VIEWPacientesMayorInversion02
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPacientesMayorInversion02";
 CREATE VIEW VIEWPacientesMayorInversion02 AS  SELECT v1.*  FROM dbo.VIEWPacientesMayorInversion01 v1  WHERE (1 =  (SELECT  COUNT(codclien)  FROM  VIEWPacientesMayorInversion01 co  WHERE co.codclien = v1.codclien)) ;


-- Dumping structure for view farmacias.VIEWPagosDEV
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosDEV";
CREATE VIEW dbo.VIEWPagosDEV
AS
SELECT     dbo.Mpagos.fechapago, dbo.Mnotacredito.numnotcre, dbo.Mnotacredito.statnc, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, 
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.Mnotacredito.workstation, dbo.Mpagos.tipo_doc, 
                      dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.Mnotacredito.codclien, dbo.Mnotacredito.usuario
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.Mnotacredito ON dbo.Mpagos.numfactu = dbo.Mnotacredito.numnotcre
WHERE     (dbo.Mpagos.tipo_doc = '04') AND (dbo.Mpagos.id_centro = '1')
;


-- Dumping structure for view farmacias.VIEWPagosDEVCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosDEVCMA";
CREATE VIEW dbo.VIEWPagosDEVCMA
AS
SELECT     dbo.Mpagos.fechapago, dbo.Mnotacredito.numnotcre, dbo.Mnotacredito.statnc, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, 
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.Mnotacredito.workstation, dbo.Mpagos.tipo_doc, 
                      dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.Mnotacredito.codclien, dbo.Mpagos.usuario, dbo.Mpagos.idempresa
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.Mnotacredito ON dbo.Mpagos.numfactu = dbo.Mnotacredito.numnotcre
WHERE     (dbo.Mpagos.tipo_doc = '04') AND (dbo.Mpagos.id_centro = '2')
;


-- Dumping structure for view farmacias.VIEWPagosDEVMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosDEVMSS";

CREATE VIEW [dbo].[VIEWPagosDEVMSS]
AS
SELECT     dbo.Mpagos.fechapago, dbo.Mnotacredito.numnotcre, dbo.Mnotacredito.statnc, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, 
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.Mnotacredito.workstation, 
                      dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.Mnotacredito.codclien
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.Mnotacredito ON dbo.Mpagos.numfactu = dbo.Mnotacredito.numnotcre
WHERE     (dbo.Mpagos.tipo_doc = '04') AND (dbo.Mpagos.id_centro = '3')

;


-- Dumping structure for view farmacias.VIEWpagosFAC
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosFAC";
/*SELECT     dbo.Mpagos.fechapago, dbo.MFactura.numfactu, dbo.MFactura.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, monto2=dbo.Mpagos.monto, 
           monto = dbo.MFactura.subtotal-dbo.MFactura.descuento + round((dbo.MFactura.subtotal-dbo.MFactura.descuento) *0.06,2 ) + round( (dbo.MFactura.subtotal-dbo.MFactura.descuento) *0.01,2 ),  
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MFactura.workstation, 
                      dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MFactura.codclien
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')
SELECT     distinct dbo.Mpagos.fechapago, dbo.MFactura.numfactu, dbo.MFactura.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, 
           monto = dbo.MFactura.subtotal-dbo.MFactura.descuento + round((dbo.MFactura.subtotal-dbo.MFactura.descuento) *0.06,2 ) + round( (dbo.MFactura.subtotal-dbo.MFactura.descuento) *0.01,2 ),  
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MFactura.workstation, 
                      dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MFactura.codclien
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')








*/
CREATE VIEW dbo.VIEWpagosFAC
AS
SELECT DISTINCT 
                      dbo.Mpagos.fechapago, dbo.MFactura.numfactu, dbo.MFactura.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, 
                      CASE WHEN dbo.MFactura.TotImpuesto = 0 THEN dbo.MFactura.subtotal - dbo.MFactura.descuento ELSE dbo.MFactura.subtotal - dbo.MFactura.descuento + round((dbo.MFactura.subtotal
                       - dbo.MFactura.descuento) * (taxa / 100), 2) + round((dbo.MFactura.subtotal - dbo.MFactura.descuento) * (taxb / 100), 2) END AS monto, dbo.Mpagos.nro_forpa, 
                      dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MFactura.workstation, dbo.Mpagos.tipo_doc, 
                      dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MFactura.codclien, dbo.MFactura.monto_flete, 
                      dbo.VIEW_TaxesInvoice2.TaxA, dbo.MFactura.usuario
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu INNER JOIN
                      dbo.VIEW_TaxesInvoice2 ON dbo.MFactura.numfactu = dbo.VIEW_TaxesInvoice2.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')
;


-- Dumping structure for view farmacias.VIEWPagosFACCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosFACCMA";
CREATE VIEW dbo.VIEWPagosFACCMA
AS
SELECT        dbo.Mpagos.fechapago, dbo.cma_MFactura.numfactu, dbo.cma_MFactura.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, 
                         dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.cma_MFactura.workstation, dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.cma_MFactura.codclien, 
                         dbo.Mpagos.usuario, dbo.Mpagos.idempresa, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.fechafac
FROM            dbo.Mpagos INNER JOIN
                         dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                         dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                         dbo.cma_MFactura ON dbo.Mpagos.numfactu = dbo.cma_MFactura.numfactu
WHERE        (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '2')
UNION
SELECT        dbo.Mpagos.fechapago, dbo.CMA_Mnotacredito.numnotcre, dbo.CMA_Mnotacredito.statnc, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, 
                         dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.CMA_Mnotacredito.workstation, dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.CMA_Mnotacredito.codclien, 
                         dbo.Mpagos.usuario, dbo.Mpagos.idempresa, dbo.CMA_Mnotacredito.codmedico, dbo.CMA_Mnotacredito.fechanot
FROM            dbo.Mpagos INNER JOIN
                         dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                         dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                         dbo.CMA_Mnotacredito ON dbo.Mpagos.numfactu = dbo.CMA_Mnotacredito.numnotcre
WHERE        (dbo.Mpagos.tipo_doc = '04') AND (dbo.Mpagos.id_centro = '2')
;


-- Dumping structure for view farmacias.VIEWpagosFACMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosFACMSS";
/*SELECT     dbo.Mpagos.fechapago, dbo.MSSMFact.numfactu, dbo.MSSMFact.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, monto2=dbo.Mpagos.monto, 
           monto = dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento + round((dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento) *0.06,2 ) + round( (dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento) *0.01,2 ),  
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MSSMFact.workstation, 
                      dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MSSMFact.codclien
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MSSMFact ON dbo.Mpagos.numfactu = dbo.MSSMFact.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')
SELECT     distinct dbo.Mpagos.fechapago, dbo.MSSMFact.numfactu, dbo.MSSMFact.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, 
           monto = dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento + round((dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento) *0.06,2 ) + round( (dbo.MSSMFact.subtotal-dbo.MSSMFact.descuento) *0.01,2 ),  
                      dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MSSMFact.workstation, 
                      dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MSSMFact.codclien
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MSSMFact ON dbo.Mpagos.numfactu = dbo.MSSMFact.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')








*/
CREATE VIEW dbo.VIEWpagosFACMSS
AS
SELECT DISTINCT 
                      dbo.Mpagos.fechapago, dbo.MSSMFact.numfactu, dbo.MSSMFact.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, 
                      CASE WHEN dbo.MSSMFact.TotImpuesto = 0 THEN dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento ELSE dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento + round((dbo.MSSMFact.subtotal
                       - dbo.MSSMFact.descuento) * (taxa / 100), 2) + round((dbo.MSSMFact.subtotal - dbo.MSSMFact.descuento) * (taxb / 100), 2) END AS monto, dbo.Mpagos.nro_forpa, 
                      dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MSSMFact.workstation, dbo.Mpagos.tipo_doc, 
                      dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MSSMFact.codclien, dbo.MSSMFact.monto_flete, 
                      dbo.VIEW_TaxesInvoice2MSS.TaxA
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MSSMFact ON dbo.Mpagos.numfactu = dbo.MSSMFact.numfactu INNER JOIN
                      dbo.VIEW_TaxesInvoice2MSS ON dbo.MSSMFact.numfactu = dbo.VIEW_TaxesInvoice2MSS.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '3') AND (dbo.MSSMFact.tipo = '01')
;


-- Dumping structure for view farmacias.VIEWPAGOSFACMSS_W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPAGOSFACMSS_W7";
CREATE VIEW dbo.VIEWPAGOSFACMSS_W7
AS
SELECT DISTINCT 
                         dbo.Mpagos.fechapago, dbo.MSSMFact.numfactu, dbo.MSSMFact.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, 
                         dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MSSMFact.workstation, dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MSSMFact.codclien, 
                         dbo.MSSMFact.monto_flete, dbo.MSSMFact.usuario AS userfac, dbo.Mpagos.usuario AS userpagos
FROM            dbo.Mpagos INNER JOIN
                         dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                         dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                         dbo.MSSMFact ON dbo.Mpagos.numfactu = dbo.MSSMFact.numfactu
WHERE        (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '3') AND (dbo.MSSMFact.tipo = '01')
union
 SELECT DISTINCT 
                         dbo.Mpagos.fechapago, dbo.MSSMFact.numfactu, dbo.MSSMFact.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, dbo.Mpagos.nro_forpa, dbo.MTipoTargeta.DesTipoTargeta, 
                         dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MSSMFact.workstation, dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MSSMFact.codclien, 
                         dbo.MSSMFact.monto_flete, dbo.MSSMFact.usuario AS userfac, dbo.Mpagos.usuario AS userpagos
FROM            dbo.Mpagos 
INNER JOIN
                         dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta 
INNER JOIN
                         dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago 
INNER JOIN
                         dbo.MSSMFact ON dbo.Mpagos.numfactu = dbo.MSSMFact.numfactu
WHERE        (dbo.Mpagos.tipo_doc ='04') AND (dbo.Mpagos.id_centro = '3') 
;


-- Dumping structure for view farmacias.VIEWPAGOSFAC_W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPAGOSFAC_W7";
CREATE VIEW dbo.VIEWPAGOSFAC_W7
AS
SELECT DISTINCT 
                      dbo.Mpagos.fechapago, dbo.MFactura.numfactu, dbo.MFactura.statfact, dbo.Mpagos.codforpa, dbo.Mpagos.codtipotargeta, dbo.Mpagos.monto, dbo.Mpagos.nro_forpa, 
                      dbo.MTipoTargeta.DesTipoTargeta, dbo.FormaPago.nombre, dbo.Mpagos.id_centro, dbo.MFactura.workstation, dbo.Mpagos.tipo_doc, 
                      dbo.MTipoTargeta.DesTipoTargeta AS modopago, dbo.FormaPago.trans_electronica, dbo.MFactura.codclien, dbo.MFactura.monto_flete, dbo.MFactura.usuario
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta INNER JOIN
                      dbo.FormaPago ON dbo.Mpagos.codforpa = dbo.FormaPago.codformapago INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.Mpagos.tipo_doc <> '04') AND (dbo.Mpagos.id_centro = '1')
;


-- Dumping structure for view farmacias.VIEWpagosPR
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosPR";
CREATE VIEW dbo.VIEWpagosPR
AS
/*SELECT     fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, 
                      trans_electronica, codclien
FROM         dbo.VIEWPagosFAC
UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, 
                      trans_electronica, codclien
FROM         dbo.VIEWPagosDEV*/ SELECT
                       fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 
                      monto_flete, montototal = monto + monto_flete,usuario
FROM         dbo.VIEWPagosFAC
UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 0, 
                      montototal = monto,usuario
FROM         dbo.VIEWPagosDEV
;


-- Dumping structure for view farmacias.VIEWpagosPRCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosPRCMA";
CREATE VIEW dbo.VIEWpagosPRCMA
AS
SELECT     fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien,usuario, idempresa
FROM         dbo.VIEWPagosFACCMA
UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, 
                      codclien,usuario, idempresa
FROM         dbo.VIEWPagosDEVCMA
;


-- Dumping structure for view farmacias.VIEWpagosPRCMACST
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosPRCMACST";
CREATE VIEW dbo.VIEWpagosPRCMACST
AS
SELECT     dbo.VIEWPagosFACCMA.fechapago, dbo.VIEWPagosFACCMA.numfactu, dbo.VIEWPagosFACCMA.statfact, dbo.VIEWPagosFACCMA.codforpa, 
                      dbo.VIEWPagosFACCMA.codtipotargeta, dbo.VIEWPagosFACCMA.monto, dbo.VIEWPagosFACCMA.DesTipoTargeta, dbo.VIEWPagosFACCMA.nombre, 
                      dbo.VIEWPagosFACCMA.id_centro, dbo.VIEWPagosFACCMA.workstation, dbo.VIEWPagosFACCMA.tipo_doc, dbo.VIEWPagosFACCMA.modopago, 
                      dbo.VIEWPagosFACCMA.trans_electronica, dbo.VIEWPagosFACCMA.codclien, c.cod_subgrupo, dbo.VIEWPagosFACCMA.usuario, dbo.VIEWPagosFACCMA.idempresa, 
                      dbo.VIEWPagosFACCMA.codmedico, dbo.VIEWPagosFACCMA.fechafac
FROM         dbo.VIEWPagosFACCMA INNER JOIN
                      dbo.viewtipofacturascma AS c ON dbo.VIEWPagosFACCMA.numfactu = c.numfactu
;


-- Dumping structure for view farmacias.VIEWpagosPRCMACST_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosPRCMACST_1";
CREATE view VIEWpagosPRCMACST_1 as
SELECT        dbo.VIEWPagosFACCMA.fechapago, dbo.VIEWPagosFACCMA.numfactu, dbo.VIEWPagosFACCMA.statfact, dbo.VIEWPagosFACCMA.codforpa, dbo.VIEWPagosFACCMA.codtipotargeta, dbo.VIEWPagosFACCMA.monto, 
                         dbo.VIEWPagosFACCMA.DesTipoTargeta, dbo.VIEWPagosFACCMA.nombre, dbo.VIEWPagosFACCMA.id_centro, dbo.VIEWPagosFACCMA.workstation, dbo.VIEWPagosFACCMA.tipo_doc, dbo.VIEWPagosFACCMA.modopago, 
                         dbo.VIEWPagosFACCMA.trans_electronica, dbo.VIEWPagosFACCMA.codclien, dbo.VIEWPagosFACCMA.usuario, dbo.VIEWPagosFACCMA.idempresa, dbo.VIEWPagosFACCMA.codmedico, 
                         dbo.VIEWPagosFACCMA.fechafac,
						 (
						  Select top 1 cod_subgrupo from  dbo.viewtipofacturascma c Where dbo.VIEWPagosFACCMA.numfactu = c.numfactu

						 ) cod_subgrupo

FROM            dbo.VIEWPagosFACCMA ;


-- Dumping structure for view farmacias.VIEWpagosPRMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWpagosPRMSS";
/*SELECT     fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, 
                      trans_electronica, codclien
FROM         dbo.VIEWPagosFAC
UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, 
                      trans_electronica, codclien
FROM         dbo.VIEWPagosDEV
  22 04 02016 UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 0, 
                      montototal = monto
FROM         dbo.VIEWPagosDEVMSS */
CREATE VIEW dbo.VIEWpagosPRMSS
AS
SELECT     fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 
                      monto_flete, monto + monto_flete AS montototal
FROM         dbo.VIEWpagosFACMSS
;


-- Dumping structure for view farmacias.VIEWPagosPRMSS_W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosPRMSS_W7";
/* 22 04  2016 UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, 
                      trans_electronica, codclien, 0, montototal = monto
FROM         dbo.VIEWPagosDEV
 */
CREATE VIEW dbo.VIEWPagosPRMSS_W7
AS
SELECT        fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, monto_flete, monto AS montototal, userfac, userpagos
FROM            dbo.VIEWPAGOSFACMSS_W7
;


-- Dumping structure for view farmacias.VIEWPagosPR_W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagosPR_W7";
CREATE VIEW dbo.VIEWPagosPR_W7
AS
SELECT     fechapago, numfactu, statfact, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 
                      monto_flete,usuario, montototal = monto
FROM         dbo.VIEWPagosFAC_W7
UNION ALL
SELECT     fechapago, numnotcre, statnc, codforpa, codtipotargeta, monto, DesTipoTargeta, nombre, id_centro, workstation, tipo_doc, modopago, trans_electronica, codclien, 0, usuario,
                      montototal = monto
FROM         dbo.VIEWPagosDEV
;


-- Dumping structure for view farmacias.VIEWPagoTotal
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagoTotal";
CREATE VIEW dbo.VIEWPagoTotal
AS
SELECT     numfactu, monto AS pago
FROM         dbo.VIEWpagosPR
WHERE     (monto > 0) AND (tipo_doc <> '04') AND (id_centro = 1)
GROUP BY numfactu, tipo_doc, monto
;


-- Dumping structure for view farmacias.VIEWPagoTotalMMS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPagoTotalMMS";
CREATE VIEW dbo.VIEWPagoTotalMMS
AS
SELECT     numfactu, monto AS pago
FROM         dbo.VIEWpagosPRMSS
WHERE     (monto > 0) AND (tipo_doc <> '04')
GROUP BY numfactu, tipo_doc, monto
;


-- Dumping structure for view farmacias.VIEWParaAjusteVentas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWParaAjusteVentas";
CREATE VIEW dbo.VIEWParaAjusteVentas
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.coditems, dbo.DFactura.cantidad, dbo.MInventario.desitems
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.MFactura.fechafac >= '20140805') AND (dbo.MFactura.statfact <> 2)
;


-- Dumping structure for view farmacias.VIEWPararAjusteNotaEntrega
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPararAjusteNotaEntrega";
CREATE VIEW dbo.VIEWPararAjusteNotaEntrega
AS
SELECT     dbo.NotaEntrega.numnotent, dbo.NotaEntrega.fechanot, dbo.NotaEntrega.statunot, dbo.NotEntDetalle.coditems, dbo.NotEntDetalle.cantidad, 
                      dbo.MInventario.desitems
FROM         dbo.NotaEntrega INNER JOIN
                      dbo.NotEntDetalle ON dbo.NotaEntrega.numnotent = dbo.NotEntDetalle.numnotent INNER JOIN
                      dbo.MInventario ON dbo.NotEntDetalle.coditems = dbo.MInventario.coditems
WHERE     (dbo.NotaEntrega.fechanot >= '20140805') AND (dbo.NotaEntrega.statunot <> 2)
;


-- Dumping structure for view farmacias.viewpivot
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewpivot";
  create view viewpivot as
  SELECT TOP 1000 [amount]
      ,[medico]
      ,[months]
      ,[periodo]
  FROM [farmacias].[dbo].[viewcompairprod];


-- Dumping structure for view farmacias.viewprodcompair
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewprodcompair";
create view viewprodcompair as
select sum(mf.total) amount,mf.codmedico,me.medico,MONTH(fechafac) months, concat(YEAR(fechafac),'/',MONTH(fechafac)) periodo,year(fechafac) year from mfactura mf 
inner join viewmedicos me on mf.codmedico=me.codmedico
where mf.fechafac between '01-01-2017' and '03-25-2017' and mf.statfact!=2
group by mf.codmedico,me.medico,MONTH(fechafac),YEAR(fechafac);


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPRODLASERSUEROINTRA";
CREATE VIEW dbo.VIEWPRODLASERSUEROINTRA
AS
 SELECT        p.numfactu, p.fechafac, p.codmedico, p.subtotal, p.descuento, p.subtotal total, p.statfact, p.TotImpuesto, p.TotalFac, p.monto_flete, p.doc, p.codsuc, 1 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 
                         'PRODUCTOS' Dventa
FROM            VentasDiarias p INNER JOIN
                         mmedicos m ON p.codmedico = m.codmedico
WHERE        p.statfact <> '2'

UNION ALL

SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 2 tventa, a.medico, 'LASER' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'Terapia Laser' AND a.statfact <> '2'

UNION ALL

SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'LASER' Dventa
FROM            VentasDiariasCMACST_2CEMALASERCIN c
WHERE        c.cod_subgrupo = 'TERAPIA LASER' AND c.statfact <> '2'

UNION ALL

SELECT        s.numfactu, s.fechafac, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.TotImpuesto, s.total AS TotalFac, s.monto_flete, s.doc, s.codsuc, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 
                         'SUERO       ' Dventa
FROM            VentasDiariasCMACST s INNER JOIN
                         mmedicos m ON s.codmedico = m.codmedico
WHERE        s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'


UNION ALL

SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 4 tventa, a.medico, 'INTRAVENOSO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'INTRAVENOSO' AND a.statfact <> '2'


UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'INTRAVENOSO' Dventa
FROM            VentasDiariasCMACST_2CEMAINTRAVCIN c
WHERE        c.cod_subgrupo = 'INTRAVENOSO' AND c.statfact <> '2'
UNION ALL
SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 4 tventa, a.medico, 'INTRAVENOSO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'BLOQUEO' AND a.statfact <> '2'
 
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'BLOQUEO' Dventa
FROM            VentasDiariasCMACST_2CEMABLOQ c
WHERE        c.cod_subgrupo = 'BLOQUEO' AND c.statfact <> '2'
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 
                         'CEL MADRE' Dventa
FROM            VentasDiariasCMACELMADRESnoCons c INNER JOIN
                         mmedicos m ON c.codmedico = m.codmedico
WHERE        c.cod_subgrupo = 'CEL MADRE' AND c.statfact <> '2'

;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRASALES
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPRODLASERSUEROINTRASALES";
CREATE VIEW dbo.VIEWPRODLASERSUEROINTRASALES
AS
SELECT        p.fechafac, p.subtotal total, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'PRODUCTOS' Dventa
FROM            VentasDiarias p INNER JOIN
                         mmedicos m ON p.codmedico = m.codmedico
WHERE        p.statfact <> '2'
UNION ALL
SELECT        a.fechafac, a.total, a.medico, 'LASER' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'Terapia Laser' AND a.statfact <> '2'
UNION ALL
SELECT        s.fechafac, s.subtotal total, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'SUERO' Dventa
FROM            VentasDiariasCMACST s INNER JOIN
                         mmedicos m ON s.codmedico = m.codmedico
WHERE        s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'
UNION ALL
SELECT        a.fechafac, a.total, a.medico, 'INTRAVENOSO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'INTRAVENOSO' AND a.statfact <> '2'
UNION ALL
SELECT        a.fechafac, a.total, a.medico, 'BLOQUEO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'BLOQUEO' 
;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRATITL
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPRODLASERSUEROINTRATITL";
CREATE VIEW dbo.VIEWPRODLASERSUEROINTRATITL
AS
SELECT        p.fechafac, 'PRODUCTOS' Dventa
FROM            VentasDiarias p
WHERE        p.statfact <> '2'
UNION ALL
SELECT        a.fechafac, 'LASER' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'Terapia Laser' AND a.statfact <> '2'
UNION ALL
SELECT        s.fechafac, 'SUERO' Dventa
FROM            VentasDiariasCMACST s
WHERE        s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'
UNION ALL
SELECT        a.fechafac, 'INTRAVENOSO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'INTRAVENOSO' AND a.statfact <> '2'
UNION ALL
SELECT        a.fechafac, 'BLOQUEO' Dventa
FROM            mssfaclaser a
WHERE        cod_subgrupo = 'BLOQUEO' AND a.statfact <> '2'
;


-- Dumping structure for view farmacias.VIEWPRODLASERSUEROINTRA_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWPRODLASERSUEROINTRA_2";
CREATE VIEW dbo.VIEWPRODLASERSUEROINTRA_2
AS
SELECT        p.numfactu, p.fechafac, p.codmedico, p.subtotal, p.descuento, p.subtotal total, p.statfact, p.TotImpuesto, p.TotalFac, p.monto_flete, p.doc, p.codsuc, 1 AS tventa, SUBSTRING(p.mediconame, 1, 1) + ' ' + p.medico medico, 
                         'PRODUCTOS' Dventa
FROM            VentasDiarias_2 p
WHERE        p.statfact <> '2'
UNION ALL
SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 2 tventa, a.medico, 'LASER' Dventa
FROM            mssfaclaser_2 a
WHERE        cod_subgrupo = 'Terapia Laser' AND a.statfact <> '2'
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'LASER' Dventa
FROM            VentasDiariasCMACST_2CEMALASERCIN c
WHERE        c.cod_subgrupo = 'TERAPIA LASER' AND c.statfact <> '2'
UNION ALL
SELECT        s.numfactu, s.fechafac, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.TotImpuesto, s.total AS TotalFac, s.monto_flete, s.doc, s.codsuc, 3 AS tventa, SUBSTRING(s.mediconame, 1, 1) + ' ' + s.medico medico, 
                         'SUERO       ' Dventa
FROM            VentasDiariasCMACST_2 s
WHERE        s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'
UNION ALL
SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 4 tventa, a.medico, 'INTRAVENOSO' Dventa
FROM            mssfaclaser_2 a
WHERE        cod_subgrupo = 'INTRAVENOSO' AND a.statfact <> '2'
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'INTRAVENOSO' Dventa
FROM            VentasDiariasCMACST_2CEMAINTRAVCIN c
WHERE        c.cod_subgrupo = 'INTRAVENOSO' AND c.statfact <> '2'
UNION ALL
SELECT        a.numfactu, a.fechafac, a.codmedico, a.subtotal, a.descuento, a.total, a.statfact, 0 TotImpuesto, 0 TotalFac, 0 monto_flete, a.doc, a.codsuc, 4 tventa, a.medico, 'BLOQUEO' Dventa
FROM            mssfaclaser_2 a
WHERE        cod_subgrupo = 'BLOQUEO' AND a.statfact <> '2'
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'BLOQUEO' Dventa
FROM            VentasDiariasCMACST_2CEMABLOQ c
WHERE        c.cod_subgrupo = 'BLOQUEO' AND c.statfact <> '2'
UNION ALL
SELECT        c.numfactu, c.fechafac, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, c.codsuc, 3 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'EXOSOMAS       ' Dventa
FROM            VentasDiariasCMACST_2CEMAnoCons c
WHERE        c.cod_subgrupo = 'CEL MADRE' AND c.statfact <> '2'
;


-- Dumping structure for view farmacias.viewProLaserSuero
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewProLaserSuero";
CREATE VIEW dbo.viewProLaserSuero
AS
SELECT     p.numfactu, p.nombres, p.fechafac, p.codclien, p.codmedico, p.subtotal, p.descuento, p.subtotal total, p.statfact, p.usuario, p.tipopago, p.TotImpuesto, p.TotalFac, 
                      p.monto_flete, p.doc, p.workstation, p.cancelado, p.codsuc, p.medio, 1 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'PRODUCTOS' Dventa
FROM         [dbo].[VentasDiarias] p INNER JOIN
                      mmedicos m ON p.codmedico = m.codmedico
WHERE     p.statfact <> '2'
UNION ALL
SELECT     l.numfactu, l.nombres, l.fechafac, l.codclien, l.codmedico, l.subtotal, l.descuento, l.subtotal total, l.statfact, l.usuario, l.tipopago, l.TotImpuesto, l.TotalFac, l.monto_flete,
                       l.doc, l.workstation, l.cancelado, l.codsuc, l.medio, 2 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'LASER' Dventa
FROM         [dbo].[VentasDiariasMSS] l INNER JOIN
                      mmedicos m ON l.codmedico = m.codmedico
WHERE     l.statfact <> '2'
UNION ALL
SELECT     s.numfactu, s.nombres, s.fechafac, s.codclien, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.usuario, s.tipopago, s.TotImpuesto, 
                      s.total AS TotalFac, s.monto_flete, s.doc, s.workstation, s.cancelado, s.codsuc, s.medio, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 
                      'SUERO       ' Dventa
FROM         VentasDiariasCMACST s INNER JOIN
                      mmedicos m ON s.codmedico = m.codmedico
WHERE     s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'
UNION ALL
SELECT     s.numfactu, s.nombres, s.fechafac, s.codclien, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.usuario, s.tipopago, s.TotImpuesto, 
                      s.total AS TotalFac, s.monto_flete, s.doc, s.workstation, s.cancelado, s.codsuc, s.medio, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 
                      'CEL MADRE       ' Dventa
FROM         VentasDiariasCMACELMADRESnoCons s INNER JOIN
                      mmedicos m ON s.codmedico = m.codmedico
WHERE     s.cod_subgrupo = 'CEL MADRE' AND s.statfact <> '2'
;


-- Dumping structure for view farmacias.viewProLaserSuero_WC
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewProLaserSuero_WC";
CREATE view viewProLaserSuero_WC as
SELECT        p.numfactu, p.nombres, p.fechafac, p.codclien, p.codmedico, p.subtotal, p.descuento, p.subtotal total, p.statfact, p.usuario, p.tipopago, p.TotImpuesto, p.TotalFac, p.monto_flete, p.doc, p.workstation, p.cancelado, p.codsuc, 
                         p.medio, 1 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'PRODUCTOS' Dventa
FROM            [dbo].[VentasDiarias] p INNER JOIN
                         mmedicos m ON p.codmedico = m.codmedico
WHERE        p.statfact <> '2'
UNION ALL



SELECT        l.numfactu, l.nombres, l.fechafac, l.codclien, l.codmedico, l.subtotal, l.descuento, l.subtotal total, l.statfact, l.usuario, l.tipopago, l.TotImpuesto, l.TotalFac, l.monto_flete, l.doc, l.workstation, l.cancelado, l.codsuc, l.medio, 
                         2 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'LASER' Dventa
FROM            [dbo].[VentasDiariasMSS] l INNER JOIN
                         mmedicos m ON l.codmedico = m.codmedico
WHERE        l.statfact <> '2'

UNION ALL
SELECT        c.numfactu,'' nombres, c.fechafac, '' codclien, c.codmedico, c.subtotal, c.descuento, c.subtotal total, c.statfact, '' usuario, 0 tipopago , c.TotImpuesto, c.total AS TotalFac, c.monto_flete, c.doc, '' workstation, c.cancelado, c.codsuc,0 medio ,  2 AS tventa, SUBSTRING(c.mediconame, 1, 1) + ' ' + c.medico medico, 
                         'LASER' Dventa
FROM            VentasDiariasCMACST_2CEMALASERCIN c
WHERE        c.cod_subgrupo = 'TERAPIA LASER' AND c.statfact <> '2'


UNION ALL
SELECT        s.numfactu, s.nombres, s.fechafac, s.codclien, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.usuario, s.tipopago, s.TotImpuesto, s.total AS TotalFac, s.monto_flete, s.doc, s.workstation, s.cancelado, 
                         s.codsuc, s.medio, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'SUERO       ' Dventa
FROM            VentasDiariasCMACST s INNER JOIN
                         mmedicos m ON s.codmedico = m.codmedico
WHERE        s.cod_subgrupo = 'SUEROTERAPIA' AND s.statfact <> '2'
UNION ALL
SELECT        s.numfactu, s.nombres, s.fechafac, s.codclien, s.codmedico, s.subtotal, s.descuento, s.subtotal total, s.statfact, s.usuario, s.tipopago, s.TotImpuesto, s.total AS TotalFac, s.monto_flete, s.doc, s.workstation, s.cancelado, 
                         s.codsuc, s.medio, 3 AS tventa, SUBSTRING(m.nombre, 1, 1) + ' ' + m.apellido medico, 'CEL MADRE       ' Dventa
FROM            VentasDiariasCMACELMADRESnoCons s INNER JOIN
                         mmedicos m ON s.codmedico = m.codmedico
WHERE        s.cod_subgrupo = 'CEL MADRE' AND s.statfact <> '2';


-- Dumping structure for view farmacias.VIEWRecordRepetido
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWRecordRepetido";


CREATE VIEW dbo.VIEWRecordRepetido
AS
SELECT dbo.Mconsultas.fecha, dbo.Mconsultas.fecha_cita, dbo.MClientes.nombres, dbo.Mmedicos.apellido + N' ' + dbo.Mmedicos.nombre AS medico, 
               dbo.MClientes.Historia, dbo.MClientes.Cedula, dbo.Mconsultas.codclien, 
               CASE WHEN asistido = 3 THEN 'ASISTIO' WHEN asistido = 0 THEN 'NO ASISTIO' ELSE '' END AS asistido
FROM  dbo.Mconsultas INNER JOIN
               dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico INNER JOIN
               dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien


;


-- Dumping structure for view farmacias.viewRepeat
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeat";
CREATE view viewRepeat  as 
SELECT        a.codclien, a.numfactu, a.statfact, a.fechafac, b.coditems, b.cantidad, c.desitems
FROM            dbo.MFactura AS a INNER JOIN
                         dbo.DFactura AS b ON a.numfactu = b.numfactu INNER JOIN
                         dbo.MInventario AS c ON b.coditems = c.coditems
WHERE        (a.statfact <> '2')
--SUERO TERAPIA
UNION
SELECT        a.codclien, a.numfactu, a.statfact, a.fechafac, 'ST' coditems /*b.coditems*/, b.cantidad, c.desitems
FROM            dbo.cma_MFactura AS a INNER JOIN
                         dbo.cma_DFactura AS b ON a.numfactu = b.numfactu INNER JOIN
                         dbo.MInventario AS c ON b.coditems = c.coditems
WHERE        (a.statfact <> '2' AND  B.coditems LIKE '%ST')
--LASER
union 
SELECT        a.codclien, a.numfactu, a.statfact, a.fechafac, 'LS' coditems, b.cantidad, c.desitems
FROM            dbo.MSSMFact AS a INNER JOIN
                         dbo.MSSDFact AS b ON a.numfactu = b.numfactu INNER JOIN
                         dbo.MInventario AS c ON b.coditems = c.coditems
WHERE        (a.statfact <> '2' and b.coditems LIKE 'TD%')
;


-- Dumping structure for view farmacias.viewRepeat2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeat2";
create view [dbo].[viewRepeat2] as
select distinct  a.codclien, a.coditems, (select count(b.codclien) veces from viewRepeat b where a.codclien=b.codclien)
 veces from viewRepeat a
;


-- Dumping structure for view farmacias.viewRepeat3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeat3";

 create view viewRepeat3 as
 select * from viewRepeat2 b
where  veces in (12,11,10,9,8,7,6,5,4,3,2,1);


-- Dumping structure for view farmacias.viewRepeatV4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeatV4";
CREATE View viewRepeatV4 as select a.codclien ,count(*) v ,a.coditems ,a.desitems  ,(SELECT  MAX(b.fechafac) AS Expr1   FROM    dbo.viewRepeat AS b   WHERE   (b.coditems = 'LS'  ) and b.codclien=a.codclien  AND (b.fechafac BETWEEN '20180901'  and '20181017'  )) AS maxi ,(SELECT  min(b.fechafac) AS Expr1   FROM    dbo.viewRepeat AS b   WHERE   (b.coditems = 'LS'  ) and b.codclien=a.codclien  AND (b.fechafac BETWEEN '20180901'  and '20181017'  )) AS mini ,(SELECT  sum(b.cantidad) AS Expr1   FROM    dbo.viewRepeat AS b   WHERE   (b.coditems = 'LS'  ) and b.codclien=a.codclien  AND (b.fechafac BETWEEN '20180901'  and '20181017'  )) AS cantidad from [dbo].[viewRepeat] a where coditems='LS'  and fechafac between  '20180901'  and '20181017'   group by a.codclien , a.coditems,a.desitems ;


-- Dumping structure for view farmacias.viewRepeatV5
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeatV5";
CREATE View viewRepeatV5 as SELECT DISTINCT a.v,'Repeticion de '+CONVERT(varchar(10), a.v)+' => ' +STR((SELECT COUNT(*) FROM viewRepeatV4 B WHERE B.v=a.V)) descripcion,(SELECT COUNT(*) FROM viewRepeatV4 f WHERE f.v=a.V) totalCell FROM  viewRepeatV4 a UNION SELECT 0 v,'Todos =>(Universo de pacientes CMA)'+STR(((SELECT count(*) FROM viewRepeatWHOLE a INNER JOIN  MClientes b ON a.codclien = b.codclien  WHERE len(b.telfhabit) >= 10 AND b.nombre IS NOT NULL))) descripcion,  (SELECT  count(*)  FROM  viewRepeatWHOLE a INNER JOIN  MClientes b ON a.codclien = b.codclien  WHERE  len(b.telfhabit) >= 10 AND b.nombre IS NOT NULL) totalCell;


-- Dumping structure for view farmacias.viewRepeatV5All
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeatV5All";
CREATE View viewRepeatV5All as SELECT  0 as v, count(*) as descripcion FROM viewRepeatWHOLE a inner join MClientes b on a.codclien=b.codclien;


-- Dumping structure for view farmacias.viewRepeatWHOLE
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewRepeatWHOLE";
CREATE View viewRepeatWHOLE as  select codclien,COUNT(*) v, Min(fechafac) mini ,  Max(fechafac) maxi from viewRepeat a where fechafac  BETWEEN '12/01/2018' AND '12/10/2018'  GROUP BY codclien ;


-- Dumping structure for view farmacias.VIEWrepeti2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWrepeti2";


CREATE VIEW dbo.VIEWrepeti2
AS
SELECT TOP 100 PERCENT nombres, Cedula, expresion, historia, codclien, fallecido, inactivo
FROM  dbo.VIEWrepetid malos
WHERE (expresion NOT IN
                   (SELECT buenos.expresion
                    FROM   VIEWrepetid buenos
                    GROUP BY buenos.expresion
                    HAVING COUNT(Buenos.expresion) = 1))
ORDER BY expresion


;


-- Dumping structure for view farmacias.VIEWrepetid
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWrepetid";


CREATE VIEW dbo.VIEWrepetid
AS
SELECT TOP 100 PERCENT nombres, historia, Cedula, codclien, fallecido, inactivo, CHARINDEX(',', nombres) AS Expr1, STUFF(nombres, CHARINDEX(',', 
               nombres), 1, '') AS Expr2, CASE WHEN STUFF(nombres, CHARINDEX(',', nombres), 1, '') IS NULL THEN nombres WHEN STUFF(nombres, CHARINDEX(',', 
               nombres), 1, '') IS NOT NULL THEN STUFF(nombres, CHARINDEX(',', nombres), 1, '') END AS expresion
FROM  dbo.MClientes
ORDER BY expresion


;


-- Dumping structure for view farmacias.VIEWrepetidos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWrepetidos";

CREATE VIEW dbo.VIEWrepetidos
AS
SELECT     TOP 100 PERCENT nombres, Cedula, CHARINDEX(',', nombres) AS Expr1, STUFF(nombres, CHARINDEX(',', nombres), 1, '') AS Expr2, 
                      CASE WHEN STUFF(nombres, CHARINDEX(',', nombres), 1, '') IS NULL THEN nombres WHEN STUFF(nombres, CHARINDEX(',', nombres), 1, '') 
                      IS NOT NULL THEN STUFF(nombres, CHARINDEX(',', nombres), 1, '') END AS expresion
FROM         dbo.MClientes
ORDER BY expresion

;


-- Dumping structure for view farmacias.VIEWrepetidos2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEWrepetidos2";

CREATE VIEW dbo.VIEWrepetidos2
AS
SELECT     TOP 100 PERCENT nombres, Cedula, expresion
FROM         dbo.VIEWrepetidos malos
WHERE     (expresion NOT IN
                          (SELECT     buenos.expresion
                            FROM          VIEWrepetidos buenos
                            GROUP BY buenos.expresion
                            HAVING      COUNT(Buenos.expresion) = 1))
ORDER BY expresion

;


-- Dumping structure for view farmacias.ViewSoloProductos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "ViewSoloProductos";
CREATE VIEW dbo.ViewSoloProductos
AS
SELECT     TOP (100) PERCENT coditems, desitems
FROM         dbo.MInventario
WHERE     (Prod_serv = 'P') AND (activo = 1)
;


-- Dumping structure for view farmacias.viewSSNoasistidos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewSSNoasistidos";
create view viewSSNoasistidos as
SELECT        a.codclien, b.nombres, b.telfhabit, c.nombre + N' ' + LTRIM(c.apellido) AS Medicos, b.Historia, a.citados, 
                         a.confirmado, a.asistido, a.noasistido, a.activa, a.usuario, a.codconsulta, a.fecha_cita, a.hora, 
                        /* dbo.tipoconsulta.descons,*/ a.observacion, b.fallecido,a.id,a.coditems
FROM            dbo.Mconsultass a
inner join dbo.MClientes b on a.codclien=b.codclien
inner join dbo.Mmedicos  c on a.codmedico=c.Codmedico
;


-- Dumping structure for view farmacias.ViewSumPagosPRMSSW7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "ViewSumPagosPRMSSW7";

CREATE VIEW [dbo].[ViewSumPagosPRMSSW7]
AS
SELECT     id_centro, numfactu, fechapago, codforpa, SUM(monto) AS Expr1
FROM         dbo.Mpagos
GROUP BY id_centro, numfactu, fechapago, codforpa
HAVING      (id_centro = 3)

;


-- Dumping structure for view farmacias.ViewSumPagosPRW7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "ViewSumPagosPRW7";
CREATE VIEW dbo.ViewSumPagosPRW7
AS
SELECT     id_centro, numfactu, fechapago, codforpa, SUM(monto) AS Expr1
FROM         dbo.Mpagos
GROUP BY id_centro, numfactu, fechapago, codforpa
HAVING      (id_centro = 1)
;


-- Dumping structure for view farmacias.viewTerapiaDelDolor
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewTerapiaDelDolor";
create view viewTerapiaDelDolor as


select c.nombres,a.numfactu,a.fechafac,a.codclien,b.coditems,b.cantidad from MSSMFact a 
inner join mssdfact b on  a.numfactu=b.numfactu
inner join MClientes c on a.codclien=c.codclien
where a.statfact=3 and a.tipo='01' and a.fechafac between '20160401'  and '20160516' --and a.codclien='1204';


-- Dumping structure for view farmacias.viewtipofacturascma
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewtipofacturascma";
CREATE VIEW dbo.viewtipofacturascma
AS
SELECT DISTINCT a.numfactu, b.cod_subgrupo
FROM            dbo.cma_DFactura AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems
UNION
SELECT DISTINCT a.numnotcre, b.cod_subgrupo
FROM            dbo.CMA_Dnotacredito AS a INNER JOIN
                         dbo.MInventario AS b ON a.coditems = b.coditems
;


-- Dumping structure for view farmacias.viewTipoPagoLaser
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewTipoPagoLaser";
create view viewTipoPagoLaser as
 SELECT 
   a.numfactu, sum( a.monto) monto , a.id_centro, a.tipo_doc,  CASE WHEN  COUNT(*) = 1 THEN max(a.modopago)  ELSE 'SPILT'END modopago 
   FROM 
   VIEWpagosPRMSS a 
   INNER JOIN  MDocumentos b ON 
    a.tipo_doc = b.codtipodoc    
   group by   a.numfactu, a.id_centro, a.tipo_doc 
   union all 
   SELECT  b.numnotcre,sum( b.monto) monto,  b.id_centro, b.tipo_doc,   CASE WHEN  COUNT(*) = 1 THEN max(b.modopago)  ELSE 'SPILT'END modopago 
   FROM   dbo.VIEWPagosDEVMSS b 
   group by   b.numnotcre, b.id_centro, b.tipo_doc;


-- Dumping structure for view farmacias.viewTipoPagoServicios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewTipoPagoServicios";
CREATE VIEW dbo.viewTipoPagoServicios
AS
SELECT     numfactu, CASE WHEN COUNT(*) > 1 THEN 'MIXED' ELSE MAX(DesTipoTargeta) END AS TipoDePago, COUNT(*) AS NumeroDePagos
FROM         dbo.VIEWpagosPRCMA
GROUP BY numfactu
;


-- Dumping structure for view farmacias.viewVentasDiarasGrmSt
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "viewVentasDiarasGrmSt";
CREATE view viewVentasDiarasGrmSt as
  select x.numfactu, x.coditems,sum(x.cantidad) cantidad,y.presentacion from cma_dFactura x
  inner join  MInventario y on x.coditems = y.coditems
 where y.presentacion  is not null
  group by x.numfactu,x.coditems,y.presentacion 
  
;


-- Dumping structure for view farmacias.VIEW_Asistidos_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Asistidos_0215";
CREATE VIEW dbo.VIEW_Asistidos_0215
AS
SELECT     fecha_cita, codmedico, asistido, citacontrol, primera_control, codclien
FROM         dbo.Mconsultas
;


-- Dumping structure for view farmacias.view_audit
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_audit";
CREATE VIEW dbo.view_audit
AS
SELECT     a.codclien, a.fechafac, a.codmedico, numfactu, 'Farmacia' company, a.total, a.usuario, 1 codcompany, id
FROM         MFactura a
WHERE     a.statfact <> '2'
UNION
SELECT     a.codclien, a.fechafac, a.codmedico, numfactu, 'Laser' company, a.total, a.usuario, 3 codcompany, id
FROM         MSSMFact a
WHERE     a.statfact <> '2'
UNION
SELECT     a.codclien, a.fechafac, a.codmedico, a.numfactu, 'Suero' company, a.total, a.usuario, 2 codcompany, a.id
FROM         cma_MFactura a INNER JOIN
                      cma_DFactura b ON a.numfactu = b.numfactu AND CHARINDEX('ST', b.coditems) > 0
where a.statfact <> '2' 
GROUP BY a.codmedico, a.codclien, a.fechafac, a.numfactu, a.total, a.usuario, a.id
UNION
SELECT     a.codclien, a.fechafac, a.codmedico, a.numfactu, 'Consulta' company, a.total, a.usuario, 4 codcompany, a.id
FROM         cma_MFactura a INNER JOIN
                      cma_DFactura b ON a.numfactu = b.numfactu AND CHARINDEX('ST', b.coditems) = 0
where a.statfact <> '2' 
GROUP BY a.codmedico, a.codclien, a.fechafac, a.numfactu, a.total, a.usuario, a.id
;


-- Dumping structure for view farmacias.VIEW_BaseImponible
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_BaseImponible";
CREATE VIEW dbo.VIEW_BaseImponible
AS
SELECT     dbo.VIEW_dimpuesto.numfactu, dbo.VIEW_dimpuesto.Imp1, dbo.VIEW_dimpuesto.Imp2, dbo.VIEW_dimpuesto.TotImpuesto, dbo.MFactura.subtotal, 
                      dbo.MFactura.fechafac AS fecha, dbo.MFactura.cancelado, 'FAC' AS documento, dbo.MFactura.iva, 
                      dbo.MFactura.Impuesto * dbo.MFactura.subtotal AS Base
FROM         dbo.VIEW_dimpuesto INNER JOIN
                      dbo.MFactura ON dbo.VIEW_dimpuesto.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.VIEW_dimpuesto.doc = '01') AND (dbo.MFactura.statfact <> '2')
UNION ALL
SELECT     dbo.VIEW_dimpuesto.numfactu, dbo.VIEW_dimpuesto.Imp1, dbo.VIEW_dimpuesto.Imp2, dbo.VIEW_dimpuesto.TotImpuesto, 
                      dbo.Mnotacredito.SUBTOTAL, dbo.Mnotacredito.fechanot AS fecha, dbo.Mnotacredito.cancelado, 'NC' AS documento, dbo.Mnotacredito.iva, 
                      dbo.Mnotacredito.Impuesto * dbo.Mnotacredito.SUBTOTAL * - 1 AS Base
FROM         dbo.VIEW_dimpuesto INNER JOIN
                      dbo.Mnotacredito ON dbo.VIEW_dimpuesto.numfactu = dbo.Mnotacredito.numnotcre
WHERE     (dbo.VIEW_dimpuesto.doc = '04') AND (dbo.Mnotacredito.statnc <> '2')
;


-- Dumping structure for view farmacias.VIEW_ChequeaFacturas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ChequeaFacturas";
CREATE VIEW dbo.VIEW_ChequeaFacturas
AS
SELECT     TOP 100 PERCENT dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.subtotal, SUM(dbo.DFactura.cantidad * dbo.DFactura.precunit) 
                      AS bruto, dbo.MFactura.descuento, SUM(dbo.DFactura.descuento) AS descuentosi, dbo.MFactura.TotImpuesto, SUM(dbo.DFactura.monto_imp) 
                      AS impuestos, dbo.MFactura.monto_flete, dbo.MFactura.total, 
                      SUM(dbo.DFactura.cantidad * dbo.DFactura.precunit - dbo.DFactura.descuento + dbo.DFactura.monto_imp) AS totalitems
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu
GROUP BY dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.TotImpuesto, 
                      dbo.MFactura.monto_flete, dbo.MFactura.total
ORDER BY dbo.MFactura.numfactu DESC
;


-- Dumping structure for view farmacias.VIEW_cierreInventario
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_cierreInventario";

CREATE VIEW dbo.VIEW_cierreInventario
AS
SELECT dbo.MInventario.desitems, 
    dbo.DCierreInventario.fechacierre, 
    dbo.DCierreInventario.existencia, 
    dbo.DCierreInventario.ventas, 
    dbo.DCierreInventario.anulaciones, 
    dbo.DCierreInventario.ajustes, 
    dbo.DCierreInventario.InvPosible, 
    dbo.DCierreInventario.InvActual, dbo.DCierreInventario.fallas, 
    dbo.DCierreInventario.coditems
FROM dbo.DCierreInventario LEFT OUTER JOIN
    dbo.MInventario ON 
    dbo.DCierreInventario.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.view_cma_consultas_detalle_facturas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_cma_consultas_detalle_facturas";
CREATE view view_cma_consultas_detalle_facturas as 
select * from cma_DFactura  where CHARINDEX('ST', coditems) = 0;


-- Dumping structure for view farmacias.VIEW_CMA_DescFactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_DescFactura";

CREATE VIEW dbo.VIEW_CMA_DescFactura
AS
SELECT     dbo.Mdescuentos.desdesct, dbo.CMA_MDesctFactura.total, dbo.CMA_MDesctFactura.base, dbo.Mdescuentos.porcentaje, 
                      dbo.CMA_MDesctFactura.codesc, dbo.CMA_MDesctFactura.numfactu, dbo.CMA_MDesctFactura.horareg
FROM         dbo.CMA_MDesctFactura LEFT OUTER JOIN
                      dbo.Mdescuentos ON dbo.CMA_MDesctFactura.codesc = dbo.Mdescuentos.codesc

;


-- Dumping structure for view farmacias.VIEW_cma_dfactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_cma_dfactura";

CREATE VIEW dbo.VIEW_cma_dfactura
AS
SELECT dbo.MInventario.desitems, dbo.CMA_DFactura.cantidad, 
    dbo.CMA_DFactura.precunit, dbo.CMA_DFactura.numfactu, 
    dbo.CMA_DFactura.coditems
FROM dbo.CMA_DFactura LEFT OUTER JOIN
    dbo.MInventario ON 
    dbo.CMA_DFactura.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_cma_dfactura_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_cma_dfactura_1";
CREATE VIEW dbo.VIEW_cma_dfactura_1
AS
SELECT     TOP (100) PERCENT dbo.MInventario.desitems, dbo.cma_DFactura.cantidad, dbo.cma_DFactura.precunit, dbo.cma_DFactura.descuento, 
                      dbo.cma_DFactura.monto_imp AS Impuesto, 
                      ROUND(dbo.cma_DFactura.precunit * dbo.cma_DFactura.cantidad - dbo.cma_DFactura.descuento + dbo.cma_DFactura.monto_imp, 2) AS subtotal, 
                      dbo.cma_DFactura.numfactu, dbo.cma_DFactura.coditems, dbo.MInventario.Prod_serv, dbo.cma_DFactura.fechafac, dbo.cma_DFactura.aplicadcto, 
                      dbo.cma_DFactura.aplicacommed, dbo.cma_DFactura.aplicacomtec, dbo.cma_DFactura.costo, dbo.cma_DFactura.aplicaiva
FROM         dbo.cma_DFactura LEFT OUTER JOIN
                      dbo.MInventario ON dbo.cma_DFactura.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_Mfactura";
CREATE VIEW dbo.VIEW_CMA_Mfactura
AS
SELECT     dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.MClientes.nombres, dbo.cma_MFactura.statfact, dbo.cma_MFactura.subtotal, 
                      dbo.cma_MFactura.iva, dbo.cma_MFactura.total
FROM         dbo.cma_MFactura LEFT OUTER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_Mfactura_1";
CREATE VIEW dbo.VIEW_CMA_Mfactura_1
AS
SELECT     dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, 
                      dbo.cma_MFactura.statfact, dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.iva AS Impuesto1, 
                      dbo.cma_MFactura.total + dbo.cma_MFactura.monto_flete AS total, dbo.Status.Status, dbo.cma_MFactura.totalpvp, dbo.cma_MFactura.TotImpuesto AS Impuesto2, 
                      dbo.cma_MFactura.Id, dbo.CMA_Mnotacredito.numnotcre, dbo.MClientes.Historia, dbo.cma_MFactura.usuario
FROM         dbo.cma_MFactura LEFT OUTER JOIN
                      dbo.CMA_Mnotacredito ON dbo.cma_MFactura.numfactu = dbo.CMA_Mnotacredito.numfactu LEFT OUTER JOIN
                      dbo.Status ON dbo.cma_MFactura.statfact = dbo.Status.statfact LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.cma_MFactura.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_Mfactura_2";
CREATE VIEW dbo.VIEW_CMA_Mfactura_2
AS
SELECT     dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, 
                      dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, 
                      dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo AS doc, dbo.cma_MFactura.workstation, 
                      dbo.cma_MFactura.cancelado, '01' AS codsuc, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, '' numnotcre, 
                      dbo.cma_MFactura.id, max(CONCAT(b.nombre, '', b.apellido)) medico, max(s.destatus) Status, max(dbo.MClientes.historia) historia
FROM         dbo.cma_MFactura LEFT OUTER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.viewtipofacturascma AS c ON dbo.cma_MFactura.numfactu = c.numfactu INNER JOIN
                      dbo.cma_DFactura AS d ON dbo.cma_MFactura.numfactu = d .numfactu LEFT JOIN
                      dbo.Mmedicos b ON dbo.cma_MFactura.codmedico = b.Codmedico INNER JOIN
                      dbo.Mstatus s ON dbo.cma_MFactura.statfact = s.status
GROUP BY dbo.cma_MFactura.numfactu, dbo.MClientes.nombres, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codclien, dbo.cma_MFactura.codmedico, 
                      dbo.cma_MFactura.subtotal, dbo.cma_MFactura.descuento, dbo.cma_MFactura.total, dbo.cma_MFactura.statfact, dbo.cma_MFactura.usuario, 
                      dbo.cma_MFactura.tipopago, dbo.cma_MFactura.TotImpuesto, dbo.cma_MFactura.monto_flete, dbo.cma_MFactura.tipo, dbo.cma_MFactura.workstation, 
                      dbo.cma_MFactura.cancelado, dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, d .coditems, dbo.cma_MFactura.id
UNION ALL
SELECT     CMA_Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot AS fechafac, CMA_Mnotacredito.codclien, 
                      CMA_Mnotacredito.codmedico, CMA_Mnotacredito.subtotal * - 1 AS subtotal, CMA_Mnotacredito.descuento * - 1 AS descuento, CMA_Mnotacredito.totalnot * - 1 AS total, 
                      CMA_Mnotacredito.statnc AS statfact, CMA_Mnotacredito.usuario, CMA_Mnotacredito.tipopago, CMA_Mnotacredito.totimpuesto * - 1 AS totimpuesto, 
                      CMA_Mnotacredito.monto_flete * - 1 AS monto_flete, CMA_Mnotacredito.tipo AS Doc, CMA_Mnotacredito.workstation, CMA_Mnotacredito.cancelado, '01' AS codsuc, 
                      dbo.MClientes.medio, c.cod_subgrupo, d .cantidad, SUM(d .cantidad) AS tocantidad, d .coditems, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.id, 
                      max(CONCAT(b.nombre, '', b.apellido)) medico, max(s.destatus) Status, max(dbo.MClientes.historia) historia
FROM         CMA_Mnotacredito LEFT JOIN
                      dbo.MClientes ON CMA_Mnotacredito.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.viewtipofacturascma AS c ON CMA_Mnotacredito.numfactu = c.numfactu INNER JOIN
                      CMA_Dnotacredito AS d ON CMA_Mnotacredito.numnotcre = d .numnotcre INNER JOIN
                      dbo.Mmedicos b ON dbo.CMA_Mnotacredito.codmedico = b.Codmedico INNER JOIN
                      dbo.Mstatus s ON dbo.CMA_Mnotacredito.statnc = s.status
GROUP BY CMA_Mnotacredito.numnotcre, dbo.MClientes.nombres, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codclien, CMA_Mnotacredito.codmedico, 
                      CMA_Mnotacredito.subtotal, CMA_Mnotacredito.descuento, CMA_Mnotacredito.totalnot, CMA_Mnotacredito.statnc, CMA_Mnotacredito.usuario, 
                      CMA_Mnotacredito.tipopago, CMA_Mnotacredito.TotImpuesto, CMA_Mnotacredito.monto_flete, CMA_Mnotacredito.tipo, CMA_Mnotacredito.workstation, 
                      CMA_Mnotacredito.cancelado, dbo.MClientes.medio;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_Mfactura_3";
CREATE view VIEW_CMA_Mfactura_3 as
SELECT DISTINCT a.numfactu , max( c.coditems)  coditems
,c.nombres
,c.fechafac
,c.codclien
,c.codmedico
,c.subtotal
,c.descuento
,c.total
,c.statfact
,c.usuario
,c.tipopago
,c.TotImpuesto
,c.monto_flete
,c.doc
,c.workstation
,c.cancelado
,c.codsuc
,c.medio
,c.cod_subgrupo
,c.cantidad
,c.tocantidad
,c.numnotcre
,c.id
,c.medico
,c.Status
,c.historia
from VIEW_CMA_Mfactura_2 a
left join (Select * FROM  VIEW_CMA_Mfactura_2 b )  c on a.numfactu=c.numfactu
WHERE 1= 1
group by a.numfactu,c.nombres
,c.fechafac
,c.codclien
,c.codmedico
,c.subtotal
,c.descuento
,c.total
,c.statfact
,c.usuario
,c.tipopago
,c.TotImpuesto
,c.monto_flete
,c.doc
,c.workstation
,c.cancelado
,c.codsuc
,c.medio
,c.cod_subgrupo
,c.cantidad
,c.tocantidad
,c.numnotcre
,c.id
,c.medico
,c.Status
,c.historia;


-- Dumping structure for view farmacias.VIEW_CMA_Mfactura_4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CMA_Mfactura_4";
create view VIEW_CMA_Mfactura_4 as
SELECT DISTINCT      a.numfactu,  a.cod_subgrupo, c.nombres, c.fechafac, c.codclien, c.codmedico, c.subtotal, c.descuento, c.total, c.statfact, c.usuario, c.tipopago, 
                      c.TotImpuesto, c.monto_flete, c.doc, c.workstation, c.cancelado, c.codsuc, c.medio,  c.numnotcre, c.id, c.medico, c.Status, 
                      c.historia
FROM         dbo.VIEW_CMA_Mfactura_2 AS a 
LEFT OUTER JOIN              (SELECT     numfactu, nombres, fechafac, codclien, codmedico, subtotal, descuento, total, statfact, usuario, tipopago, TotImpuesto, monto_flete, doc, workstation, 
                                                   cancelado, codsuc, medio, cod_subgrupo, cantidad,coditems, numnotcre, id, medico, Status, historia
                            FROM          dbo.VIEW_CMA_Mfactura_2 AS b) AS c ON a.numfactu = c.numfactu
WHERE     (1 = 1) 
GROUP BY a.numfactu, c.nombres, c.fechafac, c.codclien, c.codmedico, c.subtotal, c.descuento, c.total, c.statfact, c.usuario, c.tipopago, c.TotImpuesto, c.monto_flete, c.doc, 
                      c.workstation, c.cancelado, c.codsuc, c.medio, a.cod_subgrupo,c.numnotcre, c.id, c.medico, c.Status, c.historia;


-- Dumping structure for view farmacias.view_cma_suero_detalle_facturas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_cma_suero_detalle_facturas";
create view view_cma_suero_detalle_facturas as 
select * from cma_DFactura  where CHARINDEX('ST', coditems) > 0
;


-- Dumping structure for view farmacias.view_cma__detalle_devolucion
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_cma__detalle_devolucion";
create view view_cma__detalle_devolucion as
select distinct numnotcre ,fechanot , MAX(coditems) coditems,SUM(cantidad) cantidad  from CMA_Dnotacredito 
group by numnotcre ,fechanot 
;


-- Dumping structure for view farmacias.view_cma__detalle_facturas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_cma__detalle_facturas";
create view view_cma__detalle_facturas as
select distinct numfactu,fechafac, MAX(coditems) coditems,SUM(cantidad) cantidad  from cma_DFactura  
group by numfactu,fechafac
;


-- Dumping structure for view farmacias.view_cocientes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_cocientes";
CREATE View view_cocientes as Select  t1.coditems, t1.precunit,t1.costo ,(t2.ventas-t2.anulaciones)-(t2.NotasCreditos)ventas, t2.fecha, t2.anulaciones anuladas,t1.desitems producto
  from (select pr.coditems, pr.precunit,mi.costo,mi.desitems from mprecios pr inner join minventario mi on pr.coditems=mi.coditems and mi.Prod_serv='P' where codtipre='00' and  mi.activo=1  ) as t1 join ( select ci.fechacierre fecha, ci.coditems, ci.ventas,ci.anulaciones ,ci.NotasCreditos  from dcierreinventario ci) as t2 on t1.coditems=t2.coditems;


-- Dumping structure for view farmacias.VIEW_comisioinesMed
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_comisioinesMed";

CREATE VIEW dbo.VIEW_comisioinesMed
AS
SELECT     dbo.CMA_DFactura.numfactu, dbo.CMA_DFactura.fechafac, dbo.CMA_DFactura.coditems, dbo.CMA_DFactura.cantidad, dbo.CMA_DFactura.precunit, 
                      dbo.CMA_DFactura.aplicacommed, dbo.CMA_MFactura.statfact, dbo.CMA_DFactura.tipoitems, dbo.MInventario.desitems, 
                      dbo.CMA_MFactura.codmedico, dbo.CMA_DFactura.descuento
FROM         dbo.CMA_DFactura LEFT OUTER JOIN
                      dbo.MInventario ON dbo.CMA_DFactura.coditems = dbo.MInventario.coditems LEFT OUTER JOIN
                      dbo.CMA_MFactura ON dbo.CMA_DFactura.numfactu = dbo.CMA_MFactura.numfactu
UNION ALL
SELECT     dbo.DFactura.numfactu, dbo.DFactura.fechafac, dbo.DFactura.coditems, dbo.DFactura.cantidad, dbo.DFactura.precunit, dbo.DFactura.aplicacommed, 
                      dbo.MFactura.statfact, dbo.DFactura.tipoitems, dbo.MInventario.desitems, dbo.MFactura.codmedico, dbo.DFactura.descuento
FROM         dbo.DFactura LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems LEFT OUTER JOIN
                      dbo.MFactura ON dbo.dFactura.numfactu = dbo.MFactura.numfactu

;


-- Dumping structure for view farmacias.VIEW_comisiones
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_comisiones";


CREATE VIEW dbo.VIEW_comisiones
AS
SELECT     dbo.EstadisticasGlobales.codsuc, dbo.EstadisticasGlobales.fecha, dbo.EstadisticasGlobales.codmedico, dbo.EstadisticasGlobales.comisiones, 
                      dbo.EstadisticasGlobales.comServ, dbo.EstadisticasGlobales.NC, dbo.Mmedicos.activo, dbo.Mmedicos.nombre, dbo.Mmedicos.apellido
FROM         dbo.EstadisticasGlobales LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.EstadisticasGlobales.codmedico = dbo.Mmedicos.Codmedico COLLATE Modern_Spanish_CI_AS


;


-- Dumping structure for view farmacias.VIEW_Compras
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Compras";

CREATE VIEW dbo.VIEW_Compras
AS
SELECT     dbo.MInventario.desitems, dbo.Dcompra.cantidad, dbo.Dcompra.factcomp, dbo.Dcompra.coditems
FROM         dbo.Dcompra LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dcompra.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_consolidado
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_consolidado";

CREATE VIEW dbo.VIEW_consolidado
AS
SELECT     numfactu, subtotal, descuento, total, fechafac, statfact, tipopago, usuario, workstation, TotImpuesto, 1 AS tipo
FROM         dbo.MFactura
UNION ALL
SELECT     cma_mfactura.numfactu, cma_mfactura.subtotal, cma_mfactura.descuento, cma_mfactura.total, cma_mfactura.fechafac, cma_mfactura.statfact, 
                      cma_mfactura.tipopago, cma_mfactura.usuario, workstation, 0 AS totimpuesto, 2 AS tipo
FROM         cma_mfactura
UNION ALL
SELECT     numnotcre AS numfactu, totalnot * - 1 AS subtotal, 0 AS descuento, totalnot * - 1 AS total, fechanot AS fechafac, statnc AS statfact, 0 AS tipopago, 
                      usuario, workstation, 0 AS totimpuesto, 3 AS tipo
FROM         dbo.mnotacredito

;


-- Dumping structure for view farmacias.VIEW_ConsProductosV
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ConsProductosV";
CREATE VIEW dbo.VIEW_ConsProductosV
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.DFactura.coditems, dbo.MFactura.codclien, dbo.DFactura.cantidad
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu
WHERE     (dbo.MFactura.statfact <> 2)
;


-- Dumping structure for view farmacias.VIEW_ConsultaServicios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ConsultaServicios";
CREATE VIEW dbo.VIEW_ConsultaServicios
AS
SELECT     '07' AS codcons, desitems AS descons, '9' AS tipo, coditems, '07' + coditems AS codconsulta
FROM         dbo.MInventario
WHERE     (Prod_serv = 'S') AND (activo = 1) AND (cod_grupo = '004')
;


-- Dumping structure for view farmacias.VIEW_ConsultasMed
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ConsultasMed";
CREATE VIEW dbo.VIEW_ConsultasMed
AS
SELECT     TOP 100 PERCENT codclien, fecha, fecha_cita, primera_control, asistido, codmedico,
                          (SELECT     SUM(((cantidad * precunit) - dfactura.descuento)) AS Bs
                            FROM          dfactura INNER JOIN
                                                   mfactura ON dfactura.numfactu = mfactura.numfactu
                            WHERE      mfactura.statfact <> '2' AND dfactura.fechafac = mconsultas.fecha_cita AND mfactura.codclien = mconsultas.codclien AND 
                                                   dfactura.aplicacommed = '1') AS MONP_Bs,
                          (SELECT     SUM(total) AS Bs
                            FROM          cma_mfactura
                            WHERE      cma_mfactura.statfact <> '2' AND cma_mfactura.fechafac = mconsultas.fecha_cita AND cma_mfactura.codclien = mconsultas.codclien) 
                      AS MONS_Bs, 1 AS items, activa
FROM         dbo.Mconsultas
WHERE     (asistido = '3') AND (activa = '1')
;


-- Dumping structure for view farmacias.VIEW_ConsultasPacientes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ConsultasPacientes";
CREATE view VIEW_ConsultasPacientes as SELECT     TOP 100 PERCENT codclien, Cedula, nombres, (SELECT     isnull(COUNT(*),0) From mconsultas  WHERE      codclien = mclientes.codclien AND asistido = 3 AND activa = '1' AND FEcha_cita >= '' and fecha_cita <= '' and primera_control='0') AS cita_nro, (SELECT     isnull(COUNT(*),0) From mconsultas  WHERE      codclien = mclientes.codclien AND asistido = 3 AND activa = '1' AND FEcha_cita >= '' and fecha_cita <= '' and primera_control='1') AS item2,codmedico  FROM  dbo.MClientes ;


-- Dumping structure for view farmacias.VIEW_Consulta_Serv
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Consulta_Serv";
CREATE VIEW dbo.VIEW_Consulta_Serv
AS
SELECT     dbo.MconsultaSS.codclien, dbo.MClientes.nombres, dbo.MClientes.Historia, dbo.MInventario.desitems, 
                      dbo.Mmedicos.nombre + '  ' + dbo.Mmedicos.apellido AS medico, dbo.MClientes.telfhabit, dbo.MClientes.Cedula, dbo.MconsultaSS.fecha_cita, 
                      dbo.MconsultaSS.citados, dbo.MconsultaSS.confirmado, dbo.MconsultaSS.asistido, dbo.MconsultaSS.noasistido, dbo.MconsultaSS.activa, 
                      dbo.MconsultaSS.observacion, dbo.MconsultaSS.usuario, dbo.MconsultaSS.NoCitados
FROM         dbo.MconsultaSS INNER JOIN
                      dbo.MClientes ON dbo.MconsultaSS.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.Mmedicos ON dbo.MconsultaSS.codmedico = dbo.Mmedicos.Codmedico INNER JOIN
                      dbo.MInventario ON dbo.MconsultaSS.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_ControlServicios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ControlServicios";


CREATE VIEW dbo.VIEW_ControlServicios
AS
SELECT     TOP 100 PERCENT dbo.Controlservicios.fechaServicio, dbo.MClientes.nombres, dbo.MInventario.desitems, dbo.Controlservicios.cantidad, 
                      UPPER(dbo.Mmedicos.nombre) + ' ' + UPPER(dbo.Mmedicos.apellido) AS medico, dbo.Controlservicios.codclien, dbo.Controlservicios.coditems, 
                      dbo.Controlservicios.Codmedico
FROM         dbo.Controlservicios INNER JOIN
                      dbo.MClientes ON dbo.Controlservicios.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.MInventario ON dbo.Controlservicios.coditems = dbo.MInventario.coditems INNER JOIN
                      dbo.Mmedicos ON dbo.Controlservicios.Codmedico = dbo.Mmedicos.Codmedico
ORDER BY dbo.Controlservicios.fechaServicio DESC, dbo.MClientes.nombres, dbo.MInventario.desitems


;


-- Dumping structure for view farmacias.VIEW_CreaPreciosRemoto
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_CreaPreciosRemoto";
CREATE VIEW dbo.VIEW_CreaPreciosRemoto
AS
SELECT     dbo.MInventario.nombre_alterno, dbo.MInventario.coditems, dbo.MInventario.desitems, dbo.MInventario.fecing, dbo.MInventario.Exisminima, 
                      dbo.MInventario.Exismaxima, dbo.MInventario.activo, dbo.MInventario.aplicaIva, dbo.MInventario.aplicadcto, dbo.MInventario.aplicaComMed, 
                      dbo.MInventario.aplicaComTec, dbo.MInventario.usuario, dbo.MInventario.workstation, dbo.MInventario.ipaddress, dbo.MInventario.fecreg, 
                      dbo.NTPRODUCTOS.CapsulasXUni, dbo.MInventario.Prod_serv
FROM         dbo.MInventario LEFT OUTER JOIN
                      dbo.NTPRODUCTOS ON dbo.MInventario.coditems = dbo.NTPRODUCTOS.Cod_prod
WHERE     (dbo.MInventario.activo = 1)
;


-- Dumping structure for view farmacias.VIEW_Cuadre
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Cuadre";


CREATE VIEW dbo.VIEW_Cuadre
AS
SELECT     numfactu, fechafac, total, statfact, tipopago, 1 AS tipo, 'FACTURAS DE PRODUCTOS' AS tipodoc, isnull(usuario, ' ') AS usuario, 
                      'INGRESOS' AS operacion
FROM         dbo.MFactura
WHERE     tipopago = 0
UNION ALL
SELECT     dbo.CMA_MFactura.numfactu, dbo.CMA_MFactura.fechafac, dbo.CMA_MFactura.total, dbo.CMA_MFactura.statfact, dbo.CMA_MFactura.tipopago, 
                      2 AS tipo, 'FACTURAS DE SERVICIO' AS tipodoc, isnull(usuario, ' ') AS usuario, 'INGRESOS' AS operacion
FROM         dbo.CMA_MFactura
WHERE     tipopago = 0
UNION ALL
SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago AS fechafac, dbo.Mpagos.monto AS total, dbo.MFactura.statfact, dbo.MFactura.tipopago, '1' AS tipo, 
                      'COBRANZA FACTURA PRODUCTOS' AS tipodoc, isnull(mpagos.usuario, ' ') AS usuario, 'INGRESOS' AS operacion
FROM         dbo.Mpagos INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.MFactura.tipopago = 1)
UNION ALL
SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago AS fechafac, dbo.Mpagos.monto AS total, dbo.CMA_MFactura.statfact, dbo.CMA_MFactura.tipopago, 
                      '2' AS tipo, 'COBRANZA FACTURA SERVICIOS' AS tipodoc, isnull(mpagos.usuario, ' ') AS usuario, 'INGRESOS' AS operacion
FROM         dbo.Mpagos INNER JOIN
                      dbo.CMA_MFactura ON dbo.Mpagos.numfactu = dbo.CMA_MFactura.numfactu
WHERE     (dbo.CMA_MFactura.tipopago = 1)
UNION ALL
SELECT     numfactu, fechanul, (total * - 1) AS total, statfact, tipopago, 1 AS tipo, 'FACTURAS ANULADAS PRODUCTOS' AS tipodoc, isnull(usuario, ' ') AS usuario, 
                      'EGRESOS' AS operacion
FROM         dbo.MFactura
WHERE     statfact = 2 AND tipopago = 0
UNION ALL
SELECT     dbo.CMA_MFactura.numfactu, dbo.CMA_MFactura.fechanul, (dbo.CMA_MFactura.total * - 1) AS total, dbo.CMA_MFactura.statfact, 
                      dbo.CMA_MFactura.tipopago, 2 AS tipo, 'FACTURAS ANULADAS SERVICIOS' AS tipodoc, isnull(usuario, ' ') AS usuario, 'EGRESOS' AS operacion
FROM         dbo.CMA_MFactura
WHERE     statfact = 2 AND tipopago = 0
UNION ALL
SELECT     numnotcre AS numfactu, fechanot AS fechafac, totalnot * - 1 AS total, statnc AS statfact, 0, 1 AS tipo, 'NOTAS DE CREDITO' AS tipodoc, isnull(usuario, ' ') 
                      AS usuario, 'EGRESOS' AS operacion
FROM         dbo.mnotacredito


;


-- Dumping structure for view farmacias.VIEW_Dajuste
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dajuste";

CREATE VIEW dbo.VIEW_Dajuste
AS
SELECT MInventario.desitems, Dajustes.cantidad, 
    Dajustes.coditems, Dajustes.codajus
FROM dbo.Dajustes LEFT OUTER JOIN
    dbo.MInventario ON 
    dbo.Dajustes.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_dajuste_inv
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dajuste_inv";

CREATE VIEW dbo.VIEW_dajuste_inv
AS
SELECT     dbo.MInventario.desitems, dbo.Dajustes.cantidad, dbo.Dajustes.coditems, dbo.Dajustes.codajus, dbo.Dajustes.fechajust, dbo.Dajustes.usuario, 
                      dbo.Dajustes.workstation, dbo.Dajustes.fecreg, dbo.Dajustes.horareg, dbo.Majustes.numfactu, dbo.Majustes.observacion
FROM         dbo.Dajustes LEFT OUTER JOIN
                      dbo.Majustes ON dbo.Dajustes.codajus = dbo.Majustes.codajus LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dajustes.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_Dcompras
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dcompras";

CREATE VIEW dbo.VIEW_Dcompras
AS
SELECT     dbo.MInventario.desitems, dbo.Dcompra.cantidad, dbo.Dcompra.factcomp, dbo.Dcompra.coditems
FROM         dbo.Dcompra LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dcompra.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_Dcompras_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dcompras_1";


CREATE VIEW dbo.VIEW_Dcompras_1
AS
SELECT     dbo.MInventario.desitems, dbo.Dcompra.cantidad, dbo.Dcompra.factcomp, dbo.Dcompra.coditems, dbo.Dcompra.costo, 
                      dbo.Dcompra.cantidad * dbo.Dcompra.costo AS total
FROM         dbo.Dcompra LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dcompra.coditems = dbo.MInventario.coditems


;


-- Dumping structure for view farmacias.VIEW_DCotizacion
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DCotizacion";


CREATE VIEW dbo.VIEW_DCotizacion
AS
SELECT     TOP 100 PERCENT dbo.DCotizacion.numcot, dbo.DCotizacion.coditems, { fn UCASE(dbo.MInventario.desitems) } AS desitems, dbo.DCotizacion.Dosis, 
                      dbo.DCotizacion.Capsulas, dbo.DCotizacion.precunit, dbo.DCotizacion.cantidad, dbo.DCotizacion.precunit * dbo.DCotizacion.cantidad AS subtotal
FROM         dbo.MCotizacion INNER JOIN
                      dbo.DCotizacion INNER JOIN
                      dbo.MInventario ON dbo.DCotizacion.coditems = dbo.MInventario.coditems ON dbo.MCotizacion.numcot = dbo.DCotizacion.numcot
ORDER BY dbo.DCotizacion.numcot, dbo.MInventario.desitems


;


-- Dumping structure for view farmacias.VIEW_DescFactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DescFactura";

CREATE VIEW dbo.VIEW_DescFactura
AS
SELECT     dbo.Mdescuentos.desdesct, dbo.MDesctFactura.total, dbo.MDesctFactura.base, dbo.Mdescuentos.porcentaje, dbo.MDesctFactura.codesc, 
                      dbo.MDesctFactura.numfactu, dbo.MDesctFactura.horareg
FROM         dbo.MDesctFactura LEFT OUTER JOIN
                      dbo.Mdescuentos ON dbo.MDesctFactura.codesc = dbo.Mdescuentos.codesc

;


-- Dumping structure for view farmacias.VIEW_DescFacturaCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DescFacturaCMA";
CREATE VIEW dbo.VIEW_DescFacturaCMA
AS
SELECT     dbo.Mdescuentos.desdesct, dbo.MDesctFacturaCMA.total, dbo.MDesctFacturaCMA.base, dbo.Mdescuentos.porcentaje, 
                      dbo.MDesctFacturaCMA.codesc, dbo.MDesctFacturaCMA.numfactu, dbo.MDesctFacturaCMA.horareg
FROM         dbo.MDesctFacturaCMA LEFT OUTER JOIN
                      dbo.Mdescuentos ON dbo.MDesctFacturaCMA.codesc = dbo.Mdescuentos.codesc
;


-- Dumping structure for view farmacias.VIEW_DESCRIP_PAGOS1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DESCRIP_PAGOS1";
CREATE VIEW dbo.VIEW_DESCRIP_PAGOS1
AS
SELECT     dbo.Mpagos.numfactu, dbo.MTipoTargeta.DesTipoTargeta
FROM         dbo.Mpagos INNER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta
WHERE     (dbo.Mpagos.monto > 0) AND (dbo.Mpagos.tipo_doc = 'FAC')
GROUP BY dbo.Mpagos.numfactu, dbo.MTipoTargeta.DesTipoTargeta
;


-- Dumping structure for view farmacias.VIEW_DESCRIP_PAGOS2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DESCRIP_PAGOS2";
CREATE VIEW dbo.VIEW_DESCRIP_PAGOS2
AS
SELECT DISTINCT TOP 100 PERCENT numfactu, DesTipoTargeta
FROM         dbo.VIEW_DESCRIP_PAGOS1
WHERE     (numfactu NOT IN
                          (SELECT     NUMFACTU
                            FROM          VIEW_DESCRIP_PAGOS1 AS TMP
                            GROUP BY NUMFACTU
                            HAVING      COUNT(NUMFACTU) > 1))
ORDER BY numfactu
;


-- Dumping structure for view farmacias.VIEW_DetImpxFact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DetImpxFact";
CREATE VIEW dbo.VIEW_DetImpxFact
AS
SELECT     numfactu, ROUND(TotImpuesto * p1 / pt, 2) AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) AS Imp2, TotImpuesto
FROM         dbo.VIEW_ImpuestosxFact
;


-- Dumping structure for view farmacias.VIEW_DetImpxNC
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DetImpxNC";
CREATE VIEW dbo.VIEW_DetImpxNC
AS
SELECT     numnotcre, ROUND(TotImpuesto * p1 / pt, 2) AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) AS Imp2, TotImpuesto
FROM         dbo.VIEW_ImpuestosxNC
;


-- Dumping structure for view farmacias.VIEW_DevCompra
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_DevCompra";

CREATE VIEW dbo.VIEW_DevCompra
AS
SELECT     dbo.MInventario.desitems, dbo.DevCompra.cantidad, dbo.DevCompra.factcomp, dbo.DevCompra.coditems
FROM         dbo.DevCompra LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DevCompra.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_Dfactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dfactura";

CREATE VIEW dbo.VIEW_Dfactura
AS
SELECT MInventario.desitems, DFactura.cantidad, 
    DFactura.precunit, 
    ROUND((DFactura.precunit * DFactura.cantidad), 2) 
    AS subtotal, DFactura.numfactu, DFactura.coditems, 
    MInventario.Prod_serv
FROM dbo.DFactura LEFT OUTER JOIN
    dbo.MInventario ON 
    dbo.DFactura.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_dfactura1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dfactura1";

CREATE VIEW dbo.VIEW_dfactura1
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.DFactura.coditems, dbo.DFactura.cantidad, 
                      dbo.MInventario.desitems, dbo.DFactura.precunit, 'FACT' AS tipo
FROM         dbo.MInventario INNER JOIN
                      dbo.DFactura ON dbo.MInventario.coditems = dbo.DFactura.coditems INNER JOIN
                      dbo.MFactura ON dbo.DFactura.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.MFactura.statfact <> '2') AND (dbo.DFactura.tipoitems = 'P')
UNION
SELECT     dbo.NotaEntrega.numnotent, dbo.NotaEntrega.fechanot, dbo.NotaEntrega.codclien, '000' AS Medico, dbo.NotEntDetalle.coditems, 
                      dbo.NotEntDetalle.cantidad, dbo.MInventario.desitems, dbo.NotEntDetalle.costo, 'N/E' AS tipo
FROM         dbo.MInventario INNER JOIN
                      dbo.NotEntDetalle ON dbo.MInventario.coditems = dbo.NotEntDetalle.coditems INNER JOIN
                      dbo.NotaEntrega ON dbo.NotEntDetalle.numnotent = dbo.NotaEntrega.numnotent
WHERE     (dbo.notaentrega.statunot <> '2') AND (dbo.MInventario.Prod_serv = 'P')

;


-- Dumping structure for view farmacias.VIEW_Dfactura2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dfactura2";

CREATE VIEW dbo.VIEW_Dfactura2
AS
SELECT     dbo.CMA_MFactura.numfactu, dbo.CMA_MFactura.fechafac, dbo.CMA_MFactura.codclien, dbo.CMA_MFactura.codmedico, dbo.CMA_DFactura.coditems, 
                      dbo.CMA_DFactura.cantidad, dbo.MInventario.desitems, dbo.CMA_DFactura.precunit
FROM         dbo.MInventario INNER JOIN
                      dbo.CMA_DFactura ON dbo.MInventario.coditems = dbo.CMA_DFactura.coditems INNER JOIN
                      dbo.CMA_MFactura ON dbo.CMA_DFactura.numfactu = dbo.CMA_MFactura.numfactu
WHERE     (dbo.CMA_MFactura.statfact <> '2')

;


-- Dumping structure for view farmacias.VIEW_Dfactura_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dfactura_0215";
CREATE VIEW dbo.VIEW_Dfactura_0215
AS
SELECT     dbo.DFactura.numfactu, dbo.DFactura.fechafac, dbo.DFactura.coditems, dbo.DFactura.cantidad, dbo.DFactura.precunit, dbo.DFactura.procentaje, 
                      dbo.DFactura.descuento, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.cod_subgrupo
FROM         dbo.DFactura INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_Dfactura_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dfactura_1";
CREATE VIEW dbo.VIEW_Dfactura_1
AS
SELECT     TOP (100) PERCENT dbo.MInventario.desitems, dbo.DFactura.cantidad, dbo.DFactura.precunit, dbo.DFactura.descuento, dbo.DFactura.monto_imp AS Impuesto, 
                      ROUND(dbo.DFactura.precunit * dbo.DFactura.cantidad - dbo.DFactura.descuento + dbo.DFactura.monto_imp, 2) AS subtotal, dbo.DFactura.numfactu, 
                      dbo.DFactura.coditems, dbo.MInventario.Prod_serv, dbo.DFactura.fechafac, dbo.DFactura.aplicadcto, dbo.DFactura.aplicacommed, dbo.DFactura.aplicacomtec, 
                      dbo.DFactura.costo, dbo.DFactura.aplicaiva, dbo.DFactura.codtipre, dbo.DFactura.dosis, dbo.DFactura.cant_sugerida, dbo.DFactura.procentaje, dbo.DFactura.Id
FROM         dbo.DFactura LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_dfactura_NC1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dfactura_NC1";


CREATE VIEW dbo.VIEW_dfactura_NC1
AS
SELECT DISTINCT 
                      TOP 100 PERCENT dbo.DFactura.numfactu, dbo.DFactura.coditems, dbo.MInventario.desitems, dbo.DFactura.cantidad AS facturado, 0 AS Acreditado, 
                      dbo.DFactura.precunit, dbo.DFactura.descuento
FROM         dbo.DFactura, dbo.MInventario
WHERE     dbo.DFactura.coditems = dbo.MInventario.coditems
UNION ALL
SELECT     TOP 100 PERCENT dbo.DFactura.numfactu, dbo.DFactura.coditems, dbo.MInventario.desitems, 0 AS facturado, 
                      dbo.Dnotacredito.cantidad AS acreditado, dbo.DFactura.precunit, dbo.DFactura.descuento
FROM         dbo.Dnotacredito INNER JOIN
                      dbo.Mnotacredito ON dbo.Dnotacredito.numnotcre = dbo.Mnotacredito.numnotcre INNER JOIN
                      dbo.DFactura ON dbo.Mnotacredito.numfactu = dbo.DFactura.numfactu AND dbo.Dnotacredito.coditems = dbo.DFactura.coditems INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.Mnotacredito.codtiponotcre = '1') AND dbo.Mnotacredito.statnc <> '2'
ORDER BY DFactura.numfactu


;


-- Dumping structure for view farmacias.VIEW_dimpuesto
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dimpuesto";
CREATE VIEW dbo.VIEW_dimpuesto
AS
SELECT     numfactu, ROUND(TotImpuesto * p1 / pt, 2) AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) AS Imp2, TotImpuesto, '01' AS doc
FROM         dbo.VIEW_ImpuestosxFact
UNION ALL
SELECT     numnotcre, ROUND(TotImpuesto * p1 / pt, 2) * - 1 AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) * - 1 AS Imp2, TotImpuesto * - 1, '04' AS doc
FROM         dbo.VIEW_ImpuestosxNC
;


-- Dumping structure for view farmacias.VIEW_dimpuestoMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dimpuestoMSS";

CREATE VIEW [dbo].[VIEW_dimpuestoMSS]
AS
SELECT     numfactu, ROUND(TotImpuesto * p1 / pt, 2) AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) AS Imp2, TotImpuesto, '01' AS doc
FROM         dbo.VIEW_ImpuestosxFactMSS
/* 24 04 2016 UNION ALL
SELECT     numnotcre, ROUND(TotImpuesto * p1 / pt, 2) * - 1 AS Imp1, ROUND(TotImpuesto * p2 / pt, 2) * - 1 AS Imp2, TotImpuesto * - 1, '04' AS doc
FROM         dbo.VIEW_ImpuestosxNC  */

;


-- Dumping structure for view farmacias.view_displaypagos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_displaypagos";
CREATE VIEW dbo.view_displaypagos
AS
SELECT     b.codclien, a.numfactu, max(a.coditems) coditems, 'suero' tipo, a.fechafac, max(b.total) total, max(c.initials) usuario, max(e.initials) metodo_pago, concat(b.codclien, 
                      max(a.coditems)) keypass, CONCAT(a.numfactu, ' ', max(c.initials), ' ', max(e.initials), ' ', max(b.total)) disp , max(b.statfact) statfact
FROM         cma_DFactura a INNER JOIN
                      cma_MFactura b ON a.numfactu = b.numfactu INNER JOIN
                      loginpass c ON b.usuario = c.login INNER JOIN
                      Mpagos d ON a.numfactu = d .numfactu AND d .id_centro = 2 INNER JOIN
                      MTipoTargeta e ON d .codtipotargeta = e.codtipotargeta
WHERE     a.coditems LIKE '%ST%'
GROUP BY a.numfactu, b.codclien, a.fechafac
UNION ALL
SELECT     b.codclien, a.numfactu, max(a.coditems) coditems, 'consulta' tipo, a.fechafac, max(b.total) total, max(c.initials) usuario, max(e.initials) metodo_pago, concat(b.codclien,
                       ' ') keypass, CONCAT(a.numfactu, ' ', max(c.initials), ' ', CASE WHEN max(e.initials) IS NULL THEN 'EXO' ELSE max(e.initials) END, ' ', max(b.total)) disp , max(b.statfact) statfact
FROM         cma_DFactura a LEFT JOIN
                      cma_MFactura b ON a.numfactu = b.numfactu INNER JOIN
                      loginpass c ON b.usuario = c.login LEFT JOIN
                      Mpagos d ON a.numfactu = d .numfactu AND d .id_centro = 2 LEFT JOIN
                      MTipoTargeta e ON d .codtipotargeta = e.codtipotargeta
WHERE     a.coditems NOT LIKE '%ST%'
GROUP BY a.numfactu, b.codclien, a.fechafac
UNION ALL
SELECT     b.codclien, a.numfactu, max(a.coditems) coditems, 'laser' tipo, a.fechafac, max(b.total) total, max(c.initials) usuario, max(e.initials) metodo_pago, concat(b.codclien, 
                      max(a.coditems)) keypass, CONCAT(a.numfactu, ' ', max(c.initials), ' ', max(e.initials), ' ', max(b.total)) disp , max(b.statfact) statfact
FROM         MSSDFact a INNER JOIN
                      MSSMFact b ON a.numfactu = b.numfactu INNER JOIN
                      loginpass c ON b.usuario = c.login INNER JOIN
                      Mpagos d ON a.numfactu = d .numfactu AND d .id_centro = 3 INNER JOIN
                      MTipoTargeta e ON d .codtipotargeta = e.codtipotargeta
WHERE     a.coditems NOT LIKE '%ST%'
GROUP BY a.numfactu, b.codclien, a.fechafac
;


-- Dumping structure for view farmacias.VIEW_Dnotacre
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dnotacre";
CREATE VIEW dbo.VIEW_Dnotacredito
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.Dnotacredito.cantidad, dbo.Dnotacredito.precunit, dbo.Dnotacredito.descuento, 
                      dbo.Dnotacredito.monto_imp AS Impuesto, 
                      ROUND(dbo.Dnotacredito.precunit * dbo.Dnotacredito.cantidad - dbo.Dnotacredito.descuento + dbo.Dnotacredito.monto_imp, 2) AS subtotal, 
                      dbo.Dnotacredito.numnotcre, dbo.Dnotacredito.coditems, dbo.MInventario.Prod_serv, dbo.Dnotacredito.fechanot
FROM         dbo.Dnotacredito LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dnotacredito.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_dnotacredito2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dnotacredito2";
CREATE VIEW dbo.VIEW_dnotacredito2
AS
SELECT     TOP 100 PERCENT dbo.Dnotacredito.numnotcre, dbo.MInventario.desitems, dbo.Dnotacredito.precunit AS precio, dbo.DFactura.cantidad, 
                      dbo.Dnotacredito.cantidad AS Acreditado, dbo.Dnotacredito.monto, dbo.Dnotacredito.coditems, dbo.DFactura.descuento
FROM         dbo.Dnotacredito INNER JOIN
                      dbo.Mnotacredito ON dbo.Dnotacredito.numnotcre = dbo.Mnotacredito.numnotcre INNER JOIN
                      dbo.DFactura ON dbo.Dnotacredito.coditems = dbo.DFactura.coditems AND dbo.Mnotacredito.numfactu = dbo.DFactura.numfactu LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dnotacredito.coditems = dbo.MInventario.coditems
ORDER BY dbo.Dnotacredito.numnotcre
;


-- Dumping structure for view farmacias.VIEW_dobles1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dobles1";
CREATE VIEW dbo.VIEW_dobles1
AS
SELECT     TOP 100 PERCENT COUNT(codclien) AS Expr1, codclien, fecha_cita
FROM         dbo.Mconsultas
WHERE     (activa = 1)
GROUP BY codclien, fecha_cita
ORDER BY expr1 DESC
;


-- Dumping structure for view farmacias.VIEW_dobles2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_dobles2";
CREATE VIEW dbo.VIEW_dobles2
AS
SELECT     *
FROM         dbo.VIEW_dobles1
WHERE     (Expr1 > 1)
;


-- Dumping structure for view farmacias.VIEW_Dpedido
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dpedido";

CREATE VIEW dbo.VIEW_Dpedido
AS
SELECT dbo.MInventario.desitems, dbo.DPedido.canitdad, 
    dbo.DPedido.coditems, dbo.DPedido.numpedido
FROM dbo.DPedido LEFT OUTER JOIN
    dbo.MInventario ON 
    dbo.DPedido.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_Dpostulados
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dpostulados";


CREATE VIEW dbo.VIEW_Dpostulados
AS
SELECT     CodSuc, Lapso, Mes, cod_IM, ds, Cuota, CAST(STR(ds) + '/' + STR(Mes) + '/' + STR(Lapso) AS datetime) AS xfecha
FROM         dbo.DPostulados


;


-- Dumping structure for view farmacias.VIEW_Dpresupuestos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Dpresupuestos";

CREATE VIEW dbo.VIEW_Dpresupuestos
AS
SELECT     dbo.MInventario.desitems, dbo.Dpresupuestos.cantidad, dbo.Dpresupuestos.precunit, dbo.Dpresupuestos.numpre, dbo.Dpresupuestos.coditems
FROM         dbo.Dpresupuestos LEFT OUTER JOIN
                      dbo.MInventario ON dbo.Dpresupuestos.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_estadisticas medicos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_estadisticas medicos";
CREATE VIEW dbo.[VIEW_estadisticas medicos]
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.Mmedicos.nombre, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.total,
                          (SELECT     SUM(dbo.dfactura.cantidad)
                            FROM          dbo.dfactura
                            WHERE      dbo.dfactura.numfactu = dbo.mfactura.numfactu) AS Cant, dbo.MFactura.statfact, dbo.MFactura.codmedico, dbo.Mmedicos.apellido
FROM         dbo.MFactura INNER JOIN
                      dbo.Mmedicos ON dbo.MFactura.codmedico = dbo.Mmedicos.Codmedico
;


-- Dumping structure for view farmacias.VIEW_Estadisticas_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Estadisticas_0215";
CREATE VIEW dbo.VIEW_Estadisticas_0215
AS
SELECT     dbo.VentasDiarias.*, dbo.Mmedicos.nombre + '  ' + dbo.Mmedicos.apellido AS medico,
                          (SELECT     SUM(cantidad * precunit - descuento)
                            FROM          dfactura
                            WHERE      dfactura.numfactu = dbo.VentasDiarias.numfactu) AS dTotal,
                          (SELECT     SUM(cantidad)
                            FROM          VIEW_Dfactura_0215
                            WHERE      VIEW_Dfactura_0215.numfactu = dbo.VentasDiarias.numfactu AND VIEW_Dfactura_0215.cod_subgrupo = '1') AS unidades,
                          (SELECT     SUM(cantidad)
                            FROM          VIEW_Dfactura_0215
                            WHERE      VIEW_Dfactura_0215.numfactu = dbo.VentasDiarias.numfactu AND VIEW_Dfactura_0215.cod_subgrupo = '2') AS formulas,
                          (SELECT     SUM(cantidad)
                            FROM          VIEW_Dfactura_0215
                            WHERE      VIEW_Dfactura_0215.numfactu = dbo.VentasDiarias.numfactu) AS unidadesT
FROM         dbo.VentasDiarias INNER JOIN
                      dbo.Mmedicos ON dbo.VentasDiarias.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.VentasDiarias.statfact <> '2') AND (dbo.VentasDiarias.doc = '01')
;


-- Dumping structure for view farmacias.View_facturasxcliente
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_facturasxcliente";

CREATE VIEW dbo.View_facturasxcliente
AS
SELECT     dbo.MClientes.codclien, dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.total, 'P' AS tipo, dbo.mfactura.codseguro
FROM         dbo.MClientes INNER JOIN
                      dbo.MFactura ON dbo.MClientes.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.MFactura.statfact <> 2)
UNION
SELECT     dbo.MClientes.codclien, dbo.CMA_MFactura.numfactu, dbo.CMA_MFactura.fechafac, dbo.CMA_MFactura.total, 'S' AS tipo, 
                      dbo.CMA_MFactura.codseguro
FROM         dbo.MClientes INNER JOIN
                      dbo.CMA_MFactura ON dbo.MClientes.numfactu = dbo.CMA_MFactura.numfactu
WHERE     (dbo.CMA_MFactura.statfact <> 2)

;


-- Dumping structure for view farmacias.VIEW_FinalEsta0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_FinalEsta0215";
CREATE VIEW dbo.VIEW_FinalEsta0215
AS
SELECT     dbo.VIEW_Estadisticas_0215.*, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS medicos
FROM         dbo.VIEW_Estadisticas_0215 INNER JOIN
                      dbo.VIEW_Asistidos_0215 ON dbo.VIEW_Estadisticas_0215.fechafac = dbo.VIEW_Asistidos_0215.fecha_cita AND 
                      dbo.VIEW_Estadisticas_0215.codclien = dbo.VIEW_Asistidos_0215.codclien INNER JOIN
                      dbo.Mmedicos ON dbo.VIEW_Estadisticas_0215.codmedico = dbo.Mmedicos.Codmedico
;


-- Dumping structure for view farmacias.View_GrupoConsultas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_GrupoConsultas";
CREATE VIEW dbo.View_GrupoConsultas
AS
SELECT     a.fechafac, b.cod_subgrupo, SUM(a.cantidad * a.precunit) - SUM(a.descuento) AS monto
FROM         dbo.cma_DFactura AS a INNER JOIN
                      dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                      dbo.VentasDiariasCMACST1 AS c ON a.numfactu = c.numfactu
WHERE     (c.statfact <> 2)
GROUP BY b.cod_subgrupo, a.fechafac
;


-- Dumping structure for view farmacias.View_GrupoConsultas_2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_GrupoConsultas_2";
		  create view View_GrupoConsultas_2 as
SELECT        a.fechafac, a.cod_subgrupo, SUM(a.cantidad * a.precunit) - SUM(a.descuento) AS monto
FROM            dbo.cma_DFactura AS a 
 WHERE        (a.statfact <> 2) 
GROUP BY a.cod_subgrupo, a.fechafac;


-- Dumping structure for view farmacias.VIEW_Impuestos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Impuestos";

CREATE VIEW dbo.VIEW_Impuestos
AS
SELECT     Impuesto, Porcentaje, '0' AS Monto, Codigo
FROM         dbo.Impuestos
WHERE     (Activo = 1) AND (AplicaYN = 1) AND (Borrado <> 1)

;


-- Dumping structure for view farmacias.VIEW_ImpuestosxFact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ImpuestosxFact";
CREATE VIEW dbo.VIEW_ImpuestosxFact
AS
SELECT     numfactu, TotImpuesto,
                          (SELECT     SUM(porcentaje)
                            FROM          impuestos
                            WHERE      activo = '1' AND fecha <= mfactura.fechafac) AS pt, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '2' AND activo = '1' AND fecha <= mfactura.fechafac), 0) AS p1, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '3' AND activo = '1' AND fecha <= mfactura.fechafac), 0) AS p2
FROM         dbo.MFactura
;


-- Dumping structure for view farmacias.VIEW_ImpuestosxFactMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ImpuestosxFactMSS";

CREATE VIEW [dbo].[VIEW_ImpuestosxFactMSS]
AS
SELECT     numfactu, TotImpuesto,
                          (SELECT     SUM(porcentaje)
                            FROM          impuestos
                            WHERE      activo = '1' AND fecha <= MSSMFact.fechafac) AS pt, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '2' AND activo = '1' AND fecha <= MSSMFact.fechafac), 0) AS p1, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '3' AND activo = '1' AND fecha <= MSSMFact.fechafac), 0) AS p2
FROM         dbo.MSSMFact

;


-- Dumping structure for view farmacias.VIEW_ImpuestosxNC
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ImpuestosxNC";
CREATE VIEW dbo.VIEW_ImpuestosxNC
AS
SELECT     numnotcre, TotImpuesto,
                          (SELECT     SUM(porcentaje)
                            FROM          impuestos
                            WHERE      activo = '1' AND fecha <= mnotacredito.fechanot) AS pt, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '2' AND activo = '1' AND fecha <= mnotacredito.fechanot), 0) AS p1, ISNULL
                          ((SELECT     porcentaje
                              FROM         impuestos
                              WHERE     codigo = '3' AND activo = '1' AND fecha <= mnotacredito.fechanot), 0) AS p2
FROM         dbo.Mnotacredito
;


-- Dumping structure for view farmacias.VIEW_ImpxFact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ImpxFact";

CREATE VIEW dbo.VIEW_ImpxFact
AS
SELECT     dbo.ImpxFact.numfactu, dbo.ImpxFact.codimp, dbo.ImpxFact.montoimp, dbo.ImpxFact.porcentaje, dbo.ImpxFact.base, dbo.Impuestos.Impuesto, 
                      dbo.Impuestos.Impuesto + ' ' + CONVERT(nvarchar, dbo.ImpxFact.porcentaje) + ' %' AS Descripcion
FROM         dbo.Impuestos INNER JOIN
                      dbo.ImpxFact ON dbo.Impuestos.Codigo = dbo.ImpxFact.codimp

;


-- Dumping structure for view farmacias.VIEW_inventario
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_inventario";

CREATE VIEW dbo.VIEW_inventario
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.INVASLT.CANTIDAD, dbo.INVASLT.FECHA, dbo.INVASLT.HORA, dbo.MInventario.activo, 
                      dbo.MInventario.Prod_serv
FROM         dbo.MInventario INNER JOIN
                      dbo.INVASLT ON dbo.MInventario.coditems = dbo.INVASLT.CODITEMS
WHERE     (dbo.INVASLT.FECHA = '31/12/2003') AND (dbo.MInventario.Prod_serv = 'P')
ORDER BY dbo.MInventario.desitems

;


-- Dumping structure for view farmacias.VIEw_inventarios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEw_inventarios";

CREATE VIEW dbo.VIEw_inventarios
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.INVASLT.CODITEMS, dbo.INVASLT.CANTIDAD, dbo.INVASLT.FECHA, dbo.MInventario.activo, 
                      dbo.MInventario.Prod_serv
FROM         dbo.INVASLT INNER JOIN
                      dbo.MInventario ON dbo.INVASLT.CODITEMS = dbo.MInventario.coditems
WHERE     (dbo.INVASLT.FECHA = '31/12/2003') AND (dbo.MInventario.Prod_serv = 'P')
ORDER BY dbo.MInventario.desitems

;


-- Dumping structure for view farmacias.VIEW_IR
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_IR";

CREATE VIEW dbo.VIEW_IR
AS
SELECT     codclien, fecha_cita, citacontrol, codmedico, asistido, activa, primera_control, '01' AS codsuc
FROM         dbo.Mconsultas
WHERE     (activa = '1') AND (asistido = '3')

;


-- Dumping structure for view farmacias.VIEW_it
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_it";


CREATE VIEW dbo.VIEW_it
AS
SELECT     dbo.Mconsultas.codclien, dbo.Mconsultas.fecha, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.primera_control, dbo.Mconsultas.asistido, 
                      dbo.Mconsultas.codmedico, dbo.Mmedicos.nombre, dbo.Mmedicos.apellido,
                          (SELECT     SUM(((dbo.dfactura.cantidad * dbo.dfactura.precunit) - dbo.dfactura.descuento)) AS Bs
                            FROM          dbo.dfactura INNER JOIN
                                                   dbo.mfactura ON dbo.dfactura.numfactu = dbo.mfactura.numfactu
                            WHERE      dbo.mfactura.statfact <> '2' AND dbo.dfactura.fechafac = dbo.mconsultas.fecha_cita AND 
                                                   dbo.mfactura.codclien = dbo.mconsultas.codclien AND dbo.dfactura.aplicacommed = '1') AS MONP_Bs,
                          (SELECT     SUM(dbo.cma_mfactura.total) AS Bs
                            FROM          dbo.cma_mfactura
                            WHERE      dbo.cma_mfactura.statfact <> '2' AND dbo.cma_mfactura.fechafac = dbo.mconsultas.fecha_cita AND 
                                                   dbo.cma_mfactura.codclien = dbo.mconsultas.codclien) AS MONS_Bs, 1 AS items, dbo.Mconsultas.activa,
                          (SELECT     TOP 1 horareg
                            FROM          dbo.mfactura
                            WHERE      dbo.mfactura.codclien = dbo.mconsultas.codclien AND dbo.mfactura.fechafac = dbo.mconsultas.fecha_cita) AS horamf,
                          (SELECT     TOP 1 horareg
                            FROM          dbo.cma_mfactura
                            WHERE      dbo.cma_mfactura.codclien = dbo.mconsultas.codclien AND dbo.cma_mfactura.fechafac = dbo.mconsultas.fecha_cita) AS horaCMA, 
                      '01' AS codsuc
FROM         dbo.Mconsultas LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.Mconsultas.asistido = 3) AND (dbo.Mconsultas.activa = '1')


;


-- Dumping structure for view farmacias.VIEW_kardex
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_kardex";
CREATE VIEW dbo.VIEW_kardex
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.DCierreInventario.existencia, dbo.DCierreInventario.fechacierre, dbo.MInventario.activo,
                          (SELECT     isnull(SUM(dbo.dfactura.cantidad), 0)
                            FROM          dfactura RIGHT JOIN
                                                   mfactura ON dfactura.numfactu = mfactura.numfactu
                            WHERE      coditems = dbo.minventario.coditems AND dbo.dfactura.fechafac = dbo.DCIERREINVENTARIO.FECHACIERRE) AS ventas,
                          (SELECT     isnull(SUM(dbo.dfactura.cantidad), 0)
                            FROM          dfactura RIGHT JOIN
                                                   mfactura ON dfactura.numfactu = mfactura.numfactu
                            WHERE      coditems = dbo.minventario.coditems AND dbo.mfactura.fechanul = dbo.DCIERREINVENTARIO.FECHACIERRE AND dbo.mfactura.statfact = '2') 
                      AS DVentas,
                          (SELECT     isnull(SUM(dbo.dcompra.cantidad), 0)
                            FROM          dcompra LEFT JOIN
                                                   mcompra ON dcompra.factcomp = mcompra.factcomp
                            WHERE      coditems = dbo.minventario.coditems AND mcompra.fechapost = dbo.DCIERREINVENTARIO.FECHACIERRE) AS compra,
                          (SELECT     isnull(SUM(dbo.devcompra.cantidad), 0)
                            FROM          devcompra
                            WHERE      coditems = dbo.minventario.coditems AND fecreg = dbo.DCIERREINVENTARIO.FECHACIERRE) AS devcompra,
                          (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0)
                            FROM          dajustes
                            WHERE      coditems = dbo.minventario.coditems AND fechajust = dbo.DCIERREINVENTARIO.FECHACIERRE AND dbo.dajustes.cantidad > 0) AS Ajustes_mas,
                          (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0)
                            FROM          dajustes
                            WHERE      coditems = dbo.minventario.coditems AND fechajust = dbo.DCIERREINVENTARIO.FECHACIERRE AND dbo.dajustes.cantidad < 0) AS Ajustes_menos,
                          (SELECT     isnull(SUM(dbo.notentdetalle.cantidad), 0)
                            FROM          notentdetalle RIGHT JOIN
                                                   notaentrega ON notentdetalle.numnotent = notaentrega.numnotent
                            WHERE      coditems = dbo.minventario.coditems AND dbo.notentdetalle.fechanot = dbo.DCIERREINVENTARIO.FECHACIERRE AND notaentrega.statunot <> '2') 
                      AS NE,
                          (SELECT     isnull(SUM(dbo.dnotacredito.cantidad), 0)
                            FROM          dnotacredito RIGHT JOIN
                                                   mnotacredito ON dnotacredito.numnotcre = mnotacredito.numnotcre
                            WHERE      coditems = dbo.minventario.coditems AND dbo.dnotacredito.fechanot = dbo.DCIERREINVENTARIO.FECHACIERRE AND mnotacredito.statnc <> '2') AS Nc, 
                      dbo.DCierreInventario.coditems
FROM         dbo.DCierreInventario LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DCierreInventario.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_Label0815
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Label0815";
CREATE VIEW dbo.VIEW_Label0815
AS
SELECT     dbo.Mmedicos.apellido + ' ' + dbo.Mmedicos.nombre AS medico, dbo.MClientes.nombres, dbo.MClientes.NACIMIENTO, dbo.MClientes.sexo, 
                      dbo.MInventario.desitems, dbo.MClientes.Historia, dbo.cma_MFactura.numfactu
FROM         dbo.cma_DFactura INNER JOIN
                      dbo.MInventario ON dbo.cma_DFactura.coditems = dbo.MInventario.coditems INNER JOIN
                      dbo.cma_MFactura ON dbo.cma_DFactura.numfactu = dbo.cma_MFactura.numfactu INNER JOIN
                      dbo.MClientes ON dbo.cma_MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.Mmedicos ON dbo.cma_MFactura.codmedico = dbo.Mmedicos.Codmedico
;


-- Dumping structure for view farmacias.view_Laser_00
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_Laser_00";
Create view view_Laser_00 as
  select a.codclien,a.fechafac,b.cantidad
  from MSSMFact a 
  inner join MSSDFact b on a.numfactu=b.numfactu and  b.coditems like 'TD%' 
  and a.statfact='3'
;


-- Dumping structure for view farmacias.VIEW_listadeprecios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_listadeprecios";

CREATE VIEW dbo.VIEW_listadeprecios
AS
SELECT     dbo.MPrecios.coditems, dbo.MInventario.coditems AS Expr1, dbo.MInventario.desitems, dbo.MPrecios.precunit
FROM         dbo.MPrecios INNER JOIN
                      dbo.MInventario ON dbo.MPrecios.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_LISTA_DE_PRECIOS_AS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_LISTA_DE_PRECIOS_AS";

CREATE VIEW dbo.VIEW_LISTA_DE_PRECIOS_AS
AS
SELECT dbo.MPrecios.coditems, dbo.MInventario.desitems, 
    dbo.MPrecios.precunit
FROM dbo.MInventario INNER JOIN
    dbo.MPrecios ON 
    dbo.MInventario.coditems = dbo.MPrecios.coditems

;


-- Dumping structure for view farmacias.View_lugar
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_lugar";

CREATE VIEW dbo.View_lugar
AS
SELECT     dbo.MClientes.nombres, dbo.Pais.PAIS, dbo.States.State, dbo.MClientes.codmedico, dbo.MClientes.codclien
FROM         dbo.MClientes LEFT OUTER JOIN
                      dbo.Pais ON dbo.MClientes.Pais = dbo.Pais.COD LEFT OUTER JOIN
                      dbo.States ON dbo.MClientes.ESTADO = dbo.States.COD

;


-- Dumping structure for view farmacias.VIEW_Mcompra
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mcompra";

CREATE VIEW dbo.VIEW_Mcompra
AS
SELECT     dbo.MCompra.factcomp, dbo.MCompra.fechacomp, dbo.MProveedores.Desprov, dbo.MCompra.facclose
FROM         dbo.MCompra LEFT OUTER JOIN
                      dbo.MProveedores ON dbo.MCompra.codprov = dbo.MProveedores.codProv

;


-- Dumping structure for view farmacias.view_mcompra_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_mcompra_1";


CREATE VIEW dbo.view_mcompra_1
AS
SELECT     dbo.MCompra.factcomp, dbo.mcompra.fecreg, dbo.MProveedores.Desprov, dbo.MCompra.total, dbo.MCompra.codprov, dbo.MCompra.observacion, 
                      OnStock = CASE WHEN facclose = '0' THEN 'No' WHEN facclose = '1' THEN 'Si' END, dbo.MCompra.fechacomp
FROM         dbo.MCompra LEFT OUTER JOIN
                      dbo.MProveedores ON dbo.MCompra.codprov = dbo.MProveedores.codProv


;


-- Dumping structure for view farmacias.VIEW_Mconsultas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mconsultas";

CREATE VIEW dbo.VIEW_Mconsultas
AS
SELECT     dbo.MClientes.Cedula, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.MClientes.telfhabit,
                          (SELECT     DESESTADO
                            FROM          MESTADO
                            WHERE      ESTADO = dbo.Mconsultas.citados) AS CITADOS,
                          (SELECT     DESESTADO
                            FROM          MESTADO
                            WHERE      ESTADO = dbo.Mconsultas.confirmado) AS CONFIRMADO,
                          (SELECT     DESESTADO
                            FROM          MESTADO
                            WHERE      ESTADO = dbo.Mconsultas.asistido) AS ASISTIDOS,
                          (SELECT     DESESTADO
                            FROM          MESTADO
                            WHERE      ESTADO = dbo.Mconsultas.noasistido) AS NO_ASISTIO, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, 
                      dbo.Mconsultas.codclien, dbo.Mconsultas.fecha, dbo.Mconsultas.codmedico, dbo.Mconsultas.citacontrol, dbo.Mconsultas.activa, 
                      dbo.Mconsultas.usuario, dbo.Mconsultas.primera_control,
                          (SELECT     DESESTADO
                            FROM          MESTADO
                            WHERE      ESTADO = dbo.Mconsultas.nocitados) AS nocitados, dbo.MClientes.Historia
FROM         dbo.Mconsultas LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_mconsultas01
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_mconsultas01";

CREATE VIEW dbo.VIEW_mconsultas01
AS
SELECT     codclien, fecha, codmedico, asistido, primera_control, activa
FROM         dbo.Mconsultas
WHERE     (fecha >= '01/10/2004')

;


-- Dumping structure for view farmacias.VIEW_mconsultas_02
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_mconsultas_02";
CREATE VIEW dbo.VIEW_mconsultas_02
AS
SELECT        dbo.MClientes.Cedula, dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.hora, dbo.MClientes.telfhabit + '/' + CASE WHEN dbo.MClientes.telfofic IS NOT NULL 
                         THEN dbo.MClientes.telfofic ELSE '' END + N' Cel: ' + dbo.MClientes.celular AS telfhabit,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultas.citados) AS CITADOS,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultas.confirmado) AS CONFIRMADO,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultas.asistido) AS ASISTIDOS,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultas.noasistido) AS NO_ASISTIO, dbo.tipoconsulta.descons, dbo.Mconsultas.observacion, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, dbo.Mconsultas.codclien, 
                         dbo.Mconsultas.fecha, CASE WHEN dbo.Mconsultas.codmedico = '' THEN '000' ELSE dbo.Mconsultas.codmedico END codmedico, dbo.Mconsultas.codconsulta, dbo.Mconsultas.citacontrol, dbo.Mconsultas.activa, 
                         dbo.Mconsultas.usuario, dbo.Mconsultas.primera_control,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultas.nocitados) AS nocitados, dbo.MClientes.Historia, dbo.MClientes.exonerado, ' ' AS coditems, dbo.Mconsultas.confirmado AS codconfirm, 1 AS tipo, horain, horaout, dbo.Mconsultas.id, 
                         dbo.Mconsultas.llegada, '' mls, '' hilt, '' terapias, prioridad, dbo.Mconsultas.hora endtime, dbo.Mconsultas.fecha_cita enddate, dbo.Mconsultas.pacienteleft, dbo.Mconsultas.answered, dbo.Mconsultas.asistido
FROM            dbo.Mconsultas LEFT OUTER JOIN
                         dbo.tipoconsulta ON dbo.Mconsultas.codconsulta = dbo.tipoconsulta.codconsulta LEFT OUTER JOIN
                         dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien LEFT OUTER JOIN
                         dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico
UNION ALL
SELECT        dbo.MClientes.Cedula, dbo.MClientes.nombres, dbo.Mconsultass.fecha_cita, dbo.Mconsultass.hora, dbo.MClientes.telfhabit + '/' + CASE WHEN dbo.MClientes.telfofic IS NOT NULL 
                         THEN dbo.MClientes.telfofic ELSE '' END + N' Cel: ' + dbo.MClientes.celular AS telfhabit,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultass.citados) AS CITADOS,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultass.confirmado) AS CONFIRMADO,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultass.asistido) AS ASISTIDOS,
                             (SELECT        DESESTADO
                               FROM            MESTADO
                               WHERE        ESTADO = dbo.Mconsultass.noasistido) AS NO_ASISTIO,
                             (SELECT        i.cod_subgrupo
                               FROM            MInventario i
                               WHERE        i.coditems = dbo.Mconsultass.coditems) descons/*dbo.tipoconsulta.descons */ , dbo.Mconsultass.observacion, dbo.Mmedicos.nombre ;


-- Dumping structure for view farmacias.VIEW_mconsultas_03
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_mconsultas_03";
CREATE view  VIEW_mconsultas_03 as
SELECT        dbo.Mconsultas.codclien Cedula, dbo.Mconsultas.paciente nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.hora
,Case when dbo.Mconsultas.citados='1' Then 'Citado' END  CITADOS
,Case When dbo.Mconsultas.confirmado='2' Then 'Confirmado' END CONFIRMADO
,Case when dbo.Mconsultas.asistido='3' Then 'Asistido' END ASISTIDOS
,Case when dbo.Mconsultas.noasistido='4' then 'No asistido' END NO_ASISTIO
,Case when dbo.Mconsultas.noasistido='5' then 'No Citados' END nocitados

,dbo.tipoconsulta.descons





,dbo.Mconsultas.observacion

,dbo.Mconsultas.codclien
,dbo.Mconsultas.fecha
,CASE WHEN dbo.Mconsultas.codmedico = '' THEN '000' ELSE dbo.Mconsultas.codmedico END codmedico, dbo.Mconsultas.codconsulta
,dbo.Mconsultas.citacontrol
,dbo.Mconsultas.activa
,dbo.Mconsultas.usuario
,dbo.Mconsultas.primera_control			   
,dbo.Mconsultas.record Historia
,' ' AS coditems
,dbo.Mconsultas.confirmado AS codconfirm
,1 AS tipo
,horain
,horaout
,dbo.Mconsultas.id
,dbo.Mconsultas.llegada
,'' mls
,'' hilt
,'' terapias
,prioridad
,dbo.Mconsultas.hora endtime
,dbo.Mconsultas.fecha_cita enddate
,dbo.Mconsultas.pacienteleft
,dbo.Mconsultas.answered
,dbo.Mconsultas.asistido
FROM  dbo.Mconsultas 
LEFT OUTER JOIN  dbo.tipoconsulta ON dbo.Mconsultas.codconsulta = dbo.tipoconsulta.codconsulta 
						 

UNION ALL

SELECT        dbo.Mconsultass.codclien Cedula, dbo.Mconsultass.paciente nombres, dbo.Mconsultass.fecha_cita, dbo.Mconsultass.hora
,Case When dbo.Mconsultass.citados='1' Then  'Citado' END  CITADOS
,Case When dbo.Mconsultass.confirmado='2' Then 'Confirmado' END CONFIRMADO
,Case when dbo.Mconsultass.asistido='3' Then 'Asistido' END ASISTIDOS
,Case when dbo.Mconsultass.noasistido='4' then 'No asistido' END NO_ASISTIO
,Case when dbo.Mconsultass.noasistido='5' then 'No Citados' END nocitados
, i.cod_subgrupo descons
                        /*     (SELECT        i.cod_subgrupo
                               FROM            MInventario i
                               WHERE        i.coditems = dbo.Mconsultass.coditems) descons */
							   
							   
							   , dbo.Mconsultass.observacion, dbo.Mconsultass.codclien
							   , dbo.Mconsultass.fecha, CASE WHEN dbo.Mconsultass.codmedico = '' THEN '000' ELSE dbo.Mconsultass.codmedico END codmedico
							   , dbo.Mconsultass.codconsulta, dbo.Mconsultass.citacontrol
							   , dbo.Mconsultass.activa
							   , dbo.Mconsultass.usuario
							   , dbo.Mconsultass.primera_control
						 
						
							   
							   , dbo.Mconsultass.record Historia
							   ,  dbo.Mconsultass.CODITEMS
							   , dbo.Mconsultass.confirmado AS codconfirm
							   , 2 AS tipo, horain
							   , horaout
							   , dbo.Mconsultass.id
							   , dbo.Mconsultass.llegada
							   , dbo.Mconsultass.mls
							   , dbo.Mconsultass.hilt
							   , dbo.Mconsultass.terapias
							   , prioridad
							   , dbo.Mconsultass.endtime
							   , dbo.Mconsultass.enddate
							   , dbo.Mconsultass.pacienteleft
							   , dbo.Mconsultass.answered
							   , dbo.Mconsultass.asistido
FROM            dbo.Mconsultass 
LEFT OUTER JOIN    dbo.tipoconsulta ON dbo.Mconsultass.codconsulta = dbo.tipoconsulta.codconsulta
inner join MInventario i On i.coditems=Mconsultass.coditems
	;


-- Dumping structure for view farmacias.VIEW_MCotizacion
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MCotizacion";

CREATE VIEW dbo.VIEW_MCotizacion
AS
SELECT dbo.MCotizacion.numcot, dbo.MCotizacion.fechacot, 
    dbo.MClientes.nombre + ' ' + dbo.MClientes.apellido AS cliente, 
    dbo.MCotizacion.numfactu, dbo.MCotizacion.transferida
FROM dbo.MCotizacion LEFT OUTER JOIN
    dbo.MClientes ON 
    dbo.MCotizacion.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_Mcotizacion2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mcotizacion2";


CREATE VIEW dbo.VIEW_Mcotizacion2
AS
SELECT     TOP 100 PERCENT dbo.MCotizacion.numcot, dbo.MCotizacion.fechacot, dbo.MCotizacion.DiasPrescripcion, dbo.MCotizacion.codseguro, 
                      dbo.MClientes.codclien, dbo.MClientes.Cedula, { fn UCASE(dbo.MClientes.nombres) } AS nombres, dbo.MCotizacion.fechacit, 
                      dbo.MCotizacion.DiasPrescripcion AS Expr1, dbo.MCotizacion.codtipre
FROM         dbo.MCotizacion INNER JOIN
                      dbo.MClientes ON dbo.MCotizacion.codclien = dbo.MClientes.codclien
ORDER BY dbo.MCotizacion.numcot DESC, dbo.MClientes.nombres


;


-- Dumping structure for view farmacias.VIEW_MDevCompra
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MDevCompra";

CREATE VIEW dbo.VIEW_MDevCompra
AS
SELECT     dbo.MDevCompra.factcomp, dbo.MDevCompra.fechacomp, dbo.MProveedores.Desprov, dbo.MDevCompra.facclose
FROM         dbo.MDevCompra LEFT OUTER JOIN
                      dbo.MProveedores ON dbo.MDevCompra.codprov = dbo.MProveedores.codProv

;


-- Dumping structure for view farmacias.VIEW_Medicos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Medicos";

CREATE VIEW dbo.VIEW_Medicos
AS
SELECT     Codmedico, cedula, nombre, apellido, activo, '01' AS codsuc, 'BAYAMON' AS Sucursal, meliminado
FROM         dbo.Mmedicos

;


-- Dumping structure for view farmacias.view_medpacpercentstats
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_medpacpercentstats";
CREATE VIEW dbo.view_medpacpercentstats
AS
SELECT     codmedico, codclien, MAX(fecha_cita) AS fechafac
FROM         dbo.Mconsultas AS b
WHERE     (asistido <> 0)
GROUP BY codmedico, codclien, fecha_cita
;


-- Dumping structure for view farmacias.VIEW_MenuPerfil
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MenuPerfil";

CREATE VIEW dbo.VIEW_MenuPerfil
AS
SELECT MenuPrincipal.desopcion, MenuPerfil.acceso, 
    MenuPerfil.codperfil, MenuPerfil.nomopcion
FROM dbo.MenuPerfil INNER JOIN
    dbo.MenuPrincipal ON 
    dbo.MenuPerfil.nomopcion = dbo.MenuPrincipal.nomopcion

;


-- Dumping structure for view farmacias.VIEW_MenuPerfilSecundario
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MenuPerfilSecundario";

CREATE VIEW dbo.VIEW_MenuPerfilSecundario
AS
SELECT     dbo.MenuSecundario.dessubopcion, dbo.MenuPerfilSecundario.acceso, dbo.MenuPerfilSecundario.codperfil, dbo.MenuPerfilSecundario.nomopcion, 
                      dbo.MenuPerfilSecundario.nomsubopcion
FROM         dbo.MenuPerfilSecundario INNER JOIN
                      dbo.MenuSecundario ON dbo.MenuPerfilSecundario.nomsubopcion = dbo.MenuSecundario.nomsubopsion

;


-- Dumping structure for view farmacias.VIEW_Meses
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Meses";


CREATE VIEW dbo.VIEW_Meses
AS
SELECT     TOP 100 PERCENT codsuc, CAST(STR(DATEPART([year], fecha)) + LTRIM(STR(DATEPART(mm, fecha))) AS numeric) AS periodo, SUM(pacnue) AS totnue, 
                      SUM(paccon) AS totcon, SUM(pacser) AS totpacser, SUM(facprod + facserv) AS totgi, SUM(potes) AS totpotes, SUM(totene) AS totene, 
                      SUM(pacnue + paccon) AS totpac, SUM(paccon + pacnue + pacser) AS totpacconnueser, ROUND(SUM(PacProdBs) / SUM(pacnue + paccon) * 100, 2) 
                      AS it, SUM(totsue) AS totsue, SUM(totele) AS totele, SUM(Terneural) AS totneural
FROM         dbo.EstadisticasGlobales
GROUP BY codsuc, CAST(STR(DATEPART([year], fecha)) + LTRIM(STR(DATEPART(mm, fecha))) AS numeric)
ORDER BY codsuc, CAST(STR(DATEPART([year], fecha)) + LTRIM(STR(DATEPART(mm, fecha))) AS numeric)


;


-- Dumping structure for view farmacias.VIEW_Mfactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mfactura";
CREATE VIEW dbo.VIEW_Mfactura
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, 
                      dbo.MFactura.codmedico, dbo.MFactura.statfact, dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.iva, dbo.MFactura.total, 
                      dbo.MFactura.monto_abonado, dbo.MFactura.TotImpuesto, dbo.MFactura.Alicuota, dbo.MFactura.monto_flete
FROM         dbo.MFactura LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.MFactura.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_Mfactura_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mfactura_1";
CREATE VIEW dbo.VIEW_Mfactura_1
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, dbo.MFactura.statfact, 
                      dbo.MFactura.subtotal, dbo.MFactura.descuento, dbo.MFactura.iva AS Impuesto1, dbo.MFactura.total + dbo.MFactura.monto_flete AS total, dbo.Status.Status, 
                      dbo.MFactura.totalpvp, dbo.MFactura.TotImpuesto AS Impuesto2, dbo.MFactura.Id, dbo.MFactura.monto_flete, dbo.MFactura.TotImpuesto, dbo.MFactura.codclien, 
                      dbo.MFactura.usuario, dbo.MFactura.codmedico, dbo.MClientes.Historia
FROM         dbo.MFactura LEFT OUTER JOIN
                      dbo.Status ON dbo.MFactura.statfact = dbo.Status.statfact LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.MFactura.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_Minventario
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Minventario";

CREATE VIEW dbo.VIEW_Minventario
AS
SELECT     coditems, desitems, existencia, Prod_serv, activo, '01' AS codsuc
FROM         dbo.MInventario

;


-- Dumping structure for view farmacias.VIEW_Minventario_Precios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Minventario_Precios";


CREATE VIEW dbo.VIEW_Minventario_Precios
AS
SELECT     dbo.MInventario.desitems, dbo.TipoPrecio.destipre, dbo.MPrecios.precunit, dbo.MPrecios.coditems, dbo.MPrecios.codtipre, 
                      dbo.MPrecios.sugerido
FROM         dbo.MPrecios LEFT OUTER JOIN
                      dbo.MInventario ON dbo.MPrecios.coditems = dbo.MInventario.coditems LEFT OUTER JOIN
                      dbo.TipoPrecio ON dbo.MPrecios.codtipre = dbo.TipoPrecio.codtipre


;


-- Dumping structure for view farmacias.VIEW_mnotacre
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_mnotacre";
CREATE VIEW dbo.VIEW_mnotacre
AS
SELECT     dbo.Mnotacredito.numnotcre, dbo.Mnotacredito.fechanot, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, 
                      dbo.Mnotacredito.statnc, dbo.Mnotacredito.subtotal, dbo.Mnotacredito.descuento, dbo.Mnotacredito.alicuota, 
                      dbo.Mnotacredito.totalnot + dbo.Mnotacredito.monto_flete AS Total, dbo.Status.Status, dbo.Mnotacredito.TotImpuesto, dbo.Mnotacredito.monto_flete, 
                      dbo.Mnotacredito.Id, dbo.Mnotacredito.usuario
FROM         dbo.Mnotacredito LEFT OUTER JOIN
                      dbo.Status ON dbo.Mnotacredito.statnc = dbo.Status.statfact LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mnotacredito.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_Mnotacredito
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mnotacredito";

CREATE VIEW dbo.VIEW_Mnotacredito
AS
SELECT     dbo.Mnotacredito.numnotcre, dbo.Mnotacredito.fechanot, dbo.Mnotacredito.numfactu, dbo.MClientes.nombres, 
                      dbo.Mmedicos.apellido + '  ' + dbo.Mmedicos.nombre AS medico, dbo.Mnotacredito.monto, dbo.Mnotacredito.descuento, dbo.Mnotacredito.totalnot, 
                      dbo.Mstatus.destatus
FROM         dbo.Mnotacredito LEFT OUTER JOIN
                      dbo.Mstatus ON dbo.Mnotacredito.statnc = dbo.Mstatus.status LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mnotacredito.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_MovInvent
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MovInvent";
CREATE VIEW dbo.VIEW_MovInvent AS SELECT     TOP 100 PERCENT desitems, coditems, CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(), 112) THEN  (SELECT  isnull(SUM(dbo.dfactura.cantidad), 0) + SUM(t.ventas)    FROM    dfactura RIGHT JOIN   mfactura ON dfactura.numfactu = mfactura.numfactu    WHERE      coditems = t.coditems AND dbo.dfactura.fechafac = '20190515' ) ELSE SUM(t.ventas) END AS ventas, CASE WHEN '20190515'  = CONVERT(nvarchar(8),    getdate(), 112) THEN   (SELECT     isnull(SUM(dbo.dcompra.cantidad), 0) + SUM(t.compra)    FROM       dcompra LEFT JOIN   mcompra ON dcompra.factcomp = mcompra.factcomp   WHERE      coditems = t.coditems AND mcompra.fechapost = '20190515' ) ELSE SUM(compra) END AS compras, CASE WHEN '20190515'  = CONVERT(nvarchar(8),    getdate(), 112) THEN   (SELECT     isnull(SUM(dbo.devcompra.cantidad), 0) + SUM(t.devcompra)   FROM          devcompra   WHERE      coditems = t.coditems AND fecreg = '20190515' ) ELSE SUM(devcompra) END AS devcompra, CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(),    112) THEN   (SELECT     isnull(SUM(dbo.dfactura.cantidad), 0) + SUM(t.anulaciones)   FROM          dfactura RIGHT JOIN   mfactura ON dfactura.numfactu = mfactura.numfactu   WHERE      coditems = t.coditems AND dbo.mfactura.fechanul = '20190515'  AND dbo.mfactura.statfact = '2') ELSE SUM(anulaciones) END AS devVentas,    CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(), 112) THEN  (SELECT     isnull(SUM(dbo.notentdetalle.cantidad), 0) + SUM(t.ne)   FROM          notentdetalle RIGHT JOIN     notaentrega ON notentdetalle.numnotent = notaentrega.numnotent   WHERE      coditems = t.coditems AND dbo.notentdetalle.fechanot = '20190515'  AND notaentrega.statunot <> '2') ELSE SUM(ne) END AS NE,    CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(), 112) THEN (SELECT     isnull(SUM(dbo.dnotacredito.cantidad), 0) + SUM(t.nc)   FROM          dnotacredito RIGHT JOIN  mnotacredito ON dnotacredito.numnotcre = mnotacredito.numnotcre   WHERE      coditems = t.coditems AND dbo.dnotacredito.fechanot = '20190515'  AND mnotacredito.statnc <> '2') ELSE SUM(nc) END AS NC,    CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(), 112) THEN (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0) + SUM(ajustespos)   FROM          dajustes  WHERE      coditems = t.coditems AND fechajust = '20190515'  AND dbo.dajustes.cantidad > 0) ELSE SUM(ajustespos) END AS Ajustes_mas,    CASE WHEN '20190515'  = CONVERT(nvarchar(8), getdate(), 112) THEN  (SELECT     isnull(SUM(dbo.dajustes.cantidad), 0) + SUM(ajustesneg)   FROM          dajustes  WHERE      coditems = t.coditems AND fechajust = '20190515'  AND dbo.dajustes.cantidad < 0) ELSE SUM(ajustesneg) END AS Ajustes_neg,  (SELECT isnull(SUM(dbo.DCierreInventario.existencia), 0) From dbo.DCierreInventario    WHERE      coditems = t.coditems AND fechacierre = '20190515') AS existencia    FROM         dbo.TEST011516 t  WHERE     (fechacierre BETWEEN '20190515'  AND '20190515' ) GROUP BY desitems, coditems  ORDER BY desitems ;


-- Dumping structure for view farmacias.VIEW_MPagos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MPagos";
CREATE VIEW dbo.VIEW_MPagos
AS
SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago, dbo.MFormaPago.desforpa, dbo.Mbanco.DESBANCO, dbo.Mpagos.monto
FROM         dbo.Mpagos INNER JOIN
                      dbo.MFactura ON dbo.Mpagos.numfactu = dbo.MFactura.numfactu LEFT OUTER JOIN
                      dbo.MFormaPago ON dbo.Mpagos.codforpa = dbo.MFormaPago.codforpa LEFT OUTER JOIN
                      dbo.Mbanco ON dbo.Mpagos.codbanco = dbo.Mbanco.CODBANCO
WHERE     (dbo.MFactura.statfact <> '2')
;


-- Dumping structure for view farmacias.VIEW_MPagos1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MPagos1";
CREATE VIEW dbo.VIEW_MPagos1
AS
SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago, dbo.MFormaPago.desforpa, dbo.Mbanco.DESBANCO, dbo.Mpagos.monto, dbo.Mpagos.codforpa, 
                      dbo.Mpagos.codbanco, dbo.Mpagos.id_centro, dbo.Mpagos.tipo_doc, dbo.MTipoTargeta.DesTipoTargeta
FROM         dbo.Mpagos LEFT OUTER JOIN
                      dbo.MTipoTargeta ON dbo.Mpagos.codtipotargeta = dbo.MTipoTargeta.codtipotargeta LEFT OUTER JOIN
                      dbo.MFormaPago ON dbo.Mpagos.codforpa = dbo.MFormaPago.codforpa LEFT OUTER JOIN
                      dbo.Mbanco ON dbo.Mpagos.codbanco = dbo.Mbanco.CODBANCO
;


-- Dumping structure for view farmacias.VIEW_Mpagos2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mpagos2";
CREATE VIEW dbo.VIEW_Mpagos2
AS
SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago, dbo.Mpagos.codforpa, dbo.Mpagos.codbanco, dbo.Mpagos.codtipotargeta, dbo.Mpagos.nro_forpa, 
                      dbo.Mpagos.monto, dbo.Mpagos.monto_abonado, dbo.Mpagos.usuario, dbo.Mpagos.workstation, dbo.Mpagos.ipaddress, dbo.Mpagos.fecreg, 
                      dbo.Mpagos.horareg, dbo.Mpagos.id_centro, dbo.Mpagos.tipo_doc, dbo.Vestaciones.codestac, dbo.Vestaciones.desestac, 
                      dbo.cma_MFactura.statfact
FROM         dbo.Mpagos INNER JOIN
                      dbo.cma_MFactura ON dbo.Mpagos.numfactu = dbo.cma_MFactura.numfactu LEFT OUTER JOIN
                      dbo.Vestaciones ON dbo.Mpagos.workstation = dbo.Vestaciones.Workstation
;


-- Dumping structure for view farmacias.VIEW_Mpagos2CST
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mpagos2CST";
 CREATE VIEW VIEW_Mpagos2CST AS
 SELECT     dbo.Mpagos.numfactu, dbo.Mpagos.fechapago, dbo.Mpagos.codforpa, dbo.Mpagos.codbanco, dbo.Mpagos.codtipotargeta, dbo.Mpagos.nro_forpa, dbo.Mpagos.monto, 
                      dbo.Mpagos.monto_abonado, dbo.Mpagos.usuario, dbo.Mpagos.workstation, dbo.Mpagos.ipaddress, dbo.Mpagos.fecreg, dbo.Mpagos.horareg, 
                      dbo.Mpagos.id_centro, dbo.Mpagos.tipo_doc, dbo.Vestaciones.codestac, dbo.Vestaciones.desestac, dbo.cma_MFactura.statfact,c.[cod_subgrupo]
FROM         dbo.Mpagos INNER JOIN
                      dbo.cma_MFactura ON dbo.Mpagos.numfactu = dbo.cma_MFactura.numfactu LEFT OUTER JOIN
                      dbo.Vestaciones ON dbo.Mpagos.workstation = dbo.Vestaciones.Workstation
					  inner join [dbo].[viewtipofacturascma] c on  dbo.Mpagos.numfactu=c.numfactu
;


-- Dumping structure for view farmacias.VIEW_Mpedido
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mpedido";

CREATE VIEW dbo.VIEW_Mpedido
AS
SELECT dbo.Mpedido.numpedido, dbo.Mpedido.fecha_pedido, 
    dbo.Mproveedor.desproveedor, dbo.Mpedido.fact_compra, 
    dbo.Mpedido.fecha_recepcion, dbo.Mpedido.transferido
FROM dbo.Mpedido LEFT OUTER JOIN
    dbo.Mproveedor ON 
    dbo.Mpedido.cod_proveedor = dbo.Mproveedor.cod_proveedor

;


-- Dumping structure for view farmacias.VIEW_MPostulados
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MPostulados";

CREATE VIEW dbo.VIEW_MPostulados
AS
SELECT     dbo.MPostulados.CodSuc, dbo.MPostulados.Lapso, dbo.MPostulados.Mes, dbo.MPostulados.cod_IM, dbo.IM.Descripcion, dbo.IM.cuota_en, 
                      dbo.MPostulados.Cuota, dbo.IM.abreviatura
FROM         dbo.MPostulados LEFT OUTER JOIN
                      dbo.IM ON dbo.MPostulados.cod_IM = dbo.IM.cod_IM

;


-- Dumping structure for view farmacias.VIEW_Mprecios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mprecios";

CREATE VIEW dbo.VIEW_Mprecios
AS
SELECT     dbo.MPrecios.coditems, dbo.MInventario.desitems, dbo.MPrecios.precunit, dbo.MInventario.Prod_serv, dbo.MPrecios.codtipre, '01' AS codsuc
FROM         dbo.MInventario RIGHT OUTER JOIN
                      dbo.MPrecios ON dbo.MInventario.coditems = dbo.MPrecios.coditems

;


-- Dumping structure for view farmacias.VIEW_Mpresupuestos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Mpresupuestos";

CREATE VIEW dbo.VIEW_Mpresupuestos
AS
SELECT     dbo.Mpresupuestos.numpresu, dbo.Mpresupuestos.fechapre, dbo.MClientes.nombres, dbo.Mstatus.destatus, dbo.Mpresupuestos.codclien, 
                      dbo.Mpresupuestos.statpre, dbo.Mpresupuestos.baseimponible, dbo.Mpresupuestos.tasa, dbo.Mpresupuestos.iva, dbo.Mpresupuestos.subtotal, 
                      dbo.Mpresupuestos.total, dbo.Mpresupuestos.fechavencimiento, dbo.Mpresupuestos.facturado
FROM         dbo.Mpresupuestos LEFT OUTER JOIN
                      dbo.Mstatus ON dbo.Mpresupuestos.statpre = dbo.Mstatus.status LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mpresupuestos.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_MSemanas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSemanas";


CREATE VIEW dbo.VIEW_MSemanas
AS
SELECT     codsuc, codsemana, fec_I, fec_F,
                          (SELECT     isnull(SUM(pacnue), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totnue,
                          (SELECT     isnull(SUM(paccon), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totcon,
                          (SELECT     isnull(SUM(pacser), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpacser,
                          (SELECT     isnull(SUM(facprod + facserv), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totGI,
                          (SELECT     isnull(SUM(pacnue + paccon), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpac,
                          (SELECT     isnull(SUM(potes), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpotes,
                          (SELECT     isnull(SUM(totene), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totene
FROM         dbo.MSemanas


;


-- Dumping structure for view farmacias.VIEW_MSSDDev
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSDDev";

CREATE VIEW [dbo].[VIEW_MSSDDev]
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.MSSDDev.cantidad, dbo.MSSDDev.precunit, dbo.MSSDDev.descuento, 
                      dbo.MSSDDev.monto_imp AS Impuesto, 
                      ROUND(dbo.MSSDDev.precunit * dbo.MSSDDev.cantidad - dbo.MSSDDev.descuento + dbo.MSSDDev.monto_imp, 2) AS subtotal, 
                      dbo.MSSDDev.numnotcre, dbo.MSSDDev.coditems, dbo.MInventario.Prod_serv, dbo.MSSDDev.fechanot
FROM         dbo.MSSDDev LEFT OUTER JOIN
                      dbo.MInventario ON dbo.MSSDDev.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_MSSDescFact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSDescFact";



CREATE VIEW [dbo].[VIEW_MSSDescFact]
AS
SELECT     dbo.Mdescuentos.desdesct, dbo.MSSMDesFact.total, dbo.MSSMDesFact.base, dbo.Mdescuentos.porcentaje, dbo.MSSMDesFact.codesc, 
                      dbo.MSSMDesFact.numfactu, dbo.MSSMDesFact.horareg
FROM         dbo.MSSMDesFact LEFT OUTER JOIN
                      dbo.Mdescuentos ON dbo.MSSMDesFact.codesc = dbo.Mdescuentos.codesc



;


-- Dumping structure for view farmacias.VIEW_MSSDfact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSDfact";

CREATE VIEW [dbo].[VIEW_MSSDfact]
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems
                       , dbo.MSSDFact.cantidad
					   , dbo.MSSDFact.precunit
					   , dbo.MSSDFact.descuento
					   , dbo.MSSDFact.monto_imp AS Impuesto, ROUND(dbo.MSSDFact.precunit * dbo.MSSDFact.cantidad - dbo.MSSDFact.descuento + dbo.MSSDFact.monto_imp, 2) 
                      AS subtotal, dbo.MSSDFact.numfactu, dbo.MSSDFact.coditems, dbo.MInventario.Prod_serv, dbo.MSSDFact.fechafac
FROM         dbo.MSSDFact LEFT OUTER JOIN
                      dbo.MInventario ON dbo.MSSDFact.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.view_MSSDFact_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_MSSDFact_1";
CREATE VIEW  view_MSSDFact_1 AS
SELECT MI.desitems, DF.cantidad,DF.precunit,DF.descuento,DF.monto_imp AS Impuesto, 
ROUND(DF.precunit*DF.cantidad - DF.descuento + DF.monto_imp, 2) AS subtotal, DF.numfactu,DF.coditems,MI.Prod_serv,DF.fechafac, 
DF.aplicadcto,DF.aplicacommed,DF.aplicacomtec,DF.costo,DF.aplicaiva,DF.codtipre,DF.dosis,DF.cant_sugerida,DF.procentaje,DF.Id
FROM   MSSDFact DF LEFT OUTER JOIN
dbo.MInventario MI ON DF.coditems = MI.coditems
;


-- Dumping structure for view farmacias.VIEW_MSSMDet
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSMDet";
CREATE VIEW dbo.VIEW_MSSMDet
AS
SELECT     dbo.MSSMDev.numnotcre, dbo.MSSMDev.fechanot, dbo.MSSMDev.numfactu, dbo.MClientes.nombres, 
                      dbo.Mmedicos.apellido + '  ' + dbo.Mmedicos.nombre AS medico, dbo.MSSMDev.monto, dbo.MSSMDev.descuento, dbo.MSSMDev.totalnot, dbo.Mstatus.destatus, 
                      dbo.MSSMDev.usuario
FROM         dbo.MSSMDev LEFT OUTER JOIN
                      dbo.Mstatus ON dbo.MSSMDev.statnc = dbo.Mstatus.status LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.MSSMDev.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MSSMDev.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_MSSMfact
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSMfact";
CREATE VIEW dbo.VIEW_MSSMfact
AS
SELECT     dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, dbo.MSSMFact.statfact, 
                      dbo.MSSMFact.subtotal, dbo.MSSMFact.descuento, dbo.MSSMFact.iva AS Impuesto1, dbo.MSSMFact.total + dbo.MSSMFact.monto_flete AS total, dbo.Status.Status, 
                      dbo.MSSMFact.totalpvp, dbo.MSSMFact.TotImpuesto AS Impuesto2, dbo.MSSMFact.tipo
FROM         dbo.MSSMFact LEFT OUTER JOIN
                      dbo.Status ON dbo.MSSMFact.statfact = dbo.Status.statfact LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.MSSMFact.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MSSMFact.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_MSSMfact_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_MSSMfact_1";
CREATE VIEW dbo.VIEW_MSSMfact_1
AS
SELECT     dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, dbo.MSSMFact.statfact, 
                      dbo.MSSMFact.subtotal, dbo.MSSMFact.descuento, dbo.MSSMFact.iva AS Impuesto1, dbo.MSSMFact.total + dbo.MSSMFact.monto_flete AS total, dbo.Status.Status, 
                      dbo.MSSMFact.totalpvp, dbo.MSSMFact.TotImpuesto AS Impuesto2, dbo.MSSMFact.tipo, dbo.VIEW_MSSMDet.numnotcre, dbo.MSSMFact.Id, dbo.MClientes.Historia, 
                      dbo.MSSMFact.usuario, dbo.MSSMFact.codmedico
FROM         dbo.MSSMFact LEFT OUTER JOIN
                      dbo.VIEW_MSSMDet ON dbo.MSSMFact.numfactu = dbo.VIEW_MSSMDet.numfactu LEFT OUTER JOIN
                      dbo.Status ON dbo.MSSMFact.statfact = dbo.Status.statfact LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.MSSMFact.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.MSSMFact.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_M_Cliente
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_M_Cliente";
CREATE VIEW dbo.VIEW_M_Cliente
AS
SELECT     dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.MClientes.Cedula, dbo.MClientes.direccionH, dbo.MClientes.telfhabit, dbo.MClientes.CliDesde, 
                      dbo.MClientes.Historia, dbo.MClientes.email, dbo.Pais.PAIS, dbo.States.State, dbo.Ciudad.Ciudad, dbo.Medios.medio AS Medio
FROM         dbo.MClientes LEFT OUTER JOIN
                      dbo.Medios ON dbo.MClientes.medio = dbo.Medios.codigo LEFT OUTER JOIN
                      dbo.Ciudad ON dbo.MClientes.ciudad = dbo.Ciudad.Cod AND dbo.MClientes.Pais = dbo.Ciudad.PAIS AND 
                      dbo.MClientes.ESTADO = dbo.Ciudad.STATE LEFT OUTER JOIN
                      dbo.Pais ON dbo.MClientes.Pais = dbo.Pais.COD LEFT OUTER JOIN
                      dbo.States ON dbo.MClientes.Pais = dbo.States.PAIS AND dbo.MClientes.ESTADO = dbo.States.COD
;


-- Dumping structure for view farmacias.VIEW_M_FACTURA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_M_FACTURA";
CREATE VIEW dbo.VIEW_M_FACTURA
AS
SELECT     numfactu, 2 AS TaxA, 3 AS TaxB
FROM         dbo.MFactura
;


-- Dumping structure for view farmacias.VIEW_M_FACTURAMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_M_FACTURAMSS";

CREATE VIEW [dbo].[VIEW_M_FACTURAMSS]
AS
SELECT     numfactu, 2 AS TaxA, 3 AS TaxB
FROM         dbo.MSSMFact

;


-- Dumping structure for view farmacias.VIEW_n
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_n";
CREATE VIEW dbo.VIEW_n
AS
SELECT     TOP 100 PERCENT *
FROM         INFORMATION_SCHEMA.VIEWS
WHERE     (table_name NOT LIKE 'synco%') AND (table_name NOT LIKE 'sys%')
ORDER BY table_name
;


-- Dumping structure for view farmacias.view_name
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_name";
/****** Script for SelectTopNRows command from SSMS  ******/
CREATE VIEW view_name AS
SELECT Distinct [numfactu]
      ,[nombres]
      ,[fechafac]
      ,[codclien]
      ,[codmedico]
      ,[subtotal]
      ,[descuento]
      ,[total]
      ,[statfact]
      ,[usuario]
      ,[tipopago]
      ,[TotImpuesto]
      ,[monto_flete]
      ,[doc]
      ,[workstation]
      ,[cancelado]
      ,[codsuc]
      ,[medio]
      ,max([cod_subgrupo])  cod_subgrupo
      ,[presentacion]
      ,[cantidad]
  FROM [farmacias].[dbo].[VentasDiariasCMA]
  [numfactu]
  group by  [numfactu]  ,[nombres]
      ,[fechafac]
      ,[codclien]
      ,[codmedico]
      ,[subtotal]
      ,[descuento]
      ,[total]
      ,[statfact]
      ,[usuario]
      ,[tipopago]
      ,[TotImpuesto]
      ,[monto_flete]
      ,[doc]
      ,[workstation]
      ,[cancelado]
      ,[codsuc]
      ,[medio]      
      ,[presentacion]
      ,[cantidad];


-- Dumping structure for view farmacias.VIEW_NE3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NE3";

CREATE VIEW dbo.VIEW_NE3
AS
SELECT     TOP 100 PERCENT fechafac, coditems, precunit
FROM         dbo.DFactura
GROUP BY fechafac, coditems, precunit
ORDER BY fechafac DESC

;


-- Dumping structure for view farmacias.VIEW_NEConsolidado
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NEConsolidado";

CREATE VIEW dbo.VIEW_NEConsolidado
AS
SELECT     dbo.NotaEntrega.numnotent, dbo.NotaEntrega.fechanot, dbo.MClientes.nombres, dbo.NotaEntrega.observacion, dbo.NotaEntrega.usuario, 
                      dbo.NotaEntrega.workstation, '01' AS codsuc, dbo.NotaEntrega.statunot,
                          (SELECT     isnull(SUM(cantidad * costo), 0)
                            FROM          Farmacias.dbo.notentdetalle
                            WHERE      Farmacias.dbo.notentdetalle.numnotent = Farmacias.dbo.notaentrega.numnotent) AS costo,
                          (SELECT     isnull(SUM(cantidad), 0)
                            FROM          Farmacias.dbo.notentdetalle
                            WHERE      Farmacias.dbo.notentdetalle.numnotent = Farmacias.dbo.notaentrega.numnotent) AS items
FROM         dbo.NotaEntrega LEFT OUTER JOIN
                      dbo.MClientes ON dbo.NotaEntrega.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_NotasEntregas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NotasEntregas";
CREATE VIEW dbo.VIEW_NotasEntregas
AS
SELECT     dbo.NotaEntrega.numnotent, dbo.NotaEntrega.fechanot, dbo.MClientes.nombres, dbo.Mstatus.destatus, dbo.NotaEntrega.codclien
FROM         dbo.Mstatus RIGHT OUTER JOIN
                      dbo.NotaEntrega ON dbo.Mstatus.status = dbo.NotaEntrega.statunot LEFT OUTER JOIN
                      dbo.MClientes ON dbo.NotaEntrega.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_NotasEntregas1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NotasEntregas1";

CREATE VIEW dbo.VIEW_NotasEntregas1
AS
SELECT     dbo.NotEntDetalle.coditems, dbo.NotEntDetalle.cantidad, dbo.NotEntDetalle.costo, dbo.NotEntDetalle.numnotent, dbo.MInventario.desitems, 
                      dbo.NotEntDetalle.fechanot, dbo.NotaEntrega.statunot, '01' AS codsuc
FROM         dbo.MInventario RIGHT OUTER JOIN
                      dbo.NotEntDetalle ON dbo.MInventario.coditems = dbo.NotEntDetalle.coditems LEFT OUTER JOIN
                      dbo.NotaEntrega ON dbo.NotEntDetalle.numnotent = dbo.NotaEntrega.numnotent

;


-- Dumping structure for view farmacias.VIEW_NotEntDetalle
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NotEntDetalle";

CREATE VIEW dbo.VIEW_NotEntDetalle
AS
SELECT     dbo.MInventario.desitems, dbo.NotEntDetalle.cantidad, dbo.NotEntDetalle.fechanot, dbo.NotEntDetalle.numnotent, dbo.NotEntDetalle.coditems
FROM         dbo.MInventario RIGHT OUTER JOIN
                      dbo.NotEntDetalle ON dbo.MInventario.coditems = dbo.NotEntDetalle.coditems

;


-- Dumping structure for view farmacias.VIEW_Not_In_Mconsultas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Not_In_Mconsultas";

CREATE VIEW dbo.VIEW_Not_In_Mconsultas
AS
SELECT     TOP 100 PERCENT codclien, fechafac, numfactu, statfact
FROM         dbo.MFactura
WHERE     (codclien NOT IN
                          (SELECT     codclien AS Cli_cons
                            FROM          mconsultas))
ORDER BY numfactu

;


-- Dumping structure for view farmacias.VIEW_Not_In_Mconsultas2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Not_In_Mconsultas2";

CREATE VIEW dbo.VIEW_Not_In_Mconsultas2
AS
SELECT     dbo.VIEW_Not_In_Mconsultas.numfactu, dbo.VIEW_Not_In_Mconsultas.fechafac, dbo.DFactura.coditems, dbo.DFactura.cantidad, 
                      dbo.MInventario.desitems, dbo.MClientes.nombres, dbo.MClientes.telfhabit, dbo.VIEW_Not_In_Mconsultas.statfact, 
                      dbo.VIEW_Not_In_Mconsultas.codclien
FROM         dbo.VIEW_Not_In_Mconsultas INNER JOIN
                      dbo.DFactura ON dbo.VIEW_Not_In_Mconsultas.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems INNER JOIN
                      dbo.MClientes ON dbo.VIEW_Not_In_Mconsultas.codclien = dbo.MClientes.codclien
WHERE     (dbo.VIEW_Not_In_Mconsultas.statfact <> 2)

;


-- Dumping structure for view farmacias.VIEW_No_Citados
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_No_Citados";

CREATE VIEW dbo.VIEW_No_Citados
AS
SELECT     dbo.MClientes.nombres, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.citados, dbo.Mconsultas.NoCitados, dbo.MClientes.telfhabit, 
                      dbo.MClientes.direccionH
FROM         dbo.Mconsultas LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien
WHERE     (dbo.Mconsultas.NoCitados = '5') AND (dbo.Mconsultas.citados = '0')

;


-- Dumping structure for view farmacias.VIEW_NueRepVen
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NueRepVen";
CREATE VIEW dbo.VIEW_NueRepVen
AS
SELECT     TOP 100 PERCENT numfactu, nombres, fechafac, codclien, codmedico, subtotal, descuento, total, statfact, usuario, tipopago, doc, TotImpuesto
FROM         dbo.VIEW_NueRepVen1
ORDER BY numfactu
;


-- Dumping structure for view farmacias.VIEW_NueRepVen1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_NueRepVen1";
CREATE VIEW dbo.VIEW_NueRepVen1
AS
SELECT     dbo.MFactura.numfactu, dbo.MClientes.nombres, dbo.MFactura.fechafac, dbo.MFactura.codclien, dbo.MFactura.codmedico, dbo.MFactura.subtotal, 
                      dbo.MFactura.descuento, dbo.MFactura.total, dbo.MFactura.statfact, dbo.MFactura.usuario, dbo.MFactura.tipopago, dbo.MFactura.TotImpuesto, 
                      '01' AS doc
FROM         dbo.MFactura INNER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien
UNION ALL
SELECT     dbo.Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.Mnotacredito.fechanot AS fechafac, dbo.Mnotacredito.codclien, 
                      dbo.Mnotacredito.codmedico, dbo.Mnotacredito.subtotal, dbo.Mnotacredito.descuento, dbo.Mnotacredito.totalnot AS total, 
                      dbo.Mnotacredito.statnc AS statfact, dbo.Mnotacredito.usuario, 3 AS tipopago, mnotacredito.totimpuesto, '04' AS Doc
FROM         dbo.Mnotacredito INNER JOIN
                      dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_Pacientes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Pacientes";
CREATE VIEW dbo.VIEW_Pacientes
AS
SELECT     dbo.MClientes.codclien, dbo.MClientes.nombres, dbo.MClientes.Cedula, dbo.MClientes.telfhabit, 
                      dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS medico, dbo.MClientes.Historia, dbo.MClientes.direccionH, dbo.MClientes.codpostal, 
                      dbo.MClientes.fallecido
FROM         dbo.MClientes INNER JOIN
                      dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.MClientes.nombres NOT LIKE '%FALLECI%') AND (dbo.MClientes.fallecido = 0)
;


-- Dumping structure for view farmacias.VIEW_Pacientes1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Pacientes1";
CREATE VIEW dbo.VIEW_Pacientes1
AS
SELECT     dbo.MClientes.nombres, dbo.MClientes.Historia, dbo.MClientes.Cedula, dbo.MClientes.codclien, 
                      dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS medico, dbo.MClientes.codmedico, dbo.MClientes.telfhabit, dbo.MClientes.direccionH, 
                      dbo.MClientes.codpostal
FROM         dbo.MClientes INNER JOIN
                      dbo.Mmedicos ON dbo.MClientes.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.MClientes.inactivo = 0)
;


-- Dumping structure for view farmacias.VIEW_PacientesRepetidos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PacientesRepetidos";

CREATE VIEW dbo.VIEW_PacientesRepetidos
AS
SELECT     TOP 100 PERCENT Cedula, nombres, Historia, codclien
FROM         dbo.MClientes Malos
WHERE     (Cedula NOT IN
                          (SELECT     Buenos.Cedula
                            FROM          MClientes Buenos
                            GROUP BY Buenos.Cedula
                            HAVING      COUNT(Buenos.Cedula) = 1))
ORDER BY Cedula

;


-- Dumping structure for view farmacias.VIEW_pacientes_sin_productos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_pacientes_sin_productos";
CREATE VIEW dbo.VIEW_pacientes_sin_productos AS SELECT TOP 100 PERCENT codclien, fechafac, numfactu From dbo.mfactura WHERE (codclien NOT IN (SELECT TOP 100 PERCENT dbo.MFactura.codclien FROM dbo.MFactura INNER JOIN dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems WHERE (dbo.DFactura.aplicacommed = '1') AND (dbo.MFactura.statfact <> '2') AND mfactura.fechafac >= '3/27/2016' AND mfactura.fechafac <= '4/6/2016' )) AND (statfact <> '2') AND (fechafac >= '3/27/2016') AND (fechafac <= '4/6/2016');


-- Dumping structure for view farmacias.view_pagosgroupmss
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_pagosgroupmss";
create view view_pagosgroupmss as 
SELECT a.fechapago,a.numfactu,  a.monto, a.tipo_doc,  modopago
FROM
    VIEWPagosPRMSS_W7 a
	INNER JOIN  MDocumentos b ON
        a.tipo_doc = b.codtipodoc
where a.statfact=3

union all

SELECT   b.fechapago,b.numnotcre, monto,  b.tipo_doc,  modopago
FROM            dbo.VIEWPagosDEVMSS b 
where b.statnc=3
;


-- Dumping structure for view farmacias.VIEW_PATOLOGIAS POR EDAD
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PATOLOGIAS POR EDAD";


CREATE VIEW dbo.[VIEW_PATOLOGIAS POR EDAD]
AS
SELECT     TOP 20 dbo.MFactura.codclien, dbo.MFactura.fechafac, dbo.MClientes.NACIMIENTO, dbo.MFactura.patologia, 
                      dbo.PATOLOGIA500.PATOLOGIA AS DesPatologia, dbo.PATOLOGIA500.cantidad, dbo.PATOLOGIA500.Masculino, dbo.PATOLOGIA500.Femenino, 
                      dbo.MClientes.nombres, dbo.MFactura.statfact
FROM         dbo.MFactura INNER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.PATOLOGIA500 ON dbo.MFactura.patologia = dbo.PATOLOGIA500.CODIGO
WHERE     (dbo.MFactura.statfact <> 2) AND (dbo.PATOLOGIA500.cantidad > 0)
ORDER BY dbo.PATOLOGIA500.cantidad DESC


;


-- Dumping structure for view farmacias.VIEW_Patologias500
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Patologias500";


CREATE VIEW dbo.VIEW_Patologias500
AS
SELECT     dbo.PATOLOGIA500.PATOLOGIA, dbo.PATOLOGIA500.CODIGO, dbo.FORMULA500.CODITEMS, dbo.FORMULA500.TRIANGULO, 
                      dbo.MInventario.desitems
FROM         dbo.PATOLOGIA500 INNER JOIN
                      dbo.FORMULA500 ON dbo.PATOLOGIA500.CODIGO = dbo.FORMULA500.CODIGO INNER JOIN
                      dbo.MInventario ON dbo.FORMULA500.CODITEMS = dbo.MInventario.coditems


;


-- Dumping structure for view farmacias.VIEW_PATOLOGIA_T20
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PATOLOGIA_T20";


CREATE VIEW dbo.VIEW_PATOLOGIA_T20
AS
SELECT     TOP 20 PATOLOGIA, CODIGO, fecha, cantidad, Factura
FROM         dbo.PATOLOGIA500
ORDER BY cantidad DESC


;


-- Dumping structure for view farmacias.VIEW_patologia_t20F
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_patologia_t20F";


CREATE VIEW dbo.VIEW_patologia_t20F
AS
SELECT     TOP 20 PATOLOGIA, CODIGO, fecha, cantidad, Factura, Masculino, Femenino
FROM         dbo.PATOLOGIA500
ORDER BY Femenino DESC


;


-- Dumping structure for view farmacias.VIEW_patologia_t20M
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_patologia_t20M";


CREATE VIEW dbo.VIEW_patologia_t20M
AS
SELECT     TOP 20 PATOLOGIA, CODIGO, fecha, cantidad, Factura, Masculino, Femenino
FROM         dbo.PATOLOGIA500
ORDER BY Masculino DESC


;


-- Dumping structure for view farmacias.view_percentstats
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_percentstats";
CREATE VIEW dbo.view_percentstats
AS
SELECT     a.codclien, a.fechafac, a.codmedico
FROM         MFactura a
WHERE     a.statfact <> '2'
UNION
SELECT     a.codclien, a.fechafac, a.codmedico
FROM         MSSMFact a
WHERE     a.statfact <> '2'
UNION
SELECT     a.codclien, a.fechafac, a.codmedico
FROM         cma_MFactura a INNER JOIN
                      cma_DFactura b ON a.numfactu = b.numfactu AND CHARINDEX('ST', b.coditems) > 0
GROUP BY a.codmedico, a.codclien, a.fechafac
;


-- Dumping structure for view farmacias.VIEW_PMPF_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PMPF_0215";
CREATE VIEW dbo.VIEW_PMPF_0215
AS
SELECT     dbo.VIEW_Asistidos_0215.fecha_cita, dbo.VIEW_Stat_Rel01_0215.cantUni, dbo.VIEW_Stat_Rel01_0215.canFor, 
                      dbo.VIEW_Stat_Rel01_0215.canPVistos, dbo.VIEW_Stat_Rel01_0215.UxP, dbo.VIEW_Stat_Rel01_0215.FxP, 
                      dbo.VIEW_Stat_Rel01_0215.FacXTMedico, dbo.VIEW_Stat_Rel01_0215.promPacmed, dbo.VIEW_Stat_Rel01_0215.porcentUniForm, 
                      dbo.VIEW_Stat_Rel01_0215.NumRecords, dbo.VIEW_VentaProd_0215.*
FROM         dbo.VIEW_VentaProd_0215 INNER JOIN
                      dbo.VIEW_Asistidos_0215 ON dbo.VIEW_VentaProd_0215.codclien = dbo.VIEW_Asistidos_0215.codclien AND 
                      dbo.VIEW_VentaProd_0215.fechafac = dbo.VIEW_Asistidos_0215.fecha_cita INNER JOIN
                      dbo.VIEW_Stat_Rel01_0215 ON dbo.VIEW_VentaProd_0215.codmedico = dbo.VIEW_Stat_Rel01_0215.codmedico
;


-- Dumping structure for view farmacias.VIEW_PO
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PO";
CREATE VIEW dbo.VIEW_PO
AS
SELECT     dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.cantidad, dbo.MInventario.coditems, dbo.MInventario.desitems
FROM         dbo.MFactura INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.MFactura.statfact <> 2)
union all
Select M.numfactu,M.fechafac,M.statfact,L.cantidad,K.codikit AS coditems, I.desitems from MSSMFact M
Inner Join MSSDFact L On M.numfactu=L.numfactu
inner join Kit K ON L.coditems=K.coditems
Inner join MInventario I ON K.codikit=I.coditems
Where  M.statfact<>2 
;


-- Dumping structure for view farmacias.VIEW_Podtransf
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Podtransf";

CREATE VIEW dbo.VIEW_Podtransf
AS
SELECT     dbo.Almacen.coditems, dbo.MInventario.desitems, dbo.Almacen.existencia, dbo.Almacen.codsucursal, ' ' AS blanco, dbo.Almacen.costo
FROM         dbo.Almacen INNER JOIN
                      dbo.MInventario ON dbo.Almacen.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VIEW_postulado
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_postulado";

CREATE VIEW dbo.VIEW_postulado
AS
SELECT     dbo.DPostulados.CodSuc, CAST(STR(dbo.DPostulados.ds) + '/' + STR(dbo.DPostulados.Mes) + '/' + STR(dbo.DPostulados.Lapso) AS datetime) AS fecha,
                       dbo.DPostulados.Cuota, dbo.DPostulados.cod_IM
FROM         dbo.MPostulados INNER JOIN
                      dbo.DPostulados ON dbo.MPostulados.CodSuc = dbo.DPostulados.CodSuc AND dbo.MPostulados.Lapso = dbo.DPostulados.Lapso AND 
                      dbo.MPostulados.Mes = dbo.DPostulados.Mes AND dbo.MPostulados.cod_IM = dbo.DPostulados.cod_IM

;


-- Dumping structure for view farmacias.VIEW_PRECIOS_VIEJOS_AUDITORIA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_PRECIOS_VIEJOS_AUDITORIA";

CREATE VIEW dbo.VIEW_PRECIOS_VIEJOS_AUDITORIA
AS
SELECT     TOP 100 PERCENT dbo.MInventario.desitems, dbo.DFactura.coditems, dbo.DFactura.fechafac, dbo.DFactura.precunit, dbo.MInventario.Prod_serv
FROM         dbo.DFactura RIGHT OUTER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE     (dbo.DFactura.fechafac >= '01/11/2003') AND (dbo.DFactura.fechafac <= '31/12/2003') AND (dbo.MInventario.Prod_serv = 'P')
ORDER BY dbo.MInventario.desitems

;


-- Dumping structure for view farmacias.view_presupuesto_1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_presupuesto_1";
create view view_presupuesto_1 as 
  SELECT        TOP (100) PERCENT dbo.MInventario.desitems, dbo.presupuesto_d.cantidad, dbo.presupuesto_d.precunit, dbo.presupuesto_d.descuento, dbo.presupuesto_d.monto_imp AS Impuesto, 
                         ROUND(dbo.presupuesto_d.precunit * dbo.presupuesto_d.cantidad - dbo.presupuesto_d.descuento + dbo.presupuesto_d.monto_imp, 2) AS subtotal, dbo.presupuesto_d.numfactu, dbo.presupuesto_d.coditems, dbo.MInventario.Prod_serv, dbo.presupuesto_d.fechafac, 
                         dbo.presupuesto_d.aplicadcto, dbo.presupuesto_d.aplicacommed, dbo.presupuesto_d.aplicacomtec, dbo.presupuesto_d.costo, dbo.presupuesto_d.aplicaiva, dbo.presupuesto_d.codtipre, dbo.presupuesto_d.dosis, dbo.presupuesto_d.cant_sugerida, dbo.presupuesto_d.procentaje, 
                         dbo.presupuesto_d.Id
FROM            dbo.presupuesto_d LEFT OUTER JOIN
                         dbo.MInventario ON dbo.presupuesto_d.coditems = dbo.MInventario.coditems;


-- Dumping structure for view farmacias.view_presupuesto_list
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_presupuesto_list";
create view view_presupuesto_list as
SELECT        dbo.presupuesto_m.numfactu, dbo.presupuesto_m.fechafac, dbo.MClientes.nombres, dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS Medico, dbo.presupuesto_m.statfact, dbo.presupuesto_m.subtotal, dbo.presupuesto_m.descuento, 
                         dbo.presupuesto_m.iva AS Impuesto1, dbo.presupuesto_m.total + dbo.presupuesto_m.monto_flete AS total, dbo.Status.Status, dbo.presupuesto_m.totalpvp, dbo.presupuesto_m.TotImpuesto AS Impuesto2, dbo.presupuesto_m.Id, dbo.presupuesto_m.monto_flete, 
                         dbo.presupuesto_m.TotImpuesto, dbo.presupuesto_m.codclien, dbo.presupuesto_m.usuario, dbo.presupuesto_m.codmedico, dbo.MClientes.Historia
FROM            dbo.presupuesto_m LEFT OUTER JOIN
                         dbo.Status ON dbo.presupuesto_m.statfact = dbo.Status.statfact LEFT OUTER JOIN
                         dbo.Mmedicos ON dbo.presupuesto_m.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                         dbo.MClientes ON dbo.presupuesto_m.codclien = dbo.MClientes.codclien;


-- Dumping structure for view farmacias.VIEW_ProtocoloxFactura
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ProtocoloxFactura";


CREATE VIEW dbo.VIEW_ProtocoloxFactura
AS
SELECT     dbo.MFactura.codclien, dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.patologia, dbo.VIEW_Patologias500.desitems, 
                      dbo.VIEW_Patologias500.CODITEMS, ISNULL
                          ((SELECT     coditems
                              FROM         dfactura
                              WHERE     dfactura.numfactu = mfactura.numfactu AND dfactura.coditems = view_patologias500.coditems), '') AS tratamiento, 
                      dbo.MFactura.codmedico
FROM         dbo.MFactura RIGHT OUTER JOIN
                      dbo.VIEW_Patologias500 ON dbo.MFactura.patologia = dbo.VIEW_Patologias500.CODIGO
WHERE     (dbo.MFactura.statfact <> 2)


;


-- Dumping structure for view farmacias.VIEW_ProtocoloxFactura2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ProtocoloxFactura2";


CREATE VIEW dbo.VIEW_ProtocoloxFactura2
AS
SELECT     dbo.MFactura.codclien, dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.patologia, dbo.VIEW_Patologias500.desitems, 
                      dbo.VIEW_Patologias500.CODITEMS, ISNULL
                          ((SELECT     coditems
                              FROM         presmedica
                              WHERE     presmedica.numfactu = mfactura.numfactu AND presmedica.coditems = view_patologias500.coditems), '') AS tratamiento, 
                      dbo.MFactura.codmedico
FROM         dbo.MFactura RIGHT OUTER JOIN
                      dbo.VIEW_Patologias500 ON dbo.MFactura.patologia = dbo.VIEW_Patologias500.CODIGO
WHERE     (dbo.MFactura.statfact <> 2)


;


-- Dumping structure for view farmacias.VIEW_RegControl
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_RegControl";

CREATE VIEW dbo.VIEW_RegControl
AS
SELECT     '01' AS CodSuc, MAX(fechacierre) AS UltimaFecha,
                          (SELECT     dbo.Msucursales.Sucursal
                            FROM          msucursales
                            WHERE      dbo.Msucursales.codsuc = '01') AS suc
FROM         farmacias.dbo.MCierreInventario
WHERE     (status = '1')

;


-- Dumping structure for view farmacias.view_repconsultas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_repconsultas";
CREATE VIEW dbo.view_repconsultas
AS
SELECT     dbo.Mconsultas.codclien, dbo.MClientes.nombres, dbo.MClientes.telfhabit, dbo.Mmedicos.nombre + N' ' + LTRIM(dbo.Mmedicos.apellido) AS Medicos, 
                      dbo.MClientes.Historia, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado, dbo.Mconsultas.asistido, dbo.Mconsultas.noasistido, dbo.Mconsultas.activa, 
                      dbo.Mconsultas.usuario, dbo.Mconsultas.codconsulta, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.hora, dbo.tipoconsulta.descons, dbo.Mconsultas.observacion, 
                      dbo.MClientes.fallecido, dbo.MClientes.Id, dbo.MClientes.celular
FROM         dbo.Mconsultas LEFT OUTER JOIN
                      dbo.tipoconsulta ON dbo.Mconsultas.codconsulta = dbo.tipoconsulta.codconsulta LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_repconsultas1
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultas1";
CREATE VIEW dbo.VIEW_repconsultas1
AS
SELECT     codclien, fecha_cita AS ProximaCita, asistido, hora
FROM         dbo.Mconsultas
WHERE     (asistido <> 3)
;


-- Dumping structure for view farmacias.VIEW_repconsultas1W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultas1W7";
CREATE VIEW dbo.VIEW_repconsultas1W7
AS
SELECT     codclien, fecha_cita AS ProximaCita, asistido, hora
FROM         dbo.Mconsultas
WHERE     (asistido <> 3)
;


-- Dumping structure for view farmacias.VIEW_repconsultas3
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultas3";
CREATE VIEW dbo.VIEW_repconsultas3
AS
SELECT DISTINCT 
                      TOP (100) PERCENT dbo.view_repconsultas.codclien, dbo.view_repconsultas.nombres, dbo.view_repconsultas.telfhabit, dbo.view_repconsultas.Medicos, 
                      dbo.view_repconsultas.Historia, dbo.view_repconsultas.fecha_cita, dbo.view_repconsultas.hora, dbo.view_repconsultas.citados, dbo.view_repconsultas.confirmado, 
                      dbo.view_repconsultas.noasistido, dbo.view_repconsultas.activa, dbo.view_repconsultas.asistido, dbo.view_repconsultas.usuario, 
                      dbo.view_repconsultas.codconsulta, dbo.VIEW_repconsultas1.ProximaCita, dbo.view_repconsultas.descons, dbo.view_repconsultas.observacion, 
                      dbo.view_repconsultas.Id, dbo.view_repconsultas.celular
FROM         dbo.view_repconsultas LEFT OUTER JOIN
                      dbo.VIEW_repconsultas1 ON dbo.view_repconsultas.codclien = dbo.VIEW_repconsultas1.codclien
;


-- Dumping structure for view farmacias.VIEW_repconsultas3W7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultas3W7";
CREATE VIEW dbo.VIEW_repconsultas3W7
AS
SELECT DISTINCT 
                      TOP 100 PERCENT dbo.view_repconsultasW7.codclien, dbo.view_repconsultasW7.nombres, dbo.view_repconsultasW7.telfhabit, 
                      dbo.view_repconsultasW7.Medicos, dbo.view_repconsultasW7.Historia, dbo.view_repconsultasW7.citados, dbo.view_repconsultasW7.confirmado, 
                      dbo.view_repconsultasW7.asistido, dbo.view_repconsultasW7.noasistido, dbo.view_repconsultasW7.activa, dbo.view_repconsultasW7.usuario, 
                      dbo.view_repconsultasW7.codconsulta, dbo.view_repconsultasW7.fecha_cita, dbo.view_repconsultasW7.hora, dbo.view_repconsultasW7.descons, 
                      dbo.view_repconsultasW7.observacion, dbo.view_repconsultasW7.fallecido, dbo.view_repconsultasW7.regusuario, 
                      dbo.VIEW_repconsultas1W7.ProximaCita
FROM         dbo.VIEW_repconsultas1W7 RIGHT OUTER JOIN
                      dbo.view_repconsultasW7 ON dbo.VIEW_repconsultas1W7.codclien = dbo.view_repconsultasW7.codclien
ORDER BY dbo.view_repconsultasW7.Medicos
;


-- Dumping structure for view farmacias.VIEW_repconsultas4
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultas4";
CREATE VIEW dbo.VIEW_repconsultas4
AS
SELECT     TOP 100 PERCENT dbo.view_repconsultas.nombres, dbo.view_repconsultas.codclien, dbo.view_repconsultas.telfhabit, 
                      dbo.view_repconsultas.Medicos, dbo.view_repconsultas.Historia, dbo.view_repconsultas.citados, dbo.view_repconsultas.confirmado, 
                      dbo.view_repconsultas.asistido, dbo.view_repconsultas.noasistido, dbo.view_repconsultas.activa, dbo.view_repconsultas.usuario, 
                      dbo.view_repconsultas.codconsulta, dbo.view_repconsultas.fecha_cita, CASE len(dbo.view_repconsultas.hora) 
                      WHEN 8 THEN SUBSTRING(dbo.view_repconsultas.hora, 1, 5) ELSE SUBSTRING(dbo.view_repconsultas.hora, 1, 4) END AS hora, 
                      dbo.view_repconsultas.descons, dbo.VIEW_repconsultasb.UltimaAsistida, dbo.view_repconsultas.observacion, dbo.view_repconsultas.fallecido
FROM         dbo.view_repconsultas LEFT OUTER JOIN
                      dbo.VIEW_repconsultasb ON dbo.view_repconsultas.codclien = dbo.VIEW_repconsultasb.codclien
ORDER BY dbo.view_repconsultas.Medicos
;


-- Dumping structure for view farmacias.VIEW_repconsultasb
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultasb";


CREATE VIEW dbo.VIEW_repconsultasb
AS
SELECT     TOP 100 PERCENT codclien, MAX(fecha_cita) AS UltimaAsistida
FROM         dbo.Mconsultas
WHERE     (asistido = 3)
GROUP BY codclien


;


-- Dumping structure for view farmacias.VIEW_repconsultasNCitas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_repconsultasNCitas";

CREATE VIEW dbo.VIEW_repconsultasNCitas
AS
SELECT     TOP 100 PERCENT codclien, COUNT(fecha_cita) AS NumeroCitas
FROM         dbo.Mconsultas
WHERE     (asistido = 3)
GROUP BY codclien

;


-- Dumping structure for view farmacias.view_repconsultasW7
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_repconsultasW7";
CREATE VIEW dbo.view_repconsultasW7
AS
SELECT     dbo.Mconsultas.codclien, dbo.MClientes.nombres, dbo.MClientes.telfhabit, dbo.Mmedicos.nombre + N' ' + LTRIM(dbo.Mmedicos.apellido) AS Medicos, 
                      dbo.MClientes.Historia, dbo.Mconsultas.citados, dbo.Mconsultas.confirmado, dbo.Mconsultas.asistido, dbo.Mconsultas.noasistido, 
                      dbo.Mconsultas.activa, dbo.Mconsultas.usuario, dbo.Mconsultas.codconsulta, dbo.Mconsultas.fecha_cita, dbo.Mconsultas.hora, 
                      dbo.tipoconsulta.descons, dbo.Mconsultas.observacion, dbo.MClientes.fallecido, dbo.Mconsultas.regusuario
FROM         dbo.Mconsultas LEFT OUTER JOIN
                      dbo.tipoconsulta ON dbo.Mconsultas.codconsulta = dbo.tipoconsulta.codconsulta LEFT OUTER JOIN
                      dbo.Mmedicos ON dbo.Mconsultas.codmedico = dbo.Mmedicos.Codmedico LEFT OUTER JOIN
                      dbo.MClientes ON dbo.Mconsultas.codclien = dbo.MClientes.codclien
;


-- Dumping structure for view farmacias.VIEW_Reporte
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Reporte";
CREATE view  VIEW_Reporte as SELECT DISTINCT dbo.DFactura.coditems, dbo.MFactura.codclien, dbo.MClientes.nombres, dbo.MClientes.direccionH, dbo.MClientes.telfhabit, dbo.MClientes.celular,  dbo.MClientes.email FROM dbo.DFactura INNER JOIN dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems INNER JOIN dbo.MFactura ON dbo.DFactura.numfactu = dbo.MFactura.numfactu LEFT OUTER JOIN dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien WHERE (dbo.DFactura.fechafac BETWEEN  '20080101' AND  '20090120') ;


-- Dumping structure for view farmacias.VIEW_REPORTE_CLI_MED
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_REPORTE_CLI_MED";

CREATE VIEW dbo.VIEW_REPORTE_CLI_MED
AS
SELECT dbo.MFactura.numfactu, dbo.MFactura.fechafac, 
    dbo.MClientes.nombres, dbo.MFactura.subtotal, 
    dbo.MFactura.descuento, dbo.MFactura.total, 
    dbo.Mmedicos.nombre, dbo.MFactura.codmedico, 
    dbo.MClientes.codclien, dbo.MFactura.statfact
FROM dbo.MFactura INNER JOIN
    dbo.Mmedicos ON 
    dbo.MFactura.codmedico = dbo.Mmedicos.Codmedico INNER JOIN
    dbo.MClientes ON 
    dbo.MFactura.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.VIEW_Reposicion
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Reposicion";

CREATE VIEW dbo.VIEW_Reposicion
AS
SELECT     TOP 100 PERCENT LTRIM(STR(YEAR(dbo.DFactura.fechafac))) + LTRIM(STR(MONTH(dbo.DFactura.fechafac))) AS periodo, SUM(dbo.DFactura.cantidad) 
                      AS cantidad1, dbo.DFactura.coditems, dbo.MInventario.desitems
FROM         dbo.DFactura LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems LEFT OUTER JOIN
                      dbo.MFactura ON dbo.DFactura.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.DFactura.tipoitems = 'P') AND (dbo.MFactura.statfact <> '2')
GROUP BY dbo.DFactura.coditems, LTRIM(STR(YEAR(dbo.DFactura.fechafac))) + LTRIM(STR(MONTH(dbo.DFactura.fechafac))), dbo.MInventario.desitems
ORDER BY LTRIM(STR(YEAR(dbo.DFactura.fechafac))) + LTRIM(STR(MONTH(dbo.DFactura.fechafac)))

;


-- Dumping structure for view farmacias.VIEW_RETURN_CONSSUERO
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_RETURN_CONSSUERO";
CREATE view VIEW_RETURN_CONSSUERO AS
SELECT A.numnotcre, B.coditems,C.cod_subgrupo,B.cantidad,B.precunit, (B.cantidad*B.precunit) ST , a.subtotal, A.descuento AS DISCOUNT, A.statnc,A.descuento,A.totalnot,A.codclien,A.codmedico,A.fechanot,A.numfactu,A.usuario, D.nombres,D.Historia,A.concepto 
FROM CMA_Mnotacredito A
INNER JOIN CMA_Dnotacredito B ON A.numnotcre=B.numnotcre
INNER JOIN MInventario C ON C.coditems=B.coditems
INNER JOIN MClientes D ON A.codclien=D.codclien
WHERE A.statnc<>2
;


-- Dumping structure for view farmacias.VIEW_RETURN_LASER_SPLIT
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_RETURN_LASER_SPLIT";
CREATE view VIEW_RETURN_LASER_SPLIT AS
SELECT A.numnotcre, B.coditems,C.cod_subgrupo,B.cantidad,B.precunit, (B.cantidad*B.precunit) ST , a.subtotal, A.descuento AS DISCOUNT, A.statnc,A.descuento,A.totalnot,A.codclien,A.codmedico,A.fechanot,A.numfactu,A.usuario, D.nombres,D.Historia,A.concepto FROM MSSMDev A
INNER JOIN MSSDDev B ON A.numnotcre=B.numnotcre
INNER JOIN MInventario C ON C.coditems=B.coditems
INNER JOIN MClientes D ON A.codclien=D.codclien
WHERE A.statnc<>2
;


-- Dumping structure for view farmacias.VIEW_RETURN_PRODUCTOS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_RETURN_PRODUCTOS";
CREATE view VIEW_RETURN_PRODUCTOS AS
SELECT A.numnotcre,  a.subtotal ST, A.descuento AS DISCOUNT, A.statnc,A.descuento,A.totalnot,A.codclien,A.codmedico,A.fechanot,A.numfactu,A.usuario, D.nombres,D.Historia,A.concepto 
FROM Mnotacredito A
INNER JOIN MClientes D ON A.codclien=D.codclien
WHERE A.statnc<>2;


-- Dumping structure for view farmacias.VIEW_Semanas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Semanas";


CREATE VIEW dbo.VIEW_Semanas
AS
SELECT     codsuc, codsemana, fec_I, fec_F,
                          (SELECT     isnull(SUM(pacnue), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totnue,
                          (SELECT     isnull(SUM(paccon), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totcon,
                          (SELECT     isnull(SUM(pacser), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpacser,
                          (SELECT     isnull(SUM(facprod + facserv), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totGI,
                          (SELECT     isnull(SUM(pacnue + paccon), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpac,
                          (SELECT     isnull(SUM(potes), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totpotes,
                          (SELECT     isnull(SUM(totene), 0)
                            FROM          estadisticasglobales
                            WHERE      estadisticasglobales.codsuc = msemanas.codsuc AND fecha >= fec_i AND fecha <= fec_f) AS totene
FROM         dbo.MSemanas


;


-- Dumping structure for view farmacias.VIEW_Servicios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Servicios";

CREATE VIEW dbo.VIEW_Servicios
AS
SELECT     dbo.cma_MFactura.numfactu, dbo.cma_MFactura.codmedico, dbo.cma_DFactura.fechafac, dbo.cma_DFactura.coditems, dbo.cma_DFactura.cantidad, 
                      dbo.cma_DFactura.precunit, dbo.cma_DFactura.descuento, dbo.cma_DFactura.aplicaiva, dbo.cma_DFactura.aplicadcto, 
                      dbo.cma_DFactura.aplicacommed, dbo.cma_DFactura.aplicacomtec, dbo.cma_DFactura.tipoitems, dbo.cma_DFactura.codtipre, 
                      dbo.cma_MFactura.statfact, '01' AS codsuc
FROM         dbo.cma_MFactura RIGHT OUTER JOIN
                      dbo.cma_DFactura ON dbo.cma_MFactura.numfactu = dbo.cma_DFactura.numfactu

;


-- Dumping structure for view farmacias.VIEW_sinrepetir
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_sinrepetir";
CREATE VIEW dbo.VIEW_sinrepetir
AS
SELECT DISTINCT TOP 100 PERCENT codclien, fecha_cita
FROM         dbo.Mconsultas
WHERE     (activa = '1')
ORDER BY codclien, fecha_cita
;


-- Dumping structure for view farmacias.VIEW_SoloConsulta
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_SoloConsulta";


CREATE VIEW dbo.VIEW_SoloConsulta
AS
SELECT     TOP 100 PERCENT codconsulta AS codcons, descons, tipo, '9999999999' AS coditems, codconsulta + '9999999999' AS codconsulta
FROM         dbo.tipoconsulta
WHERE     (codconsulta <> '08') AND (codconsulta <> '07')
ORDER BY codconsulta, descons


;


-- Dumping structure for view farmacias.VIEW_SoloServicios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_SoloServicios";
CREATE VIEW dbo.VIEW_SoloServicios
AS
SELECT        TOP (100) PERCENT '07' AS codcons, desitems AS descons, '9' AS tipo, coditems, '07' + coditems AS codconsulta, cod_subgrupo
FROM            dbo.MInventario
WHERE        (Prod_serv = 'S' OR
                         Prod_serv IN ('M', 'c')) AND (activo = 1) AND (cod_grupo = '004')
ORDER BY codconsulta, descons
;


-- Dumping structure for view farmacias.VIEW_Stat_Enl_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Enl_0215";
CREATE VIEW dbo.VIEW_Stat_Enl_0215
AS
SELECT     codmedico, fechafac, codclien, cod_subgrupo, cantidad, precunit, descuento, desitems, coditems, medico
FROM         dbo.VIEW_VentaProd_0215
;


-- Dumping structure for view farmacias.VIEW_Stat_FacturacionxMedico
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_FacturacionxMedico";
 CREATE VIEW VIEW_Stat_FacturacionxMedico as SELECT     codmedico, SUM(total) AS Total  From dbo.MFactura WHERE     (fechafac BETWEEN '20180604' AND '20180604') AND (statfact = '3') GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Stat_FINAL_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_FINAL_0215";
CREATE VIEW dbo.VIEW_Stat_FINAL_0215
AS
SELECT     dbo.MInventario.coditems, dbo.MInventario.cod_subgrupo, dbo.DFactura.numfactu, dbo.DFactura.fechafac, dbo.DFactura.cantidad, 
                      dbo.DFactura.precunit, dbo.DFactura.tipoitems, dbo.DFactura.procentaje AS porcntUnitario, dbo.DFactura.descuento, 
                      dbo.VIEW_VentasDiarias_0215.codmedico, dbo.VIEW_VentasDiarias_0215.total, dbo.MInventario.desitems, dbo.VIEW_VentasDiarias_0215.medico, 
                      dbo.VIEW_VentasDiarias_0215.codclien, dbo.VIEW_Stat_PacientesVistos.numeroPacientes, dbo.VIEW_Stat_Registros.registros, 
                      dbo.VIEW_Stat_FacturacionxMedico.Total AS FACTURACIONXMEDICO, dbo.VIEW_Stat_For_0215.canFor, dbo.VIEW_Stat_Uni_0215.cantUni, 
                      dbo.VIEW_Stat_For_0215.canFor / dbo.VIEW_Stat_PacientesVistos.numeroPacientes / dbo.VIEW_Stat_Registros.registros AS formulaxpaciente, 
                      dbo.VIEW_Stat_Uni_0215.cantUni / dbo.VIEW_Stat_PacientesVistos.numeroPacientes / dbo.VIEW_Stat_Registros.registros AS unidadxpacientes, 
                      dbo.VIEW_Stat_MedFactProd.MonFacMedPod / dbo.VIEW_Stat_PacientesVistos.numeroPacientes / dbo.VIEW_Stat_Registros.registros AS facturacionxpaciente,
                       dbo.VIEW_Stat_MedFactProd.MonFacMedPod / dbo.VIEW_Stat_Registros.registros AS FxM, 
                      dbo.VIEW_Stat_For_0215.canFor / dbo.VIEW_Stat_Registros.registros AS TF, 
                      dbo.VIEW_Stat_MedFactProd.MonFacMedPod / dbo.VIEW_Stat_Registros.registros AS MFMP
FROM         dbo.MInventario INNER JOIN
                      dbo.DFactura ON dbo.MInventario.coditems = dbo.DFactura.coditems INNER JOIN
                      dbo.VIEW_VentasDiarias_0215 ON dbo.DFactura.numfactu = dbo.VIEW_VentasDiarias_0215.numfactu INNER JOIN
                      dbo.VIEW_Stat_Registros ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_Registros.codmedico INNER JOIN
                      dbo.VIEW_Stat_FacturacionxMedico ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_FacturacionxMedico.codmedico INNER JOIN
                      dbo.VIEW_Stat_MedFactProd ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_MedFactProd.codmedico LEFT OUTER JOIN
                      dbo.VIEW_Stat_PacientesVistos ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_PacientesVistos.codmedico LEFT OUTER JOIN
                      dbo.VIEW_Stat_For_0215 ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_For_0215.codmedico LEFT OUTER JOIN
                      dbo.VIEW_Stat_Uni_0215 ON dbo.VIEW_VentasDiarias_0215.codmedico = dbo.VIEW_Stat_Uni_0215.codmedico
WHERE     (dbo.DFactura.tipoitems = 'P')
;


-- Dumping structure for view farmacias.VIEW_Stat_Formulas_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Formulas_0215";
 CREATE VIEW VIEW_Stat_Formulas_0215  AS  SELECT coditems, codmedico, cantidad, fechafac, desitems, medico, cod_subgrupo  FROM dbo.VIEW_VentaProd_0215 s  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) AND (cod_subgrupo = '2') ;


-- Dumping structure for view farmacias.VIEW_Stat_For_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_For_0215";
 CREATE VIEW VIEW_Stat_For_0215  AS  SELECT     codmedico, SUM(cantidad) AS canFor  FROM         dbo.VIEW_Stat_Enl_0215 s  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) AND (cod_subgrupo = '2') GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Stat_FTM_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_FTM_0215";
 CREATE VIEW VIEW_Stat_FTM_0215  AS  SELECT codmedico, SUM(cantidad * precunit - descuento) AS FacXTMedico  FROM  dbo.VIEW_Stat_Enl_0215 ss  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Stat_MedFactProd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_MedFactProd";
CREATE VIEW VIEW_Stat_MedFactProd as  SELECT     codmedico, SUM(cantidad * precunit - Descuento) AS MonFacMedPod  From dbo.VIEW_Ventas_Medicos  WHERE  (fechafac BETWEEN '20180604' AND '20180604') AND (statfact <> '2') AND (Prod_serv = 'P') GROUP BY codmedico;


-- Dumping structure for view farmacias.VIEW_Stat_NunRecords_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_NunRecords_0215";
 CREATE VIEW VIEW_Stat_NunRecords_0215  AS  SELECT     codmedico, COUNT(cantidad) AS NumRecords  FROM         dbo.VIEW_Stat_Enl_0215 nRec  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Stat_PacientesVistos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_PacientesVistos";
 CREATE VIEW VIEW_Stat_PacientesVistos as SELECT codmedico, COUNT(codclien) AS numeroPacientes From dbo.VIEW_Asistidos_0215 WHERE  (fecha_cita BETWEEN  '20180604' AND '20180604') AND (asistido = '3') GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Stat_Registros
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Registros";
CREATE VIEW dbo.VIEW_Stat_Registros AS SELECT     dbo.VIEW_VentasDiarias_0215.codmedico, COUNT(*) AS registros FROM         dbo.DFactura INNER JOIN                       dbo.VIEW_VentasDiarias_0215 ON dbo.DFactura.numfactu = dbo.VIEW_VentasDiarias_0215.numfactu WHERE     (dbo.DFactura.tipoitems = 'P') AND (dbo.DFactura.fechafac BETWEEN '20180604' AND '20180604') GROUP BY dbo.VIEW_VentasDiarias_0215.codmedico  ;


-- Dumping structure for view farmacias.VIEW_Stat_Rel01_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Rel01_0215";
CREATE VIEW dbo.VIEW_Stat_Rel01_0215
AS
SELECT     dbo.VIEW_Stat_Uni_0215.codmedico, dbo.VIEW_Stat_Uni_0215.cantUni, dbo.VIEW_Stat_For_0215.canFor, dbo.VIEW_Sta_Vis_0215.canPVistos, 
                      dbo.VIEW_Stat_Uni_0215.cantUni / dbo.VIEW_Sta_Vis_0215.canPVistos AS UxP, 
                      dbo.VIEW_Stat_For_0215.canFor / dbo.VIEW_Sta_Vis_0215.canPVistos AS FxP, dbo.VIEW_Stat_FTM_0215.FacXTMedico, 
                      dbo.VIEW_Stat_FTM_0215.FacXTMedico / dbo.VIEW_Sta_Vis_0215.canPVistos AS promPacmed, 
                      dbo.VIEW_Stat_Uni_0215.cantUni / dbo.VIEW_Stat_For_0215.canFor AS porcentUniForm, dbo.VIEW_Stat_NunRecords_0215.NumRecords
FROM         dbo.VIEW_Stat_Uni_0215 INNER JOIN
                      dbo.VIEW_Stat_For_0215 ON dbo.VIEW_Stat_Uni_0215.codmedico = dbo.VIEW_Stat_For_0215.codmedico INNER JOIN
                      dbo.VIEW_Sta_Vis_0215 ON dbo.VIEW_Stat_Uni_0215.codmedico = dbo.VIEW_Sta_Vis_0215.codmedico INNER JOIN
                      dbo.VIEW_Stat_FTM_0215 ON dbo.VIEW_Stat_Uni_0215.codmedico = dbo.VIEW_Stat_FTM_0215.codmedico INNER JOIN
                      dbo.VIEW_Stat_NunRecords_0215 ON dbo.VIEW_Stat_Uni_0215.codmedico = dbo.VIEW_Stat_NunRecords_0215.codmedico
;


-- Dumping structure for view farmacias.VIEW_Stat_Unidades_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Unidades_0215";
 CREATE VIEW VIEW_Stat_Unidades_0215  AS  SELECT     coditems, codmedico, cantidad, fechafac, desitems, medico, cod_subgrupo  FROM   dbo.VIEW_VentaProd_0215  s  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) AND (cod_subgrupo = '1') ;


-- Dumping structure for view farmacias.VIEW_Stat_Uni_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Stat_Uni_0215";
 CREATE VIEW VIEW_Stat_Uni_0215  AS  SELECT     codmedico, SUM(cantidad) AS cantUni  FROM         dbo.VIEW_Stat_Enl_0215 s  WHERE (fechafac BETWEEN '20180604' AND '20180604' ) AND (cod_subgrupo = '1') GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_Sta_Vis_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Sta_Vis_0215";
 CREATE VIEW VIEW_Sta_Vis_0215  AS  SELECT codmedico, COUNT(DISTINCT codclien) AS canPVistos  FROM Mconsultas ss  WHERE (fecha_cita BETWEEN '20180604' AND '20180604' ) AND (asistido = '3')  GROUP BY codmedico ;


-- Dumping structure for view farmacias.VIEW_SubDpresupuesto
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_SubDpresupuesto";

CREATE VIEW dbo.VIEW_SubDpresupuesto
AS
SELECT     dbo.Dinventario.SudDesItems, dbo.SubDpresupuestos.cantidad, dbo.SubDpresupuestos.precunit, dbo.SubDpresupuestos.numpre, 
                      dbo.SubDpresupuestos.coditems, dbo.SubDpresupuestos.subcoditems
FROM         dbo.Dinventario RIGHT OUTER JOIN
                      dbo.SubDpresupuestos ON dbo.Dinventario.subcoditems = dbo.SubDpresupuestos.subcoditems AND 
                      dbo.Dinventario.coditems = dbo.SubDpresupuestos.coditems

;


-- Dumping structure for view farmacias.VIEW_SxI0715
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_SxI0715";

CREATE VIEW dbo.VIEW_SxI0715
AS
SELECT     TOP 100 PERCENT dbo.MFactura.fechafac, dbo.MFactura.numfactu, dbo.MFactura.codclien, dbo.MClientes.telfhabit, dbo.DFactura.coditems, 
                      dbo.MInventario.desitems, dbo.DFactura.cantidad, dbo.DFactura.precunit, dbo.DFactura.descuento, dbo.DFactura.procentaje, 
                      dbo.MClientes.nombres
FROM         dbo.MFactura INNER JOIN
                      dbo.MClientes ON dbo.MFactura.codclien = dbo.MClientes.codclien INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
ORDER BY dbo.MFactura.fechafac DESC

;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TaxesInvoice";
CREATE VIEW dbo.VIEW_TaxesInvoice
AS
SELECT     numfactu,
                          (SELECT     Porcentaje
                            FROM          ImpxFact
                            WHERE      ImpxFact.numfactu = dbo.VIEW_M_FACTURA.numfactu AND ImpxFact.codimp = dbo.VIEW_M_FACTURA.TaxA) AS TaxA,
                          (SELECT     Porcentaje
                            FROM          ImpxFact
                            WHERE      ImpxFact.numfactu = dbo.VIEW_M_FACTURA.numfactu AND ImpxFact.codimp = dbo.VIEW_M_FACTURA.TaxB) AS TaxB
FROM         dbo.VIEW_M_FACTURA
;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice2
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TaxesInvoice2";
CREATE VIEW dbo.VIEW_TaxesInvoice2
AS
SELECT     numfactu, CASE WHEN TaxA IS NULL THEN 0 ELSE TaxA END AS TaxA, CASE WHEN TaxB IS NULL THEN 0 ELSE taxB END AS TaxB
FROM         dbo.VIEW_TaxesInvoice
;


-- Dumping structure for view farmacias.VIEW_TaxesInvoice2MSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TaxesInvoice2MSS";

CREATE VIEW [dbo].[VIEW_TaxesInvoice2MSS]
AS
SELECT     numfactu, CASE WHEN TaxA IS NULL THEN 0 ELSE TaxA END AS TaxA, CASE WHEN TaxB IS NULL THEN 0 ELSE taxB END AS TaxB
FROM         dbo.VIEW_TaxesInvoiceMSS

;


-- Dumping structure for view farmacias.VIEW_TaxesInvoiceMSS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TaxesInvoiceMSS";

CREATE VIEW [dbo].[VIEW_TaxesInvoiceMSS]
AS
SELECT     numfactu,
                          (SELECT     Porcentaje
                            FROM          MSSImpxFact
                            WHERE      MSSImpxFact.numfactu = dbo.VIEW_M_FACTURAMSS.numfactu AND MSSImpxFact.codimp = dbo.VIEW_M_FACTURAMSS.TaxA) AS TaxA,
                          (SELECT     Porcentaje
                            FROM          MSSImpxFact
                            WHERE      MSSImpxFact.numfactu = dbo.VIEW_M_FACTURAMSS.numfactu AND MSSImpxFact.codimp = dbo.VIEW_M_FACTURAMSS.TaxB) AS TaxB
FROM         dbo.VIEW_M_FACTURAMSS

;


-- Dumping structure for view farmacias.view_tipoconsulta
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_tipoconsulta";

CREATE VIEW dbo.view_tipoconsulta
AS
SELECT     dbo.Mconsultas.codclien, dbo.Mconsultas.codconsulta, dbo.tipoconsulta.descons, dbo.tipoconsulta.tipo, dbo.Mconsultas.fecha_cita, 
                      dbo.Mconsultas.noasistido
FROM         dbo.Mconsultas INNER JOIN
                      dbo.tipoconsulta ON dbo.Mconsultas.codconsulta = dbo.tipoconsulta.codconsulta

;


-- Dumping structure for view farmacias.VIEW_TotalProtocol
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TotalProtocol";


CREATE VIEW dbo.VIEW_TotalProtocol
AS
SELECT     numfactu, COUNT(tratamiento) AS Facturados,
                          (SELECT     COUNT(coditems) AS Expr2
                            FROM          view_patologias500
                            WHERE      codigo = view_protocoloxFactura.patologia) AS Totales, patologia
FROM         dbo.VIEW_ProtocoloxFactura
WHERE     (tratamiento <> '')
GROUP BY numfactu, patologia


;


-- Dumping structure for view farmacias.VIEW_TotPagos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TotPagos";

CREATE VIEW dbo.VIEW_TotPagos
AS
SELECT     fechapago, modopago, monto, codsuc, '01' AS Tipo
FROM         dbo.vPagosPR
UNION ALL
SELECT     fechapago, modopago, monto, codsuc, '02' AS Tipo
FROM         dbo.vPagosPRCMA

;


-- Dumping structure for view farmacias.VIEW_TotVentas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_TotVentas";

CREATE VIEW dbo.VIEW_TotVentas
AS
SELECT     numfactu, fechafac, subtotal, descuento, (subtotal - descuento) AS neto, TotImpuesto, monto_flete, (total + monto_flete) AS totalfac, doc, codsuc, 
                      '01' AS tipo
FROM         dbo.VentasDiarias
WHERE     (statfact = '3') AND (cancelado = '1')
UNION ALL
SELECT     numfactu, fechafac, subtotal, descuento, (subtotal - descuento) AS neto, TotImpuesto, monto_flete, (total + monto_flete) AS totalfac, doc, codsuc, 
                      '02' AS tipo
FROM         dbo.VentasDiariasCMA
WHERE     (statfact = '3') AND (cancelado = '1')

;


-- Dumping structure for view farmacias.VIEW_Ultasistencia
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ultasistencia";
CREATE VIEW dbo.VIEW_Ultasistencia
AS
SELECT     codclien, MAX(fecha_cita) AS ultimacita
FROM         dbo.Mconsultas
WHERE     (asistido = 3)
GROUP BY codclien
;


-- Dumping structure for view farmacias.VIEW_usuarios
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_usuarios";

CREATE VIEW dbo.VIEW_usuarios
AS
SELECT loginpass.cedula, loginpass.Nombre, loginpass.apellido, 
    loginpass.login, MPerfil.desperfil, estaciones.desestac
FROM dbo.loginpass LEFT OUTER JOIN
    dbo.MPerfil ON 
    dbo.loginpass.codperfil = dbo.MPerfil.codperfil LEFT OUTER JOIN
    dbo.estaciones ON 
    dbo.loginpass.codestat = dbo.estaciones.codestac

;


-- Dumping structure for view farmacias.VIEW_VentaProd_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_VentaProd_0215";
CREATE VIEW dbo.VIEW_VentaProd_0215
AS
SELECT     dbo.MInventario.coditems, dbo.MInventario.cod_subgrupo, dbo.DFactura.numfactu, dbo.DFactura.fechafac, dbo.DFactura.cantidad, 
                      dbo.DFactura.precunit, dbo.DFactura.tipoitems, dbo.DFactura.procentaje AS porcntUnitario, dbo.DFactura.descuento, 
                      dbo.VIEW_VentasDiarias_0215.codmedico, dbo.VIEW_VentasDiarias_0215.total, dbo.MInventario.desitems, dbo.VIEW_VentasDiarias_0215.medico, 
                      dbo.VIEW_VentasDiarias_0215.codclien
FROM         dbo.MInventario INNER JOIN
                      dbo.DFactura ON dbo.MInventario.coditems = dbo.DFactura.coditems INNER JOIN
                      dbo.VIEW_VentasDiarias_0215 ON dbo.DFactura.numfactu = dbo.VIEW_VentasDiarias_0215.numfactu
WHERE     (dbo.DFactura.tipoitems = 'P')
;


-- Dumping structure for view farmacias.VIEW_Ventas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas";

CREATE VIEW dbo.VIEW_Ventas
AS
SELECT     TOP 100 PERCENT dbo.MInventario.coditems, dbo.MInventario.desitems, SUM(dbo.DFactura.cantidad) AS ventas, YEAR(dbo.MFactura.fechafac) 
                      AS lapsoFAC, MONTH(dbo.MFactura.fechafac) AS mesFAC
FROM         dbo.MInventario INNER JOIN
                      dbo.DFactura ON dbo.MInventario.coditems = dbo.DFactura.coditems INNER JOIN
                      dbo.MFactura ON dbo.DFactura.numfactu = dbo.MFactura.numfactu
WHERE     (dbo.MFactura.statfact <> 2) AND (dbo.MInventario.activo = N'1')
GROUP BY YEAR(dbo.MFactura.fechafac), MONTH(dbo.MFactura.fechafac), dbo.MInventario.coditems, dbo.MInventario.desitems
UNION
SELECT     TOP 100 PERCENT dbo.Minventario.CODITEMS, dbo.Minventario.DESITEMS, (- 1 * SUM(dbo.Dnotacredito.CANTIDAD)) AS ventas, 
                      YEAR(dbo.Mnotacredito.FECHAnot) AS lapsoFAC, MONTH(dbo.Mnotacredito.FECHAnot) AS mesFAC
FROM         dbo.Minventario INNER JOIN
                      dbo.Dnotacredito ON dbo.Minventario.CODITEMS = dbo.Dnotacredito.CODITEMS INNER JOIN
                      dbo.Mnotacredito ON dbo.Dnotacredito.NUMnotcre = dbo.Mnotacredito.NUMnotcre
WHERE     (dbo.Minventario.ACTIVO = N'1')
GROUP BY YEAR(dbo.Mnotacredito.FECHAnot), MONTH(dbo.Mnotacredito.FECHAnot), dbo.Minventario.CODITEMS, dbo.Minventario.DESITEMS
ORDER BY lapsofac, mesfac, dbo.Minventario.DESITEMS

;


-- Dumping structure for view farmacias.VIEW_Ventas01
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas01";

CREATE VIEW dbo.VIEW_Ventas01
AS
SELECT     SUM(subtotal) AS subtotal, SUM(descuento) AS descuento, SUM(TotImpuesto) AS iva, SUM(monto_flete) AS flete, SUM(total + monto_flete) AS total, 
                      fechafac, codsuc
FROM         dbo.VentasDiarias
WHERE     (statfact <> '2')
GROUP BY codsuc, fechafac

;


-- Dumping structure for view farmacias.VIEW_Ventas02
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas02";

CREATE VIEW dbo.VIEW_Ventas02
AS
SELECT     SUM(subtotal) AS subtotal, SUM(descuento) AS descuento, SUM(TotImpuesto) AS iva, SUM(monto_flete) AS flete, SUM(total + monto_flete) AS total, 
                      fechafac, codsuc
FROM         dbo.VentasDiariasCMA
WHERE     (statfact <> '2')
GROUP BY codsuc, fechafac

;


-- Dumping structure for view farmacias.VIEW_VentasBS
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_VentasBS";

CREATE VIEW dbo.VIEW_VentasBS
AS
SELECT     subtotal, descuento, iva, flete, total, fechafac, codsuc, '1' AS tipo
FROM         view_ventas01
UNION ALL
SELECT     subtotal, descuento, iva, flete, total, fechafac, codsuc, '2' AS tipo
FROM         view_ventas02

;


-- Dumping structure for view farmacias.VIEW_VentasDiarias_0215
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_VentasDiarias_0215";
CREATE VIEW dbo.VIEW_VentasDiarias_0215
AS
SELECT     dbo.VentasDiarias.numfactu, dbo.VentasDiarias.nombres, dbo.VentasDiarias.fechafac, dbo.VentasDiarias.codclien, dbo.VentasDiarias.codmedico, 
                      dbo.VentasDiarias.subtotal, dbo.VentasDiarias.descuento, dbo.VentasDiarias.total, dbo.VentasDiarias.statfact, dbo.VentasDiarias.usuario, dbo.VentasDiarias.tipopago,
                       dbo.VentasDiarias.TotImpuesto, dbo.VentasDiarias.TotalFac, dbo.VentasDiarias.monto_flete, dbo.VentasDiarias.doc, dbo.VentasDiarias.workstation, 
                      dbo.VentasDiarias.cancelado, dbo.VentasDiarias.codsuc, dbo.VentasDiarias.medio, SUBSTRING(dbo.Mmedicos.nombre, 1, 1) 
                      + '  ' + dbo.Mmedicos.apellido AS medico
FROM         dbo.VentasDiarias INNER JOIN
                      dbo.Mmedicos ON dbo.VentasDiarias.codmedico = dbo.Mmedicos.Codmedico
WHERE     (dbo.VentasDiarias.statfact <> '2') AND (dbo.VentasDiarias.doc = '01')
;


-- Dumping structure for view farmacias.View_VentasDiarias_CMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_VentasDiarias_CMA";

CREATE VIEW dbo.View_VentasDiarias_CMA
AS
SELECT     dbo.CMA_MFactura.numfactu, dbo.MClientes.nombres, dbo.CMA_MFactura.fechafac, dbo.CMA_MFactura.codclien, dbo.CMA_MFactura.codmedico, dbo.CMA_MFactura.subtotal, 
                      dbo.CMA_MFactura.descuento, dbo.CMA_MFactura.total, dbo.CMA_MFactura.statfact, dbo.CMA_MFactura.usuario, dbo.CMA_MFactura.tipopago, dbo.CMA_MFactura.TotImpuesto, 
                      dbo.CMA_MFactura.monto_flete, dbo.CMA_MFactura.tipo AS doc, dbo.CMA_MFactura.workstation, dbo.CMA_MFactura.cancelado
FROM         dbo.CMA_MFactura INNER JOIN
                      dbo.MClientes ON dbo.CMA_MFactura.codclien = dbo.MClientes.codclien
UNION ALL
SELECT     dbo.Mnotacredito.numnotcre AS numfactu, dbo.MClientes.nombres, dbo.Mnotacredito.fechanot AS fechafac, dbo.Mnotacredito.codclien, 
                      dbo.Mnotacredito.codmedico, dbo.Mnotacredito.subtotal * - 1 AS subtotal, dbo.Mnotacredito.descuento * - 1 AS descuento, 
                      dbo.Mnotacredito.totalnot * - 1 AS total, dbo.Mnotacredito.statnc AS statfact, dbo.Mnotacredito.usuario, dbo.mnotacredito.tipopago, 
                      dbo.mnotacredito.totimpuesto * - 1 AS totimpuesto, dbo.mnotacredito.monto_flete, dbo.mnotacredito.tipo AS Doc, dbo.mnotacredito.workstation, 
                      dbo.mnotacredito.cancelado
FROM         dbo.Mnotacredito INNER JOIN
                      dbo.MClientes ON dbo.Mnotacredito.codclien = dbo.MClientes.codclien

;


-- Dumping structure for view farmacias.View_VentasDiarias_CMACST
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_VentasDiarias_CMACST";
create view View_VentasDiarias_CMACST as
SELECT     dbo.CMA_MFactura.numfactu, b.nombres, dbo.CMA_MFactura.fechafac, dbo.CMA_MFactura.codclien, dbo.CMA_MFactura.codmedico, 
                      dbo.CMA_MFactura.subtotal, dbo.CMA_MFactura.descuento, dbo.CMA_MFactura.total, dbo.CMA_MFactura.statfact, dbo.CMA_MFactura.usuario, 
                      dbo.CMA_MFactura.tipopago, dbo.CMA_MFactura.TotImpuesto, dbo.CMA_MFactura.monto_flete, dbo.CMA_MFactura.tipo AS doc, dbo.CMA_MFactura.workstation, 
                      dbo.CMA_MFactura.cancelado,c.cod_subgrupo 
FROM         dbo.CMA_MFactura  INNER JOIN
                      dbo.MClientes b ON dbo.CMA_MFactura.codclien = b.codclien
					  inner join [dbo].[viewtipofacturascma] c on  dbo.CMA_MFactura.numfactu=c.numfactu







					 ;


-- Dumping structure for view farmacias.VIEW_ventasPotes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_ventasPotes";

CREATE VIEW dbo.VIEW_ventasPotes
AS
SELECT     dbo.DCierreInventario.coditems, dbo.DCierreInventario.fechacierre, dbo.DCierreInventario.ventas, dbo.DCierreInventario.NotasCreditos, 
                      dbo.DCierreInventario.NotasEntregas, dbo.DCierreInventario.anulaciones, dbo.DCierreInventario.InvPosible, dbo.MInventario.desitems, 
                      dbo.MInventario.especial, '01' AS codsuc
FROM         dbo.DCierreInventario LEFT OUTER JOIN
                      dbo.MInventario ON dbo.DCierreInventario.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.View_Ventas_Diarias_W_Ret_Qty
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "View_Ventas_Diarias_W_Ret_Qty";
  CREATE view View_Ventas_Diarias_W_Ret_Qty 
  as 
  SELECT TOP 100 PERCENT 
   SUM(cantidad ) AS qty , periodo, nombremedico,desitems, year, mes, MIN(DATEADD(m, DATEDIFF(m, 0, fechafac), 0)) AS fechafac
FROM         dbo.VIEW_Week_Report_W_Ret AS vp
WHERE     (Prod_serv = 'p')   
GROUP BY mes, periodo, nombremedico,desitems, year, MONTH(fechafac)

   ORDER BY qty desc;

;


-- Dumping structure for view farmacias.view_ventas_formulas_Selected
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_ventas_formulas_Selected";
create view view_ventas_formulas_Selected as
Select coditems, desitems from minventario where coditems IN ('888','111','113','BRAIN PLUS','110','150','2883818115','152','2883899615','129','115','187','148','IMOD116912','142','137','58','194','815','60','9774','144','169','198','109','204','112','124' )

;


-- Dumping structure for view farmacias.view_ventas_formulas_Selected_F
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_ventas_formulas_Selected_F";
 CREATE VIEW view_ventas_formulas_Selected_F  AS    Select f.desitems, isnull((  select sum(cantidad) qty FROM VIEW_Ventas_Medicos_W_Ret vnts   where vnts.nombremedico='Idamys Herrera' and vnts.fechafac between '20170401' AND '20170401' AND vnts.coditems= f.coditems   group by vnts.desitems   ),0) qty  ,   (  Select max(vnts.nombremedico)  FROM VIEW_Ventas_Medicos_W_Ret vnts   where vnts.nombremedico='Idamys Herrera' and vnts.fechafac between '20170401' AND '20170401' AND vnts.coditems= f.coditems   group by vnts.desitems ) medico  from  view_ventas_formulas_Selected f ;


-- Dumping structure for view farmacias.view_ventas_generales_12_17
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_ventas_generales_12_17";
CREATE view   view_ventas_generales_12_17  as
	SELECT a.codmedico, a.cantidad , a.precunit ,a.Descuento, a.fechafac,a.Prod_serv  From VIEW_Ventas_Medicos_Laser a 
    WHERE  (a.statfact <> '2') AND (a.Prod_serv = 'M') and a.codmedico in('004','315','317','319','320','327','328','329') 
     
	UNION
	SELECT B.codmedico, B.cantidad , B.precunit ,B.Descuento, B.fechafac, B.Prod_serv  From VIEW_Ventas_Medicos_Suero B 
    WHERE   (B.statfact <> '2') AND (B.Prod_serv = 'S') and B.codmedico in('004','315','317','319','320','327','328','329') 
    
    UNION
	SELECT B.codmedico, B.cantidad , B.precunit ,B.Descuento, B.fechafac, B.Prod_serv From VIEW_Ventas_Medicos B 
    WHERE   (B.statfact <> '2') AND (B.Prod_serv = 'P') and B.codmedico in('004','315','317','319','320','327','328','329') 
    
;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas_Medicos";
CREATE VIEW dbo.VIEW_Ventas_Medicos
AS
SELECT        RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codmedico, dbo.MFactura.statfact, dbo.DFactura.coditems, 
                         dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, dbo.DFactura.cantidad, dbo.DFactura.precunit, ROUND(dbo.DFactura.descuento, 2) AS Descuento, '1' AS id_centro, 
                         CONCAT(YEAR(dbo.MFactura.fechafac),'/', MONTH(dbo.MFactura.fechafac)) AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         dbo.MFactura ON dbo.Mmedicos.Codmedico = dbo.MFactura.codmedico INNER JOIN
                         dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                         dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
UNION ALL
SELECT        RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.statfact, 
                         dbo.cma_DFactura.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, dbo.cma_DFactura.cantidad, dbo.cma_DFactura.precunit, 
                         round(dbo.cma_DFactura.descuento, 2) AS Descuento, '2' AS id_centro, CONCAT(YEAR(dbo.cma_MFactura.fechafac),'/', MONTH(dbo.cma_MFactura.fechafac)) AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         dbo.cma_MFactura ON dbo.Mmedicos.Codmedico = dbo.cma_MFactura.codmedico INNER JOIN
                         dbo.cma_DFactura ON dbo.cma_MFactura.numfactu = dbo.cma_DFactura.numfactu INNER JOIN
                         dbo.MInventario ON dbo.cma_DFactura.coditems = dbo.MInventario.coditems
;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_Laser
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas_Medicos_Laser";
CREATE VIEW dbo.VIEW_Ventas_Medicos_Laser
AS
SELECT        CONCAT(dbo.Mmedicos.nombre, ' ', dbo.Mmedicos.apellido)  AS nombremedico, dbo.MSSMFact.numfactu, dbo.MSSMFact.fechafac, dbo.MSSMFact.codmedico, dbo.MSSMFact.statfact, 
                         dbo.MSSDFact.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, dbo.MSSDFact.cantidad, dbo.MSSDFact.precunit, ROUND(dbo.MSSDFact.descuento, 2) 
                         AS Descuento, '2' AS id_centro, CONCAT(YEAR(dbo.MSSMFact.fechafac), '/', MONTH(dbo.MSSMFact.fechafac))  AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         dbo.MSSMFact ON dbo.Mmedicos.Codmedico = dbo.MSSMFact.codmedico INNER JOIN
                         dbo.MSSDFact ON dbo.MSSMFact.numfactu = dbo.MSSDFact.numfactu INNER JOIN
                         dbo.MInventario ON dbo.MSSDFact.coditems = dbo.MInventario.coditems
union all
SELECT        RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, MSSMDev.numnotcre, MSSMDev.fechanot, MSSMDev.codmedico, MSSMDev.statnc, MSSDDev.coditems, 
                         dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, MSSDDev.cantidad * - 1 cantidad, MSSDDev.precunit, ROUND(MSSDDev.descuento, 2) * - 1 AS Descuento, 
                         '1' AS id_centro, CONCAT(YEAR(MSSMDev.fechanot), '/', MONTH(MSSMDev.fechanot)) AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         MSSMDev ON dbo.Mmedicos.Codmedico = MSSMDev.codmedico INNER JOIN
                         MSSDDev ON MSSMDev.numnotcre = MSSDDev.numnotcre INNER JOIN
                         dbo.MInventario ON MSSDDev.coditems = dbo.MInventario.coditems
WHERE        MSSMDev.statnc != 2
;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_Suero
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas_Medicos_Suero";
CREATE VIEW dbo.VIEW_Ventas_Medicos_Suero
AS
SELECT         CONCAT(dbo.Mmedicos.nombre, ' ', dbo.Mmedicos.apellido)  AS nombremedico, dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, dbo.cma_MFactura.codmedico, dbo.cma_MFactura.statfact, 
                         dbo.cma_DFactura.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, dbo.cma_DFactura.cantidad, dbo.cma_DFactura.precunit, 
                         ROUND(dbo.cma_DFactura.descuento, 2) AS Descuento, '2' AS id_centro,  CONCAT(YEAR(dbo.cma_MFactura.fechafac), '/', MONTH(dbo.cma_MFactura.fechafac))  AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         dbo.cma_MFactura ON dbo.Mmedicos.Codmedico = dbo.cma_MFactura.codmedico INNER JOIN
                         dbo.cma_DFactura ON dbo.cma_MFactura.numfactu = dbo.cma_DFactura.numfactu INNER JOIN
                         dbo.MInventario ON dbo.cma_DFactura.coditems = dbo.MInventario.coditems
WHERE        (dbo.cma_DFactura.coditems IN ('100GST', '15GST', '20150727ST', '20GST', '25GST', '30GST', '35GST', '40GST', '45GST', '50GST', '55GST', '60GST', '65GST', '70GST', '75GST', '80GST', '85GST', '90GST', 
                         '95GST'))
union all
 SELECT       CONCAT(dbo.Mmedicos.nombre, ' ', dbo.Mmedicos.apellido)  AS nombremedico, CMA_Mnotacredito.numnotcre, CMA_Mnotacredito.fechanot, CMA_Mnotacredito.codmedico, CMA_Mnotacredito.statnc, 
                         CMA_Dnotacredito.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, CMA_Dnotacredito.cantidad *-1 cantidad, CMA_Dnotacredito.precunit, 
                         ROUND(CMA_Dnotacredito.descuento, 2) *-1 AS Descuento, '2' AS id_centro, CONCAT(YEAR(CMA_Mnotacredito.fechanot), '/', MONTH(CMA_Mnotacredito.fechanot))  AS periodo
FROM            dbo.Mmedicos INNER JOIN
                         CMA_Mnotacredito ON dbo.Mmedicos.Codmedico = CMA_Mnotacredito.codmedico INNER JOIN
                         CMA_Dnotacredito ON CMA_Mnotacredito.numnotcre = CMA_Dnotacredito.numnotcre INNER JOIN
                         dbo.MInventario ON CMA_Dnotacredito.coditems = dbo.MInventario.coditems
WHERE        (CMA_Dnotacredito.coditems IN ('100GST', '15GST', '20150727ST', '20GST', '25GST', '30GST', '35GST', '40GST', '45GST', '50GST', '55GST', '60GST', '65GST', '70GST', '75GST', '80GST', '85GST', '90GST', 
                         '95GST'))
;


-- Dumping structure for view farmacias.VIEW_Ventas_Medicos_W_Ret
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Ventas_Medicos_W_Ret";
create view VIEW_Ventas_Medicos_W_Ret as
SELECT     RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.codmedico, 
                      dbo.MFactura.statfact, dbo.DFactura.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, dbo.DFactura.cantidad, 
                      dbo.DFactura.precunit, ROUND(dbo.DFactura.descuento, 2) AS Descuento, '1' AS id_centro, CONCAT(YEAR(dbo.MFactura.fechafac), '/', MONTH(dbo.MFactura.fechafac)) 
                      AS periodo
FROM         dbo.Mmedicos INNER JOIN
                      dbo.MFactura ON dbo.Mmedicos.Codmedico = dbo.MFactura.codmedico INNER JOIN
                      dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
UNION ALL
SELECT     RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, dbo.cma_MFactura.numfactu, dbo.cma_MFactura.fechafac, 
                      dbo.cma_MFactura.codmedico, dbo.cma_MFactura.statfact, dbo.cma_DFactura.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, 
                      dbo.MInventario.aplicaComMed, dbo.cma_DFactura.cantidad, dbo.cma_DFactura.precunit, round(dbo.cma_DFactura.descuento, 2) AS Descuento, '2' AS id_centro, 
                      CONCAT(YEAR(dbo.cma_MFactura.fechafac), '/', MONTH(dbo.cma_MFactura.fechafac)) AS periodo
FROM         dbo.Mmedicos INNER JOIN
                      dbo.cma_MFactura ON dbo.Mmedicos.Codmedico = dbo.cma_MFactura.codmedico INNER JOIN
                      dbo.cma_DFactura ON dbo.cma_MFactura.numfactu = dbo.cma_DFactura.numfactu INNER JOIN
                      dbo.MInventario ON dbo.cma_DFactura.coditems = dbo.MInventario.coditems
UNION ALL
SELECT     RTRIM(dbo.Mmedicos.nombre) + ' ' + RTRIM(dbo.Mmedicos.apellido) AS nombremedico, Mnotacredito.numnotcre, Mnotacredito.fechanot, Mnotacredito.codmedico, 
                      Mnotacredito.statnc, Dnotacredito.coditems, dbo.MInventario.desitems, dbo.MInventario.Prod_serv, dbo.MInventario.aplicaComMed, Dnotacredito.cantidad*-1 cantidad, 
                      Dnotacredito.precunit, ROUND(Dnotacredito.descuento, 2)*-1 AS Descuento, '1' AS id_centro, CONCAT(YEAR(Mnotacredito.fechanot), '/', MONTH(Mnotacredito.fechanot)) 
                      AS periodo
FROM         dbo.Mmedicos INNER JOIN
                      Mnotacredito ON dbo.Mmedicos.Codmedico = Mnotacredito.codmedico INNER JOIN
                      Dnotacredito ON Mnotacredito.numnotcre = Dnotacredito.numnotcre INNER JOIN
                      dbo.MInventario ON Dnotacredito.coditems = dbo.MInventario.coditems
where Mnotacredito.statnc!=2;


-- Dumping structure for view farmacias.VIEW_Week_Report_
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_";
CREATE VIEW dbo.VIEW_Week_Report_
AS
SELECT        TOP (100) PERCENT { fn MONTH(dbo.VIEW_Ventas_Medicos.fechafac) } AS mes, { fn WEEK(dbo.VIEW_Ventas_Medicos.fechafac) } AS week, { fn YEAR(dbo.VIEW_Ventas_Medicos.fechafac) } AS year, 
                         CONVERT(VARCHAR(20), dbo.VIEW_Ventas_Medicos.fechafac, 112) AS ch, dbo.VIEW_Ventas_Medicos.nombremedico, dbo.VIEW_Ventas_Medicos.numfactu, dbo.VIEW_Ventas_Medicos.fechafac, 
                         dbo.VIEW_Ventas_Medicos.codmedico, dbo.VIEW_Ventas_Medicos.statfact, dbo.VIEW_Ventas_Medicos.coditems, dbo.VIEW_Ventas_Medicos.desitems, dbo.VIEW_Ventas_Medicos.Prod_serv, 
                         dbo.VIEW_Ventas_Medicos.aplicaComMed, dbo.VIEW_Ventas_Medicos.cantidad, dbo.VIEW_Ventas_Medicos.precunit, dbo.VIEW_Ventas_Medicos.Descuento, dbo.VIEW_Ventas_Medicos.id_centro, 
                         dbo.Mmedicos.meliminado, dbo.Mmedicos.activo, dbo.VIEW_Ventas_Medicos.periodo
FROM            dbo.VIEW_Ventas_Medicos INNER JOIN
                         dbo.Mmedicos ON dbo.VIEW_Ventas_Medicos.codmedico = dbo.Mmedicos.Codmedico
WHERE        (dbo.VIEW_Ventas_Medicos.Prod_serv = 'P') AND (dbo.VIEW_Ventas_Medicos.statfact <> '2') AND (dbo.Mmedicos.activo = 1) AND (dbo.Mmedicos.meliminado = 1) AND (dbo.Mmedicos.Codmedico <> '000')
ORDER BY dbo.VIEW_Ventas_Medicos.fechafac
;


-- Dumping structure for view farmacias.VIEW_Week_Report_CompLaser
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_CompLaser";
Create view VIEW_Week_Report_CompLaser as 
Select sum(cantidad*precunit)- sum(Descuento) amount  ,periodo,nombremedico,year,mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac 
from  VIEW_Week_Report_Laser
where  nombremedico !=' - Sin medico asociado -  ' /*fechafac between '20161201' and '20170331' and  Prod_serv='p'*/    
group by mes, periodo,nombremedico,year,month(fechafac);


-- Dumping structure for view farmacias.VIEW_Week_Report_CompProd
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_CompProd";
CREATE view VIEW_Week_Report_CompProd as 
Select sum(cantidad*precunit)- sum(Descuento) amount  ,periodo,nombremedico,year,vp.mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac 
from  VIEW_Week_Report_ vp
where /*fechafac between '20161201' and '20170331' and  */ Prod_serv='p'
group by mes, periodo,nombremedico,year,month(fechafac);


-- Dumping structure for view farmacias.VIEW_Week_Report_CompProd_W_Ret
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_CompProd_W_Ret";
 Create view VIEW_Week_Report_CompProd_W_Ret as 
  Select sum(cantidad*precunit)- sum(Descuento) amount  ,periodo,nombremedico,year,vp.mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac 
  from  VIEW_Week_Report_W_Ret  vp
where  Prod_serv='p'
group by mes, periodo,nombremedico,year,month(fechafac);


-- Dumping structure for view farmacias.VIEW_Week_Report_CompSuero
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_CompSuero";
Create view VIEW_Week_Report_CompSuero as 
Select sum(cantidad*precunit)- sum(Descuento) amount  ,periodo,nombremedico,year,mes, min ( DATEADD(m, DATEDIFF(m, 0, fechafac), 0) )  fechafac 
from  VIEW_Week_Report_Suero
where  nombremedico !=' - Sin medico asociado -  ' /*fechafac between '20161201' and '20170331' and  Prod_serv='p'*/    
group by mes, periodo,nombremedico,year,month(fechafac)
;


-- Dumping structure for view farmacias.VIEW_Week_Report_Laser
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_Laser";
CREATE VIEW dbo.VIEW_Week_Report_Laser
AS
SELECT     { fn MONTH(fechafac) } AS mes, { fn WEEK(fechafac) } AS week, { fn YEAR(fechafac) } AS year, nombremedico, numfactu, fechafac, codmedico, statfact, coditems, 
                      desitems, Prod_serv, aplicaComMed, cantidad, precunit, Descuento, id_centro, periodo
FROM         dbo.VIEW_Ventas_Medicos_Laser
WHERE     (codmedico <> '000')
;


-- Dumping structure for view farmacias.VIEW_Week_Report_Suero
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_Suero";
CREATE VIEW dbo.VIEW_Week_Report_Suero
AS
SELECT     { fn MONTH(fechafac) } AS mes, { fn WEEK(fechafac) } AS week, { fn YEAR(fechafac) } AS year, nombremedico, numfactu, fechafac, codmedico, statfact, coditems, 
                      desitems, Prod_serv, aplicaComMed, cantidad, precunit, Descuento, id_centro, periodo
FROM         dbo.VIEW_Ventas_Medicos_Suero
WHERE     (codmedico <> '000')
;


-- Dumping structure for view farmacias.VIEW_Week_Report_W_Ret
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VIEW_Week_Report_W_Ret";
create view VIEW_Week_Report_W_Ret as
SELECT     TOP (100) PERCENT { fn MONTH(VIEW_Ventas_Medicos_W_Ret.fechafac) } AS mes, { fn WEEK(VIEW_Ventas_Medicos_W_Ret.fechafac) } AS week, 
                      { fn YEAR(VIEW_Ventas_Medicos_W_Ret.fechafac) } AS year, CONVERT(VARCHAR(20), VIEW_Ventas_Medicos_W_Ret.fechafac, 112) AS ch, 
                      VIEW_Ventas_Medicos_W_Ret.nombremedico, VIEW_Ventas_Medicos_W_Ret.numfactu, VIEW_Ventas_Medicos_W_Ret.fechafac, VIEW_Ventas_Medicos_W_Ret.codmedico, 
                      VIEW_Ventas_Medicos_W_Ret.statfact, VIEW_Ventas_Medicos_W_Ret.coditems, VIEW_Ventas_Medicos_W_Ret.desitems, VIEW_Ventas_Medicos_W_Ret.Prod_serv, 
                      VIEW_Ventas_Medicos_W_Ret.aplicaComMed, VIEW_Ventas_Medicos_W_Ret.cantidad, VIEW_Ventas_Medicos_W_Ret.precunit, VIEW_Ventas_Medicos_W_Ret.Descuento, 
                      VIEW_Ventas_Medicos_W_Ret.id_centro, dbo.Mmedicos.meliminado, dbo.Mmedicos.activo, VIEW_Ventas_Medicos_W_Ret.periodo
FROM         VIEW_Ventas_Medicos_W_Ret INNER JOIN
                      dbo.Mmedicos ON VIEW_Ventas_Medicos_W_Ret.codmedico = dbo.Mmedicos.Codmedico
WHERE     (VIEW_Ventas_Medicos_W_Ret.Prod_serv = 'P') AND (VIEW_Ventas_Medicos_W_Ret.statfact <> '2') AND (dbo.Mmedicos.activo = 1) AND (dbo.Mmedicos.meliminado = 1) 
                      AND (dbo.Mmedicos.Codmedico <> '000')
ORDER BY VIEW_Ventas_Medicos_W_Ret.fechafac
;


-- Dumping structure for view farmacias.view_whathappend
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_whathappend";
create view view_whathappend as
select b.Historia, b.nombres, a.fecha_cita,a.usuario, a.observacion,a.asistido,a.codclien,a.codconsulta,a.codmedico,c.descons, CONCAT( d.nombre, ' ',d.apellido) medico  from Mconsultas a
inner join MClientes b on a.codclien=b.codclien
inner join tipoconsulta c on a.codconsulta=c.codconsulta
inner join Mmedicos d on a.codmedico=d.Codmedico
union
select b.Historia, b.nombres, a.fecha_cita,a.usuario, a.observacion,a.asistido,a.codclien,a.codconsulta,a.codmedico,c.descons, CONCAT( d.nombre, ' ',d.apellido) medico  from Mconsultass a
inner join MClientes b on a.codclien=b.codclien
left join tipoconsulta c on a.codconsulta=c.codconsulta
inner join Mmedicos d on a.codmedico=d.Codmedico;


-- Dumping structure for view farmacias.VPagosPR
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VPagosPR";

CREATE VIEW dbo.VPagosPR
AS
SELECT     fechapago, modopago, monto, codsuc
FROM         dbo.PagosPR

;


-- Dumping structure for view farmacias.VPagosPRCMA
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VPagosPRCMA";

CREATE VIEW dbo.VPagosPRCMA
AS
SELECT     fechapago, modopago, monto, codsuc
FROM         dbo.PagosPRCMA

;


-- Dumping structure for view farmacias.VWANULAR
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VWANULAR";

CREATE VIEW dbo.VWANULAR
AS
SELECT numfactu, fechafac, desanul
FROM dbo.MFactura

;


-- Dumping structure for view farmacias.VWCIERREINV
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VWCIERREINV";

CREATE VIEW dbo.VWCIERREINV
AS
SELECT dbo.DCierreInventario.coditems, dbo.MInventario.desitems, 
    dbo.DCierreInventario.existencia AS Expr1, 
    dbo.DCierreInventario.fechacierre, 
    dbo.DCierreInventario.ventas, 
    dbo.DCierreInventario.anulaciones, 
    dbo.DCierreInventario.ajustes, 
    dbo.DCierreInventario.InvPosible, 
    dbo.DCierreInventario.InvActual, 
    dbo.DCierreInventario.fallas
FROM dbo.DCierreInventario INNER JOIN
    dbo.MInventario ON 
    dbo.DCierreInventario.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.VWLISTADEPRECIO
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VWLISTADEPRECIO";
CREATE VIEW dbo.VWLISTADEPRECIO
AS
SELECT     dbo.MInventario.desitems, dbo.MPrecios.precunit, dbo.MInventario.Prod_serv, dbo.MInventario.coditems, dbo.MInventario.activo
FROM         dbo.MInventario INNER JOIN
                      dbo.MPrecios ON dbo.MInventario.coditems = dbo.MPrecios.coditems
;


-- Dumping structure for view farmacias.VW_DPRICES
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "VW_DPRICES";

CREATE VIEW dbo.VW_DPRICES
AS
SELECT dbo.MInventario.desitems, dbo.DPRICES.precunit, 
    dbo.DPRICES.ACTUALIZADO, dbo.DPRICES.PORCENT, 
    dbo.DPRICES.FECHA, dbo.DPRICES.HORA, 
    dbo.DPRICES.CONTROL, dbo.DPRICES.coditems
FROM dbo.DPRICES INNER JOIN
    dbo.MInventario ON 
    dbo.DPRICES.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.V_ControlVisitas
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "V_ControlVisitas";

CREATE VIEW dbo.V_ControlVisitas
AS
SELECT dbo.ControlVisistas.Visita, dbo.ControlVisistas.Fecha, 
    dbo.Mmedicos.nombre + ' ' + dbo.Mmedicos.apellido AS NOMBRE,
     dbo.ControlVisistas.observaciones, 
    dbo.ControlVisistas.Historia, dbo.ControlVisistas.Codmedico, 
    dbo.ControlVisistas.CI
FROM dbo.ControlVisistas INNER JOIN
    dbo.Mmedicos ON 
    dbo.ControlVisistas.Codmedico = dbo.Mmedicos.Codmedico

;


-- Dumping structure for view farmacias.V_EVALUACION
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "V_EVALUACION";

CREATE VIEW dbo.V_EVALUACION
AS
SELECT dbo.cie.enfermedad, dbo.MInventario.desitems, 
    dbo.evaluacion.Historia, dbo.evaluacion.Visita, 
    dbo.evaluacion.cie, dbo.evaluacion.clascie, 
    dbo.evaluacion.coditems
FROM dbo.evaluacion INNER JOIN
    dbo.cie ON dbo.evaluacion.cie = dbo.cie.cie INNER JOIN
    dbo.MInventario ON 
    dbo.evaluacion.coditems = dbo.MInventario.coditems

;


-- Dumping structure for view farmacias.V_Medicos
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "V_Medicos";

CREATE VIEW dbo.V_Medicos
AS
SELECT Codmedico, nombre + ' ' + apellido AS medico
FROM dbo.Mmedicos

;


-- Dumping structure for view farmacias.view_NE
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS "view_NE";

CREATE view view_NE 
as
select notaentrega.numnotent,notaentrega.fechanot,notaentrega.codclien,
notentdetalle.coditems,notentdetalle.cantidad,minventario.desitems,mclientes.cedula,
mclientes.nombres, mclientes.codmedico
from notaentrega, notentdetalle, minventario, mclientes
where
notentdetalle.numnotent = notaentrega.numnotent and 
mclientes.codclien = notaentrega.codclien and
minventario.coditems = notentdetalle.coditems and
notaentrega.statunot <> '2' 


;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
