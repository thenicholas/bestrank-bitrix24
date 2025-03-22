/* eslint-disable */
this.BX = this.BX || {};
this.BX.NickCourse = this.BX.NickCourse || {};
(function (exports,main_core_events) {
    'use strict';

    function _classStaticPrivateFieldSpecSet(receiver, classConstructor, descriptor, value) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "set"); _classApplyDescriptorSet(receiver, descriptor, value); return value; }
    function _classApplyDescriptorSet(receiver, descriptor, value) { if (descriptor.set) { descriptor.set.call(receiver, value); } else { if (!descriptor.writable) { throw new TypeError("attempted to set read only private field"); } descriptor.value = value; } }
    function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
    function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
    function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
    function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
    var GridCustom = /*#__PURE__*/function () {
      function GridCustom() {
        babelHelpers.classCallCheck(this, GridCustom);
        if (_classStaticPrivateFieldSpecGet(GridCustom, GridCustom, _instance)) {
          return _classStaticPrivateFieldSpecGet(GridCustom, GridCustom, _instance);
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
        _classStaticPrivateFieldSpecSet(GridCustom, GridCustom, _instance, this);
      }
      babelHelpers.createClass(GridCustom, [{
        key: "gridUpdateHandler",
        value: function gridUpdateHandler(gridInstance) {
          var gridRows = gridInstance.getRows();
          var bodyRows = gridRows.getBodyChild();
          this.coloringCells(gridRows, bodyRows);
        }
      }, {
        key: "coloringCells",
        value: function coloringCells(gridRows, bodyRows) {
          var _gridRows$getHeadFirs;
          var headerCells = gridRows === null || gridRows === void 0 ? void 0 : (_gridRows$getHeadFirs = gridRows.getHeadFirstChild()) === null || _gridRows$getHeadFirs === void 0 ? void 0 : _gridRows$getHeadFirs.getCells();
          if (!headerCells) {
            return;
          }
          var nameCellIndex = false;
          for (var key in headerCells) {
            var _headerCells$key, _headerCells$key$data;
            if (((_headerCells$key = headerCells[key]) === null || _headerCells$key === void 0 ? void 0 : (_headerCells$key$data = _headerCells$key.dataset) === null || _headerCells$key$data === void 0 ? void 0 : _headerCells$key$data.name) === this.customGridData.coloringCells.columnName) {
              nameCellIndex = key;
              break;
            }
          }
          if (nameCellIndex === false) {
            return;
          }
          for (var rowKey in bodyRows) {
            var _bodyRowCells$nameCel, _bodyRowCells$nameCel2, _bodyRowCells$nameCel3;
            var bodyRow = bodyRows[rowKey];
            var bodyRowCells = bodyRow.getCells();
            var cellValue = parseFloat((_bodyRowCells$nameCel = bodyRowCells[nameCellIndex]) === null || _bodyRowCells$nameCel === void 0 ? void 0 : (_bodyRowCells$nameCel2 = _bodyRowCells$nameCel.querySelector('.main-grid-cell-content')) === null || _bodyRowCells$nameCel2 === void 0 ? void 0 : (_bodyRowCells$nameCel3 = _bodyRowCells$nameCel2.innerText) === null || _bodyRowCells$nameCel3 === void 0 ? void 0 : _bodyRowCells$nameCel3.replace(/[^\d]/g, ''));
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
      }]);
      return GridCustom;
    }();
    var _instance = {
      writable: true,
      value: null
    };

    var gridCustom = new GridCustom();
    BX.ready(function () {
      main_core_events.EventEmitter.subscribe('grid::ready', function (event) {
        var grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
          gridCustom.gridUpdateHandler(grid);
        }
      });
      main_core_events.EventEmitter.subscribe('grid::updated', function (event) {
        var grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
          gridCustom.gridUpdateHandler(grid);
        }
      });
    });

}((this.BX.NickCourse.GridCustom = this.BX.NickCourse.GridCustom || {}),BX.Event));
//# sourceMappingURL=grid-custom.bundle.js.map
