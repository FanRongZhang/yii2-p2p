$(function() {
    "use strict";

    //Use diy confirm box
    yii.confirm = function (message, ok, cancel) {
        bootbox.confirm(
            {
                message: message,
                buttons: {
                    confirm: {
                        label: "确认"
                    },
                    cancel: {
                        label: "取消"
                    }
                },
                callback: function (confirmed) {
                    if (confirmed) {
                        !ok || ok();
                    } else {
                        !cancel || cancel();
                    }
                }
            }
        );
        return false;
    }
})