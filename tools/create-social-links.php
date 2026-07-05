<?php
declare(strict_types=1);

if (!isset($pdo) || !($pdo instanceof PDO)) {
    require dirname(__DIR__) . '/../private/dbcon.php';
}

if (empty($DB_OK) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Database connection unavailable.\n");
    exit(1);
}

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS socials (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(64) NOT NULL,
            url VARCHAR(500) NOT NULL DEFAULT '',
            icon VARCHAR(32) NOT NULL,
            sort INT NOT NULL DEFAULT 99,
            showoncms ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
            showonweb ENUM('Yes','No') NOT NULL DEFAULT 'No',
            created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modified TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            archived TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY socials_display (showonweb, archived, sort)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->beginTransaction();

    $preferenceCheck = $pdo->prepare('SELECT id FROM cms_preferences WHERE name = ? LIMIT 1');
    $preferenceCheck->execute(['prefFontAwesomeVersion']);
    if (!$preferenceCheck->fetchColumn()) {
        $preferenceInsert = $pdo->prepare(
            "INSERT INTO cms_preferences
             (name,label,value,notes,prefCat,field,class,userlevel,sort,comment,placeholder,required,max,min,step,tooltip,showoncms,showonweb,allowedit,archived)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $preferenceInsert->execute([
            'prefFontAwesomeVersion', 'Font Awesome Version', '6.5.2', '', 1, 1, 'small', 1, 95,
            'Version used by the public Font Awesome CDN stylesheet', '6.5.2', 'Yes', 20, 1, 1,
            'Use a published Font Awesome 6 version number', 'Yes', 'Yes', 'Yes', 0,
        ]);
    }

    $tableCheck = $pdo->prepare('SELECT id FROM cms_table WHERE name = ? AND archived = 0 LIMIT 1');
    $tableCheck->execute(['socials']);
    $cmsTableId = (int) ($tableCheck->fetchColumn() ?: 0);
    if ($cmsTableId === 0) {
        $tableInsert = $pdo->prepare('INSERT INTO cms_table (title,name,showonweb,archived) VALUES (?,?,?,0)');
        $tableInsert->execute(['Social Links', 'socials', 'Yes']);
        $cmsTableId = (int) $pdo->lastInsertId();
    }

    $formCheck = $pdo->prepare('SELECT id FROM cms_form WHERE name = ? AND archived = 0 LIMIT 1');
    $formCheck->execute(['socials']);
    $formId = (int) ($formCheck->fetchColumn() ?: 0);
    if ($formId === 0) {
        $formInsert = $pdo->prepare(
            "INSERT INTO cms_form
             (title,name,`table`,col1,col1name,col1type,col2,col2name,col2type,col3,col3name,col3type,
              sort1,sort1order,viewnotes,afterAdd,afterEdit,issortable,sortcol,where1,showcopy,showdelete,
              showsearch,showarchived,showonweb,archived)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)"
        );
        $formInsert->execute([
            'Social Links', 'socials', $cmsTableId,
            'name', 'Platform', 'Search', 'url', 'URL', 'Search', 'showonweb', 'Visible', 'Select',
            'sort', 'ASC', '', 'recordViewv5.php?frm=[frm]', 'recordViewv5.php?frm=[frm]',
            'Yes', 'sort', '', 'No', 'Yes', 'Yes', 'Yes', 'Yes',
        ]);
        $formId = (int) $pdo->lastInsertId();
    }

    $addField = static function (
        PDO $pdo,
        int $formId,
        int $tableId,
        string $title,
        int $sort,
        int $fieldType,
        string $label,
        string $name,
        string $placeholder,
        string $required,
        string $comment = ''
    ): int {
        $check = $pdo->prepare('SELECT id FROM cms_form_field WHERE form = ? AND name = ? AND archived = 0 LIMIT 1');
        $check->execute([$formId, $name]);
        $existing = (int) ($check->fetchColumn() ?: 0);
        if ($existing > 0) {
            return $existing;
        }

        $insert = $pdo->prepare(
            "INSERT INTO cms_form_field
             (title,form,`table`,tab,sort,field,label,name,class,placeholder,required,selected,comment,
              sourcesqlWHERE,override_filename,issortable,showadd,showedit,allowedit,showonweb,archived)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)"
        );
        $insert->execute([
            $title, $formId, $tableId, 1, $sort, $fieldType, $label, $name, 'medium', $placeholder,
            $required, 'No', $comment, '', 'No', $name === 'sort' ? 'Yes' : 'No',
            'Yes', 'Yes', 'Yes', 'Yes',
        ]);
        return (int) $pdo->lastInsertId();
    };

    $addField($pdo, $formId, $cmsTableId, 'Platform Name', 10, 1, 'Platform name', 'name', 'LinkedIn', 'Yes');
    $addField($pdo, $formId, $cmsTableId, 'Profile URL', 20, 14, 'Full profile URL', 'url', 'https://', 'Yes');
    $iconFieldId = $addField(
        $pdo, $formId, $cmsTableId, 'Brand Icon', 30, 16, 'Font Awesome brand icon', 'icon', '', 'Yes',
        'Choose an approved brand icon; arbitrary icon classes are not accepted.'
    );
    $addField($pdo, $formId, $cmsTableId, 'Display Order', 40, 9, 'Display order', 'sort', '10', 'Yes');
    $addField($pdo, $formId, $cmsTableId, 'Show On Website', 50, 17, 'Show on website', 'showonweb', '', 'Yes');

    $icons = [
        'facebook-f' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin-in' => 'LinkedIn',
        'x-twitter' => 'X / Twitter',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'pinterest-p' => 'Pinterest',
        'threads' => 'Threads',
        'whatsapp' => 'WhatsApp',
    ];
    $optionCheck = $pdo->prepare('SELECT id FROM cms_form_field_options WHERE form_field = ? AND value = ? AND archived = 0 LIMIT 1');
    $optionInsert = $pdo->prepare(
        "INSERT INTO cms_form_field_options (form_field,value,checked,display,sort,showonweb,archived)
         VALUES (?,?,?,?,?,'Yes',0)"
    );
    $optionSort = 10;
    foreach ($icons as $value => $display) {
        $optionCheck->execute([$iconFieldId, $value]);
        if (!$optionCheck->fetchColumn()) {
            $optionInsert->execute([$iconFieldId, $value, 'No', $display, $optionSort]);
        }
        $optionSort += 10;
    }

    $socialCheck = $pdo->prepare('SELECT id FROM socials WHERE name = ? AND archived = 0 LIMIT 1');
    $socialInsert = $pdo->prepare(
        "INSERT INTO socials (name,url,icon,sort,showoncms,showonweb,archived)
         VALUES (?,?,?,?, 'Yes','No',0)"
    );
    foreach ([
        ['Facebook', '', 'facebook-f', 10],
        ['Instagram', '', 'instagram', 20],
        ['LinkedIn', '', 'linkedin-in', 30],
    ] as $social) {
        $socialCheck->execute([$social[0]]);
        if (!$socialCheck->fetchColumn()) {
            $socialInsert->execute($social);
        }
    }

    $pdo->commit();
    echo "Social links table and CMS form {$formId} are ready.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}
