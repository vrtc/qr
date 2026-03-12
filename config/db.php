<?php

$dbPath = getenv('DB_PATH') ?: __DIR__ . '/../db.sqlite';

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . $dbPath,
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
