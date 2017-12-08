(function($) {
    /*
     * Attach a click event handler to the sticky post icon link
     */
    $('table.wp-list-table').on('click', 'td.column-sticky_post > a.sticky-posts', function(event) {
        toggle_sticky_post($(this));
        event.preventDefault();
    });

    /*
     * Process a ajax request and set/unset the post sticky
     */
    function toggle_sticky_post(element)
    {
        // Init variables
        var postID = element.data('id'),
            postNonce = element.data('nonce'),
            postHandle = !element.hasClass('active') ? 'sticky' : 'unsticky';

        element.children('span.dashicons').addClass('load');

        // Queue up the sticky ajax request
        $.ajaxQueue({
            type: 'POST',
            dataType: 'json',
            url: stickyPostObject.ajaxUrl,
            data: {
                _ajax_nonce: postNonce,
                action: stickyPostObject.action,
                handle: postHandle,
                post_id: postID
            }
        }).done(function(response) {

            if(response.success) {
                // Toggle link state/icon and set post states to title
                set_sticky_icon(element, response.data.sticky);
                set_sticky_post_states(postID, response.data.states, response.data.available);
            } else {
                console.log(response.data);
            }

            element.children('span.dashicons').removeClass('load');

        }).fail(function(jqXHR, textStatus) {
            console.log(jqXHR.responseText);
            element.children('span.dashicons').removeClass('load');
        });
    }

    /*
     * Set the dashboard star icon filled/empty
     */
    function set_sticky_icon(element, sticky) {
        if(sticky) {
            element.addClass('active');
            element.children('span.dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
        } else {
            element.removeClass('active');
            element.children('span.dashicons').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
        }
    }

    /*
     * Set the post states to the title cell
     */
    function set_sticky_post_states(postID, states, available) {
        var postTitleLink = $('tr#post-'+postID).find('a.row-title');

        // Post title available
        if(postTitleLink.length) {

            // Reset the post link element, to delete the post-states
            postTitleLink.parent().html(postTitleLink);

            if(states !== '') {
                postTitleLink.after(states);

                // Change the post state color to red, if the sticky feature is not available
                if(!available) {
                    postTitleLink.parent().find('span.post-state').css('color', '#a00');
                }
            }
        }
    }
})(jQuery);