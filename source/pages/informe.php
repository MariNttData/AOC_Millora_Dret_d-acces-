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
    echo "Connected to Oracle database successfully.";
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
                            <div class="mt-3" style="font-family: Arial, Helvetica, sans-serif">
                                <p>Bon dia,<br>Adjunt l'informe de protecció de dades de l'usuari <b><?php echo htmlspecialchars($nif); ?></b></p>
                                <?php
                                foreach ($queries as $i => $q) {
                                    echo "<p><b>" . $listServices[$i] . "</b></p>";
                                    $isLike = (strpos($q['sql'], ':inputprefix') !== false);
                                    $rows = run_oracle_query($conn, $q['sql'], $nif, $isLike);
                                    echo render_table($rows);                                  
                                }
                                ?>
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
</body>
</html>