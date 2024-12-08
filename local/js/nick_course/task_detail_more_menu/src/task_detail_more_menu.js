import {Type} from 'main.core';

export class TaskDetailMoreMenu
{
	constructor(options = {name: 'TaskDetailMoreMenu'})
	{
		this.name = options.name;
	}

	setName(name)
	{
		if (Type.isString(name))
		{
			this.name = name;
		}
	}

	getName()
	{
		return this.name;
	}
}

// Создаем экземпляр при загрузке экстеншена
const menu = new TaskDetailMoreMenu();
console.log('Extension loaded, menu created:', menu);

BX.ready(function () {

	BX.Event.EventEmitter.subscribe('bx.main.popup:onFirstShow', function(event) {
		let menuItems = [];

		BX.ajax.runAction('nick:course.TaskDetailMenuItems.get', {
			data: {}
		}).then(function (response) {
			if (response.status === 'success' && Array.isArray(response.data)) {
				menuItems = response.data;  // если let
				const popup = event.target;

				if (popup.uniquePopupId === 'menu-popup-task-view-b') {
					let menuContainer = popup.contentContainer.querySelector('.menu-popup-items');

					if (menuItems.length) {
						menuItems.forEach(menuText => {
							let newItem = BX.create('span', {
								attrs: {
									title: menuText,
									className: 'menu-popup-item menu-popup-item-create',
								},
								children: [
									BX.create('span', {
										attrs: {className: 'menu-popup-item-icon'}
									}),
									BX.create('span', {
										attrs: {className: 'menu-popup-item-text'},
										text: menuText,
										events: {
											click: () => {
												alert(menuText);
												popup.close();
											}
										}
									})
								]
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

