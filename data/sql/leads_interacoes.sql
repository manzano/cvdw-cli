SELECT
	li.idinteracao as referencia,
	li.data_cad as referencia_data,
	li.ativo,
	li.idinteracao,
	li.idlead,
	li.data_cad,
	li.tipo,
	li.descricao,
	IF (li.tipo = 'L',
	li.situacao,
	NULL) AS situacao,
	IF (li.tipo = 'E',
	li.enviar_corretor,
	NULL) AS enviar_corretor,
	IF (li.tipo = 'E',
	li.enviar_imobiliaria,
	NULL) AS enviar_imobiliaria,
	IF (li.tipo = 'E',
	li.enviar_cliente,
	NULL) AS enviar_cliente,
	l.idimobiliaria,
	i.nome as imobiliaria,
	l.idcorretor,
	c.nome as corretor,
	l.idusuario as idgestor,
	adm.nome as gestor_interacao
FROM
	leads_interacoes li
LEFT JOIN leads l ON
	li.idlead = l.idlead
LEFT JOIN usuarios_adm adm ON
	li.idusuario = adm.idusuario
LEFT JOIN corretores c ON
	c.idcorretor = l.idcorretor
LEFT JOIN imobiliarias i ON
	i.idimobiliaria = l.idimobiliaria
WHERE
	(li.ativo = 'S')
	AND (l.ativo = 'S')
GROUP BY
	li.data_cad
ORDER BY
	referencia_data ASC
LIMIT 30