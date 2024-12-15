<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Кнопка");

\Bitrix\Main\UI\Extension::load('ui.icons.b24');
\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
Bitrix\Main\UI\Extension::load('ui.tooltip');
\Bitrix\Main\UI\Extension::load('ui.forms');
\Bitrix\Main\UI\Extension::load('ui.countdown');
\Bitrix\Main\UI\Extension::load('ui.hint');
?>

<p></p>

<div class='my-block'>
    <button class='ui-btn ui-btn-success' id='click-me-button'>Нажми меня</button>
</div>

<script>
    BX.ready(
        function () {
            let button = BX('click-me-button');
            BX.adjust(
                button,
                {
                    'events': {
                        'click': function () {
                            BX.ajax.runComponentAction(
                                'nick.course:simplecomponentajax',
                                'sendMessage',
                                {
                                    mode: 'class',
                                }).then(function (response) {
                                if (response.status === 'success') {
                                    let gradesCount = response.data.gradesCount;
                                    BX.UI.Notification.Center.notify({
                                        content: 'У вас получено ' + gradesCount + ' оценок'
                                    });
                                }
                            }, function (response) {
                                BX.UI.Notification.Center.notify({
                                    content: response.errors[0].message
                                });
                            });
                        }
                    }
                }
            );

        }
    )
</script>
<!--<p></p>
<div id='js-button-container'></div>
<script type='application/javascript'>
    BX.ready(
        function () {
            let button = new BX.UI.Button({
                id: 'my-button',
                text: 'Мои оценки',
                noCaps: true,
                round: true,
                menu: {
                    items: [
                        {text: 'Детальное описание', href: '/path/to/page'},
                        {text: 'Редактировать', disabled: true},
                        {
                            text: 'Перенести',
                            onclick: function (event, item) {
                                alert('Обработка нажатия на пункт');
                            }
                        },
                        {delimiter: true},
                        {
                            text: 'Закрыть',
                            onclick: function (event, item) {
                                item.getMenuWindow().close();
                            }
                        }
                    ],
                    closeByEsc: true,
                    offsetTop: 5,
                },
                props: {
                    id: 'xxx'
                },
                className: 'ddddd-dddd',
                onclick: function (btn, event) {
                    console.log('onclick', btn);
                },
                events: {
                    mouseenter: function (button, event) {
                        console.log('mouseenter', button, event, this);
                    },
                    mouseout: function (button, event) {
                        console.log('mouseout', button, event, this);
                    }
                },
                size: BX.UI.Button.Size.MEDIUM,
                color: BX.UI.Button.Color.PRIMARY,
                tag: BX.UI.Button.Tag.BUTTON,
                icon: BX.UI.Button.Icon.ADD_FOLDER,
                state: BX.UI.Button.State
            });
            let container = BX('js-button-container');
            if (container)
                BX.append(button.getContainer(), container);
        }
    )
</script>

<p></p>

<script type='text/javascript'>
    BX.ready(function () {
        BX.UI.Hint.init(BX('my-container'));
    })
</script>
<div id='my-container'>
    <div>
        Подсказка 1
        <span data-hint='Текст 1'></span>
    </div>
    <div>
        Подсказка 2
        <span data-hint='Текст 2'></span>
    </div>
</div>-->

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
