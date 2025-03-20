/* eslint-disable */
this.BX = this.BX || {};
this.BX.NickCourse = this.BX.NickCourse || {};
(function (exports,main_core_events) {
    'use strict';

    var GridCustomTasks = /*#__PURE__*/function () {
      function GridCustomTasks() {
        babelHelpers.classCallCheck(this, GridCustomTasks);
      }
      babelHelpers.createClass(GridCustomTasks, [{
        key: "gridUpdateHandler",
        value: function gridUpdateHandler(gridInstance) {
          var gridRows = gridInstance.getRows();
          var userFields = BX.NickCourse.GridCustomTasks.data;
          console.log(BX.NickCourse.GridCustomTasks.data);
        }
      }, {
        key: "gridReplacer",
        value: function gridReplacer(grid) {
          var userFields = BX.NickCourse.GridCustomTasks.data;
          console.log(userFields);
          var gridRows = grid.getRows(),
            headerCells = gridRows.getHeadFirstChild().getCells(),
            bodyRows = gridRows.getBodyChild();
          for (var i = 0; i < headerCells.length; i++) {
            var headerCell = headerCells[i];
            if (userFields.hasOwnProperty(headerCell.dataset.name)) {
              userFields[headerCell.dataset.name]['index'] = i;
            }
          }
          for (var _i = 0; _i < bodyRows.length; _i++) {
            var bodyRow = bodyRows[_i];
            for (var userCode in userFields) {
              var cell = bodyRow.getCellByIndex(userFields[userCode]['index']);
              if (!cell) continue;
              if (userFields[userCode]['items'].hasOwnProperty(bodyRow.getContent(cell))) {
                bodyRow.getContentContainer(cell).innerHTML = userFields[userCode]['items'][bodyRow.getContent(cell)];
              }
            }
          }
        }
      }]);
      return GridCustomTasks;
    }();

    var gridCustom = new GridCustomTasks();
    BX.ready(function () {
      main_core_events.EventEmitter.subscribe('grid::ready', function (event) {
        var grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
          gridCustom.gridReplacer(grid);
        }
      });
      main_core_events.EventEmitter.subscribe('grid::updated', function (event) {
        var grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
          gridCustom.gridReplacer(grid);
        }
      });
    });

}((this.BX.NickCourse.GridCustomTasks = this.BX.NickCourse.GridCustomTasks || {}),BX.Event));
//# sourceMappingURL=grid-custom-tasks.bundle.js.map
