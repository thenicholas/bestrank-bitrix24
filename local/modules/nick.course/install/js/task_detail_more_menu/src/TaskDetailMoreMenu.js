import {Type} from 'main.core';

export class TaskDetailMoreMenu {
    data = {};

    constructor(options = {name: 'TaskDetailMoreMenu'}) {
        this.name = options.name;
    }

    setName(name) {
        if (Type.isString(name)) {
            this.name = name;
        }
    }

    getName() {
        return this.name;
    }
}


