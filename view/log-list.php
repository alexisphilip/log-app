<?php App::getTemplate("common/document-top", $tplVarList) ?>

<!-- APP HEADER -->
<header class="row mx-0 g-3 p-3 justify-content-xl-between justify-content-center bg-body-tertiary border-bottom">
    <div class="col-auto hstack gap-3">
        <a href="index.php"role="button" aria-disabled="true"><img src="<?= App::getImg("main-logo.png") ?>" style="width: 2rem;"></a>
        <h4 class="mb-0"><b>Logs</b></h4>
        <div class="h-100 border-end"></div>
        <?php
            $currentUrl = "index.php?p=chart-list" . $url["directory"];
        ?>
        <a class="btn btn-sm btn-outline-secondary" role="button" href="<?= $currentUrl ?>" data-bs-placement="bottom" title="Switch to charts view">
            CHARTS
            <i class="bi bi-bar-chart-line-fill"></i>
        </a>
        <h4 class="mb-0 hstack gap-1">
            <?php
                $currentUrl = $url["base"] . $url["date"];
                componentBreadCrumb($fileExplorer, $selectedDirectoryPath, $currentUrl);
            ?>
        </h4>
        <span class="badge rounded-pill bg-secondary" data-bs-placement="bottom" title="Total logs in the selected directory for the current date">
            <?= number_format(count($logList)) ?>
        </span>
    </div>
    <div class="col-auto">
        <div class="row g-3 justify-content-center">
            <div class="col-auto">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search..." id="search" autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex shadow rounded-2">
                    <?php
                        $currentUrl = $url["base"] . $url["directory"] . $url["search"] . $url["originList"];
                        $date = new DateTime($currentDate);
                        $date->modify("-1 days");
                        $previousDay = $date->format("Y-m-d");
                        $date->modify("+2 days");
                        $nextDay = $date->format("Y-m-d");
                    ?>
                    <a href="<?= $currentUrl ?>&date=<?= $previousDay ?>" data-url-params-to-update="search,originList" role="button"class="btn btn-secondary rounded-end fw-bold" style="border-radius: 8px 0 0 8px !important;">
                        <i class="bi bi-caret-left-fill"></i>
                    </a>
                    <input class="js-date-input form-control text-center rounded-0" type="date" style="width: 8.5rem;" value="<?= $currentDate ?>">
                    <a href="<?= $currentUrl ?>&date=<?= $nextDay ?>" data-url-params-to-update="search,originList" role="button"class="btn btn-secondary rounded-start fw-bold" style="border-radius: 0 8px 8px 0 !important;">
                        <i class="bi bi-caret-right-fill"></i>
                    </a>
                </div>
                <?php if ($isCurrentDateInvalid): ?>
                    <div class="c-red mt-2">Invalid URL <code>date</code> value. Date replaced with today's date.</div>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <div class="btn-group js-origin-filter shadow" role="group" aria-label="">
                    <?php foreach ($originList as $originName): ?>
                        <?php
                            $originConfig = ORIGIN_COLOR_LIST[$originName] ?: ORIGIN_COLOR_LIST["default"];
                            $btnClass = $originConfig["bootstrapColorType"];
                        ?>
                        <input type="checkbox" class="btn-check" id="btncheck<?= $originName ?>" autocomplete="off" value="<?= $originName ?>" checked>
                        <label class="btn btn-outline-<?= $btnClass ?> fw-medium" for="btncheck<?= $originName ?>">
                            <div class="position-relative">
                                <?= mb_strtoupper($originName) ?>
                                <span class="position-absolute start-50 translate-middle badge rounded-pill text-bg-<?= $btnClass ?>" style="top: 120%;">
                                    <?= count(
                                        array_filter($logList, function ($log) use ($originName) {
                                            if ($log["origin"] === $originName) return true;
                                        })
                                    ) ?>
                                    <span class="visually-hidden">logs</span>
                                </span>
                            </div>
                        </label>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="col-auto">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bi bi-sliders"></i>
                    </button>
                    <ul class="dropdown-menu p-2">
                        <li>Date time</li>
                        <li>
                            <div class="form-check form-switch">
                                <input class="form-check-input js-switch-filter" type="checkbox" role="switch" id="dateTimeUtc" checked>
                                <label class="form-check-label user-select-none text-nowrap" for="dateTimeUtc">UTC 0 🌍</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check form-switch">
                                <input class="form-check-input js-switch-filter" type="checkbox" role="switch" id="dateTimeInstance" checked>
                                <label class="form-check-label user-select-none text-nowrap" for="dateTimeInstance">Instance 📍</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check form-switch">
                                <input class="form-check-input js-switch-filter" type="checkbox" role="switch" id="dateTimeLocal" checked>
                                <label class="form-check-label user-select-none text-nowrap" for="dateTimeLocal">Local 🏡</label>
                            </div>
                        </li>
                        <li>
                            <div class="form-check form-switch">
                                <input class="form-check-input js-switch-filter" type="checkbox" role="switch" id="dateTimeEmoji" checked>
                                <label class="form-check-label user-select-none text-nowrap" for="dateTimeEmoji">Emojis</label>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>Line clamp</li>
                        <li>
                            <label for="lineClamp" class="form-label"><small>Cell lines displayed when a line collapsed</small></label>
                            <input type="number" min="1" max="1000" class="form-control" id="lineClamp">
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>Font size</li>
                        <li>
                            <label for="fontSize" class="form-label"><small>Table's font size</small></label>
                            <input type="number" min="6" max="24" class="form-control" id="fontSize">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
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
                        $currentUrl = $url["base"] . $url["date"];
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
    <div class="h-100 d-flex flex-column flex-1 overflow-hidden">

        <!-- LOG TABLE -->
        <div class="flex-1 overflow-auto">
            <table class="log-list table table-bordered mb-0">
                <thead class="position-sticky top-0 bg-body-secondary shadow-sm">
                    <tr class="text-nowrap">
                        <th>Id</th>
                        <th>Origin</th>
                        <th>Type</th>
                        <th data-col-name="dateTime">Date & time</th>
                        <th data-col-name="message">Message</th>
                        <th>Data</th>
                        <th>File</th>
                        <th>Line</th>
                        <th>Column</th>
                        <th>Trace</th>
                        <th>Iziflo mode</th>
                        <th>ID user</th>
                        <th>ID instance</th>
                        <th>ID company</th>
                        <th>ID site</th>
                    </tr>
                </thead>
                <tbody class="font-monospace js-log-list">
                    <?php if (count($logList)): ?>
                        <?php foreach($logList as $log): ?>
                            <?php
                                $originConfig = in_array($log["origin"], array_keys(ORIGIN_COLOR_LIST)) ? $log["origin"] : "default";
                            ?>
                            <tr class="js-log" data-origin="<?= $log["origin"] ?>" style="background-color: var(--bg-color-origin-<?= $originConfig ?>);">
                                <td>
                                    <span><?= $log["id"] ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= mb_strtoupper($log["origin"]) ?></span>
                                </td>
                                <td>
                                    <span><?= ucfirst($log["type"]) ?></span>
                                </td>
                                <td data-col-name="dateTime" class="text-nowrap">
                                    <table class="no-style">
                                        <tr data-type="dateTimeUtc" data-bs-placement="right" data-bs-html="true" title="Timezone: <?= $log["dateTimeUtcTimeZone"] ?>">
                                            <td>UTC <?= $log["dateTimeUtcOffset"] ?>&nbsp;&nbsp;</td>
                                            <td><?= $log["dateTimeUtc"] ?></td>
                                            <td data-type="dateTimeEmoji" class="text-center">&nbsp;🌍</td>
                                        </tr>
                                        <?php if ($log["dateTimeInstance"]) { ?>
                                            <tr data-type="dateTimeInstance" data-bs-placement="right" data-bs-html="true" title="Timezone: <?= $log["dateTimeInstanceTimeZone"] ?><br><b>(instance time)</b>">
                                                <td>UTC <?= $log["dateTimeInstanceOffset"] ?>&nbsp;&nbsp;</td>
                                                <td><?= $log["dateTimeInstance"] ?></td>
                                                <td data-type="dateTimeEmoji" class="text-center">&nbsp;📍</td>
                                            </tr>
                                        <?php } ?>
                                        <tr data-type="dateTimeLocal" data-bs-placement="right" data-bs-html="true" title="Timezone: <?= $log["dateTimeLocalTimeZone"] ?><br><b>(your local time)</b>">
                                            <td>UTC <?= $log["dateTimeLocalOffset"] ?>&nbsp;&nbsp;</td>
                                            <td><?= $log["dateTimeLocal"] ?></td>
                                            <td data-type="dateTimeEmoji" class="text-center">&nbsp;🏡</td>
                                        </tr>
                                    </table>
                                </td>
                                <td data-col-name="message">
                                    <span class="fw-bold"><?= $log["message"] ?></span>
                                </td>
                                <td>
                                    <span>
                                        <?php if (is_array($log["data"])) { ?>
                                            <pre class="mb-0"><span><?php print_r($log["data"]) ?></span></pre>
                                        <?php } else { ?>
                                            <span><?= $log["data"] ?></span>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td>
                                    <span><?= $log["file"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["line"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["column"] ?></span>
                                </td>
                                <td>
                                    <span>
                                        <?php if (is_array($log["trace"])) { ?>
                                            <pre class="mb-0"><span><?php print_r($log["trace"]) ?></span></pre>
                                        <?php } else { ?>
                                            <span><?= $log["trace"] ?></span>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td>
                                    <span><?= $log["izifloMode"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["idUser"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["idInstance"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["idCompany"] ?></span>
                                </td>
                                <td>
                                    <span><?= $log["idSite"] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="fw-bold text-center" colspan="14">No logs 🤯</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- SIDE BAR TABS: BOTTOM OF THE PAGE -->
        <div class="js-side-bar-tab-wrapper side-bar-tab-wrapper bg-body-tertiary" data-id="bottom">
            <!-- TAB: TERMINAL -->
            <div class="js-side-bar-tab side-bar-tab d-none" data-id="terminal"></div>
        </div>

    </div>

</main>

<?php App::getTemplate("common/document-bottom", $tplVarList) ?>
