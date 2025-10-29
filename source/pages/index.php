<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dred d'acces</title>
    <link rel="stylesheet" href="../dependencies/css/bootstrap.min.css"/>
</head>
<body>
    <nav style='background-color: #f88c74'>
        <img src="../assets/logo AOC.png">
    </nav>
 
    <div class="container mt-5">
        <h1 class="h5 mb-0 fw-bold text-center">INFORME DE PROTECCIÓ DE DADES</h1>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="form-container">
                    <h2 class="h1 fw-bold text-center mb-3 page-title">Tasca Dret d'accés</h2>
 
                    <div class="text-center mb-4">
                        <a href="https://llicenciesaoc.sharepoint.com/sites/DocumentaciExp/SitePages/Informe-Protecci%C3%B3-de-dades-de-car%C3%A0cter-personal-(RGPD)---Dret-d%27acc%C3%A9s.aspx?web=1"
                            class="text-decoration-none" target="_blank" rel="noopener noreferrer">
                            Enllaç a la FAQ
                        </a>
                    </div>
 
                    <p class="text-center text-muted mb-5">Introdueix les dades de l'usuari per a obtenir l'informe</p>
 
                    <form action="informe.php" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nom i cognoms <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" required>
                        </div>
 
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIF/NIE/Passaport <span class="text-danger">*</span></label>
                            <input type="text" name="nifInput" class="form-control" id="nifInput" required>
                            <div class="invalid-feedback" id="nifError"></div>
                            <div class="form-text">
                                Format: NIF (12345678Z), NIE (X1234567Z), Passaport (ABC123456)
                            </div>
                        </div>
 
                        <div class="mb-3">
                            <label class="form-label fw-bold">Correu Electrònic</label>
                            <input type="text" id="mail" class="form-control">
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
 
                        <div class="mb-3">
                            <label class="form-label fw-bold">Telèfon</label>
                            <input type="tel" class="form-control">
                            <div class="invalid-feedback" id="telError"></div>
                        </div>
 
                        <div class="text-end text-muted mb-4" style="font-size: 0.9rem;">
                            <span class="text-danger">*</span> Camps obligatoris
                        </div>
 
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="ticketCheck">
                            <label class="form-check-label" for="ticketCheck">
                                Tiquet obert per Cap de Servei
                            </label>
                        </div>
 
                        <div class="text-center">
                            <button type="submit" id="button" class="btn btn-generate btn-secondary fw-bold">
                                GENERAR INFORME
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    <script src="../scripts/formValidations.js"></script>
    <script src="../dependencies/js/bootstrap.min.js"></script>
</body>
</html>
