<?php

/**
 * OUTPUTS HTML COMPONENT IN THE BUFFER: log file explorer
 * @param array $directory Recursive log file directory structure, coming from {@link FileExplorer->getElements()}.
 * @param string $selectedPath Current directory path to highlight in the file explorer. It is set in the URL under `directory` parameter.
 * @param string $url Current URL to put in the `<a>` tags.
 */
function componentFileExplorer($directory, $selectedPath, $url) {

    foreach ($directory as $element) {

        if ($element["type"] === "folder") { ?>

            <?php
                $id = uniqid();
                $activeClass = substr($selectedPath, 0, strlen($element["path"])) === $element["path"] ? "active" : "";
            ?>

            <!-- DIRECTORY -->
            <div class="file-explorer--folder">
                <!-- Name -->
                <div class="file-explorer--element file-explorer--folder <?= $activeClass ?>">
                    <span>
                        <i class="bi bi-folder-fill"></i>
                        <span><?= $element["name"] ?></span>
                        <!-- If its content are files -->
                        <?php if (reset($element["content"])["type"] === "file") { ?>
                            <sup data-bs-placement="right" title="Total files"><small style="font-size: 0.7rem;">(<?= number_format(count($element["content"])) ?>)</small></sup>
                        <?php } ?>
                    </span>
                    <a class="icon-link link-underline-opacity-0 link-underline-opacity-75-hover text-body-emphasis" href="<?= $url ?>&directory=<?= $element["path"] ?>">
                        <small>open</small>
                        <i class="bi bi-eye-fill"></i>
                    </a>
                </div>
                <!-- Content -->
                <div style="margin-left: 1rem;">
                    <?= componentFileExplorer($element["content"], $selectedPath, $url) ?>
                </div>
            </div>

        <?php } else { ?>

            <!-- FILE -->
            <!-- <div class="file-explorer--element file-explorer--file <?= $activeClass ?>">
                <span>
                    <i class="bi bi-filetype-json"></i>
                    <?= $element["name"] ?>
                </span>
            </div> -->

        <?php }
    }
};
