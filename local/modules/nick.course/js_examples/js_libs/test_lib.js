BX.ready(
    function()
    {
        console.log('Библиотека test_lib загружена')
        var message = BX.message('NEW_MESSAGE');
        alert(message);
    }
);
