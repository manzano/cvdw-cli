SELECT
	lh.idhistorico as referencia,
	lh.data_cad as referencia_data,
	'S' as ativo,
	lh.idhistorico,
	lh.idlead,
	lh.data_cad,
	lh.de,
	lh.para,
	'' as de_nome,
	'' as para_nome,
	mcl.nome as motivo_cancelamento,
	l.data_cancelamento
FROM
	leads_historicos lh FORCE INDEX (idx_coluna_tabela_data_cad)
INNER JOIN leads l ON
	lh.idlead = l.idlead
LEFT JOIN motivos_cancelamento_lead mcl
                        ON
	l.idmotivo_cancelamento = mcl.idmotivo
	AND mcl.ativo = 'S'
WHERE
	(lh.coluna = 'idsituacao')
	AND (lh.tabela = 'leads')
ORDER BY
	referencia_data ASC
LIMIT 30