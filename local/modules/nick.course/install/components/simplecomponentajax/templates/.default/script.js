BX.ready(
    function () {
        let button = document.querySelector('.click-me-button');
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
