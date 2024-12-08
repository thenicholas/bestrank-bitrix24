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
		const popup = event.target;

		if (popup.uniquePopupId === 'menu-popup-task-view-b') {
			let menuItems = popup.contentContainer.querySelector('.menu-popup-items');

			let newItem = BX.create('span', {
				attrs: {
					title: 'Моя подсказка',
					className: 'menu-popup-item menu-popup-item-create',
				},
				children: [
					BX.create('span', {
						attrs: {className: 'menu-popup-item-icon'}
					}),
					BX.create('span', {
						attrs: {className: 'menu-popup-item-text'},
						text: 'Мой пункт меню',
						events: {
							click: () => {
								alert('click');
								popup.close();
							}
						}
					})
				]
			});
			BX.append(newItem, menuItems);
		}
	});
});

