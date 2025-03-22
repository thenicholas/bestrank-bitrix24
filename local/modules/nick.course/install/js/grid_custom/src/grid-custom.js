export class GridCustom {
    static #instance = null;

    constructor() {
        if (GridCustom.#instance) {
            return GridCustom.#instance;
        }

        this.customGridData = {
            coloringCells: {
                columnName: 'PROPERTY_97',
                greenValue: 5,
                redValue: 2,
                greenColor: '#d9ffd9',
                redColor: '#ffbbbb'
            }
        };

        GridCustom.#instance = this;
    }

    gridUpdateHandler(gridInstance) {
        const gridRows = gridInstance.getRows();
        const bodyRows = gridRows.getBodyChild();

        this.coloringCells(gridRows, bodyRows);
    }

    coloringCells(gridRows, bodyRows) {
        const headerCells = gridRows?.getHeadFirstChild()?.getCells();

        if (!headerCells) {
            return;
        }

        let nameCellIndex = false;
        for (let key in headerCells) {
            if (headerCells[key]?.dataset?.name === this.customGridData.coloringCells.columnName) {
                nameCellIndex = key;
                break;
            }
        }

        if (nameCellIndex === false) {
            return;
        }

        for (let rowKey in bodyRows) {
            const bodyRow = bodyRows[rowKey];
            const bodyRowCells = bodyRow.getCells();

            let cellValue = parseFloat(bodyRowCells[nameCellIndex]
                ?.querySelector('.main-grid-cell-content')?.innerText?.replace(/[^\d]/g, ''));

            if (isNaN(cellValue)) {
                continue;
            }

            if (cellValue === this.customGridData.coloringCells.greenValue) {
                bodyRowCells[nameCellIndex].style.backgroundColor = this.customGridData.coloringCells.greenColor;
            } else if (cellValue <= this.customGridData.coloringCells.redValue) {
                bodyRowCells[nameCellIndex].style.backgroundColor = this.customGridData.coloringCells.redColor;
            }
        }
    }
}
