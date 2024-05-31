<?php
// Configurações do banco de dados
$host = '127.0.0.1';
$username = 'manzano';
$password = 'manzano';
$database = 'cvdw_cv';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consulta SQL
$sql = "SELECT * FROM vendas"; // Seleciona todas as colunas
$result = $conn->query($sql);

// Caminho para o arquivo de template
$templatePath = 'template/vendas.txt';
// Lê o conteúdo do template
$templateContent = file_get_contents($templatePath);

// Nome do arquivo baseado na data atual
$date = date('Y_m_d'); // Formato Ano_Mês_Dia
$outputPath = "dados/vendas_{$date}.txt";


$allContent = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $processedContent = $templateContent;
        foreach ($row as $key => $value) {
            if($value){
                $variavel = '{{'.$key.'}}';
                $processedContent = str_replace($variavel, $value, $processedContent);
            }
        }
        $allContent .= $processedContent . "\n\n"; // Adiciona duas quebras de linha entre cada registro
    }
    file_put_contents($outputPath, $allContent);
    echo "Arquivo gerado com sucesso: $outputPath\n";
} else {
    echo "Nenhum registro encontrado.";
}

$conn->close();