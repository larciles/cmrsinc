SELECT        dbo.MFactura.numfactu, dbo.MFactura.fechafac, dbo.MFactura.statfact, dbo.DFactura.cantidad, dbo.MInventario.coditems, dbo.MInventario.desitems
FROM            dbo.MFactura INNER JOIN
                         dbo.DFactura ON dbo.MFactura.numfactu = dbo.DFactura.numfactu INNER JOIN
                         dbo.MInventario ON dbo.DFactura.coditems = dbo.MInventario.coditems
WHERE        (dbo.MFactura.statfact <> 2)

union all
Select M.numfactu,M.fechafac,M.statfact,L.cantidad,K.codikit AS coditems, I.desitems from MSSMFact M
Inner Join MSSDFact L On M.numfactu=L.numfactu
inner join Kit K ON L.coditems=K.coditems
Inner join MInventario I ON K.codikit=I.coditems
Where  M.statfact<>2 