/**
 * @file Manages all events linked to the admin page.
 */


/**
 * Recursively creates an HTML directory string.
 * @param {object} elementList List of directory elements (folder or files).
 * @returns {string} The HTML string of the directory HTML element.
 */
const printDirectoryTreeRecursive = (elementList) => {

    if (elementList.constructor.name !== "Object") return "";

    let html = "";

    for (const element of Object.values(elementList)) {

        if (element.type === "folder") {

            html += `
                <!-- DIRECTORY -->
                <div class="file-explorer--folder">
                    <!-- Name -->
                    <div class="file-explorer--element file-explorer--folder">
                        <span>
                            <i class="bi bi-folder-fill"></i>
                            <span>${element.name}</span>
                            <!-- If its content are files -->
                            `+(
                                Object.values(element.content).length && Object.values(element.content)[0].type === "file" ?
                                    `<sup data-bs-placement="right" title="Total files"><small style="font-size: 0.7rem;">${Object.entries(element.content).length}</small></sup>`
                                : ""
                            )+`
                        </span>
                    </div>
                    <!-- Content -->
                    <div style="margin-left: 1rem;">
                        ${printDirectoryTreeRecursive(element.content) || ""}
                    </div>
                </div>
            `;

        } else {

            // File: do not print anything

        }
    }

    return html;

};

/**
 * Updates the DEVELOPMENT LOG FOLDER DIRECTORY and the its STATS.
 * @param {boolean} hasLoader If the loader needs to be shown.
 */
const updateDevelopmentLogsUi = async (hasLoader = true) => {

    const directoryTreeEl = document.querySelector(".js-directory-tree");
    hasLoader && showLoader(directoryTreeEl);

    const logStatsEl = document.querySelector(".js-log-stats");
    hasLoader && showLoader(logStatsEl);

    const response = await fetchRequest({
        url: "index.php?a=development-log-directory-get",
    });

    if (Object.keys(response.data.directoryElementList).length) {
        directoryTreeEl.innerHTML = printDirectoryTreeRecursive(response.data.directoryElementList);
    } else {
        directoryTreeEl.innerHTML = "<i>No development logs</i>";
    }

    hasLoader && hideLoader(logStatsEl);
    logStatsEl.querySelector(".js-log-stats-values").classList.remove("d-none");
    // logStatsEl.querySelector(".js-total-logs").innerText = response.data.totalLogs.toLocaleString("en-US");
    logStatsEl.querySelector(".js-total-files").innerText = response.data.totalFiles.toLocaleString("en-US");

    initTooltips();
};


// ######################################
// PRODUCTION/DEVELOPMENT LOG MODE SWITCH
// ######################################

const switchLogEnvironmentBtnEl = document.querySelector(".js-log-environment-switch");

switchLogEnvironmentBtnEl.addEventListener("click", async () => {

    showLoader();

    await fetchRequest({
        url: "index.php?a=log-environment-update",
        data: {
            logEnvironment: g_logEnvironment === "DEVELOPMENT" ? "" : "DEVELOPMENT",
        },
    });

    document.location.reload();
});


// ###########################
// DEVELOPMENT LOGS GENERATION
// ###########################

const logDevelopmentGenerateBtnEl = document.querySelector(".js-development-log-generate");

let logsGenerating = false;

logDevelopmentGenerateBtnEl.addEventListener("click", (e) => {

    showLoader();

    (async () => {

        logsGenerating = true;

        const response = await fetchRequest({
            url: "index.php?a=development-log-generate",
            data: {
                daysToCreatePerSubDirectories: document.querySelector("#daysToCreatePerSubDirectories").value,
                minLogsToCreatePerFile: document.querySelector("#minLogsToCreatePerFile").value,
                maxLogsToCreatePerFile: document.querySelector("#maxLogsToCreatePerFile").value,
            }
        });

        hideLoader();

        logsGenerating = false;

        // Shows specific stats.
        document.querySelector(".js-generated-in").classList.remove("d-none");
        document.querySelector(".js-stats-generation-time").innerText = response.data.perfData["generate-logs"]["elapsed"];

        // Updates the UI by getting the development directory structure and the total of log files generated.
        await updateDevelopmentLogsUi();

    })();

    // TODO: make asynchronous PHP processes to support managing multiple fetch requests at the same time from the same client
    // (async () => {

    //     while (true) {
    //         await updateDevelopmentLogsUi(false);
    //         if (!logsGenerating) break;
    //     }

    // })();

});


// #######################
// DEVELOPMENT LOGS DELETE
// #######################

const developmentLogDeleteBtnEl = document.querySelector(".js-development-log-delete");

developmentLogDeleteBtnEl.addEventListener("click", async () => {

    showLoader();

    await fetchRequest({
        url: "index.php?a=development-log-delete"
    });

    document.location.reload();
});


// #################
// ON DOCUMENT READY
// #################

(async () => {

    // Only gets the development logs UI data if we're in DEVELOPMENT logs environment.
    if (g_logEnvironment === "DEVELOPMENT") {
        await updateDevelopmentLogsUi();
    }

})();