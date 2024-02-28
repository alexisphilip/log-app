<?php

/**
 * DESCRIPTION:
 * Administration panel for the log app.
 * Current features:
 * - Development log generation.
 * 
 * INPUT:
 * No input required.
 */

App::setTitle("LOGS - Admin");


// #######
// PROCESS
// #######


// ######
// OUTPUT
// ######

App::setJs("view/admin.js");

App::getTemplate("admin", $tplVarList);
