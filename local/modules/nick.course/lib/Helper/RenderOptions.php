<?php

namespace Nick\Course\Helper;

use Bitrix\Main\Localization\Loc;

/**
 * Расширенный класс для работы с настройками модуля в админке
 */
class RenderOptions
{
    public static function __AdmSettingsSaveOptions($module_id, $arOptions)
    {
        foreach ($arOptions as $arOption) {
            self::__AdmSettingsSaveOption($module_id, $arOption);
        }
    }

    public static function __AdmSettingsSaveOption($module_id, $arOption)
    {
        if (!is_array($arOption) || isset($arOption["note"]))
            return false;

        if ($arOption[3][0] == "statictext" || $arOption[3][0] == "statichtml")
            return false;

        $arControllerOption = \CControllerClient::GetInstalledOptions($module_id);

        if (isset($arControllerOption[$arOption[0]]))
            return false;

        $name = $arOption[0];
        $isChoiceSites = array_key_exists(6, $arOption) && $arOption[6] == "Y" ? true : false;

        if ($isChoiceSites) {
            if (isset($_REQUEST[$name . "_all"]) && $_REQUEST[$name . "_all"] <> '')
                \COption::SetOptionString($module_id, $name, $_REQUEST[$name . "_all"], $arOption[1]);
            else
                \COption::RemoveOption($module_id, $name);
            $queryObject = \Bitrix\Main\SiteTable::getList(array(
                'select' => array('LID', 'NAME'),
                'filter' => array(),
                'order' => array('SORT' => 'ASC'),
            ));
            while ($site = $queryObject->fetch()) {
                if (isset($_REQUEST[$name . "_" . $site["LID"]]) && $_REQUEST[$name . "_" . $site["LID"]] <> '' &&
                    !isset($_REQUEST[$name . "_all"])) {
                    $val = $_REQUEST[$name . "_" . $site["LID"]];
                    if ($arOption[3][0] == "checkbox" && $val != "Y")
                        $val = "N";
                    if ($arOption[3][0] == "multiselectbox")
                        $val = @implode(",", $val);
                    \COption::SetOptionString($module_id, $name, $val, $arOption[1], $site["LID"]);
                } else {
                    \COption::RemoveOption($module_id, $name, $site["LID"]);
                }
            }
        } else {
            if (!isset($_REQUEST[$name])) {
                if ($arOption[3][0] <> 'checkbox' && $arOption[3][0] <> "multiselectbox" && $arOption[3][0] <> "multiuser") {
                    return false;
                }
            }

            $val = $_REQUEST[$name];

            if ($arOption[3][0] == "checkbox" && $val != "Y")
                $val = "N";
            if ($arOption[3][0] == "multiselectbox")
                $val = @implode(",", $val);
            if ($arOption[3][0] == "number")
                $val = (float) $val;

            if ($arOption[3][0] == "multitext") {
                foreach ($val as $valKey => $value)
                    if ($value == '')
                        unset($val[$valKey]);

                $val = @implode(",", $val);
            }
            if ($arOption[3][0] == "multiuser") {
                foreach ($val as $valKey => $value)
                    if ($value == '')
                        unset($val[$valKey]);

                $val = @implode(",", $val);
            }
            \COption::SetOptionString($module_id, $name, $val, $arOption[1]);
        }

        return null;
    }

    public static function __AdmSettingsDrawList($module_id, $arParams)
    {
        foreach ($arParams as $Option) {
            self::__AdmSettingsDrawRow($module_id, $Option);
        }
    }

