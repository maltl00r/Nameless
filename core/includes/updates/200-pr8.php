<?php
// 2.0.0 pr-8 to 2.0.0 pr-9 updater
try {
    $db_engine = Config::get('mysql/engine');
} catch (Exception $e) {
    // unable to retrieve from config
    echo $e->getMessage() . '<br />';
}
if (!$db_engine || ($db_engine != 'MyISAM' && $db_engine != 'InnoDB'))
    $db_engine = 'InnoDB';

try {
    $db_charset = Config::get('mysql/charset');
} catch (Exception $e) {
    // unable to retrieve from config
    echo $e->getMessage() . '<br />';
}
if (!$db_charset || ($db_charset != 'utf8mb4' && $db_charset != 'latin1'))
    $db_charset = 'latin1';

// Delete "group_id" from nl2_users table to prevent issues of it not being set
try {
    DB::getInstance()->createQuery('ALTER TABLE `nl2_users` DROP COLUMN `group_id`;');
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

// Forum labels update
try {
    DB::getInstance()->createQuery('ALTER TABLE `nl2_forums` ADD `default_labels` VARCHAR(128) NULL DEFAULT NULL');
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

try {
    DB::getInstance()->createQuery('ALTER TABLE `nl2_topics` ADD `labels` VARCHAR(128) NULL DEFAULT NULL');
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

try {
    $topics = DB::getInstance()->query('SELECT id, label FROM nl2_topics WHERE label IS NOT NULL')->results();
    if (count($topics)) {
        foreach ($topics as $topic) {
            DB::getInstance()->createQuery('UPDATE nl2_topics SET labels = ? WHERE id = ?', array($topic->label, $topic->id));
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

// Update version number
$version_number_id = $queries->getWhere('settings', array('name', '=', 'nameless_version'));

if (count($version_number_id)) {
    $version_number_id = $version_number_id[0]->id;
    $queries->update('settings', $version_number_id, array(
        'value' => '2.0.0-pr9'
    ));
} else {
    $version_number_id = $queries->getWhere('settings', array('name', '=', 'version'));
    $version_number_id = $version_number_id[0]->id;

    $queries->update('settings', $version_number_id, array(
        'value' => '2.0.0-pr9'
    ));
}

$version_update_id = $queries->getWhere('settings', array('name', '=', 'version_update'));
$version_update_id = $version_update_id[0]->id;

$queries->update('settings', $version_update_id, array(
    'value' => 'false'
));
