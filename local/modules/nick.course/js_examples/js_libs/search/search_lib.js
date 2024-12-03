BX.ready(function(){

    var container = BX('myDiv');
        console.log(container);
    var arItem = BX.bind(container, {className:'item'}, true);
        console.log(arItem);

    arItem.forEach(function($item, $i){
        BX.bind($item, 'click', function($e){
            alert($i);
        });
    });
});