    public static function __AdmSettingsDrawRow($module_id, $Option)
    {
        $arControllerOption = \CControllerClient::GetInstalledOptions($module_id);
        if ($Option === null) {
            return;
        }

        if (!is_array($Option)):
            ?>
            <tr class="heading">
                <td colspan="2"><?= $Option ?></td>
            </tr>
        <?
        elseif (isset($Option["note"])):
            ?>
            <tr>
                <td colspan="2" align="center">
                    <? echo BeginNote('align="center"'); ?>
                    <?= $Option["note"] ?>
                    <? echo EndNote(); ?>
                </td>
            </tr>
        <?
        elseif (isset($Option["wideHtml"])):
            ?>
            <tr>
                <td colspan="2" align="center">
                    <?= $Option["wideHtml"] ?>
                </td>
            </tr>
        <?
        else:
            $isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
            $listSite = array();
            $listSiteValue = array();
            if ($Option[0] != "") {
                if ($isChoiceSites) {
                    $queryObject = \Bitrix\Main\SiteTable::getList(array(
                        "select" => array("LID", "NAME"),
                        "filter" => array(),
                        "order" => array("SORT" => "ASC"),
                    ));
                    $listSite[""] = GetMessage("MAIN_ADMIN_SITE_DEFAULT_VALUE_SELECT");
                    $listSite["all"] = GetMessage("MAIN_ADMIN_SITE_ALL_SELECT");
                    while ($site = $queryObject->fetch()) {
                        $listSite[$site["LID"]] = $site["NAME"];
                        $val = \COption::GetOptionString($module_id, $Option[0], $Option[2], $site["LID"], true);
                        if ($val)
                            $listSiteValue[$Option[0] . "_" . $site["LID"]] = $val;
                    }
                    $val = "";
                    if (empty($listSiteValue)) {
                        $value = \COption::GetOptionString($module_id, $Option[0], $Option[2]);
                        if ($value)
                            $listSiteValue = array($Option[0] . "_all" => $value);
                        else
                            $listSiteValue[$Option[0]] = "";
                    }
                } else {
                    $val = \COption::GetOptionString($module_id, $Option[0], $Option[2]);
                }
            } else {
                $val = $Option[2];
            }
            if ($isChoiceSites):?>
                <tr>
                    <td colspan="2" style="text-align: center!important;">
                        <label><?= $Option[1] ?></label>
                    </td>
                </tr>
            <?endif; ?>
            <? if ($isChoiceSites):
            foreach ($listSiteValue as $fieldName => $fieldValue):?>
                <tr>
                    <?
                    $siteValue = str_replace($Option[0] . "_", "", $fieldName);
                    self::renderLabel($Option, $listSite, $siteValue);
                    self::renderInput($Option, $arControllerOption, $fieldName, $fieldValue);
                    ?>
                </tr>
            <?endforeach; ?>
        <? else:?>
            <tr data-option-name="<?= $Option[0]; ?>">
                <?
                self::renderLabel($Option, $listSite);
                self::renderInput($Option, $arControllerOption, $Option[0], $val);
                ?>
            </tr>
        <?endif; ?>
            <? if ($isChoiceSites): ?>
            <tr>
                <td width="50%">
                    <a href="javascript:void(0)" onclick="addSiteSelector(this)" class="bx-action-href">
                        <?= GetMessage("MAIN_ADMIN_ADD_SITE_SELECTOR") ?>
                    </a>
                </td>
                <td width="50%"></td>
            </tr>
        <? endif; ?>
        <?
        endif;
    }

    public static function renderLabel($Option, array $listSite, $siteValue = "")
    {
        $type = $Option[3];
        $sup_text = array_key_exists(5, $Option) ? $Option[5] : '';
        $isChoiceSites = array_key_exists(6, $Option) && $Option[6] == "Y" ? true : false;
        ?>
        <? if ($isChoiceSites): ?>
        <script type="text/javascript">
            function changeSite(el, fieldName) {
                var tr = jsUtils.FindParentObject(el, "tr");
                var sel = null, tagNames = ["select", "input", "textarea"];
                for (var i = 0; i < tagNames.length; i++) {
                    sel = jsUtils.FindChildObject(tr.cells[1], tagNames[i]);
                    if (sel) {
                        sel.name = fieldName + "_" + el.value;
                        break;
                    }

                }
            }

            function addSiteSelector(a) {
                var row = jsUtils.FindParentObject(a, "tr");
                var tbl = row.parentNode;
                var tableRow = tbl.rows[row.rowIndex - 1].cloneNode(true);
                tbl.insertBefore(tableRow, row);
                var sel = jsUtils.FindChildObject(tableRow.cells[0], "select");
                sel.name = "";
                sel.selectedIndex = 0;
                sel = jsUtils.FindChildObject(tableRow.cells[1], "select");
                sel.name = "";
                sel.selectedIndex = 0;
            }
        </script>
        <td width="50%" align="top">
            <select onchange="changeSite(this, '<?= htmlspecialcharsbx($Option[0]) ?>')">
                <? foreach ($listSite as $lid => $siteName): ?>
                    <option <? if ($siteValue == $lid) echo "selected"; ?> value="<?= htmlspecialcharsbx($lid) ?>">
                        <?= htmlspecialcharsbx($siteName) ?>
                    </option>
                <?endforeach; ?>
            </select>
        </td>
    <? else:?>
        <td<? if ($type[0] == "multiselectbox" || $type[0] == "textarea" || $type[0] == "statictext" || $type[0] == "multitext" ||
            $type[0] == "statichtml") echo ' class="adm-detail-valign-top"' ?> width="50%"><?
            if ($type[0] == "checkbox")
                echo "<label for='" . htmlspecialcharsbx($Option[0]) . "'>" . $Option[1] . "</label>";
            else
                echo $Option[1];
            if ($sup_text <> '') {
                ?><span class="required"><sup><?= $sup_text ?></sup></span><?
            }
            ?><a name="opt_<?= htmlspecialcharsbx($Option[0]) ?>"></a></td>
    <?endif;
    }

