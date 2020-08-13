SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 1 AS tipo, 0 qtySold, general, 
                      codmedico, 'Productos' cod_subgrupo
FROM         dbo.VentasDiarias
UNION ALL
SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 2 AS tipo,
      (SELECT     sum(cantidad)
                            FROM          cma_DFactura
                            WHERE      cma_DFactura.numfactu = a.numfactu) qtySold, total general, codmedico, cod_subgrupo
FROM         VentasDiariasCMACST1 a
UNION ALL
SELECT     numfactu, nombres, fechafac, subtotal, descuento, total, statfact, tipopago, TotImpuesto, monto_flete, doc, workstation, cancelado, 3 AS tipo,
                          (SELECT     sum(cantidad)
                            FROM          MSSDFact
                            WHERE      MSSDFact.numfactu = dbo.VentasDiariasMSS.numfactu) qtySold, general, codmedico, 'Laser' cod_subgrupo
FROM         dbo.VentasDiariasMSS