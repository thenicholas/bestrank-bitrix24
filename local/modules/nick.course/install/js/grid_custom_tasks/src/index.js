import {GridCustomTasks} from './grid-custom-tasks';
import {EventEmitter} from 'main.core.events';

const gridCustom = new GridCustomTasks();

BX.ready(function() {
    EventEmitter.subscribe('grid::ready', function(event) {
        const grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
            gridCustom.gridReplacer(grid);
        }
    });

    EventEmitter.subscribe('grid::updated', function(event) {
        const grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
            gridCustom.gridReplacer(grid);
        }
    });
});
