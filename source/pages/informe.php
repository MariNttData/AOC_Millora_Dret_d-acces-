<?php
require_once __DIR__ . '/db.php';

function sanitize_nif($nif)
{
    return trim(strtoupper($nif));
}

$nif = '';
$nombre = '';
$telefono = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nif = isset($_POST['nifInput']) ? sanitize_nif($_POST['nifInput']) : '';
    $nombre = isset($_POST['nombreInput']) ? trim($_POST['nombreInput']) : '';
    $telefono = isset($_POST['telefonoInput']) ? trim($_POST['telefonoInput']) : '';
    $email = isset($_POST['emailInput']) ? trim($_POST['emailInput']) : '';
}

function get_dynamics_results($searchValue, $searchType = 'name')
{
    $searchValue = trim((string) $searchValue);
    if ($searchValue === '') {
        return [];
    }

    $results = [];

    // Cargar variables de entorno
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '/*') === 0 || strpos(trim($line), '*/') === 0)
                continue;

            if (strpos($line, '=') !== false && strpos(trim($line), '=') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
                putenv(trim($key) . "=" . trim($value));
            }
        }
    }

    // =========================
    // 1️⃣ Obtener token
    // =========================
    $tokenUrl = $_ENV['MICROSOFT_ENDPOINT'] . "/" .
        $_ENV['MICROSOFT_TENANT_ID'] .
        "/oauth2/v2.0/token";

    $postData = [
        'client_id' => $_ENV['MICROSOFT_CLIENT_ID'],
        'client_secret' => $_ENV['MICROSOFT_CLIENT_SECRET'],
        'scope' => $_ENV['MICROSOFT_SCOPE'],
        'grant_type' => 'client_credentials'
    ];

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200 || !$response) {
        return [];
    }

    $tokenData = json_decode($response, true);
    if (!isset($tokenData['access_token'])) {
        return [];
    }

    $accessToken = $tokenData['access_token'];

    // =========================
    // 2️⃣ Construir filtro correcto
    // =========================
    $searchValue = str_replace("'", "''", $searchValue);

    if ($searchType === 'telefono') {
        $filter = "contains(telephone1,'$searchValue')";
    } elseif ($searchType === 'email') {
        $filter = "contains(emailaddress1,'$searchValue')";
    } else {
        $filter = "contains(fullname,'$searchValue') or contains(emailaddress1,'$searchValue')";
    }

    $dynamicsUrl = rtrim($_ENV['DYNAMICS_ENDPOINT'], '/') .
        "/api/data/v9.2/contacts?" .
        "\$select=fullname,emailaddress1,telephone1,contactid" .
        "&\$filter=" . urlencode($filter);

    // =========================
    // 3️⃣ Consultar Dynamics
    // =========================
    $ch = curl_init($dynamicsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);

        if (isset($data['value']) && is_array($data['value'])) {
            foreach ($data['value'] as $row) {
                $results[] = [
                    'Nom complet' => $row['fullname'] ?? '',
                    'Telèfon' => $row['telephone1'] ?? '',
                    'Email' => $row['emailaddress1'] ?? ''
                ];
            }
        }
    }

    return $results;
}

$listServices = ["ETRAM", "ETAULER", "IDCATMOBIL", "EACAT PL", "REPRESENTA"];
$queries = [
    // ETRAM
    [
        'type' => 'mysql',
        'sql' => "SELECT ID, ID_TRAMIT,DESCRIPCIO_BREU, CODI_ENS, DOCUMENT, DATA_RECEPCIO from ETRAM20PCI.ETRAM_TRAMIT where DOCUMENT = :input"
    ],
    // ETAULER
    [
        'type' => 'mysql',
        'sql' => "SELECT * from ETAULER3PL.ET_EDICTE_HISTORIC where usuari_id = :input"
    ],
    // IDCATMOBIL
    [
        'type' => 'mysql',
        'sql' => "SELECT * FROM IDCATMOBIL.IDCATSMS_REGISTRE where document = :input"
    ],
    // EACAT PL
    [
        'type' => 'mysql',
        'sql' => "SELECT * from usu_usuari where identificador = :input"
    ],
    // REPRESENTA
    [
        'type' => 'mysql',
        'sql' => "SELECT * from REPRESENTA.r_persona where valordocumentidentificatiu like :inputprefix"
    ]
];

