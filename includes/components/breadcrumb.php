<?php

/**
 * OUTPUTS HTML COMPONENT IN THE BUFFER: selected directory breadcrumb.
 * @param FileExplorer $fileExplorer {@link FileExplorer} object, used to recursively get the content of each breadcrumb items.
 * @param string $selectedPath Current directory path build the breadcrumb items. It is set in the URL under `directory` parameter.
 * @param string $url Current URL to put in the `<a>` tags.
 */
function componentBreadCrumb($fileExplorer, $selectedDirectory, $url) {

    $selectedDirectoryPathWithRoot = $selectedDirectory ? "root/$selectedDirectory" : "root";

    // I: "root/folder1/folder2"
    // O: ["root", "folder1", "folder2"]
    $breadCrumbDirectory = explode("/", $selectedDirectoryPathWithRoot);
    
    $dirList = [];

    foreach ($breadCrumbDirectory as $breadCrumbDirElement) {
        if ($breadCrumbDirElement === "root") {
            $folderName = "root";
            $urlParam = "";
            $title = "Root directory";
            $content = $fileExplorer->getElements();
        } else {
            $dirList[] = $breadCrumbDirElement;
            $directoryElement = $fileExplorer->getElements(implode("/", $dirList));

            $folderName = mb_strtoupper($directoryElement["name"]);
            $urlParam = "&directory=" . $directoryElement["path"];
            $title = "";

            // If the first element of its content is a folder, get its content. Other wise, do not.
            // This prevents the user from selecting files in the breadcrumb area.
            if (reset($directoryElement["content"])["type"] === "folder") {
                $content = $directoryElement["content"];
            } else {
                $content = null;
            }
        }
        ?>

        <!-- FOLDER NAME -->
        <a class="link-underline link-underline-opacity-0 link-underline-opacity-75-hover text-body-secondary"
        href="<?= $url ?><?= $urlParam ?>" data-bs-placement="bottom" title="<?= $title ?>">
            <?php if ($folderName === "root") { ?>
                <i class="bi bi-house-fill"></i>
            <?php } else { ?>
                <?= $folderName ?>
            <?php } ?>
        </a>

        <?php if ($content) { ?>
            <!-- ARROW ELEMENT -->
            <button class="text-body-secondary bg-transparent border-0 p-0"
            data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-caret-right-fill"></i>
            </button>
            <!-- DROPDOWN -->
            <ul class="dropdown-menu">
                <?php foreach ($content as $dirChildElement) { ?>
                    <li>
                        <a class="dropdown-item" href="<?= $url ?>&directory=<?= $dirChildElement["path"] ?>">
                            <i class="bi bi-folder-fill"></i>
                            <?= mb_strtoupper($dirChildElement["name"]) ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>

    <?php }
}