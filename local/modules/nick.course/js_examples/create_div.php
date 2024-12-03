<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
##use Bitrix\Main\Page\Asset;
##Asset::getInstance()->addJs(/*ПУТЬ ДО JS ФАЙЛА*/);
\Bitrix\Main\UI\Extension::load(['ui', 'main.core']);
CUtil::InitJSCore(['test_lib']);
CUtil::InitJSCore(['onpopup_lib']);
?>
<script type="application/javascript">
    BX.ready(
        function(){
            const div1 = BX.Tag.render`
                <div class="my-class" style="width: 300px">
                    Новый блок через render
                </div>
            `;
            BX.append(div1, BX('workarea-content'));

            let elements = BX.findChildren(document.body, {className: 'my-class'}, true);
            elements.forEach(function(element) {
                BX.append(element.cloneNode(true), BX('workarea-content'));
            });
        }
    );
</script>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
