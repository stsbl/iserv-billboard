/* 
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
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

IServ.Billboard = {};

IServ.Billboard.Editor = IServ.register(function(IServ) {
    
    function initialize(scope)
    {
        // Taken from @IServNewsBundle/Resources/public/js/news.js with a few adjustments (removed support for inserting images)
        // Prepare powerz
        if (!scope.is('body')) {
            return;
        }

        // Taken from Tiny's default theme.js - removed images, added fullscreen
        var defaultToolbar = "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | " +
            "bullist numlist outdent indent | link | fullscreen";

        var tinyOptions = {
            plugins: [ 'link', 'fullscreen', 'charmap', 'code', 'insertdatetime', 'wordcount' ],
            toolbar: defaultToolbar,
            convert_urls: false,
            setup: function(editor) {
                // Save content to textarea to get around "required" HTML5 validation
                // TODO: Check for a "better" solution?
            	// alse make shure the window does not close if content is modified
                editor.on('change', function(e) {
                	editor.save();
        	        $( window ).on('beforeunload', function() {
      	        	    return _('You will loose unsaved changes if you leave this page now. Are you sure?'); // Unused in browsers like Firefox
        	        });
                });
                // Toggle sidebar on fullscreen switching
                editor.on('FullscreenStateChanged', function(e) {
                    if (true === e.state) {
                        $('#sidebar-wrapper').hide();
                    }
                    else {
                        $('#sidebar-wrapper').show();
                    }
                });
            }
        };

        //Make sure no message is displayed if exiting the proper way via cancel/save
        $( '#billboard_actions_submit').click(function() {
        	$( window ).off('beforeunload');
        });

        $( '#billboard_actions_cancel').click(function() {
        	$( window ).off('beforeunload');
        });

        $('#billboard_description').tinymce(tinyOptions);
    }
    
    // Public API
    return {
        init: initialize
    };
}(IServ));