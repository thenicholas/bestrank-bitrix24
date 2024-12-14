import {TaskDetailMoreMenu} from "./TaskDetailMoreMenu";
import {EventEmitter} from 'main.core.events';

new TaskDetailMoreMenu();

BX.ready(function () {
    const menuItems = BX.NickCourse.TaskDetailMoreMenu.data.menuItems;

    EventEmitter.subscribe('bx.main.popup:onFirstShow', function (event) {
        const popup = event.target;

        if (popup.uniquePopupId === 'menu-popup-task-view-b') {
            let menu = BX.PopupMenu.getCurrentMenu();

            menuItems.forEach((item) => {
                menu.addMenuItem({
                    text: item,
                    className: 'menu-popup-item menu-popup-item-view',
                    onclick: function () {
                        alert(item);
                        popup.close();
                    }
                });
            });
        }
    });
});
