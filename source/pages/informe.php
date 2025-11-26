<?php
require_once __DIR__ . '/db.php';

function sanitize_nif($nif) {
    return trim(strtoupper($nif));
}

$nif = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nif = isset($_POST['nifInput']) ? sanitize_nif($_POST['nifInput']) : '';
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

function render_table($rows) {
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
            if ($v === null || $v === '') continue;
            $hasAny = true;
            $s = (string)$v;
            if (!in_array($s, ['0', '1'], true)) { $allBinary = false; break; }
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
            $str = (string)$v;
            $cell = htmlspecialchars($str);
            $tdClass = '';
            $html .= '<td class="' . $tdClass . '">' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div></div>';
    return $html;
}

function run_oracle_query($conn, $sql, $nif, $isLike = false) {
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
            width:100%;
        }
        .table-compact th, .table-compact td {
            padding: .35rem .5rem;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-wrapper { overflow-x: auto; width:100%; }

        .table-compact td.col-truncate { max-width: 220px; }
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
                                    <button class="btn btn-primary btn-sm" onclick="copyToClipboard()" title="Copiar todo al portapapeles">
                                        <i class="bi bi-clipboard"></i> Copiar Resultados
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="exportToExcel()" title="Exportar resultados a Excel">
                                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                                    </button>
                                </div>
                                <div id="reportContent">
                                     <p>Bon dia,<br>Adjunt l'informe de protecció de dades de l'usuari <b><?php echo htmlspecialchars($nif); ?></b></p>
                                <?php
                                foreach ($queries as $i => $q) {
                                    echo "<p><b>" . $listServices[$i] . "</b></p>";
                                    $isLike = (strpos($q['sql'], ':inputprefix') !== false);
                                    $rows = run_oracle_query($conn, $q['sql'], $nif, $isLike);
                                    echo render_table($rows);                                  
                                }
                                ?>
                                </div>
                                <br>
                            </div>
                        </div>
                    </div>
                     <div class="text-center mt-4 mb-5">
                        <a href="index.php" class="btn btn-secondary fw-bold">VOLVER</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../dependencies/js/bootstrap.min.js"></script>
    <!-- SheetJS (XLSX) para exportar tablas a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        function copyToClipboard() {
            const reportContent = document.getElementById('reportContent');
            
            // Crear un rango y seleccionar el contenido
            const range = document.createRange();
            range.selectNodeContents(reportContent);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Copiar al portapapeles
            try {
                document.execCommand('copy');
                alert('Contenido copiado al portapapeles');
                selection.removeAllRanges();
            } catch (err) {
                alert('Error al copiar: ' + err);
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

        function exportToExcel() {
            const element = document.getElementById('reportContent');
            const nif = "<?php echo htmlspecialchars($nif); ?>";

            const tables = element.querySelectorAll('table.table-compact');
            if (!tables || tables.length === 0) {
                alert('No hay tablas para exportar');
                return;
            }

            const wb = XLSX.utils.book_new();

            tables.forEach((table, idx) => {
                let sheetName = 'Sheet' + (idx + 1);
                let prev = table.previousElementSibling;
                while (prev) {
                    const b = prev.querySelector && prev.querySelector('b');
                    if (b && b.textContent.trim()) {
                        sheetName = b.textContent.trim().substring(0, 31);
                        break;
                    }
                    prev = prev.previousElementSibling;
                }

                try {
                    const ws = XLSX.utils.table_to_sheet(table);
                    XLSX.utils.book_append_sheet(wb, ws, sheetName || ('Sheet' + (idx + 1)));
                } catch (err) {
                    const ws = XLSX.utils.aoa_to_sheet([[ 'Error al convertir tabla', (err && err.message) || '' ]]);
                    XLSX.utils.book_append_sheet(wb, ws, sheetName || ('Sheet' + (idx + 1)));
                }
            });

            const filename = 'informe_dades_' + nif + '.xlsx';
            XLSX.writeFile(wb, filename);
        }
    </script>
</body>
</html>