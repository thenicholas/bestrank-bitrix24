function UserRatingCompetenceChange(action, gridId) {
    const grid = BX.Main.gridManager.getInstanceById(gridId),
        gridSelectedRows = grid?.getRows()?.getSelectedIds(),
        actionsPanel = grid?.getActionsPanel(),
        values = actionsPanel?.getValues();

    BX.ajax.runComponentAction('nick.course:userrating.competence.list',
        action,
        {
            mode: 'class',
            data: {
                data: {
                    gridSelectedRows,
                    values
                }
            }
        }).then(response => {
            if (response?.data?.updated) {
                BX.UI.Notification.Center.notify({
                    content: `Успешно обновлено элементов: ${response.data.updated}`
                });
            }
            grid.reload();
        }, response => {
            let content = [];
            if (response?.data?.updated) {
                content.push(`Успешно обновлено: ${response.data.updated}`);
            }
            if (response?.data?.errors) {
                content.push(`Ошибка обновления: ${response.data.errors}`);
            }
            if (response?.errors?.[0]?.message) {
                content.push(`Ошибка: ${response.errors[0].message}`);
            }
            BX.UI.Notification.Center.notify({
                content: content.join('<br>')
            });
            grid.reload();
        }
    );
}

function UserRatingDeleteCompetence(competenceId, gridId) {
    const grid = BX.Main.gridManager.getInstanceById(gridId);
    BX.ajax.runComponentAction('nick.course:userrating.competence.list',
        'deleteCompetence',
        {
            mode: 'class',
            data: {
                competenceId: competenceId
            }
        }).then(response => {
        BX.UI.Notification.Center.notify({
            content: 'Элемент удалён'
        });
        grid.reload();
    }, response => {
        if (response?.errors?.[0]?.message) {
            BX.UI.Notification.Center.notify({
                content: `Ошибка: ${response.errors[0].message}`
            });
        }
    });
}

BX.ready(
    function() {
        const sliderOptions = BX.NickCourse.Slider.data;
        BX.SidePanel.Instance.bindAnchors({
                rules: [
                    {
                        condition: [
                            new RegExp("/detail/competence\\.php\\?competence_id=[0-9]+")
                        ],
                        options: {
                            cacheable: sliderOptions.cacheable,
                            width: sliderOptions.width,
                            allowChangeHistory: sliderOptions.allowChangeHistory
                        }
                    }
                ]
            }
        )
    }
)
