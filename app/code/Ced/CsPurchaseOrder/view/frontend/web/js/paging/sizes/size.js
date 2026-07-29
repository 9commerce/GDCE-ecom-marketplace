define([
    'Magento_Ui/js/grid/paging/sizes'
], function (Sizes) {
    'use strict';

    return Sizes.extend({
        defaults: {
            value: 20,
            minSize: 1,
            maxSize: 100,
            statefull: {
                options: false // NOT necessary; debugging only
            },
            options: {
                '20': {
                    value: 20,
                    label: 20
                },
                '30': {
                    value: 30,
                    label: 30
                },
                '50': {
                    value: 50,
                    label: 50
                },
                '100': {
                    value: 100,
                    label: 100
                }
            },
        },
    });
});