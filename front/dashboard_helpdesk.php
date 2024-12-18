<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use Glpi\Dashboard\Dashboard;

/** @var array $CFG_GLPI */
global $CFG_GLPI;

Session::checkCentralAccess();
$default = Glpi\Dashboard\Grid::getDefaultDashboardForMenu('helpdesk');

// Redirect to "/front/ticket.php" if no dashboard found
if ($default == "") {
    Html::redirect($CFG_GLPI["root_doc"] . "/front/ticket.php");
}

$dashboard = new Dashboard($default);
if (!$dashboard->canViewCurrent()) {
    throw new AccessDeniedHttpException();
}

Html::header(__('Helpdesk Dashboard'), $_SERVER['PHP_SELF'], "helpdesk", "dashboard");

$grid = new Glpi\Dashboard\Grid($default);
$grid->showDefault();

Html::footer();
