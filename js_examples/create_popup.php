<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
##use Bitrix\Main\Page\Asset;
##Asset::getInstance()->addJs(/*ПУТЬ ДО JS ФАЙЛА*/);
?>
<div id="hideBlock" style="display:none;">
	<h1>Hello</h1>
	<p>text</p>
</div>
<script>
BX.ready(function(){
    var oPopup = new BX.PopupWindow('call_feedback', window.body, {
		autoHide : true,
		offsetTop : 1,
		offsetLeft : 0,
		lightShadow : true,
		closeIcon : true,
		closeByEsc : true,
		overlay: {
        backgroundColor: 'red', opacity: '80'
		}
	});
	oPopup.setContent(BX('hideBlock'));
	BX.bindDelegate(
        document.body, 'click', {className: 'css_popup' },
			BX.proxy(function(e){
                if(!e)
                    e = window.event;
                oPopup.show();
                return BX.PreventDefault(e);
            }, oPopup)
	);


});
</script>
<div class="css_popup">click Me</div>

<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
