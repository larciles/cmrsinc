SELECT     a.fechafac, b.cod_subgrupo, SUM(a.cantidad * a.precunit) - SUM(a.descuento) AS monto
FROM         dbo.cma_DFactura AS a INNER JOIN
                      dbo.MInventario AS b ON a.coditems = b.coditems INNER JOIN
                      dbo.VentasDiariasCMA AS c ON a.numfactu = c.numfactu
WHERE     (c.statfact <> 2)
GROUP BY b.cod_subgrupo, a.fechafac