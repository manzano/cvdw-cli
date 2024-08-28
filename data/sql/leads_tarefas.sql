SELECT
	t.idtarefa as referencia,
	t.data_cad as referencia_data,
	t.ativo,
	t.idtarefa,
	t.data_cad,
	t.data,
	t.situacao,
	t.idresponsavel,
	t.tipo_responsavel,
	COALESCE(ua.nome, c.nome, ui.nome, up.nome) AS responsavel,
	t.funcionalidade,
	t.idfuncionalidade as idlead,
	t.idinteracao,
	t.tipo_interacao,
	empta.idempreendimento,
	empta.nome as nome_empreendimento
FROM
	tarefas t
INNER JOIN leads l ON
	t.idfuncionalidade = l.idlead
LEFT JOIN usuarios_adm ua ON
	ua.idusuario = t.idresponsavel
	AND tipo_responsavel = 'G'
LEFT JOIN corretores c ON
	c.idcorretor = t.idresponsavel
	AND tipo_responsavel = 'C'
LEFT JOIN usuarios_imobiliarias ui ON
	ui.idusuario = t.idusuario
	AND tipo_responsavel = 'I'
LEFT JOIN usuarios_pdv up ON
	up.idusuario = t.idresponsavel
	AND tipo_responsavel = 'P'
LEFT JOIN empreendimentos empta ON
	empta.idempreendimento = t.idempreendimento_visita
WHERE
	t.ativo = 'S'
GROUP BY
	t.data_cad
ORDER BY
	referencia_data ASC
LIMIT 30