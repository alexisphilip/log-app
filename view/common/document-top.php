<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="<?= App::getImg("main-logo.png") ?>" type="image/x-icon">
    <title><?= App::$pageTitle ?></title>

    <!-- ------- -->
    <!-- VENDORS -->
    <!-- ------- -->

    <!--- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Bootstrap icons 1.11.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Less.js -->
    <script>
        var less = {
            logLevel: 0
        }
    </script>
    <link rel="stylesheet/less" type="text/css" href="<?= App::getCss("style.less") ?>">
    <script src="https://cdn.jsdelivr.net/npm/less"></script>

    <!-- Mark.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js" integrity="sha512-5CYOlHXGh6QpOFA/TeTylKLWfB3ftPsde7AnmhuitiTX4K5SqCLBeKro6sPS8ilsz1Q4NRx3v8Ko2IBiszzdww==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Terminal.js -->
    <script src="<?= App::getVendor("terminal.js/src/Terminal.js") ?>"></script>
    <link href="<?= App::getVendor("terminal.js/src/terminal.css") ?>" rel="stylesheet">
    

    <!-- ----------- -->
    <!-- PROPRIETARY -->
    <!-- ----------- -->

    <!-- Custom CSS background-color variables for LIGHT and DARK themes -->
    <style>
        /* LIGHT theme */
        html {
            /* Sets <tr> colors for LIGHT theme from ORIGIN_COLOR_LIST */
            <?php foreach (ORIGIN_COLOR_LIST as $originName => $originColorList) { ?>
                <?php if ($originColorList["colorLight"]) { ?>
                    <?= "--bg-color-origin-$originName: $originColorList[colorLight];" ?>
                <?php } ?>
            <?php } ?>
        }

        /* Dark theme */
        html[data-bs-theme="dark"] {
            /* Sets <tr> colors for DARK theme from ORIGIN_COLOR_LIST */
            <?php foreach (ORIGIN_COLOR_LIST as $originName => $originColorList) { ?>
                <?php if ($originColorList["colorDark"]) { ?>
                    <?= "--bg-color-origin-$originName: $originColorList[colorDark];" ?>
                <?php } ?>
            <?php } ?>
        }
    </style>
</head>
<body>
