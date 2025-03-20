export class GridCustomTasks {

    constructor() {

    }

    gridUpdateHandler(gridInstance) {
        const gridRows = gridInstance.getRows();
        const userFields = BX.NickCourse.GridCustomTasks.data
        console.log(BX.NickCourse.GridCustomTasks.data);
    }
    gridReplacer(grid) {
        const userFields = BX.NickCourse.GridCustomTasks.data
        console.log(userFields);
        const gridRows = grid.getRows(),
            headerCells = gridRows.getHeadFirstChild().getCells(),
            bodyRows = gridRows.getBodyChild();

        for (let i = 0; i < headerCells.length; i++) {
            const headerCell = headerCells[i];
            if (userFields.hasOwnProperty(headerCell.dataset.name)) {
                userFields[headerCell.dataset.name]['index'] = i;
            }
        }
        for (let i = 0; i < bodyRows.length; i++) {
            const bodyRow = bodyRows[i];

            for (let userCode in userFields) {
                const cell = bodyRow.getCellByIndex(userFields[userCode]['index']);
                if (!cell)
                    continue;
                if (userFields[userCode]['items'].hasOwnProperty(bodyRow.getContent(cell))) {
                    bodyRow.getContentContainer(cell).innerHTML = userFields[userCode]['items'][bodyRow.getContent(cell)];
                }

            }
        }
    }
}
