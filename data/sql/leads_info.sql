SELECT
	l.idlead as referencia,
	IF(l.data_ultima_alteracao > l.data_cad,
	l.data_ultima_alteracao,
	l.data_cad) as referencia_data,
	l.ativo,
	l.idlead,
	GROUP_CONCAT(DISTINCT(t.tag) SEPARATOR ', ') AS tags,
	GROUP_CONCAT(DISTINCT(lc.origem) separator '; ') AS conversoes,
	CONCAT(l.origem, '; ', l.origem_ultimo) AS origens,
	l.data_ultima_alteracao,
	CASE
		WHEN l.idcorretor IS NOT NULL THEN c.nome
		WHEN l.idusuario IS NOT NULL THEN u.nome
		WHEN l.idimobiliaria IS NOT NULL THEN i.nome
		ELSE ''
	END AS responsavel
FROM
	leads l FORCE INDEX (idx_ativo_data_cad_data_ultima_alteracao)
LEFT JOIN lead_tags lt ON
	lt.idlead = l.idlead
LEFT JOIN tags t ON
	t.idtag = lt.idtag
LEFT JOIN leads_conversao lc ON
	lc.idlead = l.idlead
LEFT JOIN corretores c ON
	c.idcorretor = l.idcorretor
LEFT JOIN imobiliarias i ON
	i.idimobiliaria = l.idimobiliaria
LEFT JOIN usuarios_adm u ON
	u.idusuario = l.idusuario
WHERE
	l.ativo = 'S'
GROUP BY
	l.idlead
ORDER BY
	referencia_data ASC
LIMIT 30