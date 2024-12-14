/* eslint-disable */
this.BX = this.BX || {};
this.BX.NickCourse = this.BX.NickCourse || {};
(function (exports,main_core,main_core_events) {
    'use strict';

    var TaskDetailMoreMenu = /*#__PURE__*/function () {
      function TaskDetailMoreMenu() {
        var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
          name: 'TaskDetailMoreMenu'
        };
        babelHelpers.classCallCheck(this, TaskDetailMoreMenu);
        babelHelpers.defineProperty(this, "data", {});
        this.name = options.name;
      }
      babelHelpers.createClass(TaskDetailMoreMenu, [{
        key: "setName",
        value: function setName(name) {
          if (main_core.Type.isString(name)) {
            this.name = name;
          }
        }
      }, {
        key: "getName",
        value: function getName() {
          return this.name;
        }
      }]);
      return TaskDetailMoreMenu;
    }();

    new TaskDetailMoreMenu();
    BX.ready(function () {
      var menuItems = BX.NickCourse.TaskDetailMoreMenu.data.menuItems;
      main_core_events.EventEmitter.subscribe('bx.main.popup:onFirstShow', function (event) {
        var popup = event.target;
        if (popup.uniquePopupId === 'menu-popup-task-view-b') {
          var menu = BX.PopupMenu.getCurrentMenu();
          menuItems.forEach(function (item) {
            menu.addMenuItem({
              text: item,
              className: 'menu-popup-item menu-popup-item-view',
              onclick: function onclick() {
                alert(item);
                popup.close();
              }
            });
          });
        }
      });
    });

}((this.BX.NickCourse.TaskDetailMoreMenu = this.BX.NickCourse.TaskDetailMoreMenu || {}),BX,BX.Event));
//# sourceMappingURL=task_detail_more_menu.bundle.js.map
