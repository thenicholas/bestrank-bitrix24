<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Детальная страница для шапкой");
\CJSCore::init("sidepanel");
if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y")
{
	$APPLICATION->RestartBuffer(); //сбрасываем весь вывод
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<?$APPLICATION->ShowHead(); ?>
	</head>
	<body>
        <div style="background-color: #fff;">
            Я детальная страница
            <pre>
            <?print_r($_REQUEST);?>
        </pre>
            <pre>
            <?print_r($_POST);?>
        </pre>
        </div>
        <script type="application/javascript">
            //https://dev.1c-bitrix.ru/api_help/js_lib/sidepanel/sidepanel_slider.php
            let slider = BX.SidePanel.Instance.getTopSlider();
            console.log(slider.getData(), 'sliderdata');
            console.log(slider.getData().get('param1'), 'param1');
            slider.setTitle('Деталка с шапкой')
        </script>
	</body>
	</html>
    <?
}
else
{
    ?>
    <div style="background-color: #0c66c3;">
        Я детальная страница
        <pre>
            <?print_r($_REQUEST);?>
        </pre>
        <pre>
            <?//print_r($_POST);?>
        </pre>
    </div>
    <?php
	//Контент компонента
}
 ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>