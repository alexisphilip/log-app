<?php App::getTemplate("common/document-top", $tplVarList) ?>

<!-- APP HEADER -->
<header class="row mx-0 g-3 p-3 justify-content-xl-between justify-content-center bg-body-tertiary">
    <div class="col-auto hstack gap-3">
        <a href="index.php"role="button" aria-disabled="true"><img src="<?= App::getImg("main-logo.png") ?>" style="width: 2rem;"></a>
        <h4 class="mb-0">Logs <b>App</b></h4>
    </div>
    <div class="col-auto hstack">
        <button type="button" class="btn btn-link js-bs-theme-btn text-decoration-none"></button>
    </div>
</header>

<!-- APP CONTENT -->
<main class="flex-1 overflow-auto container">
    <div class="row pt-4 g-3">
        <?php foreach($fullDirectory as $directoryElement): ?>
            <div class="col">
                <div class="rounded border border-2 p-3 bg-body-tertiary">
                    <div class="row justify-content-between mb-3">
                        <div class="col-auto"></div>
                        <div class="col-auto">
                            <h2 class="text-center"><?= mb_strtoupper($directoryElement["name"]) ?></h2>
                        </div>
                        <div class="col-auto">
                            <a href="index.php?p=chart-list&directory=<?= $directoryElement["path"] ?>" class="btn btn-outline-secondary" title="Charts">
                                <i class="bi bi-bar-chart-line-fill"></i>
                            </a>
                        </div>
                    </div>
                    <div class="row justify-content-center g-3">
                        <?php foreach($directoryElement["content"] as $subDirectoryElement): ?>
                            <div class="col-auto">
                                <div class="card shadow border-0" style="width: 18rem;">
                                    <div class="card-body">
                                        <div class="row justify-content-between">
                                            <div class="col-auto">
                                                <h4 class="card-title mb-3"><?= mb_strtoupper($subDirectoryElement["name"]) ?></h4>
                                            </div>
                                            <div class="col-auto">
                                                <a href="index.php?p=chart-list&directory=<?= $subDirectoryElement["path"] ?>" class="btn btn-outline-secondary" title="Charts">
                                                    <i class="bi bi-bar-chart-line-fill"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="d-flex" style="gap: 0.5rem;">
                                            <?php if ($subDirectoryElement["content"]): ?>
                                                <?php foreach($subDirectoryElement["content"] as $originName => $subSubDirectoryElement): ?>
                                                    <?php
                                                        if ($subSubDirectoryElement["type"] === "file") continue;
                                                        $originConfig = ORIGIN_COLOR_LIST[$originName] ?: ORIGIN_COLOR_LIST["default"];
                                                        $btnClass = $originConfig["bootstrapColorType"];
                                                    ?>
                                                    <span class="badge rounded-pill text-bg-<?= $btnClass ?>">
                                                        <?= mb_strtoupper($originName) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <p class="mb-0"><small><span class="fst-italic">No logs yet</span> 🤯</small></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex justify-content-center mt-3">
                                            <a href="<?= "index.php?p=log-list&directory=$subDirectoryElement[path]" ?>" class="btn btn-primary fw-medium shadow-sm position-relative">
                                                SEE LOGS
                                                <?php if ($subDirectoryElement["totalLogs"]): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary border border-1 border-light">
                                                        <span class="fw-normal">Today:</span> <?= $subDirectoryElement["totalLogs"] ?>
                                                        <span class="visually-hidden">logs</span>
                                                    </span>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!count($fullDirectory)) { ?>
            <div class="col text-center">
                <p>No logs in the current directory!</p>
                <p>Directory: <code><?= LOGS_DIR ?></code></p>
            </div>
        <?php } ?>
    </div>

</main>

<?php App::getTemplate("common/document-bottom", $tplVarList) ?>
