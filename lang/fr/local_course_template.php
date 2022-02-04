<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the plugin.
 *
 * @package    local_course_template
 * @copyright  2016 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_backups'] = 'Sauvegarde du modèle de cours (template)';
$string['cachedef_templates'] = 'Ids du modèle de cours';
$string['cleanuptask'] = 'Nettoyage de la sauvegarde du modèle de cours';
$string['defaulttemplate'] = 'Nom abrégé du cours modèle par défaut';
$string['defaulttemplate_desc'] = "Nom abrégé du cours modèle par defaut. Les cours qui ne correspondent pas à un modèle de cours utiliseront celci-ci, s'il existe.";
$string['enablecaching'] = 'Activer le cache';
$string['enablecaching_desc'] = "Quand le cache est actvé, le plugin créera une sauvegarde du cours modèle et utilisera cette sauvegarde pour les nouveaux cours jusqu'à ce que le cache soit vidé ou que la sauvegarde soit supprimée.";
$string['event:template_copied:description'] = "Le cours modèle avec l'id {$a->templateid} a été copié dans le cours avec l'id {$a->courseid}";
$string['event:template_copied:name'] = 'Cours modèle copié';
$string['extracttermcode'] = 'Term code';
$string['extracttermcode_desc'] = "Utilisé pour générer [TERMCODE]. Derivé du N° d'identification du cours.";
$string['pluginname'] = "Utilisation d'un modèle à la créaction d'un cours";
$string['privacy:metadata'] = "L'utilisation du plugin Utilisation d'un modèle à la créaction d'un cours ne stocke aucune donnée personnelle.";
$string['templatenameformat'] = 'Préfix des noms abrégés des modèles de cours.';
$string['templatenameformat_desc'] = 'Préfix attendu des noms abrégés des modèles de cours';
$string['defaulttermcode'] = ' [TERMCODE] par défaut';
$string['defaulttermcode_desc'] = '[TERMCODE] par defaut si la valeur est vide ( doit être >= 2 caractères)';

