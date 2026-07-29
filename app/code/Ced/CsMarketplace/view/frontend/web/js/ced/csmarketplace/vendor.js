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
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

require(
    [
    'jquery', // jquery Library
    'jquery/ui', // Jquery UI Library
    'jquery/validate', // Jquery Validation Library
    'mage/translate' // Magento text translate (Validation message translte as per language)
    ], function($){

        $.validator.addMethod(
            'validate-shopurl', function (value, element, param) {
                if (this.optional(element)) {
                    return "dependency-mismatch";
                }

                var previous = this.previousValue(element);
                if (!this.settings.messages[element.name]) {
                    this.settings.messages[element.name] = {};
                }
                previous.originalMessage = this.settings.messages[element.name].remote;
                this.settings.messages[element.name].remote = previous.message;

                param = typeof param === "string" && {url: param} || param;

                if (this.pending[element.name]) {
                    return "pending";
                }
                if (previous.old === value) {
                    return previous.valid;
                }

                previous.old = value;
                var validator = this;
                this.startRequest(element);
                var formId = element.form.id;
                var ok = false;
                if(document.getElementById('activity-loading')) { document.getElementById('activity-loading').show(); }
                var data = {};
                data[element.name] = value;
                data['is_vendor'] = 1;
                $.ajax(
                    $.extend(
                        true, {
                            url: param,
                            asynchronous: false,
                            mode: "abort",
                            port: "validate" + element.name,
                            dataType: "json",
                            data: data,
                            success: function(response) {
                                validator.settings.messages[element.name].remote = previous.originalMessage;
                                var valid = response.success;
                                if(document.getElementById('activity-loading')) { document.getElementById('activity-loading').hide(); }

                                validateTrueEmailMsg = response.message;

                                if (response.success == 0) {
                                    if (document.getElementById('advice-validate-shopurl-ced-shop-url-field')) {
                                        document.getElementById('advice-validate-shopurl-ced-shop-url-field').innerHTML = '';
                                    }
                                    if (document.getElementById('advice-validate-shopurl-shop_url')) {
                                        document.getElementById('advice-validate-shopurl-shop_url').innerHTML = '';
                                    }

                                    if(document.getElementById('ced-csmarketplace-availability')) {
                                        if(response.suggestion != '') {
                                            var shopUrlField = 0;
                                            if(document.getElementById('ced-shop-url-field')) {
                                                document.getElementById('ced-csmarketplace-availability-suggestion').innerHTML = response.suggestion +'<input type="checkbox" value="1" name="suggestion_1" onClick="toggleSuggestion(this,document.getElementById(\'ced-shop-url-field\'),\''+response.raw_shop_url+'\',\''+response.shop_url+'\',\''+validator+'\')"></input>';
                                            }
                                            if(document.getElementById('shop_url')) {
                                                document.getElementById('ced-csmarketplace-availability-suggestion').innerHTML = response.suggestion +'<input type="checkbox" value="1" name="suggestion_1" onClick="toggleSuggestion(this,document.getElementById(\'shop_url\'),\''+response.raw_shop_url+'\',\''+response.shop_url+'\',\''+validator+'\')"></input>';
                                            }
                                        } else {
                                            document.getElementById('ced-csmarketplace-availability').className = 'ced-csmarketplace-availability-failed';
                                        }
                                    }
                                    /* Validation.get('validate-shopurl').error = validateTrueEmailMsg; */

                                    var errors = {};
                                    var message = response || validator.defaultMessage(element, "remote");
                                    errors[element.name] = previous.message = $.isFunction(validateTrueEmailMsg) ? validateTrueEmailMsg(value) : validateTrueEmailMsg;
                                    validator.invalid[element.name] = true;
                                    validator.showErrors(errors);

                                    ok = false;
                                } else {
                                    if(document.getElementById('ced-shop-url-field')) {
                                        document.getElementById('ced-shop-url-field').value = response.shop_url;
                                    }
                                    if(document.getElementById('shop_url')) {
                                        document.getElementById('shop_url').value = response.shop_url;
                                    }
                                    if(document.getElementById('ced-csmarketplace-availability')) {
                                        document.getElementById('ced-csmarketplace-availability').className = 'ced-csmarketplace-availability-passed';
                                    }
                                    if (document.getElementById('ced-csmarketplace-availability-suggestion')) {
                                        document.getElementById('ced-csmarketplace-availability-suggestion').innerHTML = response.suggestion;
                                    }
                                    if (document.getElementById('advice-validate-shopurl-ced-shop-url-field')) {
                                        document.getElementById('advice-validate-shopurl-ced-shop-url-field').innerHTML = '';
                                    }
                                    if (document.getElementById('advice-validate-shopurl-shop_url')) {
                                        document.getElementById('advice-validate-shopurl-shop_url').innerHTML = '';
                                    }

                                    var submitted = validator.formSubmitted;
                                    validator.prepareElement(element);
                                    validator.formSubmitted = submitted;
                                    validator.successList.push(element);
                                    delete validator.invalid[element.name];
                                    validator.showErrors();
                                    ok = true;
                                }
                                previous.valid = valid;
                                validator.stopRequest(element, valid);
                            }
                        }, param
                    )
                );
                return "pending";
            }, '&nbsp;'
        );

    }
);

