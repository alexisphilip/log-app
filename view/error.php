<?php App::getTemplate("common/document-top", $tplVarList) ?>

<main class="d-flex flex-column container p-5">

    <h1>An error happened...</h1>

    <br>

    <div class="bg-body-tertiary p-4">
        Message: <code><?= $errorObject["message"] ?></code>
        <br>
        File: <code><?= $errorObject["file"] ?></code>
        <br>
        Line: <code><?= $errorObject["line"] ?></code>
        <br>
        Trace:
        <br>
        <code><?= $errorObject["file"] ?></code>
    </div>

</main>

<?php App::getTemplate("common/document-bottom", $tplVarList) ?>
