<?php App::getTemplate("common/document-top", $tplVarList) ?>

<!-- APP HEADER -->
<header class="row mx-0 g-3 p-3 justify-content-xl-between justify-content-center bg-body-tertiary border-bottom">
    <div class="col-auto hstack gap-3">
        <a href="index.php"role="button" aria-disabled="true"><img src="<?= App::getImg("main-logo.png") ?>" style="width: 2rem;"></a>
        <h4 class="mb-0"><b>Charts</b></h4>
        <div class="h-100 border-end"></div>
        <?php
            $currentUrl = "index.php?p=log-list" . $url["directory"];
        ?>
        <a class="btn btn-sm btn-outline-secondary" role="button" href="<?= $currentUrl ?>" data-bs-placement="bottom" title="Switch to logs view">
            LOGS
            <i class="bi bi-list-check"></i>
        </a>
        <h4 class="mb-0 hstack gap-1">
            <?php
                $currentUrl = $url["base"] . $url["dateFilter"] . $url["startDate"] . $url["endDate"];
                componentBreadCrumb($fileExplorer, $selectedDirectoryPath, $currentUrl)
            ?>
        </h4>
        <span class="badge rounded-pill text-bg-secondary" data-bs-placement="bottom" title="Total logs in the selected directory">
            <?= number_format($totalLogsInSelectedDirectory) ?>
        </span>
    </div>
    <div class="col-auto hstack">
        <button type="button" class="btn btn-link js-bs-theme-btn" style="text-decoration: none;"></button>
    </div>
</header>