    public static function renderInput($Option, $arControllerOption, $fieldName, $val)
    {
        $type = $Option[3];
        $disabled = array_key_exists(4, $Option) && $Option[4] == 'Y' ? ' disabled' : '';
        ?>
        <td width="50%"><?
        $methodName = 'renderInput' . ucfirst(strtolower($type[0]));

        if (method_exists(RenderOptions::class, $methodName)) {
            self::$methodName($Option, $arControllerOption, $fieldName, $val, $type, $disabled);
        } else {
            self::renderInputStatichtml($Option, $arControllerOption, $fieldName, $val, $type, $disabled);
        }
        ?></td><?
    }


    /** checkbox **/
    private static function renderInputCheckbox($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $type = $Option[3];
        ?><input
        type="checkbox" <? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?>
        id="<? echo htmlspecialcharsbx($Option[0]) ?>" name="<?= htmlspecialcharsbx($fieldName) ?>"
        value="Y"<? if ($val == "Y") echo " checked"; ?><?= $disabled ?><? if ($type[2] <> '') echo " " . $type[2] ?>>
        <?
    }

    /** radio **/
    private static function renderInputRadio($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        Extension::load("ui.forms");
        $arr = $type[1];
        if (!is_array($arr))
            $arr = [];

        foreach ($arr as $key => $v):
            $attributes = ($val == $key) ? "checked" : "";
            $textValue = $v;
            if (is_array($v)) {
                $textValue = $v[0];
                if (in_array('disabled', $v[1]))
                    $attributes .= ' disabled';
            }
            ?>
            <div>
                <label class="ui-ctl ui-ctl-radio ui-ctl-block ui-ctl-xs">
                    <input type="radio" name="<?= htmlspecialcharsbx($fieldName) ?>" <?= $attributes; ?>
                           value="<?= $key ?>" class="ui-ctl-element"/>
                    <div class="ui-ctl-label-text"><?= htmlspecialcharsbx($textValue); ?></div>
                </label>
            </div>
        <?php endforeach;
    }

    /** text **/
    private static function renderInputText($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $disabled = array_key_exists(4, $Option) && $Option[4] == 'Y' ? ' disabled' : '';
        ?>
        <input type="<? echo $type[0] ?>"<? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?>
               size="<? echo $type[1] ?>" maxlength="255"
               value="<? echo htmlspecialcharsbx($val) ?>" name="<?= htmlspecialcharsbx($fieldName) ?>"
            <?= $disabled ?>
            <?= ($type[0] == "password" || $type["noautocomplete"] ? ' autocomplete="new-password"' : '') ?>
        />
        <?
    }

    /** password **/
    private static function renderInputPassword($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        self::renderInputText($Option, $arControllerOption, $fieldName, $val, $type, $disabled);
    }

    /** number **/
    private static function renderInputNumber($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        self::renderInputText($Option, $arControllerOption, $fieldName, $val, $type, $disabled);
    }

