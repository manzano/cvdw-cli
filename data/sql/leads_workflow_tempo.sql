SELECT
	lwt.idtempo as referencia,
	lwt.data_cad as referencia_data,
	'S' as ativo,
	lwt.idtempo,
	lwt.idlead,
	lwt.idsituacao,
	lwt.tempo,
	lwt.data_cad,
	lw.nome as situacao,
	lw.sigla
FROM
	leads_workflow_tempo lwt FORCE INDEX (idx_leads_workflow_tempo_data_cad)
INNER JOIN leads_workflow lw ON
	lwt.idsituacao = lw.idsituacao
INNER JOIN leads l ON
	lwt.idlead = l.idlead
WHERE
	(lwt.idlead IS NOT NULL)
	AND (l.ativo = 'S')
ORDER BY
	referencia_data ASC
LIMIT 30