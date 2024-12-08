/* eslint-disable */
this.BX = this.BX || {};
this.BX.NickCourse = this.BX.NickCourse || {};
(function (exports,main_core) {
	'use strict';

	var TaskDetailMoreMenu = /*#__PURE__*/function () {
	  function TaskDetailMoreMenu() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      name: 'TaskDetailMoreMenu'
	    };
	    babelHelpers.classCallCheck(this, TaskDetailMoreMenu);
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

	// Создаем экземпляр при загрузке экстеншена
	var menu = new TaskDetailMoreMenu();
	console.log('Extension loaded, menu created:', menu);
	BX.ready(function () {
	  BX.Event.EventEmitter.subscribe('bx.main.popup:onFirstShow', function (event) {
	    var popup = event.target;
	    if (popup.uniquePopupId === 'menu-popup-task-view-b') {
	      var menuItems = popup.contentContainer.querySelector('.menu-popup-items');
	      var newItem = BX.create('span', {
	        attrs: {
	          title: 'Моя подсказка',
	          className: 'menu-popup-item menu-popup-item-create'
	        },
	        children: [BX.create('span', {
	          attrs: {
	            className: 'menu-popup-item-icon'
	          }
	        }), BX.create('span', {
	          attrs: {
	            className: 'menu-popup-item-text'
	          },
	          text: 'Мой пункт меню',
	          events: {
	            click: function click() {
	              alert('click');
	              popup.close();
	            }
	          }
	        })]
	      });
	      BX.append(newItem, menuItems);
	    }
	  });
	});

	exports.TaskDetailMoreMenu = TaskDetailMoreMenu;

}((this.BX.NickCourse.TaskDetailMoreMenu = this.BX.NickCourse.TaskDetailMoreMenu || {}),BX));
//# sourceMappingURL=task_detail_more_menu.bundle.js.map
