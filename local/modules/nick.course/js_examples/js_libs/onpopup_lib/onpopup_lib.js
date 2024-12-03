BX.ready(
    function () {
        console.log('библиотека подключена');
        BX.addCustomEvent('onPopupFirstShow', function(obj){
            if (obj.uniquePopupId=='menu-popup-user-menu')
            {
                BX.util.array_merge()
                console.log(obj);
            }
        });
    }
)
