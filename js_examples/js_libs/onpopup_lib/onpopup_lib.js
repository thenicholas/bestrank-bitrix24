BX.ready(
    function () {
        BX.addCustomEvent('onPopupFirstShow', function(obj){
            if (obj.uniquePopupId=='menu-popup-user-menu')
            {
                BX.util.array_merge()
                console.log(obj);
            }
        });
    }
)