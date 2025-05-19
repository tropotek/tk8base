/**
 * Init all application specific scripts here
 */

jQuery(function ($) {
    // Init page javascript functions
    tkbase.initDialogConfirm();
    tkbase.initTkInputLock();
    tkbase.initDataToggle();
    tkbase.initTinymce();
    tkbase.initTkFormTabs();
    tkbase.initDatepicker();
    tkbase.initPasswordToggle();
    tkbase.initHtmxConfirmDialog();

    // app.initHtmxToasts();
    app.initNotifications();
});

let app = function () {
    "use strict";

    // let initHtmxToasts = function () {
    //   // Enable HTMX logging in the console
    //   //htmx.logAll();
    //   // Trigger on finished request loads (ie: after a form submits)
    //   $(document).on('htmx:afterSettle', '.toastPanel', function () {
    //     $('.toast', this).toast('show');
    //   });
    // };

    let initNotifications = function () {
        if (typeof Notification === 'undefined') return;

        if (Notification.permission !== 'granted') {
            let promise = Notification.requestPermission();
            promise.then(function () {
                if (Notification.permission === 'granted') {
                    $(document).trigger('notify:reload');
                }
            });
        }
    };


    return {
        // initHtmxToasts: initHtmxToasts,
        initNotifications: initNotifications,
    }

}();