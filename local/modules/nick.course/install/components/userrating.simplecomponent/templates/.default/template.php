<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $arResult
 * @var $arparams
 */
?>

<table class='table' cellpadding="3" border="1">
    <thead>
    <tr>
        <?php
        if (!empty($arResult['ITEMS'][0])) {
            foreach (array_keys($arResult['ITEMS'][0]) as $columnName): ?>
                <th><?= str_replace('_', ' ', $columnName) ?></th>
            <?php
            endforeach;
        } ?>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($arResult['ITEMS'] as $item): ?>
        <tr>
            <?php
            foreach ($item as $key => $value): ?>
                <td>
                    <?php
                    if ($key === 'CREATE_DATE') {
                        echo $value->format('d.m.Y H:i:s');
                    } elseif (is_string($value)) {
                        echo htmlspecialchars($value);
                    } else {
                        echo $value;
                    }
                    ?>
                </td>
            <?php
            endforeach; ?>
        </tr>
    <?php
    endforeach; ?>
    </tbody>
</table>
