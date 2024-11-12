<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
##use Bitrix\Main\Page\Asset;
##Asset::getInstance()->addJs(/*ПУТЬ ДО JS ФАЙЛА*/);
?>
<script type="application/javascript">
    BX.ready(
        function(){
            var objectDiv = BX.create('div', {
                style: {
                    width: '300px'
                },
                text: 'Новый блок'
            });
            console.log(objectDiv);
        }
    );
</script>
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');