try {
    $conn = get_oracle_connection();
} catch (Exception $e) {
    echo "<p>Error en la connexió a Oracle: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

function render_table($rows)
{
    if (empty($rows)) {
        return '<p>No hi ha resultats.</p>';
    }

    $cols = array_keys($rows[0]);

    $finalCols = [];
    foreach ($cols as $col) {
        $allBinary = true;
        $hasAny = false;
        foreach ($rows as $r) {
            $v = $r[$col] ?? null;
            if ($v === null || $v === '')
                continue;
            $hasAny = true;
            $s = (string) $v;
            if (!in_array($s, ['0', '1'], true)) {
                $allBinary = false;
                break;
            }
        }
        if ($hasAny && $allBinary) {
            continue;
        }
        $finalCols[] = $col;
    }

    $html = '<div class="table-wrapper"><div class="table-scroll">';
    $html .= '<table class="table table-sm table-bordered table-striped table-compact">';
    $html .= '<thead><tr>';
    foreach ($finalCols as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr></thead>';
    $html .= '<tbody>';
    foreach ($rows as $r) {
        $html .= '<tr>';
        foreach ($finalCols as $col) {
            $v = $r[$col] ?? '';
            $str = (string) $v;
            $cell = htmlspecialchars($str);
            $tdClass = '';
            $html .= '<td class="' . $tdClass . '">' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div></div>';
    return $html;
}

function run_oracle_query($conn, $sql, $nif, $isLike = false)
{
    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception('OCI parse error: ' . ($e['message'] ?? 'unknown'));
    }

    if ($isLike) {
        $param = $nif . '%';
        if (!oci_bind_by_name($stid, ':inputprefix', $param, -1)) {
            $e = oci_error($stid);
            throw new Exception('OCI bind error: ' . ($e['message'] ?? 'unknown'));
        }
    } else {
        if (!oci_bind_by_name($stid, ':input', $nif, -1)) {
            $e = oci_error($stid);
            throw new Exception('OCI bind error: ' . ($e['message'] ?? 'unknown'));
        }
    }

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        throw new Exception('OCI execute error: ' . ($e['message'] ?? 'unknown'));
    }

    $rows = [];
    // Usar OCI_RETURN_LOBS para que los CLOB/BLOB se devuelvan como strings
    while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS)) !== false) {
        $rows[] = $row;
    }
    oci_free_statement($stid);
    return $rows;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dred d'acces</title>
    <link rel="stylesheet" href="../dependencies/css/bootstrap.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .table-compact {
            font-size: 0.85rem;
            table-layout: fixed;
            width: 100%;
        }

        .table-compact th,
        .table-compact td {
            padding: .35rem .5rem;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-wrapper {
            overflow-x: auto;
            width: 100%;
        }

        .table-compact td.col-truncate {
            max-width: 220px;
        }
    </style>
</head>

<body>
    <nav style='background-color: #f88c74'>
        <img src="../assets/logo AOC.png">
    </nav>

    <div class="container mt-5">
        <h1 class="h5 mb-0 fw-bold text-center">INFORME DE PROTECCIÓ DE DADES</h1>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="form-container">
                    <h2 class="h1 fw-bold text-center mb-3 page-title">Tasca Dret d'accés</h2>
                    <div class="text-center mb-2">
                        <a href="https://llicenciesaoc.sharepoint.com/sites/DocumentaciExp/SitePages/Informe-Protecci%C3%B3-de-dades-de-car%C3%A0cter-personal-(RGPD)---Dret-d%27acc%C3%A9s.aspx?web=1"
                            class="text-decoration-none" target="_blank" rel="noopener noreferrer">
                            Enllaç a la FAQ
                        </a>
                    </div>

                    <div id="informe" class="row justify-content-center mb-4">
                        <div class="col-9 card">
                            <div class="mt-3 p-3" style="font-family: Arial, Helvetica, sans-serif">
                                <div class="mb-3 d-flex gap-2">
                                    <button class="btn btn-primary btn-sm" onclick="copyToClipboard()"
                                        title="Copiar tot al portapapers">
                                        <i class="bi bi-clipboard"></i> Copiar Resultats
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="exportToExcel()"
                                        title="Exportar resultats a Excel">
                                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                                    </button>
                                </div>
                                <div id="reportContent">
                                    <p>Bon dia,<br>Adjunt l'informe de protecció de dades de l'usuari
                                        <b><?php echo htmlspecialchars($nif); ?></b>
                                    </p>
                                    <?php
                                    $dynamicsSearches = [];
                                    if (!empty($nombre)) {
                                        $dynamicsSearches[] = ['label' => 'Nom', 'value' => $nombre, 'type' => 'name'];
                                    }
                                    if (!empty($telefono)) {
                                        $dynamicsSearches[] = ['label' => 'Telèfon', 'value' => $telefono, 'type' => 'telefono'];
                                    }
                                    if (!empty($email)) {
                                        $dynamicsSearches[] = ['label' => 'Correu electrònic', 'value' => $email, 'type' => 'email'];
                                    }

                                    foreach ($dynamicsSearches as $search) {
                                        $dynamicsResults = get_dynamics_results($search['value'], $search['type']);
                                        echo '<div class="card mb-3" style="border:2px solid #0078d4;">';
                                        echo '<div class="card-header bg-primary text-white">Resultats Dynamics - ' . htmlspecialchars($search['label']) . '</div>';
                                        echo '<div class="card-body">';

                                        if (!empty($dynamicsResults) && count($dynamicsResults) > 0) {
                                            $totalResultados = count($dynamicsResults);
                                            $descriptor = '';
                                            if ($search['type'] === 'telefono') {
                                                $descriptor = 'el telèfon';
                                            } elseif ($search['type'] === 'email') {
                                                $descriptor = 'el correu electrònic';
                                            } else {
                                                $descriptor = 'el nom';
                                            }

                                            echo '<div class="alert alert-success">';
                                            echo 'S\'han trobat <b>' . $totalResultados . '</b> resultat(s) a Dynamics que contenen ' . $descriptor . ' <b>' . htmlspecialchars($search['value']) . '</b>.';
                                            echo '</div>';
                                            echo render_table($dynamicsResults);
                                        } else {
                                            $descriptor = '';
                                            if ($search['type'] === 'telefono') {
                                                $descriptor = 'el telèfon';
                                            } elseif ($search['type'] === 'email') {
                                                $descriptor = 'el correu electrònic';
                                            } else {
                                                $descriptor = 'el nom';
                                            }

                                            echo '<div class="alert alert-warning">';
                                            echo 'No s\'han trobat resultats a Dynamics que contenen ' . $descriptor . ' <b>' . htmlspecialchars($search['value']) . '</b>.';
                                            echo '</div>';
                                        }

                                        echo '</div>';
                                        echo '</div>';
                                    }

                                    if (empty($dynamicsSearches)) {
                                        echo '<div class="alert alert-info">No s\'ha proporcionat cap nom, telèfon o correu electrònic per a la cerca a Dynamics.</div>';
                                    }
                                    ?>
                                    <!-- Resultats Oracle -->
                                    <?php
                                    foreach ($queries as $i => $q) {
                                        $serviceName = $listServices[$i];
                                        $isLike = (strpos($q['sql'], ':inputprefix') !== false);
                                        $rows = run_oracle_query($conn, $q['sql'], $nif, $isLike);
                                        
                                        echo '<div class="card mb-3" style="border:2px solid #0078d4;">';
                                        echo '<div class="card-header bg-primary text-white">' . htmlspecialchars($serviceName) . '</div>';
                                        echo '<div class="card-body">';
                                        
                                        if (!empty($rows) && count($rows) > 0) {
                                            $totalResultados = count($rows);
                                            echo '<div class="alert alert-success">';
                                            echo 'S\'han trobat <b>' . $totalResultados . '</b> resultat(s) a ' . htmlspecialchars($serviceName) . '.';
                                            echo '</div>';
                                            echo render_table($rows);
                                        } else {
                                            echo '<div class="alert alert-warning">';
                                            echo 'No s\'han trobat resultats a ' . htmlspecialchars($serviceName) . '.';
                                            echo '</div>';
                                        }
                                        
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <br>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4 mb-5">
                        <a href="index.php" class="btn btn-secondary fw-bold">TORNAR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../dependencies/js/bootstrap.min.js"></script>

    <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody"></div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script>

        function showMessage(message, type = 'info', duration = 3000) {
            const modalEl = document.getElementById('messageModal');
            if (!modalEl) {
                alert(message);
                return;
            }
            const modalLabel = modalEl.querySelector('#messageModalLabel');
            const modalBody = modalEl.querySelector('#messageModalBody');
            const header = modalEl.querySelector('.modal-header');

            header.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white');

            if (type === 'success') {
                header.classList.add('bg-success', 'text-white');
                modalLabel.textContent = 'Correcte';
            } else if (type === 'danger' || type === 'error') {
                header.classList.add('bg-danger', 'text-white');
                modalLabel.textContent = 'Error';
            } else if (type === 'warning') {
                header.classList.add('bg-warning', 'text-white');
                modalLabel.textContent = 'Atenció';
            } else {
                header.classList.add('bg-info', 'text-white');
                modalLabel.textContent = '';
            }

            modalBody.textContent = message;
            const bsModal = new bootstrap.Modal(modalEl, { keyboard: true });
            try {
                bsModal.show();
            } catch (e) {
                console.error('Bootstrap modal show failed', e);
                alert(message);
                return;
            }

            if (duration > 0) {
                setTimeout(() => { try { bsModal.hide(); } catch (e) { } }, duration);
            }
        }

        function copyToClipboard() {
            const reportContent = document.getElementById('reportContent');
            if (!reportContent) { showMessage('No es va trobar el contingut per a copiar', 'warning'); return; }
            const html = reportContent.innerHTML;
            const text = reportContent.innerText || reportContent.textContent || '';

            if (navigator.clipboard && navigator.clipboard.write) {
                const blobHtml = new Blob([html], { type: 'text/html' });
                const blobText = new Blob([text], { type: 'text/plain' });
                const item = new ClipboardItem({ 'text/html': blobHtml, 'text/plain': blobText });
                navigator.clipboard.write([item]).then(() => {
                    showMessage('Contingut copiat al portapapers', 'success');
                }).catch((err) => {
                    console.warn('clipboard.write failed, falling back', err);
                    fallbackSelectionCopy();
                });
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    showMessage('Contingut copiat al portapapers (texto)', 'success');
                }).catch(() => {
                    fallbackSelectionCopy();
                });
                return;
            }

            fallbackSelectionCopy();

            function fallbackSelectionCopy() {
                try {
                    const range = document.createRange();
                    range.selectNodeContents(reportContent);
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    const ok = document.execCommand('copy');
                    selection.removeAllRanges();
                    if (ok) showMessage('Contingut copiat al portapapers', 'success');
                    else showMessage('No es va poder copiar al portapapers', 'warning');
                } catch (e) {
                    try {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed'; ta.style.top = '0'; ta.style.left = '0';
                        ta.style.width = '1px'; ta.style.height = '1px'; ta.style.padding = '0';
                        ta.style.border = 'none'; ta.style.outline = 'none'; ta.style.boxShadow = 'none'; ta.style.background = 'transparent';
                        document.body.appendChild(ta);
                        ta.select();
                        const ok2 = document.execCommand('copy');
                        document.body.removeChild(ta);
                        if (ok2) showMessage('Contingut copiat al portapapers (texto)', 'success');
                        else showMessage('No es va poder copiar al portapapers', 'warning');
                    } catch (e2) {
                        showMessage('Error en copiar: ' + (e2 && e2.message ? e2.message : e2), 'danger');
                    }
                }
            }
        }

        function exportToPDF() {
            const element = document.getElementById('reportContent');
            const nif = "<?php echo htmlspecialchars($nif); ?>";

            const opt = {
                margin: 10,
                filename: 'informe_dades_' + nif + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };

            html2pdf().set(opt).from(element).save();
        }

        async function exportToExcel() {
            const element = document.getElementById('reportContent');
            const nif = "<?php echo htmlspecialchars($nif); ?>";

            const tables = element.querySelectorAll('table.table-compact');
            if (!tables || tables.length === 0) {
                showMessage('No hi ha taules per a exportar', 'warning');
                return;
            }


            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'Informe';
            workbook.created = new Date();

            tables.forEach((table, idx) => {
                let sheetName = 'Sheet' + (idx + 1);
                const card = table.closest && table.closest('.card');
                if (card) {
                    const cardHeader = card.querySelector('.card-header');
                    if (cardHeader) {
                        sheetName = cardHeader.textContent.trim();
                    }
                }
                sheetName = sheetName.replace(/[\\\/\?\*\[\]\:]/g, '').substring(0, 31) || ('Sheet' + (idx + 1));

                const ws = workbook.addWorksheet(sheetName);

                const thead = table.querySelector('thead');
                const headers = [];
                if (thead) {
                    const ths = thead.querySelectorAll('th');
                    ths.forEach(th => headers.push(th.textContent.trim()));
                }

                if (headers.length) {
                    const headerRow = ws.addRow(headers);
                    headerRow.eachCell((cell) => {
                        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                        cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFA500' } };
                        cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
                    });
                }

                const tbody = table.querySelector('tbody');
                if (tbody) {
                    const trs = tbody.querySelectorAll('tr');
                    trs.forEach((tr, rindex) => {
                        const cells = [];
                        tr.querySelectorAll('td').forEach(td => cells.push(td.textContent.trim()));
                        const row = ws.addRow(cells);
                        row.eachCell((cell) => {
                            cell.alignment = { wrapText: true, vertical: 'top' };
                            cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
                        });
                        if ((rindex % 2) === 1) {
                            row.eachCell((cell) => {
                                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF7F7F7' } };
                            });
                        }
                    });
                }

                ws.columns.forEach((col) => {
                    let maxLength = 10;
                    col.eachCell({ includeEmpty: true }, (cell) => {
                        const v = cell.value ? String(cell.value) : '';
                        const lines = v.split('\n');
                        const longest = lines.reduce((a, b) => Math.max(a, b.length), 0);
                        if (longest > maxLength) maxLength = longest;
                    });
                    col.width = Math.min(Math.max(maxLength + 2, 8), 80);
                });
            });

            const buf = await workbook.xlsx.writeBuffer();
            const filename = 'informe_dades_' + nif + '.xlsx';
            saveAs(new Blob([buf]), filename);
        }
    </script>
</body>

</html>