<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato Bancário - Leitor OFX</title>
    <!-- Inclusão do Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos base para a tabela, complementados pelo Tailwind */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #e2e8f0; /* Cor da borda mais suave, alinhada ao Tailwind */
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="container mx-auto p-4 mt-8 bg-white shadow-xl rounded-lg">
        <h1 class="text-4xl font-extrabold text-center mb-8 text-gray-800">
            <span class="text-blue-600">Leitor de Extratos OFX</span>
        </h1>

        <form method="post" enctype="multipart/form-data" class="mb-8 p-6 border border-gray-200 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-6">
            <label for="arquivos" class="block text-lg font-medium text-gray-700 cursor-pointer">
                <span class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 ease-in-out">
                    1. Escolher Arquivos OFX
                </span>
                <input type="file" name="arquivos[]" id="arquivos" multiple directory="" webkitdirectory="" mozdirectory="" class="hidden">
            </label>
            <button type="submit" class="px-8 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-75 transition duration-300 ease-in-out transform hover:scale-105">
                2. Gerar Tabela
            </button>
            <button onclick="copiarTabela()" class="mt-4 mb-8 px-8 py-3 bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75 transition duration-300 ease-in-out transform hover:scale-105">
                3. Copiar Tabela
            </button>
        </form>


        <script>
            function copiarTabela() {
                var tabela = document.querySelector('table');
                var texto = '';

                // Adicionar cabeçalho
                var cabecalho = tabela.querySelectorAll('th');
                for (var i = 0; i < cabecalho.length; i++) {
                    texto += cabecalho[i].innerText + '\t';
                }
                texto += '\n';

                // Adicionar linhas
                var linhas = tabela.querySelectorAll('tr');
                for (var i = 1; i < linhas.length; i++) { // Começar do 1 para pular o cabeçalho
                    var celulas = linhas[i].querySelectorAll('td');
                    for (var j = 0; j < celulas.length; j++) {
                        texto += celulas[j].innerText + '\t';
                    }
                    texto += '\n';
                }

                // Copiar para a área de transferência
                navigator.clipboard.writeText(texto)
                    .then(function() {
                        alert('Tabela copiada para a área de transferência com sucesso, meu jovem padawan!');
                    })
                    .catch(function(err) {
                        console.error('Erro ao copiar tabela: ', err);
                        alert('Erro ao copiar tabela para a área de transferência. Consulte o console para mais detalhes, e que a Força esteja com você!');
                    });
            }
        </script>

        <?php

        function extrairLancamentosTexto(string $arquivoCaminho, string $nomeArquivo): ?array
        {
            $conteudo = file_get_contents($arquivoCaminho);
            if ($conteudo === false) {
                error_log("Erro ao ler o arquivo.");
                return null;
            }

            $lancamentos = [];
            $linhas = explode("\n", $conteudo); // Dividir o conteúdo em linhas

            // Variáveis para armazenar os dados do lançamento atual
            $tipo = null;
            $dataPostada = null;
            $valor = null;
            $titulo = null;
            $dataBrasil = null;
            $tituloCompleto = ''; // Variável para acumular o título completo
            $uniqueId = null;
            $checkNumber = null;
            $transactionUid = null;
            $agencyNumber = null;
            $accountNumber = null;
            $routingNumber = null;
            $accountType = null;
            $balance = null;
            $balanceDate = null;
            $startDate = null;
            $endDate = null;
            $currency = null;
            $signOnCode = null;
            $signOnSeverity = null;
            $signOnMessage = null;
            $accountInfoDesc = null;
            $accountInfoNumber = null;

            // Flag para indicar o início dos lançamentos
            $inicioLancamentos = false;
            $inicioSignOn = false;
            $inicioAccountInfo = false;

            foreach ($linhas as $linha) {
                $linha = trim($linha); // Remover espaços em branco no início e no final

                // Detectar o início dos lançamentos (ignorando cabeçalhos)
                if (strpos($linha, '<STMTTRN>') !== false) {
                    $inicioLancamentos = true;
                    // Resetar as variáveis para um novo lançamento
                    $tipo = null;
                    $dataPostada = null;
                    $valor = null;
                    $titulo = null;
                    $dataBrasil = null;
                    $tituloCompleto = ''; // Variável para acumular o título completo
                    $uniqueId = null;
                    $checkNumber = null;
                    $transactionUid = null;
                }

                // Detectar o início do SignOn
                if (strpos($linha, '<SIGNONMSGSRSV1>') !== false) {
                    $inicioSignOn = true;
                    $signOnCode = null;
                    $signOnSeverity = null;
                    $signOnMessage = null;
                }

                // Detectar o início do AccountInfo
                if (strpos($linha, '<BANKACCTFROM>') !== false) {
                    $inicioAccountInfo = true;
                    $agencyNumber = null;
                    $accountNumber = null;
                    $routingNumber = null;
                    $accountType = null;
                }

                // Processar apenas as linhas dentro da seção <STMTTRN>
                if ($inicioLancamentos) {
                    // Extrair TRNTYPE
                    if (strpos($linha, '<TRNTYPE>') !== false) {
                        $tipo = strip_tags($linha); // Remover tags HTML/XML
                        $tipo = trim(str_replace('<TRNTYPE>', '', $tipo));
                    }

                    // Extrair DTPOSTED
                    if (strpos($linha, '<DTPOSTED>') !== false) {
                        $dataPostada = strip_tags($linha);
                        $dataPostada = trim(str_replace('<DTPOSTED>', '', $dataPostada));
                        // Formatar a data para dd/mm/aaaa
                        try {
                            $dataBrasil = date('d/m/Y', strtotime(substr($dataPostada, 0, 4) . '-' . substr($dataPostada, 4, 2) . '-' . substr($dataPostada, 6, 2)));
                        } catch (Exception $e) {
                            $dataBrasil = $dataPostada; // Se a data for inválida, usar a original
                        }
                    }

                    // Extrair TRNAMT
                    if (strpos($linha, '<TRNAMT>') !== false) {
                        $valor = strip_tags($linha);
                        $valor = trim(str_replace('<TRNAMT>', '', $valor));
                    }

                    // Extrair MEMO
                    if (strpos($linha, '<MEMO>') !== false) {
                        $titulo = strip_tags($linha);
                        $titulo = trim(str_replace('<MEMO>', '', $titulo));
                        $tituloCompleto .= $titulo . " "; // Acumular o título
                    }

                    // Extrair CHECKNUM
                    if (strpos($linha, '<CHECKNUM>') !== false) {
                        $checkNumber = strip_tags($linha);
                        $checkNumber = trim(str_replace('<CHECKNUM>', '', $checkNumber));
                    }

                    // Extrair FITID
                    if (strpos($linha, '<FITID>') !== false) {
                        $uniqueId = strip_tags($linha);
                        $uniqueId = trim(str_replace('<FITID>', '', $uniqueId));
                    }

                    // Extrair TRNUID
                    if (strpos($linha, '<TRNUID>') !== false) {
                        $transactionUid = strip_tags($linha);
                        $transactionUid = trim(str_replace('<TRNUID>', '', $transactionUid));
                    }

                }

                // Processar apenas as linhas dentro da seção <SIGNONMSGSRSV1>
                if ($inicioSignOn) {
                    // Extrair CODE
                    if (strpos($linha, '<CODE>') !== false) {
                        $signOnCode = strip_tags($linha);
                        $signOnCode = trim(str_replace('<CODE>', '', $signOnCode));
                    }

                    // Extrair SEVERITY
                    if (strpos($linha, '<SEVERITY>') !== false) {
                        $signOnSeverity = strip_tags($linha);
                        $signOnSeverity = trim(str_replace('<SEVERITY>', '', $signOnSeverity));
                    }

                    // Extrair MESSAGE
                    if (strpos($linha, '<MESSAGE>') !== false) {
                        $signOnMessage = strip_tags($linha);
                        $signOnMessage = trim(str_replace('<MESSAGE>', '', $signOnMessage));
                    }
                }

                // Processar apenas as linhas dentro da seção <BANKACCTFROM>
                if ($inicioAccountInfo) {
                    // Extrair BANKID (Agency Number)
                    if (strpos($linha, '<BANKID>') !== false) {
                        $agencyNumber = strip_tags($linha);
                        $agencyNumber = trim(str_replace('<BANKID>', '', $agencyNumber));
                    }

                    // Extrair ACCTID (Account Number)
                    if (strpos($linha, '<ACCTID>') !== false) {
                        $accountNumber = strip_tags($linha);
                        $accountNumber = trim(str_replace('<ACCTID>', '', $accountNumber));
                    }

                    // Extrair ACCTTYPE (Account Type)
                    if (strpos($linha, '<ACCTTYPE>') !== false) {
                        $accountType = strip_tags($linha);
                        $accountType = trim(str_replace('<ACCTTYPE>', '', $accountType));
                    }
                }

                // Detectar o fim dos lançamentos (ignorando rodapés)
                if (strpos($linha, '</STMTTRN>') !== false) {
                    $inicioLancamentos = false;
                    // Adicionar o lançamento anterior ao array (se houver)
                    if ($tipo !== null && $dataBrasil !== null && $valor !== null) {
                        // Converter para UTF-8 e escapar para HTML
                        $tituloCompletoUTF8 = utf8_encode($tituloCompleto);
                        // Formatar o valor com vírgula
                        $valorFormatado = number_format((float)$valor, 2, ',', '.');
                        $lancamentos[] = [
                            'fileName' => htmlspecialchars($nomeArquivo),
                            'date' => htmlspecialchars($dataBrasil),
                            'uniqueId' => htmlspecialchars($uniqueId),
                            'name' => htmlspecialchars($tituloCompletoUTF8),
                            'memo' => htmlspecialchars($tituloCompletoUTF8),
                            'checkNumber' => htmlspecialchars($checkNumber),
                            'type' => htmlspecialchars($tipo),
                            'amount' => htmlspecialchars($valorFormatado),
                            'signOn code' => htmlspecialchars($signOnCode),
                            'signOn severity' => htmlspecialchars($signOnSeverity),
                            'signOn message' => htmlspecialchars($signOnMessage),
                            'accountInfo desc' => htmlspecialchars($accountInfoDesc),
                            'accountInfo number' => htmlspecialchars($accountInfoNumber),
                            'transactionUid' => htmlspecialchars($transactionUid),
                            'agencyNumber' => htmlspecialchars($agencyNumber),
                            'accountNumber' => htmlspecialchars($accountNumber),
                            'routingNumber' => htmlspecialchars($routingNumber),
                            'accountType' => htmlspecialchars($accountType),
                            'balance' => htmlspecialchars($balance),
                            'balanceDate' => htmlspecialchars($balanceDate),
                            'startDate' => htmlspecialchars($startDate),
                            'endDate' => htmlspecialchars($endDate),
                            'currency' => htmlspecialchars($currency),
                        ];
                    }
                }

                // Detectar o fim do SignOn
                if (strpos($linha, '</SIGNONMSGSRSV1>') !== false) {
                    $inicioSignOn = false;
                }

                // Detectar o fim do AccountInfo
                if (strpos($linha, '</BANKACCTFROM>') !== false) {
                    $inicioAccountInfo = false;
                }
            }

            return $lancamentos;
        }

        function processarArquivosOFX(array $arquivos): array
        {
            $lancamentosGeral = [];

            foreach ($arquivos['name'] as $key => $name) {
                $tmp_name = $arquivos['tmp_name'][$key];
                $error = $arquivos['error'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    $nomeArquivo = basename($name);
                    $lancamentos = extrairLancamentosTexto($tmp_name, $nomeArquivo);

                    if ($lancamentos !== null) {
                        $lancamentosGeral = array_merge($lancamentosGeral, $lancamentos);
                    }
                }
            }

            return $lancamentosGeral;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["arquivos"])) {
            $arquivos = $_FILES["arquivos"];
            $lancamentosGeral = processarArquivosOFX($arquivos);

            // Gerar a tabela HTML com todos os lançamentos
            if (!empty($lancamentosGeral)) {
                echo "<div class='overflow-x-auto shadow-md rounded-lg'>"; // Wrapper para responsividade e sombra
                echo "<table class='min-w-full divide-y divide-gray-200'>"; // Classes Tailwind para a tabela
                echo "<thead class='bg-gray-50'>"; // Cabeçalho da tabela
                echo "<tr>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>fileName</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>date</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>uniqueId</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>name</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>memo</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Classificação</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>checkNumber</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>type</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>amount</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>signOn code</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>signOn severity</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>signOn message</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>accountInfo desc</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>accountInfo number</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>transactionUid</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>agencyNumber</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>accountNumber</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>routingNumber</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>accountType</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>balance</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>balanceDate</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>startDate</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>endDate</th>";
                echo "<th scope='col' class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>currency</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody class='bg-white divide-y divide-gray-200'>"; // Corpo da tabela
                $rowIndex = 0; // Adicionar um contador de linha
                foreach ($lancamentosGeral as $lancamento) {
                    $rowClass = ($rowIndex % 2 == 0) ? 'bg-white' : 'bg-gray-50'; // Alternar cores de linha
                    echo "<tr class='" . $rowClass . " hover:bg-gray-100 transition duration-150 ease-in-out'>"; // Efeito hover
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . $lancamento['fileName'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['date'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['uniqueId'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . $lancamento['name'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['memo'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'></td>"; // Classificação
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['checkNumber'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['type'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900'>" . $lancamento['amount'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['signOn code'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['signOn severity'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['signOn message'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['accountInfo desc'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['accountInfo number'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['transactionUid'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['agencyNumber'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['accountNumber'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['routingNumber'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['accountType'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['balance'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['balanceDate'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['startDate'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['endDate'] . "</td>";
                    echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . $lancamento['currency'] . "</td>";
                    echo "</tr>";
                    $rowIndex++; // Incrementar o contador de linha
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>"; // Fechar o wrapper
            } else {
                echo "<p class='p-4 bg-yellow-100 text-yellow-700 rounded-lg mt-4 text-center font-medium'>Nenhum lançamento encontrado nos arquivos selecionados. Verifique se os arquivos OFX estão corretos.</p>";
            }
        }
        ?>

    </div> <!-- Fecha o container principal -->
</body>
</html>