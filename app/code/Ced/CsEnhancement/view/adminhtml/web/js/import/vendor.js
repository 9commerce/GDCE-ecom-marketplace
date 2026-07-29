/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/lib/validation/validator',
        'jquery/file-uploader',
        'jquery/ui'
    ], function ($, _, validator) {
        'use strict';

        return {
            fileUploaderObject: {
                dataType: 'json',
                url: null,
                autoUpload: true,
                acceptFileTypes: /(\.|\/)(csv)$/i,
                sequentialUploads: true,
                maxFileSize: null,
                formData: {
                    'form_key': window.FORM_KEY
                },
            },
            form: null,
            fileDetails: null,
            urlExportCsv: null,
            urlUpload: null,
            urlDelete: null,
            urlValidate: null,
            urlImport: null,
            urlRedirect: null,
            vendorsData: null,
            registrationAttributes: null,
            headers: null,
            importFile: null,
            uploadOutput: null,
            savingArray: [],

            init: function (Object) {
                this.urlExportCsv = Object.urlExportCsv;
                this.urlUpload = Object.urlUpload;
                this.urlDelete = Object.urlDelete;
                this.urlValidate = Object.urlValidate;
                this.urlImport = Object.urlImport;
                this.urlRedirect = Object.urlRedirect;
                this.vendorsData = Object.vendorsData;
                this.registrationAttributes = Object.registrationAttributes;
                this.headers = this.registrationAttributes.headers;
                this.importFile = $('#import_csv_file');
                this.importFile.attr("accept", '.csv');
                this.uploadOutput = $("#upload_output");
                this.setFileUploaderObject(Object, this);
                this.importFile.fileupload(this.fileUploaderObject);
                this.form = $('#edit_form');
                $("input[name=required_attribute]").val(this.registrationAttributes.required);
                $("input[name=unique_attribute]").val(this.registrationAttributes.unique);
            },

            setFileUploaderObject: function(Object, self) {
                let formBody = $("body");

                self.fileUploaderObject.url = Object.urlUpload;
                self.fileUploaderObject.maxFileSize = Object.maxFileSize;

                self.fileUploaderObject.add = function (e, data) {
                    self.resetData();
                    formBody.loader("hide");

                    if (_.isObject(self.fileDetails)) {
                        self.deleteFile();
                    }

                    formBody.loader("show");
                    $(e.target).fileupload('process', data).done(function () {
                        data.submit();
                    });
                };

                self.fileUploaderObject.done = function (e, data) {
                    formBody.loader("hide");

                    if (data.result && !data.result.hasOwnProperty('errorcode')) {
                        self.fileDetails = data;

                        $("#upload_button").show();
                        self.appendMessage(
                            'success',
                            data.result.message + 'Please click "Check Data" to validate the file.'
                        );
                    } else {
                        $("#upload_button").hide();
                        self.appendMessage(
                            'error',
                            data.result.error
                        );
                    }
                }

            },

            resetData: function () {
                $("#import").hide();
            },

            deleteFile: function () {
                let path = '';
                let object = $.Deferred();
                if (_.isObject(this.fileDetails)) {
                    path = this.fileDetails.result.file_path;
                }

                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: this.urlDelete,
                    data: {path: path},
                    success: function (resp) {
                        object.resolve(resp);
                        return true;
                    }
                });

                return object.promise();
            },

            appendMessage: function (element_class, msg, clear = true) {
                if (clear) this.uploadOutput.empty();

                this.uploadOutput.append(
                    $('<div>', {
                        class: this.getMessageClass(element_class),
                        html: msg
                    })
                );
            },

            getMessageClass: function (type = 'success') {
                let result = 'message';

                switch (type) {
                    case 'success':
                        result = 'message message-success success';
                        break;

                    case 'error':
                        result = 'message message-error error';
                        break;

                    case 'notice':
                        result = 'message message-warning warning';
                        break;
                }

                return result;
            },

            export: function () {
                location.href = this.urlExportCsv;
            },

            validate: function () {
                let self = this;
                $.when(this.readFile()).then(function (data) {
                    let result = $.parseJSON(data);
                    let flag = false;
                    self.savingArray = [];

                    if (result.length > 1) {
                        $.each(result, function (index, row) {
                            let savingData = false;
                            if (index === 0)
                                flag = self.validateHeaders(row);
                            else {
                                if (flag) {
                                    savingData = self.validateColumns(row, index);
                                    if (savingData && savingData !== 'undefined') {
                                        self.savingArray.push(savingData);
                                        self.appendToForm(savingData, index);
                                    }
                                }
                            }
                        });

                        let saveLength = self.savingArray.length;
                        if (saveLength > 0) {
                            $("#import").show();
                            $("#upload_button").hide();

                            self.appendMessage(
                                'success',
                                saveLength + " record(s) will be imported from total " + (result.length - 1) + " records. Please click 'Import' to import data.",
                                false
                            );
                        }
                    } else {
                        self.appendMessage(
                            'error',
                            "File is empty"
                        );
                    }
                });

            },

            appendToForm: function (savingData, rowNumber) {
                let obj = $.extend({}, savingData);
                let self = this;
                $.each(obj, function (index, value) {
                    self.form.append($('<input>',{
                        type: 'hidden',
                        class: 'import_data',
                        name: 'import_data['+ rowNumber +']['+ index +']',
                        val: value
                    }));
                });
            },

            import: function () {
                let self = this;
                let ajaxData = new FormData();
                ajaxData.append('form_key', window.FORM_KEY);
                $('.import_data').each(function(index, object) {
                    ajaxData.append($(object).attr('name'), $(object).val());
                });

                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: self.urlImport,
                    data: ajaxData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        //location.reload();
                        location.href = self.urlRedirect;
                    }
                });
            },

            readFile: function () {
                let path = '';
                let object = $.Deferred();
                this.uploadOutput.empty();

                if (_.isObject(this.fileDetails)) {
                    path = this.fileDetails.result.file_path;
                }

                $.ajax({
                    type: "POST",
                    showLoader: true,
                    url: this.urlValidate,
                    data: {path: path},
                    success: function (resp) {
                        object.resolve(resp);
                        return true;
                    }
                });

                return object.promise();
            },

            validateHeaders: function (row) {
                let header_arr = [];

                if (this.headers.length > 0) {
                    if (this.headers.length === row.length) {
                        let array = _.intersection(row, this.headers);
                        if (array.length === this.headers.length) {
                            $.each(this.headers, function(index, value) {
                                let index_of_header = _.indexOf(row, value);
                                header_arr[index_of_header] = value;
                            });
                            this.headers = header_arr.slice();

                            return true;
                        } else {
                            this.appendMessage(
                                'error',
                                'File Format Validation Failed. Wrong column names. Please recheck the column names'
                            );
                        }
                    } else {
                        this.appendMessage(
                            'error',
                            'File Format Validation Failed. Wrong column names. Please recheck the number of columns'
                        );
                    }
                }

                return false;
            },

            validateColumns: function (row, rowNumber) {
                let toSaveRow = [];
                let error_msg = '';
                let result = false;
                let self = this;
                $.each(row, function(index, value) {
                    let attribute_code = self.headers[index];
                    if ( _.contains(self.registrationAttributes.required, attribute_code) && (typeof value === typeof undefined || $.trim(value) === '')) {
                        error_msg += self.addBold(attribute_code) + ' value is required.' + self.addNewLine();
                    } else {
                        let attribute_classes = self.registrationAttributes.attributes[attribute_code].split(' ');
                        $.each(attribute_classes, function(key, attribute_class) {
                            switch (attribute_class) {
                                case 'required-entry':
                                    if (typeof value === typeof undefined || $.trim(value) === '')
                                        error_msg += self.addBold(attribute_code) + ' value is required.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;
                                case 'validate-email':
                                    result = validator('validate-email', value);
                                    if (!result.passed)
                                        error_msg += self.addBold(attribute_code) + ' value has an invalid email format.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;
                                case 'validate-alpha'  :
                                    result = validator('validate-alpha', value);
                                    if (!result.passed)
                                        error_msg += self.addBold(attribute_code) + ' can have alphabets only.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;
                                case 'validate-alphanum':
                                    result = validator('validate-alphanum', value);
                                    if (!result.passed)
                                        error_msg += self.addBold(attribute_code) + ' can have alpha-numeric value only.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;

                                case 'validate-no-html-tags':
                                    result = validator('validate-no-html-tagsl', value);
                                    if (!result.passed)
                                        error_msg += self.addBold(attribute_code) + ' value has an invalid email format.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;

                                case 'validate-digits'  :
                                case 'validate-number' :
                                    result = validator('validate-number', value);
                                    if (!result.passed)
                                        error_msg += self.addBold(attribute_code) + ' cannot contain any html tags.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;

                                case 'validate-shopurl' :
                                    let re = new RegExp("/[^0-9a-z-]+/gi");
                                    value = $.trim(value);
                                    if (value === '' || value === undefined || re.test(value))
                                        error_msg += self.addBold(attribute_code) + ' is an invalid shop-url.' + self.addNewLine();
                                    else
                                        toSaveRow[attribute_code] = value;
                                    break;

                                default:
                                    toSaveRow[attribute_code] = value;
                            }
                        });
                    }
                });

                error_msg += this.checkDuplicateRow(toSaveRow);
                if (error_msg !== '') {
                    this.appendMessage(
                        'notice',
                        self.addPara(self.addBold('Error on line ' + rowNumber + ': ') + self.addNewLine() + error_msg),
                        false
                    );
                    return false;
                }

                return toSaveRow;
            },

            addNewLine: function () {
                return "<br></br>";
            },

            addBold: function (text) {
                return "<b>" + text + "</b>";
            },

            addPara: function (text) {
                return "<p>" + text + "</p>";
            },

            checkDuplicateRow: function(row) {
                let duplicate_msg = '';
                let uniqueAttributes = this.registrationAttributes.unique;
                let uniqueVendorData = this.vendorsData;
                let self = this;


                $.each(uniqueAttributes, function(key, attribute) {
                    $.each(self.savingArray, function(i, savingRow) {
                        if (row[attribute] !== undefined && row[attribute].toLowerCase() === savingRow[attribute].toLowerCase()) {
                            duplicate_msg += self.duplicateMessage(attribute);
                        }
                    });

                    if (row[attribute] !== undefined && $.inArray(row[attribute].toLowerCase(), uniqueVendorData[attribute]) !== -1) {
                        duplicate_msg += self.duplicateMessage(attribute);
                    }
                });

                return duplicate_msg;
            },

            duplicateMessage: function (attribute) {
                return "Duplicate value found for attribute " + this.addBold(attribute) +this.addNewLine();
            }
        };
    });