<!-- APP CONTENT -->
<main class="d-flex flex-1 overflow-hidden position-relative">

    <!-- SIDE BAR -->
    <div class="js-side-bar d-flex h-100">
        <!-- SIDE BAR BUTTONS -->
        <div class="js-side-bar-btn-wrapper h-100 d-flex flex-column bg-body-tertiary border-end">
            <button class="btn btn-sm btn-outline-secondary side-bar-action js-side-bar-btn" data-id="explorer">
                <i class="bi bi-folder"></i>
            </button>
            <!-- <button class="btn btn-sm btn-outline-secondary side-bar-action js-side-bar-btn" data-id="calendar">
                <i class="bi bi-calendar-week"></i>
            </button> -->
            <button class="btn btn-sm btn-outline-secondary side-bar-action js-side-bar-btn" data-id="terminal">
                <i class="bi bi-terminal"></i>
            </button>
        </div>
        <!-- SIDE BAR TABS: LEFT OF THE PAGE -->
        <div class="js-side-bar-tab-wrapper side-bar-tab-wrapper h-100 bg-body-tertiary" data-id="left">
            <!-- TAB: EXPLORER -->
            <div class="js-side-bar-tab side-bar-tab d-flex flex-column h-100 overflow-hidden d-none" data-id="explorer">
                <!-- Header -->
                <header class="p-3 border-bottom">
                    <h5 class="mb-0">Explorer</h5>
                </header>
                <!-- Content -->
                <div class="p-3 flex-1 overflow-auto">
                    <?php
                        $currentUrl = $url["base"] . $url["dateFilter"] . $url["startDate"] . $url["endDate"];
                        componentFileExplorer($fullDirectory, $selectedDirectoryPath, $currentUrl);
                    ?>
                </div>
            </div>
            <!-- TAB: CALENDAR -->
            <!-- <div class="js-side-bar-tab side-bar-tab d-flex flex-column h-100 overflow-hidden d-none" data-id="calendar">
                <header class="p-3 border-bottom">
                    <h5 class="mb-0">Calendar</h5>
                </header>
                <div class="p-3 flex-1 overflow-auto">
                    Coming soon!
                </div>
            </div> -->
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="h-100 d-flex flex-column flex-1">

        <!-- CHART FILTERS -->
        <div class="p-4 shadow-sm">
            <div class="js-chart-filter-list bg-body-secondary rounded p-3 shadow-sm">
                <div class="row mx-0 g-4">
                    <div class="col-auto">
                        <div class="bg-body-tertiary px-3 py-2 border border-2 rounded shadow" style="font-family: consolas; font-size: 14px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="mb-0"><u>Stats</u></p>
                                <?php
                                    $currentUrl = $url["base"] . $url["directory"] . $url["dateFilter"] . $url["startDate"] . $url["endDate"];
                                ?>
                                <a href="<?= $currentUrl ?>&db-log-generate-all=1" class="btn btn-outline-secondary btn-sm" role="button" style="padding: 0 0.125rem; font-size: 14px;" title="Regenerates the JSON temporary table used to store & get all the logs faster than looping though all the logs files individually">Regenerate table</a>
                            </div>
                            <ul class="ps-4 mb-0">
                                <li>
                                    <span class="text-success-emphasis fw-bold">Logs up to date!</span> <small>(today, <?= (new DateTime())->format("Y-m-d") ?>)</small>
                                </li>
                                <?php if ($perfList["file-system-parse-all-logs"]) { ?>
                                    <li>
                                        <i>Log App</i> updated <code><?= $jsonLogTableName ?>.json</code> with the latest logs:
                                        <ul>
                                            <li>
                                                Parsed all logs from file system in 
                                                <span class="badge rounded-pill text-bg-secondary">
                                                    <?= number_format($perfList["file-system-parse-all-logs"]["elapsed"], 3) ?>
                                                </span>
                                                secs
                                            </li>
                                            <li>
                                                Deleted previous <code><?= $jsonLogTableName ?>.json</code> table in
                                                <span class="badge rounded-pill text-bg-secondary">
                                                    <?= number_format($perfList["table-log-delete"]["elapsed"], 3) ?>
                                                </span>
                                                secs
                                            </li>
                                            <li>
                                                Inserted
                                                <span class="badge rounded-pill text-bg-secondary">
                                                    <?= number_format($totalLogs) ?>
                                                </span>
                                                logs in <code><?= $jsonLogTableName ?>.json</code> in
                                                <span class="badge rounded-pill text-bg-secondary">
                                                    <?= number_format($perfList["table-log-insert"]["elapsed"], 3) ?>
                                                </span>
                                                secs
                                            </li>
                                        </ul>
                                    </li>
                                <?php } ?>
                                <li>
                                    Parsed
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <?= number_format($totalLogs) ?>
                                    </span>
                                    logs from <code><?= $jsonLogTableName ?>.json</code> in
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <?= number_format($perfList["table-log-select"]["elapsed"], 3) ?>
                                    </span>
                                    secs
                                </li>
                                <li>
                                    Calculated charts in
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <?= number_format($perfList["chart-list-calculation"]["elapsed"], 3) ?>
                                    </span>
                                    secs
                                </li>
                                <li>
                                    Showing
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval) ?>
                                    </span>
                                    logs over
                                    <span class="badge rounded-pill text-bg-secondary">
                                        <?= number_format($totalDaysInSelectedTimeInterval) ?>
                                    </span>
                                    days
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-auto">
                        <p class="mb-1">Custom interval <sub><i>366 days max</i></sub></p>
                        <div class="row mb-2">
                            <label for="startDate" class="col-sm-2 col-form-label col-form-label-sm">Start</label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control form-control-sm" id="startDate" placeholder="col-form-label-sm" value="<?= !$startDate ?: $startDate->format("Y-m-d") ?>">
                            </div>
                        </div>
                        <div class="row">
                            <label for="endDate" class="col-sm-2 col-form-label col-form-label-sm">End</label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control form-control-sm" id="endDate" placeholder="col-form-label" value="<?= !$endDate ?: $endDate->format("Y-m-d") ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-auto">
                                <p class="mb-1">Week</p>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "last-7-days" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "last-7-days" ? "" : "&dateFilter=last-7-days" ?>" data-type="last-7-days">Last 7 days</a>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "this-week" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "this-week" ? "" : "&dateFilter=this-week" ?>" data-type="this-week">This week</a>
                            </div>
                            <div class="col-auto">
                                <p class="mb-1">Month</p>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "last-30-days" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "last-30-days" ? "" : "&dateFilter=last-30-days" ?>" data-type="last-30-days">Last 30 days</a>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "this-month" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "this-month" ? "" : "&dateFilter=this-month" ?>" data-type="this-month">This month (<?= (new DateTime())->format("M") ?>)</a>
                            </div>
                            <div class="col-auto">
                                <p class="mb-1">Year</p>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "last-12-months" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "last-12-months" ? "" : "&dateFilter=last-12-months" ?>" data-type="last-12-months">Last 12 months</a>
                                <a role="button" class="btn btn-sm btn-outline-secondary <?= $dateFilter === "this-year" ? "active" : "" ?>" href="<?= $url["base"] . $url["directory"] ?><?= $dateFilter === "this-year" ? "" : "&dateFilter=this-year" ?>" data-type="this-year">This year (<?= (new DateTime())->format("Y") ?>)</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART LIST -->
        <div class="flex-1 overflow-auto">
            <div class="row mx-0 p-4 gy-3">
                <div class="col-12 col-xl-8 col-xxl-5">
                    <div class="d-flex gap-3 mb-3">
                        <h4 class="mb-0">Total per origin <i><?= strtolower($dateFilterTranslation) ?></i></h4>
                        <div>
                            <span class="badge rounded-pill text-bg-secondary" title="Total logs in the selected directory for the selected time interval">
                                <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval) ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-2 bg-body-secondary rounded"> 
                        <canvas id="logsPerOriginPerDate"></canvas>
                    </div>
                </div>
                <div class="col-12 col-xl-4 col-xxl-2">
                    <div class="d-flex gap-3 mb-3">
                        <h4 class="mb-0">Total per origin</h4>
                        <div>
                            <span class="badge rounded-pill text-bg-secondary" title="Total logs in the selected directory for the selected time interval">
                                <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval) ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-2 bg-body-secondary rounded"> 
                        <canvas id="totalLogPerOrigin" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-12 col-xl-8 col-xxl-5">
                    <div class="d-flex gap-3 mb-2">
                        <h4 class="mb-0">Average logs per hour</h4>
                    </div>
                    <div class="hstack gap-2 mb-2">
                        <p class="mb-0">
                            On
                            <span class="badge rounded-pill text-bg-secondary">
                                <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval) ?>
                            </span>
                            logs over
                            <span class="badge rounded-pill text-bg-secondary">
                                <?= number_format($totalDaysInSelectedTimeInterval) ?>
                            </span>
                            days
                        </p>
                        <div class="vr"></div>
                        <p class="mb-0">
                            Average per day
                            <span class="badge rounded-pill text-bg-secondary" title="Average logs per day in the selected directory for the selected time interval">
                                <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval / $totalDaysInSelectedTimeInterval, 1) ?>
                            </span>
                        </p>
                    </div>
                    <div class="p-2 bg-body-secondary rounded"> 
                        <canvas id="logsPerHourInADay"></canvas>
                    </div>
                </div>
                <div class="col-12 col-xl-4 col-xxl-4">
                    <h4>Top log volume per instances</h4>
                    <p><i>Log volume is evenly spread through all instances.</i></p>
                    <div class="p-2 bg-body-secondary rounded">
                        <div class="overflow-auto" style="max-height: 400px;">
                            <table class="table">
                                <thead class="sticky-top bg-body-secondary">
                                    <tr>
                                        <th>Instance</th>
                                        <th>Log volume</th>
                                        <th>
                                            Total 
                                            <sup>
                                                <span class="badge rounded-pill text-bg-secondary" title="Total logs in the selected directory for the selected time interval">
                                                    <?= number_format($totalLogsInSelectedDirectoryInSelectedTimeInterval) ?>
                                                </span>
                                            </sup>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chartList["logVolumePerInstance"] as $logVolume) { ?>
                                        <tr>
                                            <td><?= $logVolume["idInstance"] ?></td>
                                            <td><?= number_format($logVolume["logVolume"]) ?> %</td>
                                            <td><?= number_format($logVolume["logTotal"]) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDE BAR TABS: BOTTOM OF THE PAGE -->
        <div class="js-side-bar-tab-wrapper side-bar-tab-wrapper bg-body-tertiary" data-id="bottom">
            <!-- TAB: TERMINAL -->
            <div class="js-side-bar-tab side-bar-tab d-none" data-id="terminal">
                
            </div>
        </div>

    </div>

</main>

<!-- GLOBAL MEMBERS -->
<script>
    const g_chartList = <?= json_encode($chartList) ?>;
    const g_originList = <?= json_encode($originList) ?>;
</script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<?php App::getTemplate("common/document-bottom", $tplVarList) ?>
