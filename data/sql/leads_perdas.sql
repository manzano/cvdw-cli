SELECT
	lr.idlead_reativacao as referencia,
	lr.data_cad as referencia_data,
	'S' as ativo,
	lr.idlead_reativacao,
	l.idlead,
	l.nome,
	l.email,
	l.telefone,
	lr.data_cad as data_perda,
	lr.idusuario,
	lr.painel as painel_usuario
FROM
	leads_reativacao lr FORCE INDEX (idx_leads_reativacao_data_cad)
INNER JOIN leads l ON
	lr.idlead = l.idlead
WHERE
	(lr.tipo = 'cancelada')
	AND (l.ativo = 'S')
GROUP BY
	lr.data_cad
ORDER BY
	referencia_data ASC
LIMIT 30
 