    /** multitext **/
    private static function renderInputMultitext($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $key = 0;

        if (strlen(trim($val))) {
            $arValues = explode(',', $val);
        }
        if (!empty($arValues)) {
            foreach ($arValues as $key => $value) {
                ?>
                <input type="text" size="30" maxlength="255" value="<?= htmlspecialcharsbx($value) ?>"
                       name="<?= htmlspecialcharsbx($fieldName) ?>[<?= $key; ?>]"/>
                <br/>
                <?
            }
            $key++;
        }
        ?>
        <input type="text" size="30" maxlength="255" value=""
               name="<?= htmlspecialcharsbx($fieldName) ?>[<?= $key; ?>]"/>

        <br/>
        <button class="ui-btn ui-btn-sm" onclick="addMoreMultiTextOptionRow(event)"
                title="<?= Loc::getMessage('INPUT_MULTITEXT_MORE_TITLE'); ?>"><?= Loc::getMessage('INPUT_MULTITEXT_MORE'); ?></button>

        <script type="text/javascript">
            function addMoreMultiTextOptionRow(event) {
                event.preventDefault();
                const parentElement = event.target.parentElement,
                    inputs = parentElement.querySelectorAll('input'),
                    lastInput = inputs[inputs.length - 1],
                    newInput = document.createElement('input');

                newInput.setAttribute('type', 'text');
                newInput.setAttribute('size', '30');
                newInput.setAttribute('maxlength', '255');
                newInput.setAttribute('name', '<?= htmlspecialcharsbx($fieldName) ?>[' + inputs.length + ']');

                lastInput.after(newInput);
                lastInput.after(document.createElement('br'));
            }
        </script>
        <?php
    }

    /** multiuser **/
    private static function renderInputMultiuser($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        \CJSCore::Init(array('access'));
        $arUsers = $type[1];
        $key = 0;
        $access = new \CAccess();
        $arNames = $access->GetNames($arUsers); ?>
        <script type="text/javascript">
            function InsertAccess<?=$fieldName;?>(arRights, divId, hiddenName) {
                var div = BX(divId);
                for (var provider in arRights) {
                    for (var id in arRights[provider]) {
                        var pr = BX.Access.GetProviderPrefix(provider, id);
                        var newDiv = document.createElement('DIV');
                        newDiv.style.marginBottom = '4px';
                        newDiv.innerHTML = '<input type=\"hidden\" name=\"' + hiddenName + '\" value=\"' + id + '\">' + (pr ? pr + ': ' : '') + BX.util.htmlspecialchars(arRights[provider][id].name) + '&nbsp;<a href=\"javascript:void(0);\" onclick=\"DeleteAccess<?=$fieldName;?>(this, \'' + id + '\')\" class=\"access-delete\"></a>';
                        div.appendChild(newDiv);
                    }
                }
            }

            function DeleteAccess<?=$fieldName;?>(ob, id) {
                var div = BX.findParent(ob, {'tag': 'div'});
                div.parentNode.removeChild(div);
            }

            function ShowPanelFor<?=$fieldName;?>() {
                BX.Access.Init({
                    other: {disabled: true},
                    group: {disabled: true},
                    intranet: {disabled: true},
                    socnetgroup: {disabled: true},
                });
                BX.Access.SetSelected({});
                BX.Access.ShowForm({
                    callback: function (obSelected) {
                        InsertAccess<?=$fieldName;?>(obSelected, 'bx_user_search-<?=$fieldName;?>', '<?=$fieldName;?>[]');
                    }
                });
            }


        </script>
        <div id="bx_user_search-<?= $fieldName; ?>">
            <?
            foreach ($arUsers as $code) {
                ?>
                <div style="margin-bottom:4px">
                    <input type="hidden" name="<?= $fieldName; ?>[]" value="<?= $code; ?>"/>
                    <? ($arNames[$code]["provider"] <> '') ? $arNames[$code]["provider"] . ': ' : ''; ?>
                    <?= htmlspecialcharsbx($arNames[$code]["name"]); ?>
                    <a href="javascript:void(0);" onclick="DeleteAccess<?= $fieldName; ?>(this, '<?= $code; ?>')"
                       class="access-delete"></a>
                </div>
                <?
            }
            ?>

        </div>
        <a href="javascript:void(0)" class="bx-action-href"
           onclick="ShowPanelFor<?= $fieldName; ?>()"><?= Loc::getMessage('INPUT_MULTIUSER_MORE'); ?></a>
        <?php
    }

