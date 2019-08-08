/* 
 * The MIT License
 *
 * Copyright 2018 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/* globals tinymce */
(function () {
    "use strict";
    
    function initialize()
    {
        // Taken from Tiny's default theme.js - replaced image with tiny.images, added fullscreen
        // https://www.tinymce.com/docs/advanced/editor-control-identifiers/
        const defaultToolbar = "undo redo | styleselect | bold italic underline strikethrough" +
            " | alignleft aligncenter alignright alignjustify | " +
            "bullist numlist | outdent indent | forecolor backcolor | removeformat | link";

        // Image plugin doesn't work
        const tinyOptions = {
            branding: false,
            selector: '#billboard_description',
            plugins: ['link', 'fullscreen', 'paste', 'charmap', 'code', 'insertdatetime', 'textcolor', 'wordcount'],
            toolbar: defaultToolbar,
            menubar: 'edit insert view format table tools',
            convert_urls: false,
            height: window.screen.height * 0.4,
            skin_url: '/iserv/assets/vendor/tinymce/skins/lightgray',
            setup: function (editor) {
                editor.on('change', function (e) {
                    editor.save();
                });
                editor.on('FullscreenStateChanged', function (e) {
                    if (true === e.state) {
                        $('#sidebar-wrapper').hide();
                    } else {
                        $('#sidebar-wrapper').show();
                    }
                });
            }
        };

        // Make sure no message is displayed if exiting the proper way via cancel/save
        $('#billboard_actions_submit').click(function () {
            $(window).off('beforeunload');
        });

        $('#billboard_actions_cancel').click(function () {
            $(window).off('beforeunload');
        });

        tinymce.init(tinyOptions);
    }

    $(document).ready(initialize);
}());