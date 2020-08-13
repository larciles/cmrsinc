newconsol3_2_w_cm

SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 1 AS tipo, general, 'Productos' cod_subgrupo
FROM            dbo.VentasDiarias_2
WHERE        statfact <> '2'
UNION
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 2 AS tipo, total general, '' cod_subgrupo
FROM            VentasDiariasCMACST_4_NOCM
WHERE        statfact <> '2'
UNION
SELECT        numfactu, fechafac, subtotal, descuento, total, statfact, TotImpuesto, monto_flete, doc, cancelado, 6 AS tipo, total general, 'CELULAS MADRE' cod_subgrupo
FROM            VentasDiariasCMACST_4_CM
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
SELECT        I.numfactu, fechafac, sum(I.subtotalnew) subtotal, sum(I.descuentonew) AS descuento, sum(I.totalnew) AS total, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado, 5 AS tipo, Sum(I.general) AS general, 
                         'Bloqueo' cod_subgrupo
FROM            VentasDiariasMSSIV_5 I
WHERE        statfact <> '2'
GROUP BY I.numfactu, fechafac, I.statfact, I.totimpuesto, I.monto_flete, I.doc, I.cancelado