    /** selectbox **/
    private static function renderInputSelectbox($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $arr = $type[1];
        if (!is_array($arr))
            $arr = array();
        ?><select
        name="<?= htmlspecialcharsbx($fieldName) ?>" <? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?> <?= $disabled ?>><?
        foreach ($arr as $key => $v):
            ?>
            <option value="<? echo $key ?>"<? if ($val == $key) echo " selected" ?>><? echo htmlspecialcharsbx($v) ?></option><?
        endforeach;
        ?></select>
        <?php
    }

    /** multiselectbox **/
    private static function renderInputMultiselectbox($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $arr = $type[1];
        if (!is_array($arr))
            $arr = array();
        $arr_val = explode(",", $val);
        ?><select
        size="5" <? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?>
        multiple name="<?= htmlspecialcharsbx($fieldName) ?>[]"<?= $disabled ?>><?
        foreach ($arr as $key => $v):
            ?>
            <option value="<? echo $key ?>"<? if (in_array($key, $arr_val)) echo " selected" ?>><? echo htmlspecialcharsbx($v) ?></option><?
        endforeach;
        ?></select>
        <?php
    }

    /** textarea **/
    private static function renderInputTextarea($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        ?>
        <textarea <? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?>
        rows="<? echo $type[1] ?>"
        cols="<? echo $type[2] ?>"
        name="<?= htmlspecialcharsbx($fieldName) ?>"<?= $disabled ?>><? echo htmlspecialcharsbx($val) ?></textarea>
        <?php
    }

    /** color **/
    private static function renderInputColor($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        \CJSCore::init("color_picker");
        ?>
        <input type="text"<? if (isset($arControllerOption[$Option[0]])) echo ' disabled title="' . GetMessage("MAIN_ADMIN_SET_CONTROLLER_ALT") . '"'; ?>
               size="10" value="<? echo htmlspecialcharsbx($val) ?>"
               name="<?= htmlspecialcharsbx($fieldName) ?>"<?= $disabled ?>
               onfocus="colorPicker_<?= $fieldName; ?>(this)">
        <span class="main-color-picker-preview"
              style="display: inline-block; vertical-align: middle; background-color: <?= ($val ?: "transparent") ?>;"></span>
        <script>
            function colorPicker_<?=$fieldName;?>(input) {
                new BX.ColorPicker({
                    bindElement: input,
                    allowCustomColor: true,
                    selectedColor: input.value,
                    defaultColor: input.value,
                    onColorSelected: function (item) {
                        input.value = item;
                        input.nextElementSibling.style.backgroundColor = item;
                    },
                    popupOptions: {angle: true, autoHide: true, closeByEsc: true}
                }).open();
            }
        </script>
        <?php
    }

    /** buttonColor **/
    private static function renderInputButtoncolor($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        $reflectionClassColor = new \ReflectionClass('Bitrix\UI\Buttons\Color');
        $colorList = $reflectionClassColor->getConstants();
        ?>
        <div class="option-button-color-field-list">
            <?php foreach ($colorList as $colorCode => $colorClassName) : ?>
                <input type="radio" name="<?= htmlspecialcharsbx($fieldName) ?>"
                       id="<?= htmlspecialcharsbx($fieldName . '_' . $colorCode) ?>"
                       value="<?= $colorCode; ?>" <?= ($val == $colorCode ? "checked" : ""); ?>>
                <label for="<?= htmlspecialcharsbx($fieldName . '_' . $colorCode) ?>"
                       class="ui-btn <?= $colorClassName; ?>"></label>
            <?php endforeach; ?>
        </div>
        <style>
            .option-button-color-field-list {
                max-width: 300px;
            }

            .option-button-color-field-list label {
                margin: 0 5px 5px 0 !important;
                padding: 0 20px !important;
            }

            .option-button-color-field-list input[type="radio"] {
                display: none;
            }

            .option-button-color-field-list input[type="radio"]:checked + label {
                box-shadow: 0 0 0 1px inset black;
            }

            .option-button-color-field-list label:hover {
                box-shadow: 0 0 0 1px inset #505050;
            }
        </style>
        <?php
    }

    /** statictext **/
    private static function renderInputStatictext($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        echo htmlspecialcharsbx($val);
    }

    /** statichtml **/
    private static function renderInputStatichtml($Option, $arControllerOption, $fieldName, $val, $type, $disabled)
    {
        echo $val;
    }
}
