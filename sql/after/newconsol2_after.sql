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