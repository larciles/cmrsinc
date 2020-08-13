
-- 1
alter view VentasDiariasCMACST_4_CONS as
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
                               WHERE        (cod_subgrupo = 'CONSULTA') ))

--2---------------------------------------------------------------------------------------------------------------------
alter view VentasDiariasCMACST_4_SUERO as
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

--3-------------------------------------------------------------------------------------------------------------------------

alter view VentasDiariasCMACST_4_INTRA as
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
					

--4--------------------------------------------------------------------------------------------------------------------------------

alter view VentasDiariasCMACST_4_LASER as
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
                               WHERE        (cod_subgrupo = 'TERAPIA LASER') ))
---5-----------------------------------------------------------------------------------------------------------------------------------

alter view VentasDiariasCMACST_4_EXO as
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
					

---6---------------------------------------------------------------------------------------------------------------------------------

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
					

----7-----------------------------------------------------------------------------------------------------------------------------

create view VentasDiariasCMACST_4_BLOQUEO_REYES as
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
                               WHERE        (cod_subgrupo = 'BLOQUEO') ))


























