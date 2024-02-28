
    <?php if ($_SESSION["logEnvironment"] === "DEVELOPMENT"): ?>
        <!-- DEVELOPMENT ENVIRONMENT WARNING -->
        <div class="bg-success d-flex justify-content-center align-items-center gap-2 p-2 text-white">
            <p class="mb-0">
                <small>
                    You are in <b>development log</b> mode. The logs you are seeing are from the <code class="text-white"><?= LOGS_DIR_DEVELOPMENT ?></code> directory.
                    <a href="index.php?p=admin" class="text-white">Go to <b>administration</b> page to switch the <b>log environment mode</b></a>
                </small>
            </p>
        </div>
    <?php endif; ?>

    <!-- APP'S FOOTER -->
    <footer class="d-flex justify-content-between p-2" style="font-size: 12px;">
        <div class="hstack gap-2">
            <p class="mb-0">Sitemap:</p>
            <ul class="nav gap-2">
                <li class="nav-item">
                    <a href="index.php?p=app-list" class="btn btn-secondary btn-xs">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?p=log-list" class="btn btn-secondary btn-xs">
                        Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?p=chart-list" class="btn btn-secondary btn-xs">
                        Charts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?p=admin" class="btn btn-secondary btn-xs">
                        <i class="bi bi-gear-fill"></i>
                        Admin
                    </a>
                </li>
            </ul>
        </div>
        <div class="hstack gap-2">
            <p class="mb-0">
                Made with <i class="bi bi-suit-heart-fill"></i> by
                <a href="https://github.com/alexisphilip/log-app" target="_blank" rel="noopener noreferrer">Alexis Philip</a>
                <i class="bi bi-github"></i>
            </p>
        </div>
    </footer>

    <!-- BOOTSTRAP'S TOAST CONTAINER -->
    <div class="toast-container js-toast-container bottom-0 end-0 p-3"></div>

    <!-- TEMPLATES -->
    <div class="template-list">

        <div class="js-toast toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header js-toast-header">
                <strong class="me-auto js-toast-title"></strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body js-toast-body">
                <div class="js-toast-content"></div>
            </div>
        </div>

    </div>

    <!-- APP'S JS MEMBERS -->
    <script>
        // g_globalMember = "";
    </script>

    <!-- APP'S JS -->
    <script src="<?= App::getJs("class/Toast.js") ?>"></script>
    <script src="<?= App::getJs("class/App.js") ?>"></script>
    <script src="<?= App::getJs("functions.js") ?>"></script>
    <script src="<?= App::getJs("listeners.js") ?>"></script>

    <!-- PAGE'S JS -->
    <?php foreach (App::$staticFileList["js"] as $fileName) { ?>
        <script src="<?= App::getJs($fileName) ?>"></script>
    <?php } ?>

</body>
</html>