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
	    var menuItems = [];
	    BX.ajax.runAction('nick:course.TaskDetailMenuItems.get', {
	      data: {}
	    }).then(function (response) {
	      if (response.status === 'success' && Array.isArray(response.data)) {
	        menuItems = response.data; // если let
	        var popup = event.target;
	        if (popup.uniquePopupId === 'menu-popup-task-view-b') {
	          var menuContainer = popup.contentContainer.querySelector('.menu-popup-items');
	          if (menuItems.length) {
	            menuItems.forEach(function (menuText) {
	              var newItem = BX.create('span', {
	                attrs: {
	                  title: menuText,
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
	                  text: menuText,
	                  events: {
	                    click: function click() {
	                      alert(menuText);
	                      popup.close();
	                    }
	                  }
	                })]
	              });
	              BX.append(newItem, menuContainer);
	            });
	          }
	        }
	      }
	    }, function (error) {
	      console.log(error);
	    });
	  });
	});

	exports.TaskDetailMoreMenu = TaskDetailMoreMenu;

}((this.BX.NickCourse.TaskDetailMoreMenu = this.BX.NickCourse.TaskDetailMoreMenu || {}),BX));
//# sourceMappingURL=task_detail_more_menu.bundle.js.map
