define(function (require) {
    'use strict';

    var utils = {},
        _ = require('underscore');

    return _.extend(
        utils,
        require('mage/utils/arrays'),
        require('mage/utils/compare'),
        require('mage/utils/misc'),
        require('mage/utils/objects'),
        require('mage/utils/strings'),
        require('mage/utils/template'),
        // Overrule submit-method, so it includes file uploads:
        {
            /**
             * Serializes and sends data via POST request.
             *
             * @param {Object} options - Options object that consists of
             *      a 'url' and 'data' properties.
             * @param {Object} attrs - Attributes that will be added to virtual form.
             */
            submit: function (options, attrs) {
                var defaultAttributes = {
                    method: 'post',
                    enctype: 'multipart/form-data'
                };

                var form        = document.createElement('form'),
                    data        = this.serialize(options.data),
                    attributes  = _.extend({}, defaultAttributes, attrs || {}),
                    field;

                if (!attributes.action) {
                    attributes.action = options.url;
                }

                data['form_key'] = window.FORM_KEY;

                _.each(attributes, function (value, name) {
                    form.setAttribute(name, value);
                });

                var fileUploadField;

                _.each(data, function (value, name) {
                    field = document.createElement('input');

                    // Check if this is a file upload field:
                    if (fileUploadField = document.querySelector('input[type="file"][name="' + name + '"]')) {
                        // field = fileUploadField.clone();
                        form.appendChild(fileUploadField);
                    } else {
                        field.setAttribute('name', name);
                        field.setAttribute('type', 'hidden');
                        field.value = value;
                        form.appendChild(field);
                    }
                });

                document.body.appendChild(form);

                form.submit();
            }
        }
    );
    
});
