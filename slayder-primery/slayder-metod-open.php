<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Слайдер. Метод open");
\CJSCore::init("sidepanel");
\Bitrix\Main\UI\Extension::load("ui.buttons");
?>
    <script type="application/javascript">
        BX.ready(
            function()
            {
            let button = BX('myButton');
                BX.adjust(
                button,
                {
                    'events':
                    {
                        'click': function()
                        {
                            //let url = "/slayder-primery/detalnaya-stranitsa-dlya-slaydera.php";
                            //let url = "/slayder-primery/detalnaya-stranitsa-s-shapkoy.php";
                            //let url = "https://ya.ru";
                            //let url = "https://apps.oshr.ru/bitrix-lk/clientsbymodules.php";
                            //let sliderId = "myFantasticSlider";
                            //let url = "/crm/deal/details/0/";
                            let url = "/slayder-primery/komponent-obertka.php";
                            BX.SidePanel.Instance.open(
                                url,
                                //sliderId,
                                {
                                    //ширина
                                    width: 1000,

                                    //кэширование
                                    cacheable:false,

                                    //меняем адрес в браузере
                                    allowChangeHistory:true,

                                    //смена заголовка страницы
                                    allowChangeTitle:true,
                                    title: 'Детальная страница слайдера',

                                    //метод отправки запроса Get/Post
                                    requestMethod: 'post',
                                    requestParams: { // post-параметры
                                        action: "load",
                                        ids: [1, 2, 3],
                                        dictionary: {
                                            one: 1,
                                            two: 2
                                        }
                                    },

                                    //параметры для слйдера
                                    data:{
                                            param1: 30,
                                            param2: 100,
                                            param3: "=========="
                                    },

                                    //ярлык
                                    label:{
                                        text: 'Ярлык',
                                        color: "#fff", //цвет текста
                                        bgColor: "red", //цвет фона
                                        opacity: 0.8 //прозрачность фона
                                    },

                                    //икнока печати
                                    printable:true,

                                    // иконка открыть в новом окне
                                    newWindowLabel:true,

                                    //иконка скопировать ссылку
                                    copyLinkLabel:true,
                                    minimizeLabel:true,

                                    //время открытия слайдера в млс
                                    //animationDuration: 1000

                                    //события
                                    /*events:{
                                        //https://dev.1c-bitrix.ru/api_help/js_lib/sidepanel/events/events.php
                                        //https://dev.1c-bitrix.ru/api_help/js_lib/sidepanel/events/bx_sidePanel_event.php
                                        'onClose': function(event) {
                                            console.log(event, 'event');
                                            let slider = event.getSlider();
                                            console.log(slider, 'slider');
                                            let curSliderUrl = slider.getUrl();
                                            console.log(curSliderUrl, 'curSliderUrl');
                                            if (curSliderUrl==url)
                                            {
                                                //отмена действия
                                                event.denyAction();
                                            }

                                        }
                                    }*/

                                    //кроссдоменные запросы
                                    //allowCrossOrigin:true,

                                    //произвольные контент
                                    //loader: 'crm:entity-details-loader',
                                    /*contentCallback: function(slider) {
                                        let promise = new BX.Promise();
                                        //Скорее всего тут у вас будет аякс
                                        setTimeout(function() {
                                            let result =
                                                "<div>Текст<br>" +
                                                "<div>Текст<br>" +
                                                "<div>Текст<br>";
                                            promise.fulfill(result);
                                        }, 2000);
                                        return promise;
                                    },*/
                                    /*contentCallback: function(slider) {

                                        //Callback должен вернуть промис или HTML (строка или DOM-элемент)
                                        return new Promise(
                                             function(resolve, reject) {

                                             let request = BX.ajax.runAction(
                                                     'study:userrating.test.testComp',
                                                     {
                                                         data: {
                                                             param1: 'hhh'
                                                         }
                                                     }
                                                 );

                                                 request.then(
                                                     function(response){
                                                         //успех
                                                         let html = BX.prop.getString(response.data, "html", []);
                                                         let assets = BX.prop.getArray(response.data, "assets", []);
                                                         let  css = BX.prop.getArray(assets, "css", []);
                                                         let  js = BX.prop.getArray(assets, "js", []);
                                                         let  strings = BX.prop.getArray(assets, "string", []);
                                                         console.log(css, 'css');
                                                         console.log(js, 'js');
                                                         console.log(strings, 'strings');
                                                         if (css.length>0)
                                                             BX.loadCSS(css);
                                                         if (js.length>0)
                                                         {
                                                             js.forEach(
                                                                 function(jsItem)
                                                                 {
                                                                     BX.loadScript(jsItem)
                                                                 }
                                                             )
                                                        }
                                                        resolve(html);
                                                        console.log(response);
                                                    },
                                                    function(response){
                                                        //ошибка
                                                        console.log(response);
                                                        reject(response.errors.pop().message)
                                                    },
                                                );
                                            }
                                        );
                                    }*/
                                }
                            );
                        }
                    }
                })
            }
        )
    </script>
<button class="ui-btn ui-btn-success" id="myButton">Кнопка</button>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>