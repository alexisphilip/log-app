<?php App::getTemplate("common/document-top", $tplVarList) ?>

<!-- APP HEADER -->
<header class="row mx-0 g-3 p-3 justify-content-xl-between justify-content-center bg-body-tertiary">
    <div class="col-auto hstack gap-3">
        <a href="index.php"role="button" aria-disabled="true"><img src="<?= App::getImg("main-logo.png") ?>" style="width: 2rem;"></a>
        <h4 class="mb-0"><b>Admin</b></h4>
    </div>
    <div class="col-auto hstack">
        <button type="button" class="btn btn-link js-bs-theme-btn text-decoration-none"></button>
    </div>
</header>

<!-- APP CONTENT -->
<main class="flex-1 overflow-auto py-4">

    <div class="container">

        <div class="row">

            <div class="col">

                <?php
                    $title = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "" : "To use development logs, you must activate the mode by clicking on the button.";
                ?>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="hstack gap-2">
                        <h3 class="mb-0">
                            Development Logs
                        </h3>
                        <?php $class = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "text-bg-success" : "text-bg-danger"; ?>
                        <span class="badge <?= $class ?>" title="<?= $title ?>"><?= $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "ON" : "OFF" ?></span>
                    </div>
                    <?php if ($_SESSION["logEnvironment"] === "DEVELOPMENT") { ?>
                        <button type="button" class="btn btn-primary js-log-environment-switch">Switch to <b>production logs</b> mode</button>
                    <?php } else { ?>
                        <button type="button" class="btn btn-primary js-log-environment-switch">Switch to <b>development logs</b> mode</button>
                    <?php } ?>
                </div>

                <p>This feature allows you to <u>generate large quantities</u> of <b>development logs</b> in a separate folder (configurable in <code>config.json</code> under the <code>LOGS_DIR_DEVELOPMENT</code> property).</p>
                <ul>
                    <li><b>Production logs</b> are stored in <code><?= LOGS_DIR_PRODUCTION ?></code></li>
                    <li><b>Development logs</b> are stored in <code><?= LOGS_DIR_DEVELOPMENT ?></code></li>
                </ul>
                <p>This allows you to test the app's capabilities with large-scale data 🔥</p>
                <p>All the logs you'll be seeing in the app will be fetched from the <b>development logs</b> folder instead of the <b>production logs</b> folder, until you switch back to the <b>production logs</b> mode 😉</p>

                <?php
                    $disabled = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "" : "disabled";
                    $striped = $_SESSION["logEnvironment"] === "DEVELOPMENT" ? "" : "striped-overlay striped-warning";
                ?>

                <div title="<?= $title ?>">

                    <div class="container border border-warning p-3 rounded <?= $striped ?>" <?= $disabled ?>>

                        <div class="row gy-3">
                            <div class="col-md-6 col-lg-auto">
                                <label for="daysToCreatePerSubDirectories" class="form-label">Days to create per sub-directories</label>
                                <input type="number" class="form-control" id="daysToCreatePerSubDirectories"  min="1" max="731" value="450" style="width: 5rem;" required>
                                <small>Max: 731 (2 years)</small>
                            </div>
                            <div class="col-md-6 col-lg-auto">
                                <label class="form-label">Logs to create per file</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="minLogsToCreatePerFile" min="0" max="100" value="0" style="max-width: 5rem;" required>
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" id="maxLogsToCreatePerFile" min="0" max="100" value="50" style="max-width: 5rem;" required>
                                </div>
                                <small>1 file = 1 day. A random value will be picked between the interval</small>
                            </div>
                            <!-- <div class="col-md-6 col-lg-auto">
                                <label class="form-label">Trace</label>
                                <p class="mb-0">4/5 chances to generate a trace</p>
                            </div>
                            <div class="col-md-6 col-lg-auto">
                                <label class="form-label">Data</label>
                                <p class="mb-0">1/5 chances to generate data</p>
                            </div> -->
                            <div class="col-12">
                                <div class="hstack gap-2">
                                    <button type="submit" class="btn btn-success js-development-log-generate">Generate <b>dev.</b> logs</button>
                                    <p class="mb-0">Generates a series of <b>development log</b> files.</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="hstack gap-2">
                                    <button type="button" class="btn btn-danger js-development-log-delete">Delete <b>dev.</b> logs</button>
                                    <p class="mb-0">Deletes the <b>development log</b> folder.</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($_SESSION["logEnvironment"] === "DEVELOPMENT") { ?>

                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="p-2 rounded bg-body-secondary js-directory-tree" style="min-height: 4rem;"></div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-body-tertiary px-3 py-2 border border-2 rounded shadow js-log-stats" style="min-height: 4rem; font-family: consolas; font-size: 14px;">
                                        <p class="mb-0"><u>Stats</u></p>
                                        <ul class="ps-4 mb-0 js-log-stats-values d-none">
                                            <!-- <li>
                                                Logs: <span class="badge rounded-pill text-bg-secondary js-total-logs"></span>
                                            </li> -->
                                            <li>
                                                Files: <span class="badge rounded-pill text-bg-secondary js-total-files"></span>
                                            </li>
                                            <li class="js-generated-in d-none">
                                                Generated in
                                                <span class="badge rounded-pill text-bg-secondary js-stats-generation-time"></span>
                                                seconds
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

</main>

<script>
    g_logEnvironment = "<?= $_SESSION["logEnvironment"] ?: "" ?>";
</script>

<?php App::getTemplate("common/document-bottom", $tplVarList) ?>
