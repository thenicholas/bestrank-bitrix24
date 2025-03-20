import {GridCustom} from './grid-custom';
import {EventEmitter} from 'main.core.events';

const gridCustom = new GridCustom();

BX.ready(function() {
    EventEmitter.subscribe('grid::ready', function(event) {
        const grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
            gridCustom.gridUpdateHandler(grid);
        }
    });

    EventEmitter.subscribe('grid::updated', function(event) {
        const grid = event.getData()[0];
        if (grid instanceof BX.Main.grid) {
            gridCustom.gridUpdateHandler(grid);
        }
    });
});
