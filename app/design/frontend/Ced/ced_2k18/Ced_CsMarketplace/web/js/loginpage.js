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
 * @license      https://cedcommerce.com/license-agreement.txt
 */
require(["jquery", "jquery/ui"],
    function ($) {

        $(document).ready(function () {
            "use strict";
            Manage_top_space();
            Get_scrolling_header();
            $(window).on('scroll', function () {
                Get_scrolling_header();
                scrolltop();
            });
            setInterval(function () {
                Get_scrolling_header();
            }, 2000);

            Mobile_login_form();
            Social_Login_poipup();
            Social_Login_poipup_mobile();
            backtotop();
        });

        /** Common variable **/
        var header_height = $('#head').outerHeight();
        var current_scroll_position = $(window).scrollTop();

        /**  Function to manage top space **/
        function Manage_top_space() {
            if ($('#head').length > 0) {
                var header_height1 = $('#head').height();
                var oheader_height1 = $('#head').outerHeight();
                $('#maincontent').css('padding-top', oheader_height1);
            }
        }

        /** Get page scroll direction **/
        function Get_scrolling_header() {
            var running_scroll_position = $(window).scrollTop();
            if (current_scroll_position > running_scroll_position || running_scroll_position > current_scroll_position) {
                if (running_scroll_position > current_scroll_position) {
                    $('#head').css('top', '-' + header_height + 'px');
                } else {
                    $('#head').css('top', '0');
                }
                current_scroll_position = running_scroll_position;
            } else {
                $('#head').css('top', '0');
            }
        }

        // Login popup if social login not enable
        function Mobile_login_form() {
            $('.mobile_sign .mobile_login_link').on('click', function (e) {
                $(this).parents('.mobile_sign').siblings('.form-login-wrapper-main').addClass('active');
                $('.overlay_vi').addClass('active');
                $('body').addClass('hidden_body');
            });
            $('.overlay_vi').on('click', function () {
                $('.form-login-wrapper-main').removeClass('active');
                $(this).removeClass('active');
                $('body').removeClass('hidden_body');
            });
        }

        // Login popup if social login is enable for desktop
        function Social_Login_poipup() {
            $('.ced_social_login_header .nav-list-items a.socail_login_link').on('click', function () {
                $('.overlay-popup.social_login').addClass('active');
            });
            $('.overlay-popup.social_login .close-button-popup').on('click', function () {
                $('.overlay-popup.social_login').removeClass('active');
            });
        }

        /** Add smooth scrolling to all links **/
        $("a").on('click', function (event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function () {
                    window.location.hash = hash;
                });
            }
        });


        /** Login popup if social login is enable for mobile **/
        function Social_Login_poipup_mobile() {
            if ($('.ced_social_login_header').length > 0) {
                if ($(window).width() < 768) {
                    $('.overlay-popup.social_login #login-form').appendTo('.form-login-wrapper-after');
                }
            }
        }

        $('#msg-popup-wrapper, .csmarketplace-account-login').on('click', '#close-msg-pop', function () {
            $('#error-msg-modal').hide();
        });

        // scroll top
        function scrolltop() {
            var scrolltop = $(window).scrollTop();
            if (scrolltop > 50) {
                $('.scroll-icon').fadeIn();
            } else {
                $('.scroll-icon').fadeOut();
            }
        }

        function backtotop() {
            $('.scroll-icon').on('click', function (e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, '300');
            });
        }

        /*scroll to top*/
        $(window).scroll(function () {
            var height = $(window).scrollTop();
            if (height > 100) {
                $('.scroll-icon').fadeIn();
            } else {
                $('.scroll-icon').fadeOut();
            }
        });
        $(document).ready(function () {
            $(".scroll-icon").click(function (event) {
                event.preventDefault();
                $("html, body").animate({scrollTop: 0}, "slow");
                return false;
            });

        });

    });




