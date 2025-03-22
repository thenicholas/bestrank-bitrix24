<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Детальная страница для слайдера");
\CJSCore::init("sidepanel");?>
<div style="background-color: #0c66c3;">
    Я детальная страница
    <pre>
        <?print_r($_REQUEST);?>
    </pre>
    <pre>
        <?//print_r($_POST);?>
    </pre>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>