function toggleSuggestion(element,inputElement,raw_shop_url,shop_url,validator) {
    csAdviseDivId = document.getElementById('advice-validate-shopurl-'+inputElement.id);
    if(element.checked) {
        inputElement.value = shop_url;
        if(csAdviseDivId) { csAdviseDivId.style.display = 'none'; }
        inputElement2 = document.getElementById('ced-csmarketplace-availability');
        inputElement2.className = inputElement2.className.replace('-failed','-passed');
        if(document.getElementById('ced-csmarketplace-availability-suggestion')) {
            document.getElementById('ced-csmarketplace-availability-suggestion').innerHTML = '';
        }
        if(document.getElementById('ced-shop-url-field-error')) {
            elem = document.getElementById('ced-shop-url-field-error');
            if(elem) { elem.parentNode.removeChild(elem); }
            // document.getElementById('ced-shop-url-field-error').innerHTML = '';
        }
        if (document.getElementById('advice-validate-shopurl-ced-shop-url-field')) {
            document.getElementById('advice-validate-shopurl-ced-shop-url-field').innerHTML = '';
        }
        if (document.getElementById('advice-validate-shopurl-shop_url')) {
            document.getElementById('advice-validate-shopurl-shop_url').innerHTML = '';
        }
        // ced_csmarketplace.checkUrlAvailability(validator,inputElement);
    } else {
        inputElement.value = raw_shop_url;
        if(csAdviseDivId) { csAdviseDivId.style.display = 'block'; }
        inputElement2 = document.getElementById('ced-csmarketplace-availability');
        inputElement2.className = inputElement2.className.replace('-passed','-failed');
    }
}


if (typeof imagePreview != 'function') {
    function imagePreview(element){
        if($(element)) {
            var win = window.open('', 'preview', 'width=400,height=400,resizable=1,scrollbars=1');
            win.document.open();
            win.document.write('<body style="padding:0;margin:0"><img src="'+$(element).src+'" id="image_preview"></img></body>');
            win.document.close();
            Event.observe(
                win, 'load', function(){
                    var img = win.document.getElementById('image_preview');
                    win.resizeTo(img.width+40, img.height+80)
                }
            );
        }
    }
}

function addGroup() {
    var group = document.getElementById('group_select');
    var group_name = prompt("Please enter a new group name","");
    group_name = group_name.strip();

    if(group && group_name.length > 0) {
        group.options[group.options.length] = new Option(group_name, group_name, true, true);
    }
}
