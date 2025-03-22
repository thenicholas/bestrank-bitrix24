<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Слайдер. Ссылки");
\CJSCore::init("sidepanel");
?>
<script type="application/javascript">
    BX.ready(
        function()
        {
            BX.SidePanel.Instance.bindAnchors({
                rules:
                    [
                        {
                            condition: [
                                "/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php",
                                "/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php?userid=(\\d+)"
                            ],
                            //Игноривать ссылки, содержащие следующие параметры в Query String
                            stopParameters: [
                                "stopParamId"
                            ],
                            options:{
                                width: 200
                            }
                        },
                        {
                            condition: [
                                new RegExp("/my/[0-9]+/", "i")
                            ],
                            /*validate: function(link)
                            {
                                console.log(link, 'validate - link');
                                return null;
                            },*/
                            handler: function(event, link)
                            {
                                console.log(event, 'event');
                                console.log(link, 'link');
                                BX.SidePanel.Instance.open("/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php");
                                event.preventDefault(false);
                            }
                        }
                    ]
                }
            )
        }
    )
</script>
    <p><a href="/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php">Ссылка на страницу портала</a></p>
    <p><a href="/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php?userid=1">Ссылка c ID в параметре</a></p>
    <p><a href="/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php?userid=1" data-slider-ignore-autobinding="true">Ссылка c ID в параметре и data аттрибутом</a></p>
    <p><a href="/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php?stopParamId=1">Ссылка c стоп параметром</a></p>
    <p><a href="/my/1/">Ссылка c обработчиком</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
